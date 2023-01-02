<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

use Zencart\Plugins\Catalog\InstantSearch\InstantSearch;

class zcAjaxInstantSearchPage extends InstantSearch
{
    /**
     * Array of allowed search fields (keys) for building the sql sequence by calling the
     * corresponding sql build method(s) with parameters (values).
     *
     * @var array
     */
    protected const VALID_SEARCH_FIELDS = [
        'model-exact' => [
            ['buildSqlProductModel', [true]],
        ],
        'name' => [
            ['buildSqlProductName', [true]],
            ['buildSqlProductNameDescriptionMatch', [false, INSTANT_SEARCH_PAGE_USE_QUERY_EXPANSION === 'true']],
            ['buildSqlProductName', [false]],
        ],
        'name-description' => [
            ['buildSqlProductName', [true]],
            ['buildSqlProductNameDescriptionMatch', [true, INSTANT_SEARCH_PAGE_USE_QUERY_EXPANSION === 'true']],
            ['buildSqlProductName', [false]],
        ],
        'model-broad' => [
            ['buildSqlProductModel', [false]],
        ],
    ];

    /**
     * The current result page.
     *
     * @var int
     */
    protected int $resultPage;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->resultPage = 1;

        parent::__construct();
    }

    /**
     * AJAX-callable method that performs the search on $_POST['keyword'] and returns the results in HTML format.
     *
     * @return string HTML-formatted results
     */
    public function instantSearch(): string
    {
        if (!isset($_POST['keyword'])) {
            return '';
        }

        if (!empty($_POST['resultPage']) && (int)$_POST['resultPage'] > 0) {
            $this->resultPage = (int)$_POST['resultPage'];
        }

        if (isset($_POST['alpha_filter_id']) && (int)$_POST['alpha_filter_id'] > 0) {
            $this->alphaFilterId = (int)$_POST['alpha_filter_id'];
        }

        return $this->performSearch($_POST['keyword']);
    }

    /**
     * Returns the exploded fields list setting and the error message to show in case of error while
     * parsing the list.
     *
     * @return array First element: search fields array; second element: error message
     */
    protected function loadSearchFieldsConfiguration(): array
    {
        $searchFields = explode(',', preg_replace('/,$/', '', str_replace(' ', '', INSTANT_SEARCH_PAGE_FIELDS_LIST))); // Remove spaces and extra comma at the end
        $errorMessage = sprintf(TEXT_INSTANT_SEARCH_CONFIGURATION_ERROR, 'INSTANT_SEARCH_PAGE_FIELDS_LIST');

        return [$searchFields, $errorMessage];
    }

    /**
     * Sanitizes the input query, runs the search and formats the results.
     *
     * @param string $inputQuery The search query
     * @return string HTML-formatted results
     */
    protected function performSearch(string $inputQuery): string
    {
        $this->searchQuery = html_entity_decode(strtolower(strip_tags(trim($inputQuery))), ENT_NOQUOTES, 'utf-8');

        if ($this->searchQuery !== '') {
            return parent::performSearch($inputQuery);
        }

        return '';
    }

    /**
     * Returns the search results formatted with the template.
     *
     * @return string HTML output with the formatted results.
     */
    protected function formatResults(): string
    {
        global $zco_notifier, $current_page_base, $cPath, $request_type, $template;

        if (empty($this->results)) {
            return '';
        }

        $_GET['main_page']       = FILENAME_INSTANT_SEARCH_RESULT;
        $_GET['act']             = '';
        $_GET['method']          = '';
        $_GET['keyword']         = $_POST['keyword'];
        $_GET['page']            = $_POST['resultPage'];
        $_GET['alpha_filter_id'] = $_POST['alpha_filter_id'];
        // TODO: sort $_GET

        $define_list = [
            'PRODUCT_LIST_MODEL'        => PRODUCT_LIST_MODEL,
            'PRODUCT_LIST_NAME'         => PRODUCT_LIST_NAME,
            'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
            'PRODUCT_LIST_PRICE'        => PRODUCT_LIST_PRICE,
            'PRODUCT_LIST_QUANTITY'     => PRODUCT_LIST_QUANTITY,
            'PRODUCT_LIST_WEIGHT'       => PRODUCT_LIST_WEIGHT,
            'PRODUCT_LIST_IMAGE'        => PRODUCT_LIST_IMAGE
        ];
        asort($define_list);
        $column_list = [];
        foreach ($define_list as $column => $value) {
            if ($value) {
                $column_list[] = $column;
            }
        }

        // TODO: sorting
        /*
        // set the default sort order setting from the Admin when not defined by customer
        if (!isset($_GET['sort']) and PRODUCT_LISTING_DEFAULT_SORT_ORDER != '') {
            $_GET['sort'] = PRODUCT_LISTING_DEFAULT_SORT_ORDER;
        }
        if ((!isset($_GET['sort'])) || (!preg_match('/[1-8][ad]/', $_GET['sort'])) || (substr($_GET['sort'], 0, 1) > count($column_list))) {
            for ($col = 0, $n = sizeof($column_list); $col < $n; $col++) {
                if ($column_list[$col] == 'PRODUCT_LIST_NAME') {
                    $_GET['sort'] = $col + 1 . 'a';
                    $order_str .= ' ORDER BY pd.products_name';
                    break;
                } else {
                    // sort by products_sort_order when PRODUCT_LISTING_DEFAULT_SORT_ORDER ia left blank
                    // for reverse, descending order use:
                    //       $listing_sql .= " order by p.products_sort_order desc, pd.products_name";
                    $order_str .= " order by p.products_sort_order, pd.products_name";
                    break;
                }
            }
            // if set to nothing use products_sort_order and PRODUCTS_LIST_NAME is off
            if (PRODUCT_LISTING_DEFAULT_SORT_ORDER == '') {
                $_GET['sort'] = '20a';
            }
        } else {
            $sort_col = substr($_GET['sort'], 0, 1);
            $sort_order = substr($_GET['sort'], -1);
            $order_str = ' order by ';
            switch ($column_list[$sort_col - 1]) {
                case 'PRODUCT_LIST_MODEL':
                    $order_str .= "p.products_model " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
                    break;
                case 'PRODUCT_LIST_NAME':
                    $order_str .= "pd.products_name " . ($sort_order == 'd' ? "desc" : "");
                    break;
                case 'PRODUCT_LIST_MANUFACTURER':
                    $order_str .= "m.manufacturers_name " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
                    break;
                case 'PRODUCT_LIST_QUANTITY':
                    $order_str .= "p.products_quantity " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
                    break;
                case 'PRODUCT_LIST_IMAGE':
                    $order_str .= "pd.products_name";
                    break;
                case 'PRODUCT_LIST_WEIGHT':
                    $order_str .= "p.products_weight " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
                    break;
                case 'PRODUCT_LIST_PRICE':
                    //        $order_str .= "final_price " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
                    $order_str .= "p.products_price_sorter " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
                    break;
            }
        }*/

        // Begin of variables used by the product_listing module and the listing template
        $listing_split = (object)[
            'number_of_rows' => count($this->results)
        ];
        $listing = $this->results;
        // End of variables used by the product_listing module and the listing template

        ob_start();
        include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCT_LISTING_INSTANT_SEARCH));
        require $template->get_template_dir('tpl_ajax_instant_search_results_listing.php', DIR_WS_TEMPLATE, FILENAME_DEFAULT, 'templates') . '/tpl_ajax_instant_search_results_listing.php';
        return ob_get_clean();
    }

    /**
     * Calculate the sql LIMIT value based on the max number of results allowed and the
     * number of results found so far.
     *
     * @return int LIMIT value
     */
    protected function calcResultsLimit(): int
    {
        return ((int)INSTANT_SEARCH_PAGE_MAX_RESULTS_PER_PAGE * $this->resultPage) - count($this->results);
    }
}

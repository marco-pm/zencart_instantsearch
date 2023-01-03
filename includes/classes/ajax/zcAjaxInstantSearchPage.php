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
     * Maximum number of results displayed globally (not per "ajax page") in the page.
     *
     * @var int
     */
    protected const INSTANT_SEARCH_PAGE_MAX_RESULTS_SCREEN = 500;

    /**
     * Association between displayed fields and their column position in the listing.
     *
     * @var array
     */
    protected const DEFINE_LIST = [
        'PRODUCT_LIST_MODEL'        => PRODUCT_LIST_MODEL,
        'PRODUCT_LIST_NAME'         => PRODUCT_LIST_NAME,
        'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
        'PRODUCT_LIST_PRICE'        => PRODUCT_LIST_PRICE,
        'PRODUCT_LIST_QUANTITY'     => PRODUCT_LIST_QUANTITY,
        'PRODUCT_LIST_WEIGHT'       => PRODUCT_LIST_WEIGHT,
        'PRODUCT_LIST_IMAGE'        => PRODUCT_LIST_IMAGE
    ];

    /**
     * Association between displayed fields and their database field names.
     *
     * @var array
     */
    protected const DEFINE_DB_FIELDS = [
        'PRODUCT_LIST_MODEL'        => 'products_model',
        'PRODUCT_LIST_NAME'         => 'products_name',
        'PRODUCT_LIST_MANUFACTURER' => 'manufacturers_name',
        'PRODUCT_LIST_PRICE'        => 'products_price_sorter',
        'PRODUCT_LIST_QUANTITY'     => 'products_quantity',
        'PRODUCT_LIST_WEIGHT'       => 'products_weight',
        'PRODUCT_LIST_IMAGE'        => 'products_name'
    ];

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
        $this->addToSearchLog = INSTANT_SEARCH_PAGE_ADD_LOG_ENTRY === 'true';
        $this->searchLogPrefix = TEXT_SEARCH_LOG_ENTRY_PAGE_PREFIX;
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

            if ($this->resultPage !== 1) {
                $this->addToSearchLog = false;
            }
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

        // Begin of variables used by the product_listing module and the listing template
        $_GET['main_page']       = FILENAME_INSTANT_SEARCH_RESULT;
        $_GET['act']             = '';
        $_GET['method']          = '';
        $_GET['keyword']         = $_POST['keyword'];
        $_GET['page']            = $_POST['resultPage'];
        $_GET['alpha_filter_id'] = $_POST['alpha_filter_id'];
        $_GET['sort']            = $_POST['sort'];

        $define_list = self::DEFINE_LIST;
        asort($define_list);
        $column_list = [];
        foreach ($define_list as $column => $value) {
            if ($value) {
                $column_list[] = $column;
            }
        }
        $listing_split = (object)[
            'number_of_rows' => count($this->results)
        ];
        $listing = $this->results;
        // End of variables used by the product_listing module and the listing template


        // Apply custom sort to the results based on $_POST['sort']
        if (!empty($_POST['sort'])
            && $_POST['sort'] !== '20a' // not equal to the default value
            && preg_match('/[1-8][ad]/', $_GET['sort'])
            && $_GET['sort'][0] <= count($column_list)
        ) {
            $sortCol     = $_GET['sort'][0];
            $sortOrder   = substr($_GET['sort'], -1);
            $sortDbField = self::DEFINE_DB_FIELDS[$column_list[$sortCol - 1]];
            usort($this->results, static fn($prod1, $prod2) =>
                $sortOrder === 'd'
                    ? [$prod2[$sortDbField]] <=> [$prod1[$sortDbField]]
                    : [$prod1[$sortDbField]] <=> [$prod2[$sortDbField]]
            );
            $listing = $this->results;
        }


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
        // If a custom sort is applied, set the sql limit to the maximum value (we need to fetch all
        // the products from the database in order to properly sort them, otherwise at every "ajax page" loaded
        // the displayed results would change)
        if (!empty($_POST['sort']) && $_POST['sort'] !== '20a') {
            return self::INSTANT_SEARCH_PAGE_MAX_RESULTS_SCREEN;
        }

       $maxResultsPage = ((int)INSTANT_SEARCH_PAGE_RESULTS_PER_PAGE * $this->resultPage) - count($this->results);

        return min($maxResultsPage, self::INSTANT_SEARCH_PAGE_MAX_RESULTS_SCREEN);
    }
}

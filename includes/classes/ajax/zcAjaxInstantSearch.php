<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.1
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

use Zencart\Plugins\Catalog\InstantSearch\InstantSearch;
use Zencart\Plugins\Catalog\InstantSearch\MySqlInstantSearch;

class zcAjaxInstantSearch extends base
{
    /**
     * The search query.
     *
     * @var string
     */
    protected string $searchQuery;

    /**
     * Array of search results (for testing purposes).
     *
     * @var array
     */
    protected array $results;

    /**
     * The InstantSearch concrete class to use.
     *
     * @var InstantSearch|MySqlInstantSearch
     */
    protected InstantSearch $instantSearch;

    /**
     * Constructor.
     *
     * @param InstantSearch|null $instantSearch
     */
    public function __construct(InstantSearch $instantSearch = null)
    {
        $this->results = [];

        if ($instantSearch !== null) {
            $this->instantSearch = $instantSearch;
        } else {
            if (INSTANT_SEARCH_ENGINE === 'Typesense') {
                // todo
                // todo if typesense is not available, switch to mysql
                // $this->instantSearch = new MySqlInstantSearch(INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true');
            } else {
                $this->instantSearch = new MySqlInstantSearch(INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true');
            }
        }
    }

    /**
     * AJAX-callable method that performs the search on $_POST['keyword'] with scope $_POST['scope'] (dropdown or page)
     * and returns a JSON-encoded array with the results count and the results in HTML format.
     *
     * @return string
     */
    public function instantSearch(): string
    {
        // Initial checks on the $_POST variables
        if (!isset($_POST['keyword']) || !isset($_POST['scope']) || ($_POST['scope'] !== 'dropdown' && $_POST['scope'] !== 'page')) {
            return json_encode(['count' => 0, 'results' => []]);
        }

        $this->searchQuery = trim(html_entity_decode(str_replace('&nbsp;', ' ', strtolower(strip_tags($_POST['keyword']))), ENT_NOQUOTES, 'utf-8'));
        $searchQueryLength = strlen($this->searchQuery);

        // Additional checks on the input query
        if ($this->searchQuery === '' ||
            (
                $_POST['scope'] === 'dropdown' && (
                    $searchQueryLength < INSTANT_SEARCH_DROPDOWN_MIN_WORDSEARCH_LENGTH ||
                    $searchQueryLength > INSTANT_SEARCH_DROPDOWN_MAX_WORDSEARCH_LENGTH
                )
            )
        ) {
            return json_encode(['count' => 0, 'results' => []]);
        }


        // ------
        // Begin of arguments setting for the search function
        // ------

        $searchFields = explode(',', INSTANT_SEARCH_FIELDS_LIST);

        if ($_POST['scope'] === 'dropdown') {
            $limit           = (int)INSTANT_SEARCH_DROPDOWN_MAX_RESULTS;
            $alphaFilterId   = null;
            $addToSearchLog  = INSTANT_SEARCH_DROPDOWN_ADD_LOG_ENTRY === 'true';
            $searchLogPrefix = TEXT_SEARCH_LOG_ENTRY_DROPDOWN_PREFIX;

        } elseif ($_POST['scope'] === 'page') {
            // Category and manufacturer search is not performed in the results page
            $searchFields = array_diff($searchFields, ['category']);
            $searchFields = array_diff($searchFields, ['manufacturer']);

            $resultPage = !empty($_POST['resultPage']) && (int)$_POST['resultPage'] > 0
                ? $_POST['resultPage']
                : 1;

            // If a custom sort is applied, set the sql limit to the maximum value (we need to fetch all
            // the products from the database in order to properly sort them, otherwise at every "ajax page" loaded
            // the displayed results would change)
            $limit = !empty($_POST['sort']) && $_POST['sort'] !== '20a'
                ? (int)INSTANT_SEARCH_PAGE_RESULTS_PER_SCREEN
                : min((int)INSTANT_SEARCH_PAGE_RESULTS_PER_SCREEN, (int)INSTANT_SEARCH_PAGE_RESULTS_PER_PAGE * $resultPage);

            $alphaFilterId = isset($_POST['alpha_filter_id']) && (int)$_POST['alpha_filter_id'] > 0
                ? (int)$_POST['alpha_filter_id']
                : null;

            if ($resultPage !== 1) {
                $addToSearchLog = false; // avoid saving multiple log entries when the user scrolls the results page
            } else {
                $addToSearchLog = INSTANT_SEARCH_PAGE_ADD_LOG_ENTRY === 'true';
            }

            $searchLogPrefix = TEXT_SEARCH_LOG_ENTRY_PAGE_PREFIX;
        }

        // ------
        // End of arguments setting for the search function
        // ------


        // Run the search and get the results
        $results = $this->instantSearch->runSearch(
            $this->searchQuery,
            $searchFields,
            $limit,
            $alphaFilterId,
            $addToSearchLog,
            $searchLogPrefix
        );

        $this->results = $results;

        $this->notify('NOTIFY_INSTANT_SEARCH_BEFORE_FORMAT_RESULTS', $this->searchQuery, $results);

        try {
            return json_encode([
                'count' => count($results),
                'results' => $_POST['scope'] === 'dropdown'
                    ? $this->formatDropdownResults($results)
                    : $this->formatPageResults($results),
            ], JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return json_encode(['count' => 0, 'results' => []]);
        }
    }

    /**
     * Returns the search results formatted with the dropdown template.
     *
     * @param  array  $results
     * @return string HTML output with the formatted results.
     */
    protected function formatDropdownResults(array $results): string
    {
        global $template;

        $dropdownResults = [];

        foreach ($results as $result) {
            if (!empty($result['products_id'])) {
                $id    = $result['products_id'];
                $name  = zen_get_products_name($id);
                $img   = $result['products_image'];
                $model = zen_get_products_model($id);

                $dropdownResult['link']  = zen_href_link(zen_get_info_page($id), 'products_id=' . $id);
                $dropdownResult['model'] = INSTANT_SEARCH_DROPDOWN_DISPLAY_PRODUCT_MODEL === 'true'
                    ? (INSTANT_SEARCH_DROPDOWN_INCLUDE_PRODUCT_MODEL === 'true' ? $this->highlightSearchWords($model) : $model)
                    : '';
                $dropdownResult['price'] = INSTANT_SEARCH_DROPDOWN_DISPLAY_PRODUCT_PRICE === 'true'
                    ? zen_get_products_display_price($id)
                    : '';
            } elseif (!empty($result['categories_id'])) {
                $id    = $result['categories_id'];
                $name  = $result['categories_name'];
                $img   = $result['categories_image'];

                $dropdownResult['link']  = zen_href_link(FILENAME_DEFAULT, 'cPath=' . $id);
                $dropdownResult['count'] = INSTANT_SEARCH_DROPDOWN_DISPLAY_CATEGORIES_COUNT === 'true'
                    ? zen_count_products_in_category($id)
                    : '';
            } elseif (!empty($result['manufacturers_id'])) {
                $id    = $result['manufacturers_id'];
                $name  = $result['manufacturers_name'];
                $img   = $result['manufacturers_image'];

                $dropdownResult['link']  = zen_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $id);
                $dropdownResult['count'] = INSTANT_SEARCH_DROPDOWN_DISPLAY_MANUFACTURERS_COUNT === 'true'
                    ? zen_count_products_for_manufacturer((int)$id)
                    : '';
            } else {
                continue;
            }

            $dropdownResult['id']   = (int)$id;
            $dropdownResult['name'] = $this->highlightSearchWords(strip_tags($name));
            $dropdownResult['img'] = INSTANT_SEARCH_DROPDOWN_DISPLAY_IMAGE === 'true' && $img !== ''
                ? zen_image(DIR_WS_IMAGES . strip_tags($img), strip_tags($img), INSTANT_SEARCH_DROPDOWN_IMAGE_WIDTH, INSTANT_SEARCH_DROPDOWN_IMAGE_HEIGHT)
                : '';

            $this->notify('NOTIFY_INSTANT_SEARCH_DROPDOWN_ADD_DROPDOWN_RESULT', $result, $dropdownResult);

            $dropdownResults[] = $dropdownResult;
        }

        ob_start();
        require $template->get_template_dir('tpl_ajax_instant_search_results_dropdown.php', DIR_WS_TEMPLATE, FILENAME_DEFAULT, 'templates') . '/tpl_ajax_instant_search_results_dropdown.php';
        return ob_get_clean();
    }

    /**
     * Returns the search results formatted with the page template.
     *
     * @param  array  $results
     * @return string HTML output with the formatted results.
     */
    protected function formatPageResults(array $results): string
    {
        global $zco_notifier, $current_page_base, $cPath, $request_type, $template;

        if (empty($results)) {
            return '';
        }

        // ------
        // Begin of constant and variables used by the product_listing module and the listing template
        // ------

        // Association between displayed fields and their column position in the listing.
        define("DEFINE_LIST", [
            'PRODUCT_LIST_MODEL'        => PRODUCT_LIST_MODEL,
            'PRODUCT_LIST_NAME'         => PRODUCT_LIST_NAME,
            'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
            'PRODUCT_LIST_PRICE'        => PRODUCT_LIST_PRICE,
            'PRODUCT_LIST_QUANTITY'     => PRODUCT_LIST_QUANTITY,
            'PRODUCT_LIST_WEIGHT'       => PRODUCT_LIST_WEIGHT,
            'PRODUCT_LIST_IMAGE'        => PRODUCT_LIST_IMAGE
        ]);

        // Association between displayed fields and their database field names.
        define("DEFINE_DB_FIELDS", [
            'PRODUCT_LIST_MODEL'        => 'products_model',
            'PRODUCT_LIST_NAME'         => 'products_name',
            'PRODUCT_LIST_MANUFACTURER' => 'manufacturers_name',
            'PRODUCT_LIST_PRICE'        => 'products_price_sorter',
            'PRODUCT_LIST_QUANTITY'     => 'products_quantity',
            'PRODUCT_LIST_WEIGHT'       => 'products_weight',
            'PRODUCT_LIST_IMAGE'        => 'products_name'
        ]);

        $_GET['main_page']       = FILENAME_INSTANT_SEARCH_RESULT;
        $_GET['act']             = '';
        $_GET['method']          = '';
        $_GET['keyword']         = $_POST['keyword'];
        $_GET['page']            = $_POST['resultPage'];
        $_GET['alpha_filter_id'] = $_POST['alpha_filter_id'];
        $_GET['sort']            = $_POST['sort'];

        $define_list = DEFINE_LIST;
        asort($define_list);
        $column_list = [];
        foreach ($define_list as $column => $value) {
            if ($value) {
                $column_list[] = $column;
            }
        }
        $listing_split = (object)[
            'number_of_rows' => count($results)
        ];
        $listing = $results;

        // ------
        // End of variables used by the product_listing module and the listing template
        // ------


        // Apply custom sort to the results based on $_POST['sort']
        if (!empty($_POST['sort'])
            && $_POST['sort'] !== '20a' // not equal to the default value
            && preg_match('/[1-8][ad]/', $_GET['sort'])
            && $_GET['sort'][0] <= count($column_list)
        ) {
            $sortCol     = $_GET['sort'][0];
            $sortOrder   = substr($_GET['sort'], -1);
            $sortDbField = DEFINE_DB_FIELDS[$column_list[$sortCol - 1]];
            usort($results, static fn($prod1, $prod2) =>
            $sortOrder === 'd'
                ? [$prod2[$sortDbField]] <=> [$prod1[$sortDbField]]
                : [$prod1[$sortDbField]] <=> [$prod2[$sortDbField]]
            );
            $listing = $results;
        }


        ob_start();
        include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCT_LISTING_INSTANT_SEARCH));
        require $template->get_template_dir('tpl_ajax_instant_search_results_listing.php', DIR_WS_TEMPLATE, FILENAME_DEFAULT, 'templates') . '/tpl_ajax_instant_search_results_listing.php';
        return ob_get_clean();
    }

    /**
     * Highlights in bold the tokens/suggestions in the results.
     *
     * @param string $text
     * @return string
     */
    protected function highlightSearchWords(string $text): string
    {
        if (INSTANT_SEARCH_DROPDOWN_HIGHLIGHT_TEXT === 'none') {
            return $text;
        }

        $searchQueryPreg =  str_replace(' ', '|', preg_replace('/\s+/', ' ', preg_quote($this->searchQuery, '&')));

        return preg_replace('/(' . str_replace('/', '\/', $searchQueryPreg) . ')/i', '<span>$1</span>', $text);
    }

    /**
     * For testing purposes.
     *
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }
}

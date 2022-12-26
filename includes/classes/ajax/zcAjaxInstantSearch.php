<?php
/**
 * @package Instant Search Results
 * @copyright Copyright Ayoob G 2009-2011
 * @copyright Portions Copyright 2003-2006 The Zen Cart Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Instant Search 2.2.0
 */

class zcAjaxInstantSearch extends base
{
    /** @var string input query */
    protected string $searchQuery;

    /** @var string input query after preg_replace and preg_quote */
    protected string $searchQueryPreg;

    /** @var array input query as array of tokens */
    protected Array $searchQueryArray;

    /** @var string input query as tokens divided by | */
    protected string $searchQueryRegexp;

    /** @var array search results */
    protected array $results;

    /**
     * zcAjaxInstantSearch constructor.
     */
    public function __construct()
    {
        $this->searchQuery = '';
        $this->results     = [];
    }

    /**
     * AJAX-callable method that performs the search on $_POST['query'] and returns the results.
     *
     * @return string formatted results
     */
    public function instantSearch(): string
    {
        $this->searchQuery = html_entity_decode(strtolower(strip_tags(trim($_POST['query']))), ENT_NOQUOTES, 'utf-8');
        $searchQueryLength = strlen($this->searchQuery);

        if ($this->searchQuery !== '' &&
            $searchQueryLength >= INSTANT_SEARCH_MIN_WORDSEARCH_LENGTH &&
            $searchQueryLength <= INSTANT_SEARCH_MAX_WORDSEARCH_LENGTH
        ) {
            $this->searchQueryPreg   = preg_replace('/\s+/', ' ', preg_quote($this->searchQuery, '&'));
            $this->searchQueryArray  = explode(' ', $this->searchQueryPreg);
            $this->searchQueryRegexp = str_replace(' ', '|', $this->searchQueryPreg);

            // search product models (exact matches)
            if (INSTANT_SEARCH_INCLUDE_PRODUCT_MODEL === 'true') {
                $this->searchDb('product_model_exact');
            }

            // for single-word queries, search product names (begins with)
            if (count($this->searchQueryArray) === 1) {
                $this->searchDb('product_name_begins');
            }

            // search product names and descriptions
            $this->searchDb('product_name_description');

            // search product names (contains)
            $this->searchDb('product_name_contains');

            // search product models (broad matches)
            if (INSTANT_SEARCH_INCLUDE_PRODUCT_MODEL === 'true') {
                $this->searchDb('product_model');
            }

            // search categories
            if (INSTANT_SEARCH_INCLUDE_CATEGORIES === 'true') {
                $this->searchDb('category');
            }

            // search manufacturers
            if (INSTANT_SEARCH_INCLUDE_MANUFACTURERS === 'true') {
                $this->searchDb('manufacturer');
            }

            $this->notify('NOTIFY_INSTANT_SEARCH_BEFORE_FORMAT_RESULTS', $this->results, $this->searchQuery);

            return $this->formatResults();
        }

        return '';
    }

    /**
     * Searches the database and saves the results in $results.
     *
     * @param string $type type of search to perform for the query. Possible values:
     *                     product_name_description, products_name_begins, product_name_contains, product_model_exact,
     *                     product_model, category, manufacturer
     * @return void
     */
    protected function searchDb(string $type): void
    {
        global $db;

        $resultsLimit = (int)INSTANT_SEARCH_MAX_NUMBER_OF_RESULTS - count($this->results);

        // perform the search only if we don't already have enough results to display
        if ($resultsLimit > 0) {
            $foundIds = implode(',', array_column($this->results, 'id')); // exclude products that are already in results

            switch ($type) {
                case 'product_name_description':
                    $sql = "SELECT pd.products_id, pd.products_name, p.products_image,
                            MATCH(pd.products_name) AGAINST(:searchBooleanQuery IN BOOLEAN MODE) AS name_relevance_boolean,
                            MATCH(pd.products_name) AGAINST(:searchQuery WITH QUERY EXPANSION) AS name_relevance_natural, " .
                            (
                                INSTANT_SEARCH_INCLUDE_PRODUCT_DESCRIPTION === 'true'
                                ? "MATCH(pd.products_description) AGAINST(:searchQuery WITH QUERY EXPANSION) AS description_relevance"
                                : ""
                            ) . "
                            FROM " . TABLE_PRODUCTS_DESCRIPTION . " pd
                            JOIN " . TABLE_PRODUCTS . " p ON (p.products_id = pd.products_id)
                            WHERE p.products_status <> 0
                            AND pd.language_id = :languageId " .
                            ($foundIds !== '' ? "AND p.products_id NOT IN (" . $foundIds . ") " : "") . "
                            AND
                                (
                                    (
                                        MATCH(pd.products_name) AGAINST(:searchBooleanQuery IN BOOLEAN MODE)
                                        +
                                        MATCH(pd.products_name) AGAINST(:searchQuery WITH QUERY EXPANSION)
                                    ) > 0 " .
                                (
                                    INSTANT_SEARCH_INCLUDE_PRODUCT_DESCRIPTION === 'true'
                                    ? "OR MATCH(pd.products_description) AGAINST(:searchQuery WITH QUERY EXPANSION) > 0 "
                                    : ""
                                ) . "
                                )
                            ORDER BY name_relevance_boolean DESC, name_relevance_natural DESC, " .
                            (
                                INSTANT_SEARCH_INCLUDE_PRODUCT_DESCRIPTION === 'true'
                                ? "description_relevance DESC, "
                                : ""
                            ) . "
                            p.products_sort_order, pd.products_name
                            LIMIT " . $resultsLimit;
                    break;

                case 'product_name_begins':
                case 'product_name_contains':
                    $sql = "SELECT p.products_id, p.products_image
                            FROM " . TABLE_PRODUCTS . " p
                            JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id)
                            LEFT JOIN " . TABLE_COUNT_PRODUCT_VIEWS . " cpv ON (p.products_id = cpv.product_id AND cpv.language_id = :languageId)
                            WHERE p.products_status <> 0
                            AND pd.products_name " . ($type === 'product_name_begins' ? "LIKE :searchBeginsQuery" : "REGEXP :regexpQuery") . "
                            AND pd.language_id = :languageId " .
                            ($foundIds !== '' ? "AND p.products_id NOT IN (" . $foundIds . ") " : "") . "
                            ORDER BY cpv.views DESC, p.products_sort_order, pd.products_name
                            LIMIT " . $resultsLimit;
                    break;

                case 'product_model_exact':
                case 'product_model':
                    $sql = "SELECT p.products_id, p.products_image
                            FROM " . TABLE_PRODUCTS . " p
                            JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id)
                            WHERE p.products_status <> 0
                            AND p.products_model " . ($type === 'product_model_exact' ? "= :searchQuery" : "REGEXP :regexpQuery") . "
                            AND pd.language_id = :languageId " .
                            ($foundIds !== '' ? "AND p.products_id NOT IN (" . $foundIds . ") " : "") . "
                            ORDER BY p.products_sort_order, pd.products_name
                            LIMIT " . $resultsLimit;
                    break;

                case 'category':
                    $sql = "SELECT c.categories_id, cd.categories_name, c.categories_image
                            FROM " . TABLE_CATEGORIES . " c
                            LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON cd.categories_id = c.categories_id
                            WHERE c.categories_status <> 0
                            AND (cd.categories_name REGEXP :regexpQuery)
                            AND cd.language_id = :languageId
                            ORDER BY c.sort_order, cd.categories_name
                            LIMIT " . $resultsLimit;
                    break;

                case 'manufacturer':
                    $sql = "SELECT DISTINCT m.manufacturers_id, m.manufacturers_name, m.manufacturers_image
                            FROM " . TABLE_PRODUCTS . " p
                            LEFT JOIN " . TABLE_MANUFACTURERS . " m ON m.manufacturers_id = p.manufacturers_id
                            WHERE p.products_status <> 0
                            AND (m.manufacturers_name REGEXP :regexpQuery)
                            ORDER BY m.manufacturers_name
                            LIMIT " . $resultsLimit;
                    break;
            }

            $this->notify('NOTIFY_INSTANT_SEARCH_BEFORE_SQL', $type, $sql, $this->searchQuery);

            // remove all non-word characters and add wildcard operator for boolean mode search
            $searchBooleanQuery = str_replace(' ', '* ', trim(preg_replace('/[^\p{L}\p{N}_]+/u', ' ', $this->searchQuery))) . '*';
            $sql = $db->bindVars($sql, ':searchBooleanQuery', $searchBooleanQuery, 'string');

            $sql = $db->bindVars($sql, ':searchQuery', $this->searchQuery, 'string');
            $sql = $db->bindVars($sql, ':searchBeginsQuery', $this->searchQuery . '%', 'string');
            $sql = $db->bindVars($sql, ':regexpQuery', $this->searchQueryRegexp, 'string');
            $sql = $db->bindVars($sql, ':languageId', $_SESSION['languages_id'], 'integer');

            $sqlResults = $db->Execute($sql);
            foreach ($sqlResults as $sqlResult) {
                switch ($type) {
                    case 'category':
                        $id    = $sqlResult['categories_id'];
                        $name  = $sqlResult['categories_name'];
                        $img   = $sqlResult['categories_image'];
                        $model = '';
                        break;

                    case 'manufacturer':
                        $id    = $sqlResult['manufacturers_id'];
                        $name  = $sqlResult['manufacturers_name'];
                        $img   = $sqlResult['manufacturers_image'];
                        $model = '';
                        break;

                    default:
                        $id    = $sqlResult['products_id'];
                        $name  = zen_get_products_name($id);
                        $img   = $sqlResult['products_image'];
                        $model = zen_get_products_model($id);
                        break;
                }

                $result = [
                    'type'  => $type,
                    'id'    => (int)$id,
                    'name'  => $name,
                    'img'   => $img ?? '',
                    'model' => $model,
                ];

                $this->notify('NOTIFY_INSTANT_SEARCH_BEFORE_ADD_RESULT', $result['type'], $result);

                $this->results[] = $result;
            }
        }
    }

    /**
     * Formats the search results, based on the template.
     *
     * @return string HTML output with the formatted results.
     */
    public function formatResults(): string
    {
        global $template;

        foreach ($this->results as $i => $result) {
            $this->results[$i] = $this->formatResult($result);
        }

        ob_start();
        require $template->get_template_dir('tpl_ajax_instant_search_results.php', DIR_WS_TEMPLATE, FILENAME_DEFAULT, 'templates') . '/tpl_ajax_instant_search_results.php';
        return ob_get_clean();
    }

    /**
     * Formats a search result (adds image, link, etc.).
     *
     * @param array $result the result to be formatted
     * @return array formatted result
     */
    protected function formatResult(array $result): array
    {
        $formattedResult = [
            'name' => $this->highlightSearchWords(strip_tags($result['name'])),
            'img'  => INSTANT_SEARCH_DISPLAY_IMAGE === 'true'
                ? zen_image(DIR_WS_IMAGES . strip_tags($result['img']), strip_tags($result['img']), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT)
                : '',
        ];

        switch ($result['type']) {
            case 'product':
            default:
                $formattedResult['link']  = zen_href_link(zen_get_info_page($result['id']), 'products_id=' . $result['id']);
                $formattedResult['model'] = INSTANT_SEARCH_DISPLAY_PRODUCT_MODEL === 'true'
                    ? (INSTANT_SEARCH_INCLUDE_PRODUCT_MODEL === 'true' ? $this->highlightSearchWords($result['model']) : $result['model'])
                    : '';
                $formattedResult['price'] = INSTANT_SEARCH_DISPLAY_PRODUCT_PRICE === 'true'
                    ? zen_get_products_display_price($result['id'])
                    : '';
                break;

            case 'category':
                $formattedResult['link']  = zen_href_link(FILENAME_DEFAULT, 'cPath=' . $result['id']);
                $formattedResult['count'] = INSTANT_SEARCH_DISPLAY_CATEGORIES_COUNT === 'true'
                    ? zen_count_products_in_category($result['id'])
                    : '';
                break;

            case 'manufacturer':
                $formattedResult['link']  = zen_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $result['id']);
                $formattedResult['count'] = INSTANT_SEARCH_DISPLAY_MANUFACTURERS_COUNT === 'true'
                    ? zen_count_products_for_manufacturer($result['id'])
                    : '';
                break;
        }

        $this->notify('NOTIFY_INSTANT_SEARCH_AFTER_FORMAT_RESULT', $result['type'], $result, $formattedResult);

        return $formattedResult;
    }

    /**
     * Displays the tokens in the input string in bold.
     *
     * @param string $text input string
     * @return string output string
     */
    protected function highlightSearchWords(string $text): string
    {
        return preg_replace('/(' . str_replace('/', '\/', $this->searchQueryRegexp) . ')/i', '<strong>$1</strong>', $text);
    }
}

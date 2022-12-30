<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

namespace classes;
use base;

abstract class InstantSearch extends base
{
    /**
     * The input query.
     *
     * @var string
     */
    protected string $searchQuery;

    /**
     * The input query after preg_replace and preg_quote.
     *
     * @var string
     */
    protected string $searchQueryPreg;

    /**
     * The input query as array of tokens.
     *
     * @var array
     */
    protected array $searchQueryArray;

    /**
     * The input query as string of tokens separated by '|'.
     *
     * @var string
     */
    protected string $searchQueryRegexp;

    /**
     * The search results.
     *
     * @var array
     */
    protected array $results;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->searchQuery = '';
        $this->results = [];
    }

    /**
     * Sanitizes the input query, runs the search and formats the results.
     *
     * @param string $inputQuery The search query
     *
     * @return string HTML-formatted results
     */
    protected function performSearch(string $inputQuery): string
    {
        $this->searchQueryPreg = preg_replace('/\s+/', ' ', preg_quote($this->searchQuery, '&'));
        $this->searchQueryArray = explode(' ', $this->searchQueryPreg);
        $this->searchQueryRegexp = str_replace(' ', '|', $this->searchQueryPreg);

        $this->searchDb();

        $this->notify('NOTIFY_INSTANT_SEARCH_BEFORE_FORMAT_RESULTS', $this->searchQuery, $this->results);

        return $this->formatResults();
    }

    /**
     * Runs the sequence of database queries for the search, until we have enough results.
     *
     * @return void
     */
    protected function searchDb(): void
    {
        $queriesSequence = $this->buildSqlSequence();

        foreach ($queriesSequence as $query) {
            if ($this->calcResultsLimit() <= 0) { // we already have enough results
                return;
            }
            $this->execQuery($query);
        }
    }

    /**
     * Prepares the query, runs it and saves the results in $results.
     *
     * @param string $sql The sql to execute
     *
     * @return void
     */
    protected function execQuery(string $sql): void
    {
        global $db;

        $foundIds = implode(',', array_column($this->results, 'id'));

        // Remove all non-word characters and add wildcard operator for boolean mode search
        $searchBooleanQuery = str_replace(' ', '* ', trim(preg_replace('/[^\p{L}\p{N}_]+/u', ' ', $this->searchQuery))) . '*';

        // Prepares the sql
        $sql = $db->bindVars($sql, ':searchBooleanQuery', $searchBooleanQuery, 'string');
        $sql = $db->bindVars($sql, ':searchQuery', $this->searchQuery, 'string');
        $sql = $db->bindVars($sql, ':searchBeginsQuery', $this->searchQuery . '%', 'string');
        $sql = $db->bindVars($sql, ':regexpQuery', $this->searchQueryRegexp, 'string');
        $sql = $db->bindVars($sql, ':languageId', $_SESSION['languages_id'], 'integer');
        $sql = $db->bindVars($sql, ':foundIds', $foundIds ?? "''", 'string');
        $sql = $db->bindVars($sql, ':resultsLimit', $this->calcResultsLimit(), 'integer');

        $this->notify('NOTIFY_INSTANT_SEARCH_DROPDOWN_SQL', $this->searchQuery, $sql);

        // Run the sql
        $dbResults = $db->Execute($sql);

        // Save the results
        foreach ($dbResults as $dbResult) {
            $this->results[] = $dbResult;
        }
    }

    /**
     * Builds the sql for product name and description Full-Text search.
     *
     * @param bool $includeDescription Match also against product's description
     *
     * @return string Sql
     */
    protected function buildSqlProductNameDescriptionMatch(bool $includeDescription = true): string
    {
        $sql = "SELECT p.products_id, p.products_image, p.products_sort_order, p.manufacturers_id,
                       p.products_price, p.products_tax_class_id, p.products_price_sorter, p.products_quantity,
                       p.products_qty_box_status, p.master_categories_id, p.product_is_call, pd.products_name,
                       MATCH(pd.products_name) AGAINST(:searchBooleanQuery IN BOOLEAN MODE) AS name_relevance_boolean,
                       MATCH(pd.products_name) AGAINST(:searchQuery WITH QUERY EXPANSION) AS name_relevance_natural" .
            ($includeDescription === true ? ", MATCH(pd.products_description) AGAINST(:searchQuery WITH QUERY EXPANSION) AS description_relevance" : "") . "
                FROM " . TABLE_PRODUCTS_DESCRIPTION . " pd
                JOIN " . TABLE_PRODUCTS . " p ON (p.products_id = pd.products_id)
                WHERE p.products_status <> 0
                  AND pd.language_id = :languageId
                  AND p.products_id NOT IN (:foundIds)
                  AND
                      (
                          (
                              MATCH(pd.products_name) AGAINST(:searchBooleanQuery IN BOOLEAN MODE)
                              +
                              MATCH(pd.products_name) AGAINST(:searchQuery WITH QUERY EXPANSION)
                          ) > 0 " .
            ($includeDescription === true ? "OR MATCH(pd.products_description) AGAINST(:searchQuery WITH QUERY EXPANSION) > 0 " : "") . "
                  )
                ORDER BY name_relevance_boolean DESC, name_relevance_natural DESC, " .
            ($includeDescription === true ? "description_relevance DESC, " : "") . "
                         p.products_sort_order, pd.products_name
                LIMIT :resultsLimit";

        return $sql;
    }

    /**
     * Builds the sql for product name LIKE/REGEXP search.
     *
     * @param bool $beginsWith If true search with LIKE, with REGEXP otherwise
     *
     * @return string Sql
     */
    protected function buildSqlProductName(bool $beginsWith = true): string
    {
        $sql = "SELECT p.products_id, p.products_image, p.products_sort_order, p.manufacturers_id,
                       p.products_price, p.products_tax_class_id, p.products_price_sorter, p.products_quantity,
                       p.products_qty_box_status, p.master_categories_id, p.product_is_call, pd.products_name
                FROM " . TABLE_PRODUCTS . " p
                JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id)
                LEFT JOIN " . TABLE_COUNT_PRODUCT_VIEWS . " cpv ON (p.products_id = cpv.product_id AND cpv.language_id = :languageId)
                WHERE p.products_status <> 0
                  AND pd.products_name " . ($beginsWith === true ? "LIKE :searchBeginsQuery" : "REGEXP :regexpQuery") . "
                  AND pd.language_id = :languageId
                  AND p.products_id NOT IN (:foundIds)
                ORDER BY cpv.views DESC, p.products_sort_order, pd.products_name
                LIMIT :resultsLimit";

        return $sql;
    }

    /**
     * Builds the sql for product model LIKE/REGEXP search.
     *
     * @param bool $beginsWith If true search with LIKE, with REGEXP otherwise
     *
     * @return string Sql
     */
    protected function buildSqlProductModel(bool $exactMatch = true): string
    {
        $sql = "SELECT p.products_id, p.products_image, p.products_sort_order, p.manufacturers_id,
                       p.products_price, p.products_tax_class_id, p.products_price_sorter, p.products_quantity,
                       p.products_qty_box_status, p.master_categories_id, p.product_is_call, pd.products_name
                FROM " . TABLE_PRODUCTS . " p
                JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id)
                LEFT JOIN " . TABLE_COUNT_PRODUCT_VIEWS . " cpv ON (p.products_id = cpv.product_id AND cpv.language_id = :languageId)
                WHERE p.products_status <> 0
                  AND pd.products_name " . ($exactMatch === true ? "LIKE :searchBeginsQuery" : "REGEXP :regexpQuery") . "
                  AND pd.language_id = :languageId
                  AND p.products_id NOT IN (:foundIds)
                ORDER BY cpv.views DESC, p.products_sort_order, pd.products_name
                LIMIT :resultsLimit";

        return $sql;
    }

    /**
     * Builds the sql for category search.
     *
     * @return string Sql
     */
    protected function buildSqlCategory(): string
    {
        $sql = "SELECT c.categories_id, cd.categories_name, c.categories_image
                FROM " . TABLE_CATEGORIES . " c
                LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON cd.categories_id = c.categories_id
                WHERE c.categories_status <> 0
                  AND (cd.categories_name REGEXP :regexpQuery)
                  AND cd.language_id = :languageId
                ORDER BY c.sort_order, cd.categories_name
                LIMIT :resultsLimit";

        return $sql;
    }

    /**
     * Builds the sql for manufacturer search.
     *
     * @return string Sql
     */
    protected function buildSqlManufacturer(): string
    {
        $sql = "SELECT DISTINCT m.manufacturers_id, m.manufacturers_name, m.manufacturers_image
                FROM " . TABLE_PRODUCTS . " p
                LEFT JOIN " . TABLE_MANUFACTURERS . " m ON m.manufacturers_id = p.manufacturers_id
                WHERE p.products_status <> 0
                 AND (m.manufacturers_name REGEXP :regexpQuery)
                ORDER BY m.manufacturers_name
                LIMIT :resultsLimit";

        return $sql;
    }

    /**
     * AJAX-callable method that performs the search on $_POST['keyword'] and returns the results in HTML format.
     *
     * @return string HTML-formatted results
     */
    abstract public function instantSearch(): string;

    /**
     * Builds the sequence of database queries for the search.
     *
     * @return void
     */
    abstract protected function buildSqlSequence(): array;

    /**
     * Returns the search results formatted with the template.
     *
     * @return string HTML-formatted results
     */
    abstract protected function formatResults(): string;

    /**
     * Calculates the sql LIMIT value based on the max number of results allowed and the
     * number of results found so far.
     *
     * @return int
     */
    abstract protected function calcResultsLimit(): int;
}

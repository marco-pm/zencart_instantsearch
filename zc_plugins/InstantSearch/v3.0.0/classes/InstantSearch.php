<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

namespace Zencart\Plugins\Catalog\InstantSearch;

use Zencart\Plugins\Catalog\InstantSearch\Exceptions\InstantSearchConfigurationException;

abstract class InstantSearch extends \base
{
    /**
     * Array of allowed search fields (keys) for building the sql sequence by calling the
     * corresponding sql build method with parameters (values).
     *
     * @var array
     */
    protected const VALID_SEARCH_FIELDS = [];

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
     * Optional alpha filter value.
     *
     * @var int
     */
    protected int $alphaFilterId;

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
        $this->alphaFilterId = 0;
        $this->results = [];
    }

    /**
     * Sanitizes the input query, runs the search and formats the results.
     *
     * @param string $inputQuery The search query
     * @return string HTML-formatted results
     */
    protected function performSearch(string $inputQuery): string
    {
        $this->searchQueryPreg = preg_replace('/\s+/', ' ', preg_quote($this->searchQuery, '&'));
        $this->searchQueryArray = explode(' ', $this->searchQueryPreg);
        $this->searchQueryRegexp = str_replace(' ', '|', $this->searchQueryPreg);

        try {
            $this->searchDb();
        } catch (InstantSearchConfigurationException $e) {
            return '<strong>' . $e->getMessage() . '</strong>';
        }

        $this->notify('NOTIFY_INSTANT_SEARCH_BEFORE_FORMAT_RESULTS', $this->searchQuery, $this->results);

        return $this->formatResults();
    }

    /**
     * Runs the sequence of database queries for the search, until we have enough results.
     *
     * @return void
     * @throws InstantSearchConfigurationException
     */
    protected function searchDb(): void
    {
        $sqlSequence = $this->buildSqlSequence();

        foreach ($sqlSequence as $sql) {
            if ($this->calcResultsLimit() <= 0) { // we already have enough results
                return;
            }
            $this->execQuery($sql);
        }
    }

    /**
     * Builds the sequence of database queries for the search.
     *
     * @return array The sql sequence
     * @throws InstantSearchConfigurationException
     */
    protected function buildSqlSequence(): array
    {
        // Load search fields list
        [$searchFields, $errorMessage] = $this->loadSearchFieldsConfiguration();

        // Check that there are no duplicates
        if (count(array_unique($searchFields)) < count($searchFields)) {
            throw new InstantSearchConfigurationException($errorMessage);
        }

        // Check that there is only one value between name and name-description in the list
        if (in_array('name', $searchFields) && in_array('name-description', $searchFields)) {
            throw new InstantSearchConfigurationException($errorMessage);
        }

        $sqlSequence = [];

        foreach ($searchFields as $searchField) {
            // Check that $searchField is a valid field name
            if (!array_key_exists($searchField, static::VALID_SEARCH_FIELDS)) {
                throw new InstantSearchConfigurationException($errorMessage);
            }

            foreach (static::VALID_SEARCH_FIELDS[$searchField] as $searchMethod) {
                $methodName = $searchMethod[0];
                if (!empty($searchMethod[1])) {
                    $methodArguments = $searchMethod[1];
                }
                if (isset($methodArguments)) {
                    $sqlSequence[] = $this->$methodName(...$methodArguments);
                } else {
                    $sqlSequence[] = $this->$methodName();
                }

            }
        }

        return $sqlSequence;
    }

    /**
     * Prepares the query, runs it and saves the results in $results.
     *
     * @param string $sql The sql to execute
     * @return void
     */
    protected function execQuery(string $sql): void
    {
        global $db;

        $foundIds = implode(',', array_column($this->results, 'products_id'));

        // Remove all non-word characters and add wildcard operator for boolean mode search
        $searchBooleanQuery = str_replace(' ', '* ', trim(preg_replace('/[^\p{L}\p{N}_]+/u', ' ', $this->searchQuery))) . '*';

        // Prepares the sql
        $sql = $db->bindVars($sql, ':searchBooleanQuery', $searchBooleanQuery, 'string');
        $sql = $db->bindVars($sql, ':searchQuery', $this->searchQuery, 'string');
        $sql = $db->bindVars($sql, ':searchBeginsQuery', $this->searchQuery . '%', 'string');
        $sql = $db->bindVars($sql, ':regexpQuery', $this->searchQueryRegexp, 'string');
        $sql = $db->bindVars($sql, ':languageId', $_SESSION['languages_id'], 'integer');
        $sql = $db->bindVars($sql, ':foundIds', $foundIds ?? "''", 'inConstructInteger');
        $sql = $db->bindVars($sql, ':alphaFilterId', chr($this->alphaFilterId) . '%', 'string');
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
     * @param bool $withQueryExpansion Use Query Expansion
     * @return string Sql
     */
    protected function buildSqlProductNameDescriptionMatch(bool $includeDescription = true, bool $withQueryExpansion = true): string
    {
        $queryExpansion = $withQueryExpansion === true ? ' WITH QUERY EXPANSION' : '';

        $sql = "SELECT p.*, pd.products_name, m.manufacturers_name,
                       MATCH(pd.products_name) AGAINST(:searchBooleanQuery IN BOOLEAN MODE) AS name_relevance_boolean,
                       MATCH(pd.products_name) AGAINST(:searchQuery" . $queryExpansion . ") AS name_relevance_natural" .
                       ($includeDescription === true ? ", MATCH(pd.products_description) AGAINST(:searchQuery" . $queryExpansion . ") AS description_relevance" : "") . "
                FROM " . TABLE_PRODUCTS_DESCRIPTION . " pd
                JOIN " . TABLE_PRODUCTS . " p ON (p.products_id = pd.products_id)
                LEFT JOIN " . TABLE_MANUFACTURERS . " m ON (m.manufacturers_id = p.manufacturers_id)
                WHERE p.products_status <> 0 " .
                  (($this->alphaFilterId > 0 ) ? "AND pd.products_name LIKE :alphaFilterId " : "") . "
                  AND pd.language_id = :languageId
                  AND p.products_id NOT IN (:foundIds)
                  AND
                      (
                          (
                              MATCH(pd.products_name) AGAINST(:searchBooleanQuery IN BOOLEAN MODE)
                              +
                              MATCH(pd.products_name) AGAINST(:searchQuery" . $queryExpansion . ")
                          ) > 0 " .
                          ($includeDescription === true ? "OR MATCH(pd.products_description) AGAINST(:searchQuery" . $queryExpansion . ") > 0 " : "") . "
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
     * @return string Sql
     */
    protected function buildSqlProductName(bool $beginsWith = true): string
    {
        $sql = "SELECT p.*, pd.products_name, m.manufacturers_name
                FROM " . TABLE_PRODUCTS . " p
                JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id)
                LEFT JOIN " . TABLE_MANUFACTURERS . " m ON (m.manufacturers_id = p.manufacturers_id)
                LEFT JOIN " . TABLE_COUNT_PRODUCT_VIEWS . " cpv ON (p.products_id = cpv.product_id AND cpv.language_id = :languageId)
                WHERE p.products_status <> 0 " .
                  (($this->alphaFilterId > 0 ) ? "AND pd.products_name LIKE :alphaFilterId " : "") . "
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
     * @param bool $exactMatch If true exact match, broad match otherwise
     * @return string Sql
     */
    protected function buildSqlProductModel(bool $exactMatch = true): string
    {
        $sql = "SELECT p.*, pd.products_name, m.manufacturers_name
                FROM " . TABLE_PRODUCTS . " p
                JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id)
                LEFT JOIN " . TABLE_MANUFACTURERS . " m ON (m.manufacturers_id = p.manufacturers_id)
                LEFT JOIN " . TABLE_COUNT_PRODUCT_VIEWS . " cpv ON (p.products_id = cpv.product_id AND cpv.language_id = :languageId)
                WHERE p.products_status <> 0 " .
                  (($this->alphaFilterId > 0 ) ? "AND pd.products_name LIKE :alphaFilterId " : "") . "
                  AND p.products_model " . ($exactMatch === true ? "= :searchQuery" : "REGEXP :regexpQuery") . "
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
     * Returns the exploded fields list setting and the error message to show in case of error while
     * parsing the list.
     *
     * @return array First element: search fields array; second element: error message
     */
    abstract protected function loadSearchFieldsConfiguration(): array;

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
     * @return int LIMIT value
     */
    abstract protected function calcResultsLimit(): int;
}

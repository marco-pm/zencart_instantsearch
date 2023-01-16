<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.1
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

namespace Zencart\Plugins\Catalog\InstantSearch\SearchEngineProviders;

class MySqlSearchEngineProvider extends \base implements SearchEngineProviderInterface
{
    /**
     * Array of search fields (keys) with the corresponding sql build method (values).
     *
     * @var array
     */
    protected const FIELDS_TO_BUILD_METHODS = [
        'category'         => ['buildSqlCategory'],
        'manufacturer'     => ['buildSqlManufacturer'],
        'meta-keywords'    => ['buildSqlProductMetaKeywords'],
        'model-broad'      => ['buildSqlProductModelBroad'],
        'model-exact'      => ['buildSqlProductModelExact'],
        'name'             => ['buildSqlProductNameBegins',
                               'buildSqlProductNameWithoutDescription',
                               'buildSqlProductNameContains'],
        'name-description' => ['buildSqlProductNameBegins',
                               'buildSqlProductNameWithDescription',
                               'buildSqlProductNameContains'],
    ];

    /**
     * Use Query Expansion in the Full-Text searches.
     *
     * @var bool
     */
    protected bool $useQueryExpansion;

    /**
     * Alphabetical filter id.
     *
     * @var null|int
     */
    protected ?int $alphaFilter;

    /**
     * Array of search results.
     *
     * @var array
     */
    protected array $results;

    /**
     * Constructor.
     *
     * @param bool $useQueryExpansion
     */
    public function __construct(bool $useQueryExpansion = true)
    {
        $this->useQueryExpansion = $useQueryExpansion;
        $this->alphaFilter = null;
        $this->results = [];
    }

    /**
     * Search for $queryText and return the results.
     *
     * @param string $queryText the string to search
     * @param array $fieldsList
     * @param int $limit maximum number of results to return
     * @param int|null $alphaFilter
     * @return array
     */
    public function search(string $queryText, array $fieldsList, int $limit, int $alphaFilter = null): array
    {
        $this->alphaFilter = $alphaFilter ?? 0;

        $sqlSequence = $this->buildSqlSequence($fieldsList);

        // Run the sequence of database queries for the search, until we have enough results
        foreach ($sqlSequence as $sql) {
            if (count($this->results) >= $limit) {
                break;
            }
            $result = $this->execQuery($sql, $queryText, $limit - count($this->results));
            if (!empty($result)) {
                array_push($this->results, ...$result);
            }
        }

        return $this->results;
    }

    /**
     * Builds the sequence of database queries for the search.
     * Note: validation of $fieldsList is made by the InstantSearchConfigurationValidation class, therefore
     * we don't manage Exceptions while reading the list here.
     *
     * @param array $fieldsList
     * @return array
     */
    protected function buildSqlSequence(array $fieldsList): array
    {
        $sqlSequence = [];

        foreach ($fieldsList as $field) {
            foreach (static::FIELDS_TO_BUILD_METHODS[$field] as $buildMethod) {
                $sqlSequence[] = $this->$buildMethod();
            }
        }

        return $sqlSequence;
    }

    /**
     * Prepares the query, runs it and saves the results in $results.
     *
     * @param string $sql
     * @param string $queryText
     * @param int $limit
     * @return array
     */
    protected function execQuery(string $sql, string $queryText, int $limit): array
    {
        global $db;

        $foundIds = implode(',', array_column($this->results, 'products_id'));

        $searchQueryPreg = preg_replace('/\s+/', ' ', preg_quote($queryText, '&'));
        $searchQueryRegexp = str_replace(' ', '|', $searchQueryPreg);

        // Remove all non-word characters and add wildcard operator for boolean mode search
        $searchBooleanQuery = str_replace(' ', '* ', trim(preg_replace('/[^\p{L}\p{N}_]+/u', ' ', $queryText))) . '*';

        // Prepare the sql
        $sql = $db->bindVars($sql, ':searchBooleanQuery', $searchBooleanQuery, 'string');
        $sql = $db->bindVars($sql, ':searchQuery', $queryText, 'string');
        $sql = $db->bindVars($sql, ':searchBeginsQuery', $queryText . '%', 'string');
        $sql = $db->bindVars($sql, ':regexpQuery', $searchQueryRegexp, 'string');
        $sql = $db->bindVars($sql, ':languageId', $_SESSION['languages_id'], 'integer');
        $sql = $db->bindVars($sql, ':foundIds', $foundIds ?? "''", 'inConstructInteger');
        $sql = $db->bindVars($sql, ':alphaFilter', chr($this->alphaFilter) . '%', 'string');
        $sql = $db->bindVars($sql, ':resultsLimit', $limit, 'integer');

        $this->notify('NOTIFY_INSTANT_SEARCH_MYSQL_ENGINE_BEFORE_SQL', $queryText, $sql, $limit, $this->alphaFilter);

        // Run the sql
        $dbResults = $db->Execute($sql);

        // Save the results
        $results = [];
        foreach ($dbResults as $dbResult) {
            $results[] = $dbResult;
        }

        return $results;
    }

    /**
     * Builds the Full-Text search sql on product name and (optionally) description.
     *
     * @param bool $includeDescription
     * @return string
     */
    protected function buildSqlProductNameDescription(bool $includeDescription = true): string
    {
        $queryExpansion = $this->useQueryExpansion === true ? ' WITH QUERY EXPANSION' : '';

        return "
            SELECT
                p.*,
                pd.products_name,
                m.manufacturers_name,
                MATCH(pd.products_name) AGAINST(:searchBooleanQuery IN BOOLEAN MODE) AS name_relevance_boolean,
                MATCH(pd.products_name) AGAINST(:searchQuery " . $queryExpansion . ") AS name_relevance_natural " .
                ($includeDescription === true ? ", MATCH(pd.products_description) AGAINST(:searchQuery " . $queryExpansion . ") AS description_relevance " : "") . "
            FROM
                " . TABLE_PRODUCTS_DESCRIPTION . " pd
                JOIN " . TABLE_PRODUCTS . " p ON (p.products_id = pd.products_id)
                LEFT JOIN " . TABLE_MANUFACTURERS . " m ON (m.manufacturers_id = p.manufacturers_id)
            WHERE
                p.products_status <> 0 " .
                (($this->alphaFilter > 0 ) ? " AND pd.products_name LIKE :alphaFilter " : "") . "
                AND pd.language_id = :languageId
                AND p.products_id NOT IN (:foundIds)
                AND (
                    ( MATCH(pd.products_name) AGAINST(:searchBooleanQuery IN BOOLEAN MODE) + MATCH(pd.products_name) AGAINST(:searchQuery " . $queryExpansion . ") ) > 0 " .
                    ($includeDescription === true ? " OR MATCH(pd.products_description) AGAINST(:searchQuery " . $queryExpansion . ") > 0 " : "") . "
                )
            ORDER BY
                name_relevance_boolean DESC,
                name_relevance_natural DESC,
                " . ($includeDescription === true ? " description_relevance DESC, " : "") . "
                p.products_sort_order,
                pd.products_name
            LIMIT
                :resultsLimit
        ";
    }

    /**
     * Builds the Full-Text search sql on product name and description.
     *
     * @return string
     */
    protected function buildSqlProductNameWithDescription(): string
    {
        return $this->buildSqlProductNameDescription();
    }

    /**
     * Builds the Full-Text search sql on product name (no description).
     *
     * @return string
     */
    protected function buildSqlProductNameWithoutDescription(): string
    {
        return $this->buildSqlProductNameDescription(false);
    }

    /**
     * Builds the LIKE/REGEXP search sql on product name.
     *
     * @param bool $beginsWith
     * @return string
     */
    protected function buildSqlProductName(bool $beginsWith = true): string
    {
        return "
            SELECT
                p.*,
                pd.products_name,
                m.manufacturers_name,
                SUM(cpv.views) AS total_views
            FROM
                " . TABLE_PRODUCTS . " p
                JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id)
                LEFT JOIN " . TABLE_MANUFACTURERS . " m ON (m.manufacturers_id = p.manufacturers_id)
                LEFT JOIN " . TABLE_COUNT_PRODUCT_VIEWS . " cpv ON (
                    p.products_id = cpv.product_id
                    AND cpv.language_id = :languageId
                )
            WHERE
                p.products_status <> 0 " .
                ($this->alphaFilter > 0 ? " AND pd.products_name LIKE :alphaFilter " : " ") . "
                AND pd.products_name " . ($beginsWith === true ? " LIKE :searchBeginsQuery "  : " REGEXP :regexpQuery ") . "
                AND pd.language_id = :languageId
                AND p.products_id NOT IN (:foundIds)
            GROUP BY
                p.products_id,
                pd.products_name,
                m.manufacturers_name
            ORDER BY
                total_views DESC,
                p.products_sort_order,
                pd.products_name
            LIMIT
                :resultsLimit
        ";
    }

    /**
     * Builds the LIKE search sql on product name.
     *
     * @return string
     */
    protected function buildSqlProductNameBegins(): string
    {
        return $this->buildSqlProductName();
    }

    /**
     * Builds the REGEXP search sql on product name.
     *
     * @return string
     */
    protected function buildSqlProductNameContains(): string
    {
        return $this->buildSqlProductName(false);
    }

    /**
     * Builds the LIKE/REGEXP search sql on product model.
     *
     * @param bool $exactMatch
     * @return string
     */
    protected function buildSqlProductModel(bool $exactMatch = true): string
    {
        return "
            SELECT
                p.*,
                pd.products_name,
                m.manufacturers_name,
                SUM(cpv.views) AS total_views
            FROM
                " . TABLE_PRODUCTS . " p
                JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id)
                LEFT JOIN " . TABLE_MANUFACTURERS . " m ON (m.manufacturers_id = p.manufacturers_id)
                LEFT JOIN " . TABLE_COUNT_PRODUCT_VIEWS . " cpv ON (
                    p.products_id = cpv.product_id
                    AND cpv.language_id = :languageId
                )
            WHERE
                p.products_status <> 0 " .
                ($this->alphaFilter > 0 ? " AND pd.products_name LIKE :alphaFilter " : " ") . "
                AND p.products_model " . ($exactMatch === true ? "= :searchQuery" : "REGEXP :regexpQuery") . "
                AND pd.language_id = :languageId
                AND p.products_id NOT IN (:foundIds)
            GROUP BY
                p.products_id,
                pd.products_name,
                m.manufacturers_name
            ORDER BY
                total_views DESC,
                p.products_sort_order,
                pd.products_name
            LIMIT
                :resultsLimit
        ";
    }

    /**
     * Builds the LIKE/REGEXP search sql on product model (exact match).
     *
     * @return string
     */
    protected function buildSqlProductModelExact(): string
    {
        return $this->buildSqlProductModel();
    }

    /**
     * Builds the LIKE/REGEXP search sql on product model (broad match).
     *
     * @return string
     */
    protected function buildSqlProductModelBroad(): string
    {
        return $this->buildSqlProductModel(false);
    }

    /**
     * Builds the REGEXP search sql on product meta keywords.
     *
     * @return string
     */
    protected function buildSqlProductMetaKeywords(): string
    {
        return "
            SELECT
                p.*,
                pd.products_name,
                m.manufacturers_name,
                SUM(cpv.views) AS total_views
            FROM
                " . TABLE_PRODUCTS . " p
                JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id)
                JOIN " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " mtpd ON (
                    p.products_id = mtpd.products_id
                    AND mtpd.language_id = :languageId
                )
                LEFT JOIN " . TABLE_MANUFACTURERS . " m ON (m.manufacturers_id = p.manufacturers_id)
                LEFT JOIN " . TABLE_COUNT_PRODUCT_VIEWS . " cpv ON (
                    p.products_id = cpv.product_id
                    AND cpv.language_id = :languageId
                )
            WHERE
                p.products_status <> 0 " .
                ($this->alphaFilter > 0 ? " AND pd.products_name LIKE :alphaFilter " : " ") . "
                AND (mtpd.metatags_keywords REGEXP :regexpQuery)
                AND pd.language_id = :languageId
                AND p.products_id NOT IN (:foundIds)
            GROUP BY
                p.products_id,
                pd.products_name,
                m.manufacturers_name
            ORDER BY
                total_views DESC,
                p.products_sort_order,
                pd.products_name
            LIMIT
                :resultsLimit
        ";
    }

    /**
     * Builds the REGEXP search sql on categories.
     *
     * @return string
     */
    protected function buildSqlCategory(): string
    {
        return "
            SELECT
                c.categories_id,
                cd.categories_name,
                c.categories_image
            FROM
                " . TABLE_CATEGORIES . " c
                LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON cd.categories_id = c.categories_id
            WHERE
                c.categories_status <> 0
                AND (cd.categories_name REGEXP :regexpQuery)
                AND cd.language_id = :languageId
            ORDER BY
                c.sort_order,
                cd.categories_name
            LIMIT
                :resultsLimit
        ";
    }

    /**
     * Builds the REGEXP search sql on manufacturers.
     *
     * @return string
     */
    protected function buildSqlManufacturer(): string
    {
        return "
        SELECT
            DISTINCT m.manufacturers_id,
            m.manufacturers_name,
            m.manufacturers_image
        FROM
            " . TABLE_PRODUCTS . " p
            LEFT JOIN " . TABLE_MANUFACTURERS . " m ON m.manufacturers_id = p.manufacturers_id
        WHERE
            p.products_status <> 0
            AND (m.manufacturers_name REGEXP :regexpQuery)
        ORDER BY
            m.manufacturers_name
        LIMIT
            :resultsLimit
        ";
    }
}

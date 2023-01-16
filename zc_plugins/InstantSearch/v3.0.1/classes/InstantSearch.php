<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.1
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

namespace Zencart\Plugins\Catalog\InstantSearch;

use Zencart\Plugins\Catalog\InstantSearch\SearchEngineProviders\SearchEngineProviderInterface;

abstract class InstantSearch extends \base
{
    /**
     * Factory method that returns the Search engine provider.
     *
     * @return SearchEngineProviderInterface
     */
    abstract public function getSearchEngineProvider(): SearchEngineProviderInterface;

    /**
     * Search for $queryText with the chosen Search Engine Provider and return the results.
     *
     * @param string $queryText
     * @param array $fieldsList
     * @param int $limit
     * @param int|null $alphaFilter
     * @param bool $addToSearchLog
     * @param string $searchLogPrefix
     * @return array
     */
    public function runSearch(
        string $queryText,
        array $fieldsList,
        int $limit,
        int $alphaFilter = null,
        bool $addToSearchLog = false,
        string $searchLogPrefix = ''
    ): array
    {
        $searchEngineProvider = $this->getSearchEngineProvider();

        if ($addToSearchLog === true) {
            $this->addEntryToSearchLog($queryText, $searchLogPrefix);
        }

        return $searchEngineProvider->search($queryText, $fieldsList, $limit, $alphaFilter);
    }

    /**
     * Adds the searched terms to the search log table (if the table exists, i.e.
     * if the Search Log plugin is installed).
     *
     * @param string $query
     * @param string $prefix
     * @return void
     */
    protected function addEntryToSearchLog(string $query, string $prefix): void
    {
        global $db;

        $searchLogTableName = DB_PREFIX . 'search_log';

        $sql = "
            SELECT TABLE_NAME
              FROM information_schema.TABLES
             WHERE (TABLE_SCHEMA = :table_schema)
               AND (TABLE_NAME = :table_name)
        ";

        $sql = $db->bindVars($sql, ':table_schema', DB_DATABASE, 'string');
        $sql = $db->bindVars($sql, ':table_name', $searchLogTableName, 'string');
        $check = $db->Execute($sql);

        if ($check->RecordCount() > 0) {
            $sql = "
                INSERT INTO :table_name (search_term, search_time)
                VALUES (:search_term, NOW())
            ";
            $sql = $db->bindVars($sql, ':table_name', $searchLogTableName, 'noquotestring');
            $sql = $db->bindVars($sql, ':search_term', $prefix . ' ' . $query, 'string');
            $db->Execute($sql);
        }
    }
}

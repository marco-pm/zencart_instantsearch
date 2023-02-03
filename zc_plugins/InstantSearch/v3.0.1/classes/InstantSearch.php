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

use Zencart\Plugins\Catalog\InstantSearch\Exceptions\InstantSearchEngineSearchException;
use Zencart\Plugins\Catalog\InstantSearch\SearchEngineProviders\SearchEngineProviderInterface;

abstract class InstantSearch extends \base
{
    /**
     * The search engine provider.
     *
     * @var SearchEngineProviderInterface
     */
    protected SearchEngineProviderInterface $searchEngineProvider;

    /**
     * Factory method that returns the Search engine provider.
     *
     * @return SearchEngineProviderInterface
     */
    abstract public function getSearchEngineProvider(): SearchEngineProviderInterface;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->searchEngineProvider = $this->getSearchEngineProvider();
    }

    /**
     * Search for $queryText with the chosen Search Engine Provider and return the results.
     *
     * @param string $queryText
     * @param array $productFieldsList
     * @param int $productsLimit
     * @param int $categoriesLimit
     * @param int $manufacturersLimit
     * @param int|null $alphaFilter
     * @param bool $addToSearchLog
     * @param string $searchLogPrefix
     * @return array
     * @throws InstantSearchEngineSearchException
     */
    public function runSearch(
        string $queryText,
        array $productFieldsList,
        int $productsLimit,
        int $categoriesLimit = 0,
        int $manufacturersLimit = 0,
        int $alphaFilter = null,
        bool $addToSearchLog = false,
        string $searchLogPrefix = ''
    ): array {
        if ($addToSearchLog === true) {
            $this->addEntryToSearchLog($queryText, $searchLogPrefix);
        }

        try {
            $results = $this->searchEngineProvider->search(
                $queryText,
                $productFieldsList,
                $productsLimit,
                $categoriesLimit,
                $manufacturersLimit,
                $alphaFilter
            );

            return $results;
        } catch (\Exception $e) {
            $this->writeLog("Error while searching for \"$queryText\"", $e);
            throw new InstantSearchEngineSearchException();
        }
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

    /**
     * Write an entry in the Instant Search log.
     *
     * @param string $message
     * @param \Exception $e
     * @return void
     */
    public function writeLog(string $message, \Exception $e): void
    {
        $logName = DIR_FS_LOGS . "/instant-search-" . date('Y-m-d') . ".log";
        $providerClassParts = explode('\\', get_class($this->searchEngineProvider));
        $providerName = end($providerClassParts);
        error_log(date('Y-m-d H:i:s') . " [$providerName] $message: " . $e->getMessage() . PHP_EOL, 3, $logName);
    }

    /**
     * @param SearchEngineProviderInterface $searchEngineProvider
     */
    public function setSearchEngineProvider(SearchEngineProviderInterface $searchEngineProvider): void
    {
        $this->searchEngineProvider = $searchEngineProvider;
    }
}

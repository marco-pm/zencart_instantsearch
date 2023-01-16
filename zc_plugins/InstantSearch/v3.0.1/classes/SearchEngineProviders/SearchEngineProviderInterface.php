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

interface SearchEngineProviderInterface
{
    /**
     * Search for $queryText and return the results.
     *
     * @param string $queryText the string to search
     * @param array $fieldsList
     * @param int $limit maximum number of results to return
     * @param int|null $alphaFilter
     * @return array
     */
    public function search(string $queryText, array $fieldsList, int $limit, int $alphaFilter = null): array;
}

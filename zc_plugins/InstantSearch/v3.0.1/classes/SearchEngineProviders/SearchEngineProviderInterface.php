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
     * Searches for $queryText and returns the results.
     *
     * @param string $queryText
     * @param array $productFieldsList
     * @param int $productsLimit
     * @param int|null $alphaFilter
     * @return array
     */
    public function search(
        string $queryText,
        array $productFieldsList,
        int $productsLimit,
        int $alphaFilter = null
    ): array;
}

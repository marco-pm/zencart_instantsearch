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
use Zencart\Plugins\Catalog\InstantSearch\SearchEngineProviders\MySqlSearchEngineProvider;

class MySqlInstantSearch extends InstantSearch
{
    /**
     * Use Query Expansion in the Full-Text searches.
     *
     * @var bool
     */
    protected bool $useQueryExpansion;

    /**
     * Constructor.
     *
     * @param $useQueryExpansion
     */
    public function __construct($useQueryExpansion)
    {
        $this->useQueryExpansion = $useQueryExpansion;
    }

    /**
     * Factory method that returns the MySQL Search engine provider.
     *
     * @return SearchEngineProviderInterface
     */
    public function getSearchEngineProvider(): SearchEngineProviderInterface {
        return new MySqlSearchEngineProvider($this->useQueryExpansion);
    }
}

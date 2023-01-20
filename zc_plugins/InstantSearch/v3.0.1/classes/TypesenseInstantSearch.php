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

use Typesense\Exceptions\ConfigError;
use Zencart\Plugins\Catalog\InstantSearch\Exceptions\InstantSearchEngineInitException;
use Zencart\Plugins\Catalog\InstantSearch\SearchEngineProviders\SearchEngineProviderInterface;
use Zencart\Plugins\Catalog\InstantSearch\SearchEngineProviders\TypesenseSearchEngineProvider;

class TypesenseInstantSearch extends InstantSearch
{
    /**
     * Constructor.
     * @throws InstantSearchEngineInitException
     */
    public function __construct()
    {
        try {
            parent::__construct();
        } catch (\Exception $e) {
            $this->writeLog("Error while initializing Typesense, check the configuration parameters", $e);
            throw new InstantSearchEngineInitException();
        }
    }

    /**
     * Factory method that returns the Typesense Search engine provider.
     *
     * @return SearchEngineProviderInterface
     * @throws ConfigError
     */
    public function getSearchEngineProvider(): SearchEngineProviderInterface
    {
        return new TypesenseSearchEngineProvider();
    }
}

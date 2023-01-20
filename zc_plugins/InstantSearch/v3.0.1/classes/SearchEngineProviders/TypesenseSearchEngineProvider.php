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

use Http\Client\Exception as HttpClientException;
use Typesense\Client;
use Typesense\Exceptions\ConfigError;
use Typesense\Exceptions\TypesenseClientError;
use Zencart\Plugins\Catalog\InstantSearch\Typesense\TypesenseZencart;

class TypesenseSearchEngineProvider extends \base implements SearchEngineProviderInterface
{
    /**
     * Array of product fields (keys) with the corresponding Typesense parameter values for query_by,
     * prefix, infix, num_typos.
     *
     * @var array
     */
    protected const FIELDS_TO_PARAMETERS = [
        'category'         => ['category_<lang>', 'true', 'fallback', 2],
        'manufacturer'     => ['manufacturer', 'true', 'fallback', 2],
        'meta-keywords'    => ['meta-keywords_<lang>', 'true', 'fallback', 2],
        'model-broad'      => ['model', 'true', 'fallback', 2],
        'model-exact'      => ['model', 'false', 'off', 0],
        'name'             => ['name_<lang>', 'true', 'fallback', 2],
        'name-description' => ['name_<lang>,description_<lang>', 'true,true', 'fallback,fallback', 2],
    ];

    /**
     * The Typesense collection name to use in searches.
     *
     * @var string
     */
    protected string $collectionName;

    /**
     * The Typesense PHP client.
     *
     * @var Client
     */
    protected Client $client;

    /**
     * Array of search results.
     *
     * @var array
     */
    protected array $results;

    /**
     * Constructor.
     *
     * @throws ConfigError
     */
    public function __construct()
    {
        $typesense = new TypesenseZencart();
        $this->client = $typesense->getClient();
        $this->collectionName = $typesense::COLLECTION_NAME;
        $this->results = [];
    }

    /**
     * Searches for $queryText and returns the results.
     *
     * @param string $queryText
     * @param array $productFieldsList
     * @param int $productsLimit
     * @param int|null $alphaFilter
     * @return array
     * @throws TypesenseClientError|HttpClientException
     */
    public function search(
        string $queryText,
        array $productFieldsList,
        int $productsLimit,
        int $alphaFilter = null
    ): array {
        global $db;

        $sql = "SELECT code FROM " . TABLE_LANGUAGES . " WHERE languages_id = :languages_id";
        $sql = $db->bindVars($sql, ':languages_id', (int)$_SESSION['languages_id'], 'integer');
        $languageCode = $db->Execute($sql)->fields['code'];

        $searchParameters = [
            'q'          => $queryText,
            'query_by'   => '',
            'prefix'     => '',
            'infix'      => '',
            'sort_by'    => "_text_match:desc,views_$languageCode:desc,sort-order:asc",
            'per_page'   => $productsLimit
        ];

        foreach ($productFieldsList as $productField) {
            $searchParameters['query_by'] .= str_replace('<lang>', $languageCode, self::FIELDS_TO_PARAMETERS[$productField][0]) . ',';
            $searchParameters['prefix']   .= self::FIELDS_TO_PARAMETERS[$productField][1] . ',';
            $searchParameters['infix']    .= self::FIELDS_TO_PARAMETERS[$productField][2] . ',';
            $searchParameters['num_typos'] = self::FIELDS_TO_PARAMETERS[$productField][3];
        }

        if ($alphaFilter !== null) {
            $searchParameters['filter_by'] = "name_$languageCode: " . chr($alphaFilter);
        }

        $typesenseResults = $this->client->collections[$this->collectionName]->documents->search($searchParameters);

        foreach ($typesenseResults['hits'] as $hit) {
            $document = $hit['document'];
            $result['products_id']    = $document['id'];
            $result['products_name']  = $document["name_$languageCode"];
            $result['products_image'] = $document['image'];
            $result['products_model'] = $document['model'];
            $result['products_price'] = $document['price'];

            $this->results[] = $result;
        }

        return $this->results;
    }
}

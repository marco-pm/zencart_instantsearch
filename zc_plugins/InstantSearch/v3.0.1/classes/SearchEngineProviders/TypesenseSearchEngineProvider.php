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
        $this->results = [];
    }

    /**
     * Searches for $queryText and returns the results.
     *
     * @param string $queryText
     * @param array $productFieldsList
     * @param int $productsLimit
     * @param int $categoriesLimit
     * @param int $manufacturersLimit
     * @param int|null $alphaFilter
     * @return array
     * @throws TypesenseClientError|HttpClientException
     */
    public function search(
        string $queryText,
        array $productFieldsList,
        int $productsLimit,
        int $categoriesLimit = 0,
        int $manufacturersLimit = 0,
        int $alphaFilter = null
    ): array {
        global $db;

        $sql = "SELECT code FROM " . TABLE_LANGUAGES . " WHERE languages_id = :languages_id";
        $sql = $db->bindVars($sql, ':languages_id', (int)$_SESSION['languages_id'], 'integer');
        $languageCode = $db->Execute($sql)->fields['code'];

        $productsSearch = [
            'query_by'   => '',
            'prefix'     => '',
            'infix'      => '',
            'sort_by'    => "_text_match:desc,views_$languageCode:desc,sort-order:asc",
            'per_page'   => $productsLimit
        ];

        $categoriesSearch = [
            'query_by'   => "name_$languageCode",
            'prefix'     => 'true',
            'infix'      => 'fallback',
            'sort_by'    => "_text_match:desc,name_$languageCode:asc",
            'per_page'   => $categoriesLimit
        ];

        $brandsSearch = [
            'query_by'   => "name",
            'prefix'     => 'true',
            'infix'      => 'fallback',
            'sort_by'    => "_text_match:desc,name:asc",
            'per_page'   => $manufacturersLimit
        ];

        foreach ($productFieldsList as $productField) {
            $productsSearch['query_by'] .= str_replace('<lang>', $languageCode, self::FIELDS_TO_PARAMETERS[$productField][0]) . ',';
            $productsSearch['prefix']   .= self::FIELDS_TO_PARAMETERS[$productField][1] . ',';
            $productsSearch['infix']    .= self::FIELDS_TO_PARAMETERS[$productField][2] . ',';
            $productsSearch['num_typos'] = self::FIELDS_TO_PARAMETERS[$productField][3];
        }

        if ($alphaFilter !== null) {
            $productsSearch['filter_by']   = "name_$languageCode: " . chr($alphaFilter);
        }

        $searchRequests = [
            'searches' => [
                [
                    'collection' => TypesenseZencart::PRODUCTS_COLLECTION_NAME,
                    ...$productsSearch
                ],
                [
                    'collection' => TypesenseZencart::CATEGORIES_COLLECTION_NAME,
                    ...$categoriesSearch
                ],
                [
                    'collection' => TypesenseZencart::BRANDS_COLLECTION_NAME,
                    ...$brandsSearch
                ]
            ]
        ];

        $commonSearchParams =  [
            'q' => $queryText,
        ];

        try {
            $typesenseResults = $this->client->multiSearch->perform($searchRequests, $commonSearchParams);
        } catch (\Exception $e) {
            $this->writeTypesenseSearchLog('Error while performing search: ' . $e->getMessage());
            $this->results = [];
        }

        foreach ($typesenseResults['results'] as $result) {
            if (isset($result['error'])) {
                $this->writeTypesenseSearchLog('Error while performing search: ' . $result['error']);
                continue;
            }
            if ($result['found'] === 0) {
                continue;
            }
            $collectionName = $result['request_params']['collection_name'];
            foreach ($result['hits'] as $hit) {
                $document = $hit['document'];

                if ($collectionName === TypesenseZencart::PRODUCTS_COLLECTION_NAME) {
                    $result['products_id']              = $document['id'];
                    $result['products_name']            = $document["name_$languageCode"];
                    $result['products_image']           = $document['image'];
                    $result['products_model']           = $document['model'];
                    $result['products_price']           = $document['price'];
                    $result['products_displayed_price'] = $document['displayed-price_' . $_SESSION['currency']] ?? '';

                } elseif ($collectionName === TypesenseZencart::CATEGORIES_COLLECTION_NAME) {
                    $result['categories_id']    = $document['id'];
                    $result['categories_name']  = $document["name_$languageCode"];
                    $result['categories_image'] = $document['image'];
                    $result['categories_count'] = $document['products-count'];

                } elseif ($collectionName === TypesenseZencart::BRANDS_COLLECTION_NAME) {
                    $result['manufacturers_id']    = $document['id'];
                    $result['manufacturers_name']  = $document['name'];
                    $result['manufacturers_image'] = $document['image'];
                    $result['manufacturers_count'] = $document['products-count'];
                }

                $this->results[] = $result;
            }
        }

        return $this->results;
    }

    /**
     * Write an entry in the log.
     *
     * @param string $message
     * @return void
     */
    function writeTypesenseSearchLog(string $message): void
    {
        $logName = DIR_FS_LOGS . "/instantsearch_typesense_search-" . date('Y-m-d') . ".log";
        error_log(date('Y-m-d H:i:s') . " $message" . PHP_EOL, 3, $logName);
    }
}

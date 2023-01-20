<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.1
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

namespace Zencart\Plugins\Catalog\InstantSearch\Typesense;

require __DIR__ . '/../../vendor/autoload.php';

use Http\Client\Exception as HttpClientException;
use Symfony\Component\HttpClient\HttplugClient;
use Typesense\Client;
use Typesense\Exceptions\ConfigError;
use Typesense\Exceptions\ObjectNotFound;
use Typesense\Exceptions\TypesenseClientError;

class TypesenseZencart
{
    /**
     * The Typesense collection name linked to the Zen Cart database.
     */
    public const COLLECTION_NAME = DB_DATABASE . "_products";

    /**
     * The Typesense PHP client.
     *
     * @var Client
     */
    protected Client $client;

    /**
     * Constructor. Initializes the client.
     *
     * @throws ConfigError
     */
    public function __construct()
    {
        $this->client = new Client(
            [
                'api_key' => INSTANT_SEARCH_TYPESENSE_KEY,
                'nodes' => [
                    [
                        'host'     => INSTANT_SEARCH_TYPESENSE_HOST,
                        'port'     => INSTANT_SEARCH_TYPESENSE_PORT,
                        'protocol' => INSTANT_SEARCH_TYPESENSE_PROTOCOL,
                    ],
                ],
                'client' => new HttplugClient(),
            ]
        );
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Creates the collection.
     *
     * @throws TypesenseClientError|HttpClientException
     */
    public function createCollection(): void
    {
        global $db;

        // Create the collection only if it does not already exist
        try {
            $this->client->collections[self::COLLECTION_NAME]->retrieve();
        } catch (ObjectNotFound) {
            $schema = [
                'name'      => self::COLLECTION_NAME,
                'fields'    => [
                    [
                        'name'  => 'id',
                    ],
                    [
                        'name'  => 'model',
                        'type'  => 'string',
                        'infix' => true
                    ],
                    [
                        'name'  => 'price',
                        'type'  => 'float'
                    ],
                    [
                        'name'  => 'quantity',
                        'type'  => 'float'
                    ],
                    [
                        'name'  => 'weight',
                        'type'  => 'float'
                    ],
                    [
                        'name'  => 'image',
                        'type'  => 'string'
                    ],
                    [
                        'name'  => 'manufacturer',
                        'type'  => 'string',
                        'infix' => true,
                        'facet' => true
                    ],
                    [
                        'name'  => 'sort-order',
                        'type'  => 'int32'
                    ],
                ]
            ];

            $languages = $db->Execute("SELECT languages_id, code FROM " . TABLE_LANGUAGES);
            foreach ($languages as $language) {
                $schema['fields'][] = [
                    'name'  => 'name_' . $language['code'],
                    'type'  => 'string',
                    'infix' => true
                ];
                $schema['fields'][] = [
                    'name'  => 'description_' . $language['code'],
                    'type'  => 'string',
                    'infix' => true,
                ];
                $schema['fields'][] = [
                    'name'  => 'meta-keywords_' . $language['code'],
                    'type'  => 'string',
                    'infix' => true
                ];
                $schema['fields'][] = [
                    'name'  => 'views_' . $language['code'],
                    'type'  => 'int32'
                ];
                $schema['fields'][] = [
                    'name'  => 'category_' . $language['code'],
                    'type'  => 'string',
                    'infix' => true,
                    'facet' => true
                ];
            }

            $this->client->collections->create($schema);
        }
    }

    /**
     * Deletes the collection.
     *
     * @throws TypesenseClientError|HttpClientException
     */
    public function deleteCollection(): void
    {
        $this->client->collections[self::COLLECTION_NAME]->delete();
    }

    /**
     * Sync changes after the last sync (products added/modified/deleted).
     *
     * @throws TypesenseClientError|HttpClientException
     */
    public function syncChanges()
    {
    }

    /**
     * Full re-index of the collection.
     *
     * @throws TypesenseClientError|HttpClientException
     */
    public function syncFull()
    {
        global $db;

        $productsToImport = [];

        $languages = $db->Execute("SELECT languages_id, code FROM " . TABLE_LANGUAGES);

        $sql = "
            SELECT
                p.products_id,
                p.products_model,
                p.products_price_sorter,
                p.products_quantity,
                p.products_weight,
                p.products_image,
                p.master_categories_id,
                p.products_sort_order,
                m.manufacturers_name
            FROM
                " . TABLE_PRODUCTS . " p
                LEFT JOIN " . TABLE_MANUFACTURERS . " m ON (m.manufacturers_id = p.manufacturers_id)
            WHERE
                p.products_status <> 0
        ";
        $products = $db->Execute($sql);

        foreach ($products as $product) {
            foreach ($languages as $language) {
                $sql = "
                    SELECT
                        pd.products_name,
                        pd.products_description,
                        mtpd.metatags_keywords,
                        cd.categories_name,
                        SUM(cpv.views) AS total_views
                    FROM
                        " . TABLE_PRODUCTS_DESCRIPTION . " pd
                        LEFT JOIN " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " mtpd ON (
                            mtpd.products_id = pd.products_id
                            AND mtpd.language_id = pd.language_id
                        )
                        LEFT JOIN " . TABLE_COUNT_PRODUCT_VIEWS . " cpv ON (
                            cpv.product_id = pd.products_id
                            AND cpv.language_id = pd.language_id
                        )
                        LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (
                            cd.categories_id = :categories_id
                            AND cd.language_id = pd.language_id
                        )
                    WHERE
                        pd.products_id = :products_id
                        AND pd.language_id = :languages_id
                    GROUP BY
                        pd.products_id
                ";
                $sql = $db->bindVars($sql, ':categories_id', $product['master_categories_id'], 'integer');
                $sql = $db->bindVars($sql, ':products_id', $product['products_id'], 'integer');
                $sql = $db->bindVars($sql, ':languages_id', $language['languages_id'], 'integer');
                $productAdditionalData = $db->Execute($sql);

                if ($productAdditionalData->RecordCount() > 0) {
                    $productsToImport[] = [
                        'id'                                 => (string)$product['products_id'],
                        'model'                              => $product['products_model'],
                        'price'                              => (float)$product['products_price_sorter'],
                        'quantity'                           => (float)$product['products_quantity'],
                        'weight'                             => (float)$product['products_weight'],
                        'image'                              => $product['products_image'] ?? '',
                        'manufacturer'                       => $product['manufacturers_name'] ?? '',
                        'sort-order'                         => (int)$product['products_sort_order'],
                        'name_' . $language['code']          => $productAdditionalData->fields['products_name'],
                        'description_' . $language['code']   => $productAdditionalData->fields['products_description'],
                        'meta-keywords_' . $language['code'] => $productAdditionalData->fields['metatags_keywords'] ?? '',
                        'views_' . $language['code']         => (int)$productAdditionalData->fields['total_views'],
                        'category_' . $language['code']      => $productAdditionalData->fields['categories_name'] ?? '',
                    ];
                }
            }
        }

        $beo = $this->client->collections[self::COLLECTION_NAME]->documents->import($productsToImport, ['action' => 'upsert']);

        $a = 1;
    }
}

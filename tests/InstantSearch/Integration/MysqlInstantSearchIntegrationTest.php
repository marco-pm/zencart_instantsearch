<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  4.0.2
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

namespace Tests\InstantSearch\Integration;

use Composer\Autoload\ClassLoader;
use Tests\Support\Traits\DatabaseConcerns;
use Tests\Support\zcUnitTestCase;

abstract class MysqlInstantSearchIntegrationTest extends zcUnitTestCase
{
    use DatabaseConcerns;

    public array $databaseFixtures = [
        'categoriesDemo'              => ['categories'],
        'categoriesDescriptionDemo'   => ['categories_description'],
        'countProductsViewsDemo'      => ['count_product_views'],
        'manufacturersDemo'           => ['manufacturers'],
        'metaTagsProductsDescription' => ['meta_tags_products_description'],
        'productsDemo'                => ['products'],
        'productsDescriptionDemo'     => ['products_description'],
        'productTypes'                => ['product_types']
    ];

    // Addition to the DatabaseConcerns setUp()
    public function instantSearchSetUp(): void
    {
        parent::setUp();

        $this->pdoConnection->query("SET GLOBAL sql_mode = 'ONLY_FULL_GROUP_BY'");

        $classLoader = new ClassLoader();
        $classLoader->addPsr4("Zencart\\Plugins\\Catalog\\InstantSearch\\", "zc_plugins/InstantSearch/v4.0.2/classes/", true);
        $classLoader->register();

        require DIR_FS_CATALOG . DIR_WS_CLASSES . 'ajax/zcAjaxInstantSearch.php';

        require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'html_output.php');
        require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_products.php');
        require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_strings.php');

        $_SESSION['languages_id'] = 1;

        define('INSTANT_SEARCH_ENGINE', 'MySQL');
        define('PRODUCT_LIST_MODEL', '0');
        define('PRODUCT_LIST_NAME', '1');
        define('PRODUCT_LIST_MANUFACTURER', '2');
        define('PRODUCT_LIST_PRICE', '3');
        define('PRODUCT_LIST_QUANTITY', '4');
        define('PRODUCT_LIST_WEIGHT', '5');
        define('PRODUCT_LIST_IMAGE', '6');
    }

    /**
     * @dataProvider keywordProvider
     *
     * @param string $keyword
     * @param string $productFieldsList
     * @param bool $queryExpansion use query expansion true/false
     * @param int $maxProducts max number of results
     * @param int $expectedResultsCount
     * @param array $expectedFirstResultsIds first elements of the expected array of results' IDs
     * @param array $postVariables
     */
    public function testKeywordReturnsProducts(
        string $keyword,
        string $productFieldsList,
        bool   $queryExpansion,
        int    $maxProducts,
        int    $expectedResultsCount,
        array  $expectedFirstResultsIds,
        array  $postVariables = []
    ): void {
        $this->instantSearchSetUp();

        $_POST['keyword'] = $keyword;

        foreach ($postVariables as $k => $postVariable) {
            $_POST[$k] = $postVariable;
        }

        $ajaxInstantSearchMock = $this->getMockBuilder('zcAjaxInstantSearch')
                                      ->onlyMethods(['formatDropdownResults', 'formatPageResults'])
                                      ->getMock();

        $ajaxInstantSearchMock->instantSearch();
        $results = $ajaxInstantSearchMock->getResults();

        $this->assertCount($expectedResultsCount, $results);

        $resultsProductIds = array_column($results, 'products_id');
        $this->assertCount(count(array_unique($resultsProductIds, SORT_NUMERIC)), $resultsProductIds);

        $resultsToCheck = [];
        $expectedFirstResultsIdsCount = count($expectedFirstResultsIds);
        for ($i = 0; $i < $expectedFirstResultsIdsCount; $i++) {
            if (isset($results[$i]['products_id'])) {
                $resultsToCheck[] = $results[$i]['products_id'];
            } elseif (isset($results[$i]['categories_id'])) {
                $resultsToCheck[] = $results[$i]['categories_id'];
            } elseif (isset($results[$i]['manufacturers_id'])) {
                $resultsToCheck[] = $results[$i]['manufacturers_id'];
            }
        }
        $this->assertEquals($expectedFirstResultsIds, $resultsToCheck);
    }

    public function keywordProvider(): array
    {
        return [
            'empty' => [
                '', 'name', true, 5, 0, []
            ],
            'model-exact - match' => [
                'MG400-32MB', 'model-exact', true, 5, 1, ['2']
            ],
            'model-exact - match multiple products' => [
                'Testcall', 'model-exact', true, 5, 3, ['174', '40', '41']
            ],
            'model-exact - match with spaces around' => [
                ' Test120-New100  ', 'model-exact', true, 5, 1, ['93']
            ],
            'model-exact - no match if model is partial' => [
                'MG400-32M', 'model-exact', true, 5, 0, []
            ],
            'model-exact - no match if model is not at the beginning' => [
                'ram MG400-32MB', 'model-exact', true, 5, 0, []
            ],
            'model-broad - match' => [
                'MAXSAMPLE', 'model-broad', true, 5, 2, ['105', '106']
            ],
            'model-broad - no match' => [
                'packard', 'model-broad', true, 5, 0, []
            ],
            'model-broad - match with other terms' => [
                'whatever maxsample term', 'model-broad', true, 5, 2, ['105', '106']
            ],
            'model-broad - match even if partial' => [
                'whatever maxs term', 'model-broad', true, 5, 2, ['105', '106']
            ],
            'model - results are unique' => [
                'MG400-32MB', 'model-exact,model-broad', true, 5, 1, ['2']
            ],
            'model - results are sorted by views' => [
                'MAXSAMPLE Testcall', 'model-exact,model-broad', true, 5, 5, ['174', '105', '40', '41', '106']
            ],
            'model - total number of results limited by max limit' => [
                'dvd', 'model-broad', true, 3, 3, ['34', '20', '5']
            ],
            'name - no match' => [
                'Dolby Surround', 'name', true, 0, 0, []
            ],
            'name, no query expansion - single match' => [
                'disciples', 'name', false, 5, 1, ['24']
            ],
            'name, with query expansion - single match' => [
                'disciples', 'name', true, 5, 5, ['24', '20', '5', '16', '12']
            ],
            'name, no query expansion - multiple matches' => [
                'life', 'name', false, 5, 2, ['34', '8']
            ],
            'name-description, no query expansion - single match' => [
                'intellieye', 'name-description', false, 5, 1, ['26']
            ],
            'name-description, with query expansion - multiple matches' => [
                'Dolby Surround', 'name-description', true, 5, 5, ['34', '7', '8']
            ],
            'name-description, no query expansion - partial name match' => [
                'ntellimou', 'name-description', false, 5, 2, ['26', '3']
            ],
            'name-description, no query expansion - description match' => [
                'industrial', 'name-description', false, 5, 1, ['26']
            ],
            'name-description, no query expansion - partial description does not match (no boolean mode for description)' => [
                'industria', 'name-description', false, 5, 0, []
            ],
            'model-broad,name-description, no query expansion - results order is correct (model first)' => [
                'industrial maxsample', 'model-broad,name-description', false, 5, 3, ['105', '106', '26']
            ],
            'name-description,model-broad, no query expansion - results order is correct (name first)' => [
                'industrial maxsample', 'name-description,model-broad', false, 5, 3, ['26', '105', '106']
            ],
            'meta-keywords - match' => [
                'top-rated', 'meta-keywords', true, 5, 1, ['19']
            ],
            'category - single match' => [
                'simulation', 'category', true, 5, 1, ['21']
            ],
            'category - multiple matches' => [
                'simulation strategy', 'category', true, 5, 3, ['24', '21', '23']
            ],
            'manufacturer - single match' => [
                'matrox', 'manufacturer', true, 5, 2, ['1', '2']
            ],
            'manufacturer -  multiple matches' => [
                'matrox fox', 'manufacturer', true, 5, 5, []
            ],
        ];
    }
}

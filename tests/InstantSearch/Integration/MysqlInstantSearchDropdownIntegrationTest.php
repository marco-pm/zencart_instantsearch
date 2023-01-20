<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.1
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

namespace Tests\InstantSearch\Integration;

class MysqlInstantSearchDropdownIntegrationTest extends MysqlInstantSearchIntegrationTest
{
    public function instantSearchSetUp(): void
    {
        parent::instantSearchSetUp();

        define('INSTANT_SEARCH_DROPDOWN_MIN_WORDSEARCH_LENGTH', '3');
        define('INSTANT_SEARCH_DROPDOWN_MAX_WORDSEARCH_LENGTH', '30');
        define('INSTANT_SEARCH_DROPDOWN_ADD_LOG_ENTRY', 'false');
        define('TEXT_SEARCH_LOG_ENTRY_DROPDOWN_PREFIX', '');
    }

    /**
     * @dataProvider keywordProvider
     * @dataProvider dropdownSpecificKeywordProvider
     */
    public function testKeywordReturnsProducts(
        string $keyword,
        string $productFieldsList,
        bool   $queryExpansion,
        int    $maxProducts,
        int    $expectedResultsCount,
        array  $expectedFirstResultsIds,
        array  $postVariables = [],
        int    $maxCategories = 0,
        int    $maxManufacturers = 0,
    ): void {
        define('INSTANT_SEARCH_PRODUCT_FIELDS_LIST', $productFieldsList);
        define('INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION', $queryExpansion === true ? 'true' : 'false');
        define('INSTANT_SEARCH_DROPDOWN_MAX_PRODUCTS', $maxProducts);
        define('INSTANT_SEARCH_DROPDOWN_MAX_CATEGORIES', $maxCategories);
        define('INSTANT_SEARCH_DROPDOWN_MAX_MANUFACTURERS', $maxManufacturers);

        $_POST['scope'] = 'dropdown';

        parent::testKeywordReturnsProducts(
            $keyword,
            $productFieldsList,
            $queryExpansion,
            $maxProducts,
            $expectedResultsCount,
            $expectedFirstResultsIds,
            $postVariables
        );
    }

    public function dropdownSpecificKeywordProvider(): array
    {
        return [
            'categories - no match' => [
                'gibberish', 'name', true, 5, 0, [], [], 2, 0
            ],
            'categories - single match' => [
                'dvd', 'name', true, 0, 1, ['3'], [], 2, 0
            ],
            'categories - multiple matches' => [
                'big linked', 'name', true, 0, 2, ['22', '53'], [], 2, 0
            ],
            'categories - number of results limited by categories limit' => [
                'sale', 'name', true, 0, 3, ['58'], [], 3, 0
            ],
            'manufacturers - no match' => [
                'gibberish', 'name', true, 0, 0, [], [], 0, 2
            ],
            'manufacturers - single match' => [
                'ewle', 'name', true, 0, 1, ['9'], [], 0, 2
            ],
            'name,categories,manufacturers - results order is correct' => [
                'hewlett fox unlinked', 'name', true, 3, 5, ['27', '61', '100', '53', '4'], [], 1, 1
            ],
        ];
    }
}

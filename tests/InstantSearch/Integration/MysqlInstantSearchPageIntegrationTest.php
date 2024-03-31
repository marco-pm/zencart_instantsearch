<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  4.0.3
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

namespace Tests\InstantSearch\Integration;

class MysqlInstantSearchPageIntegrationTest extends MysqlInstantSearchIntegrationTest
{
    protected const MAX_RESULTS_PER_SCREEN = 20;

    public function instantSearchSetUp(): void
    {
        parent::instantSearchSetUp();

        define('INSTANT_SEARCH_PAGE_RESULTS_PER_SCREEN', self::MAX_RESULTS_PER_SCREEN);
        define('INSTANT_SEARCH_PAGE_ADD_LOG_ENTRY', 'false');
        define('TEXT_SEARCH_LOG_ENTRY_PAGE_PREFIX', '');
    }

    /**
     * @runInSeparateProcess
     * @dataProvider keywordProvider
     * @dataProvider pageSpecificKeywordProvider
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
        define('INSTANT_SEARCH_PRODUCT_FIELDS_LIST', $productFieldsList);
        define('INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION', $queryExpansion === true ? 'true' : 'false');
        define('INSTANT_SEARCH_PAGE_RESULTS_PER_PAGE', $maxProducts);

        $_POST['scope'] = 'page';

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

    public function pageSpecificKeywordProvider(): array
    {
        return [
            'alpha filter' => [
                'dvd', 'model-exact,name-description,model-broad', false, 5, 2, ['11', '15'], ['alpha_filter_id' => '70']
            ],
            'results count for two pages of results' => [
                'dvd', 'model-exact,name-description,model-broad', true, 3, 6, ['2'], ['resultPage' => '2']
            ],
            'total number of results limited by INSTANT_SEARCH_PAGE_RESULTS_PER_SCREEN' => [
                'dvd', 'model-exact,name-description,model-broad', true, 3, self::MAX_RESULTS_PER_SCREEN, ['2'], ['resultPage' => '100']
            ],
        ];
    }
}

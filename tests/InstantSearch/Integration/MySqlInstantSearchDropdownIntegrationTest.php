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

class MySqlInstantSearchDropdownIntegrationTest extends MySqlInstantSearchIntegrationTest
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
        string $fieldsList,
        bool $queryExpansion,
        int $maxResults,
        int $expectedResultsCount,
        array $expectedFirstResultsIds,
        array $postVariables = []
    ): void
    {
        define('INSTANT_SEARCH_FIELDS_LIST', $fieldsList);
        define('INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION', $queryExpansion === true ? 'true' : 'false');
        define('INSTANT_SEARCH_DROPDOWN_MAX_RESULTS', $maxResults);

        $_POST['scope'] = 'dropdown';

        parent::testKeywordReturnsProducts(
            $keyword,
            $fieldsList,
            $queryExpansion,
            $maxResults,
            $expectedResultsCount,
            $expectedFirstResultsIds,
            $postVariables
        );
    }

    public function dropdownSpecificKeywordProvider(): array
    {
        return [
            'category - no match' => [
                'gibberish', 'category', true, 5, 0, []
            ],
            'category - single match' => [
                'dvd', 'category', true, 5, 1, ['3']
            ],
            'category - multiple matches' => [
                'big linked', 'category', true, 5, 2, ['22', '53']
            ],
            'category - total number of results limited by max limit' => [
                'sale', 'category', true, 2, 2, ['58', '48']
            ],
            'manufacturer - no match' => [
                'gibberish', 'manufacturer', true, 5, 0, []
            ],
            'manufacturer - single match' => [
                'ewle', 'manufacturer', true, 5, 1, ['9']
            ],
            'category,manufacturer,name - results order is correct' => [
                'hewlett fox', 'category,manufacturer,name', true, 5, 5, ['4', '9', '27']
            ],
        ];
    }
}

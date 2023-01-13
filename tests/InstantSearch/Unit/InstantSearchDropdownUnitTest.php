<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.1
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

namespace Tests\InstantSearch\Unit;

use zcAjaxInstantSearchDropdown;

class InstantSearchDropdownUnitTest extends InstantSearchUnitTest
{
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName, zcAjaxInstantSearchDropdown::class);
    }

    public function setUp(): void
    {
        parent::setUp();

        define('INSTANT_SEARCH_DROPDOWN_MIN_WORDSEARCH_LENGTH', '3');
        define('INSTANT_SEARCH_DROPDOWN_MAX_WORDSEARCH_LENGTH', '30');
        define('INSTANT_SEARCH_DROPDOWN_MAX_RESULTS', '5');
        define('INSTANT_SEARCH_DROPDOWN_USE_QUERY_EXPANSION', 'true');
        define('INSTANT_SEARCH_DROPDOWN_ADD_LOG_ENTRY', 'true');
        define('TEXT_SEARCH_LOG_ENTRY_DROPDOWN_PREFIX', '');
    }

    public function keywordProvider(): array
    {
        return [
            'empty'                    => ['', ''],
            'spaces only'              => ['            ', ''],
            'html tags'                => ['<p></p>', ''],
            'space as html entity'     => ['&nbsp;&nbsp;&nbsp;&nbsp;', ''],
            'length less than minimum' => ['ab', ''],
            'length more than maximum' => ['Lorem ipsum dolor sit amet erat justo invidunt odio et clita molestie eirmod dolore', ''],
        ];
    }

    public function testCommonFieldsValuesCallCorrespondingSql(bool $useQueryExpansion = true): void {
        parent::testCommonFieldsValuesCallCorrespondingSql(INSTANT_SEARCH_DROPDOWN_USE_QUERY_EXPANSION === 'true');
    }

    public function testNameWithDescriptionFieldCallsCorrespondingSql(bool $useQueryExpansion = true): void {
        parent::testCommonFieldsValuesCallCorrespondingSql(INSTANT_SEARCH_DROPDOWN_USE_QUERY_EXPANSION === 'true');
    }

    public function testDropdownExclusiveFieldsValuesCallCorrespondingSql(): void
    {
        define('INSTANT_SEARCH_DROPDOWN_FIELDS_LIST', 'category,manufacturer');

        $dropdownMock = $this->getMockBuilder($this->instantSearchClassName)
                             ->onlyMethods(['execQuery', 'formatResults', 'addEntryToSearchLog', 'buildSqlCategory', 'buildSqlManufacturer'])
                             ->getMock();

        $_POST['keyword'] = 'whatever';

        $dropdownMock->expects($this->once())
                     ->method('buildSqlCategory');

        $dropdownMock->expects($this->once())
                     ->method('buildSqlManufacturer');

        $dropdownMock->instantSearch();
    }
}

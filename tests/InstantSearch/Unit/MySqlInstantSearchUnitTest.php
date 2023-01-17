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

use Zencart\Plugins\Catalog\InstantSearch\MySqlInstantSearch;
use Zencart\Plugins\Catalog\InstantSearch\SearchEngineProviders\MySqlSearchEngineProvider;

class MySqlInstantSearchUnitTest extends InstantSearchUnitTest
{
    public function setUp(): void
    {
        parent::setUp();

        define('INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION', 'true');
    }

    public function testDropdownFieldsValuesCallCorrespondingSql(): void
    {
        define('INSTANT_SEARCH_FIELDS_LIST', 'name,model-exact,model-broad,meta-keywords,category,manufacturer');

        $mySqlSearchEngineProviderMock = $this->getMockBuilder(MySqlSearchEngineProvider::class)
                                              ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                              ->onlyMethods([
                                                  'execQuery', 'buildSqlProductModel', 'buildSqlProductName',
                                                  'buildSqlProductNameDescription', 'buildSqlProductMetaKeywords',
                                                  'buildSqlCategory', 'buildSqlManufacturer'
                                              ])
                                              ->getMock();

        $mysqlInstantSearchMock = $this->getMockBuilder(MySqlInstantSearch::class)
                                       ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                       ->onlyMethods(['getSearchEngineProvider', 'addEntryToSearchLog'])
                                       ->getMock();
        $mysqlInstantSearchMock->method('getSearchEngineProvider')
                               ->willReturn($mySqlSearchEngineProviderMock);

        $ajaxInstantSearchMock = $this->getMockBuilder('zcAjaxInstantSearch')
                                      ->setConstructorArgs([$mysqlInstantSearchMock])
                                      ->onlyMethods(['formatDropdownResults', 'formatPageResults'])
                                      ->getMock();

        $mySqlSearchEngineProviderMock->expects($this->exactly(2))
                                      ->method('buildSqlProductModel')
                                      ->withConsecutive([true], [false]);

        $mySqlSearchEngineProviderMock->expects($this->exactly(2))
                                      ->method('buildSqlProductName')
                                      ->withConsecutive([true], [false]);

        $mySqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlProductNameDescription')
                                      ->with(false);

        $mySqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlProductMetaKeywords');

        $mySqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlCategory');

        $mySqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlManufacturer');

        $_POST['keyword'] = 'whatever';
        $_POST['scope']   = 'dropdown';
        $ajaxInstantSearchMock->instantSearch();
    }

    public function testPageFieldsValuesCallCorrespondingSql(): void
    {
        define('INSTANT_SEARCH_FIELDS_LIST', 'name,model-exact,model-broad,meta-keywords,category,manufacturer');

        $mySqlSearchEngineProviderMock = $this->getMockBuilder(MySqlSearchEngineProvider::class)
                                              ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                              ->onlyMethods([
                                                  'execQuery', 'buildSqlProductModel', 'buildSqlProductName',
                                                  'buildSqlProductNameDescription', 'buildSqlProductMetaKeywords',
                                                  'buildSqlCategory', 'buildSqlManufacturer'
                                              ])
                                              ->getMock();

        $mysqlInstantSearchMock = $this->getMockBuilder(MySqlInstantSearch::class)
                                       ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                       ->onlyMethods(['getSearchEngineProvider', 'addEntryToSearchLog'])
                                       ->getMock();
        $mysqlInstantSearchMock->method('getSearchEngineProvider')
                               ->willReturn($mySqlSearchEngineProviderMock);

        $ajaxInstantSearchMock = $this->getMockBuilder('zcAjaxInstantSearch')
                                      ->setConstructorArgs([$mysqlInstantSearchMock])
                                      ->onlyMethods(['formatDropdownResults', 'formatPageResults'])
                                      ->getMock();

        $mySqlSearchEngineProviderMock->expects($this->exactly(2))
                                      ->method('buildSqlProductModel')
                                      ->withConsecutive([true], [false]);

        $mySqlSearchEngineProviderMock->expects($this->exactly(2))
                                      ->method('buildSqlProductName')
                                      ->withConsecutive([true], [false]);

        $mySqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlProductNameDescription')
                                      ->with(false);

        $mySqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlProductMetaKeywords');

        // category should be ignored when scope is page
        $mySqlSearchEngineProviderMock->expects($this->never())
                                      ->method('buildSqlCategory');

        // manufacturer should be ignored when scope is page
        $mySqlSearchEngineProviderMock->expects($this->never())
                                      ->method('buildSqlManufacturer');

        $_POST['keyword'] = 'whatever';
        $_POST['scope']   = 'page';
        $ajaxInstantSearchMock->instantSearch();
    }

    public function testNameDescriptionFieldCallCorrespondingSql(): void
    {
        define('INSTANT_SEARCH_FIELDS_LIST', 'name-description');

        $mySqlSearchEngineProviderMock = $this->getMockBuilder(MySqlSearchEngineProvider::class)
                                              ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                              ->onlyMethods(['execQuery', 'buildSqlProductNameDescription', 'buildSqlProductName'])
                                              ->getMock();

        $mysqlInstantSearchMock = $this->getMockBuilder(MySqlInstantSearch::class)
                                       ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                       ->onlyMethods(['getSearchEngineProvider', 'addEntryToSearchLog'])
                                       ->getMock();
        $mysqlInstantSearchMock->method('getSearchEngineProvider')
                               ->willReturn($mySqlSearchEngineProviderMock);

        $ajaxInstantSearchMock = $this->getMockBuilder('zcAjaxInstantSearch')
                                      ->setConstructorArgs([$mysqlInstantSearchMock])
                                      ->onlyMethods(['formatDropdownResults', 'formatPageResults'])
                                      ->getMock();


        $mySqlSearchEngineProviderMock->expects($this->exactly(2))
                                      ->method('buildSqlProductName')
                                      ->withConsecutive([true], [false]);

        $mySqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlProductNameDescription')
                                      ->with(true);

        $_POST['keyword'] = 'whatever';
        $_POST['scope']   = 'dropdown';
        $ajaxInstantSearchMock->instantSearch();
    }
}

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

use Zencart\Plugins\Catalog\InstantSearch\MysqlInstantSearch;
use Zencart\Plugins\Catalog\InstantSearch\SearchEngineProviders\MysqlSearchEngineProvider;

class MysqlInstantSearchUnitTest extends InstantSearchUnitTest
{
    public function setUp(): void
    {
        parent::setUp();

        define('INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION', 'true');
    }

    public function testDropdownFieldsValuesCallCorrespondingSql(): void
    {
        define('INSTANT_SEARCH_FIELDS_LIST', 'name,model-exact,model-broad,meta-keywords,category,manufacturer');

        $mysqlSearchEngineProviderMock = $this->getMockBuilder(MysqlSearchEngineProvider::class)
                                              ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                              ->onlyMethods([
                                                  'execQuery', 'buildSqlProductModel', 'buildSqlProductName',
                                                  'buildSqlProductNameDescription', 'buildSqlProductMetaKeywords',
                                                  'buildSqlCategory', 'buildSqlManufacturer'
                                              ])
                                              ->getMock();

        $mysqlInstantSearchMock = $this->getMockBuilder(MysqlInstantSearch::class)
                                       ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                       ->onlyMethods(['getSearchEngineProvider', 'addEntryToSearchLog'])
                                       ->getMock();
        $mysqlInstantSearchMock->method('getSearchEngineProvider')
                               ->willReturn($mysqlSearchEngineProviderMock);

        $ajaxInstantSearchMock = $this->getMockBuilder('zcAjaxInstantSearch')
                                      ->setConstructorArgs([$mysqlInstantSearchMock])
                                      ->onlyMethods(['formatDropdownResults', 'formatPageResults'])
                                      ->getMock();

        $mysqlSearchEngineProviderMock->expects($this->exactly(2))
                                      ->method('buildSqlProductModel')
                                      ->withConsecutive([true], [false]);

        $mysqlSearchEngineProviderMock->expects($this->exactly(2))
                                      ->method('buildSqlProductName')
                                      ->withConsecutive([true], [false]);

        $mysqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlProductNameDescription')
                                      ->with(false);

        $mysqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlProductMetaKeywords');

        $mysqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlCategory');

        $mysqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlManufacturer');

        $_POST['keyword'] = 'whatever';
        $_POST['scope']   = 'dropdown';
        $ajaxInstantSearchMock->instantSearch();
    }

    public function testPageFieldsValuesCallCorrespondingSql(): void
    {
        define('INSTANT_SEARCH_FIELDS_LIST', 'name,model-exact,model-broad,meta-keywords,category,manufacturer');

        $mysqlSearchEngineProviderMock = $this->getMockBuilder(MysqlSearchEngineProvider::class)
                                              ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                              ->onlyMethods([
                                                  'execQuery', 'buildSqlProductModel', 'buildSqlProductName',
                                                  'buildSqlProductNameDescription', 'buildSqlProductMetaKeywords',
                                                  'buildSqlCategory', 'buildSqlManufacturer'
                                              ])
                                              ->getMock();

        $mysqlInstantSearchMock = $this->getMockBuilder(MysqlInstantSearch::class)
                                       ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                       ->onlyMethods(['getSearchEngineProvider', 'addEntryToSearchLog'])
                                       ->getMock();
        $mysqlInstantSearchMock->method('getSearchEngineProvider')
                               ->willReturn($mysqlSearchEngineProviderMock);

        $ajaxInstantSearchMock = $this->getMockBuilder('zcAjaxInstantSearch')
                                      ->setConstructorArgs([$mysqlInstantSearchMock])
                                      ->onlyMethods(['formatDropdownResults', 'formatPageResults'])
                                      ->getMock();

        $mysqlSearchEngineProviderMock->expects($this->exactly(2))
                                      ->method('buildSqlProductModel')
                                      ->withConsecutive([true], [false]);

        $mysqlSearchEngineProviderMock->expects($this->exactly(2))
                                      ->method('buildSqlProductName')
                                      ->withConsecutive([true], [false]);

        $mysqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlProductNameDescription')
                                      ->with(false);

        $mysqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlProductMetaKeywords');

        // category should be ignored when scope is page
        $mysqlSearchEngineProviderMock->expects($this->never())
                                      ->method('buildSqlCategory');

        // manufacturer should be ignored when scope is page
        $mysqlSearchEngineProviderMock->expects($this->never())
                                      ->method('buildSqlManufacturer');

        $_POST['keyword'] = 'whatever';
        $_POST['scope']   = 'page';
        $ajaxInstantSearchMock->instantSearch();
    }

    public function testNameDescriptionFieldCallCorrespondingSql(): void
    {
        define('INSTANT_SEARCH_FIELDS_LIST', 'name-description');

        $mysqlSearchEngineProviderMock = $this->getMockBuilder(MysqlSearchEngineProvider::class)
                                              ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                              ->onlyMethods(['execQuery', 'buildSqlProductNameDescription', 'buildSqlProductName'])
                                              ->getMock();

        $mysqlInstantSearchMock = $this->getMockBuilder(MysqlInstantSearch::class)
                                       ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                       ->onlyMethods(['getSearchEngineProvider', 'addEntryToSearchLog'])
                                       ->getMock();
        $mysqlInstantSearchMock->method('getSearchEngineProvider')
                               ->willReturn($mysqlSearchEngineProviderMock);

        $ajaxInstantSearchMock = $this->getMockBuilder('zcAjaxInstantSearch')
                                      ->setConstructorArgs([$mysqlInstantSearchMock])
                                      ->onlyMethods(['formatDropdownResults', 'formatPageResults'])
                                      ->getMock();


        $mysqlSearchEngineProviderMock->expects($this->exactly(2))
                                      ->method('buildSqlProductName')
                                      ->withConsecutive([true], [false]);

        $mysqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlProductNameDescription')
                                      ->with(true);

        $_POST['keyword'] = 'whatever';
        $_POST['scope']   = 'dropdown';
        $ajaxInstantSearchMock->instantSearch();
    }
}

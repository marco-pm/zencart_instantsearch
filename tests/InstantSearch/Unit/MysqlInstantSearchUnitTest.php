<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  4.0.2
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
        define('INSTANT_SEARCH_PRODUCT_FIELDS_LIST', 'name,model-exact,model-broad,meta-keywords,category,manufacturer');
        define('INSTANT_SEARCH_DROPDOWN_MAX_CATEGORIES', '2');
        define('INSTANT_SEARCH_DROPDOWN_MAX_MANUFACTURERS', '2');

        $mysqlSearchEngineProviderMock = $this->getMockBuilder(MysqlSearchEngineProvider::class)
                                              ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                              ->onlyMethods([
                                                  'searchProducts', 'buildSqlProductModel', 'buildSqlProductName',
                                                  'buildSqlProductNameDescription', 'buildSqlProductMetaKeywords',
                                                  'buildSqlProductCategory', 'buildSqlProductManufacturer',
                                                  'searchCategories', 'searchManufacturers'
                                              ])
                                              ->getMock();

        $mysqlInstantSearchMock = $this->getMockBuilder(MysqlInstantSearch::class)
                                       ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true', $mysqlSearchEngineProviderMock])
                                       ->onlyMethods([
                                           'getSearchEngineProvider', 'addEntryToSearchLog'
                                       ])
                                       ->getMock();
        $mysqlInstantSearchMock->method('getSearchEngineProvider')
                               ->willReturn($mysqlSearchEngineProviderMock);
        // This is necessary too, because the constructor of $mysqlSearchEngineProviderMock is calling the
        // original implementation of getSearchEngineProvider()
        $mysqlInstantSearchMock->setSearchEngineProvider($mysqlSearchEngineProviderMock);

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
                                      ->method('buildSqlProductCategory');

        $mysqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlProductManufacturer');

        $mysqlSearchEngineProviderMock->expects($this->once())
                                      ->method('searchCategories');

        $mysqlSearchEngineProviderMock->expects($this->once())
                                      ->method('searchManufacturers');

        $_POST['keyword'] = 'whatever';
        $_POST['scope']   = 'dropdown';
        $ajaxInstantSearchMock->instantSearch();
    }

    public function testPageFieldsValuesCallCorrespondingSql(): void
    {
        define('INSTANT_SEARCH_PRODUCT_FIELDS_LIST', 'name,model-exact,model-broad,meta-keywords,category,manufacturer');
        define('INSTANT_SEARCH_DROPDOWN_MAX_CATEGORIES', '2');
        define('INSTANT_SEARCH_DROPDOWN_MAX_MANUFACTURERS', '2');

        $mysqlSearchEngineProviderMock = $this->getMockBuilder(MysqlSearchEngineProvider::class)
                                              ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                              ->onlyMethods([
                                                  'searchProducts', 'buildSqlProductModel', 'buildSqlProductName',
                                                  'buildSqlProductNameDescription', 'buildSqlProductMetaKeywords',
                                                  'buildSqlProductCategory', 'buildSqlProductManufacturer',
                                                  'searchCategories', 'searchManufacturers'
                                              ])
                                              ->getMock();

        $mysqlInstantSearchMock = $this->getMockBuilder(MysqlInstantSearch::class)
                                       ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true', $mysqlSearchEngineProviderMock])
                                       ->onlyMethods([
                                           'getSearchEngineProvider', 'addEntryToSearchLog'
                                       ])
                                       ->getMock();
        $mysqlInstantSearchMock->method('getSearchEngineProvider')
                               ->willReturn($mysqlSearchEngineProviderMock);
        $mysqlInstantSearchMock->setSearchEngineProvider($mysqlSearchEngineProviderMock);

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
                                      ->method('buildSqlProductCategory');

        $mysqlSearchEngineProviderMock->expects($this->once())
                                      ->method('buildSqlProductManufacturer');

        // categories should be ignored when scope is page
        $mysqlSearchEngineProviderMock->expects($this->never())
                                      ->method('searchCategories');

        // manufacturer should be ignored when scope is page
        $mysqlSearchEngineProviderMock->expects($this->never())
                                      ->method('searchManufacturers');

        $_POST['keyword'] = 'whatever';
        $_POST['scope']   = 'page';
        $ajaxInstantSearchMock->instantSearch();
    }

    public function testNameDescriptionFieldCallCorrespondingSql(): void
    {
        define('INSTANT_SEARCH_PRODUCT_FIELDS_LIST', 'name-description');
        define('INSTANT_SEARCH_DROPDOWN_MAX_CATEGORIES', '2');
        define('INSTANT_SEARCH_DROPDOWN_MAX_MANUFACTURERS', '2');

        $mysqlSearchEngineProviderMock = $this->getMockBuilder(MysqlSearchEngineProvider::class)
                                              ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                              ->onlyMethods([
                                                  'searchProducts', 'buildSqlProductNameDescription', 'buildSqlProductName',
                                                  'searchCategories', 'searchManufacturers'
                                              ])
                                              ->getMock();

        $mysqlInstantSearchMock = $this->getMockBuilder(MysqlInstantSearch::class)
                                       ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                       ->onlyMethods([
                                           'getSearchEngineProvider', 'addEntryToSearchLog',
                                       ])
                                       ->getMock();
        $mysqlInstantSearchMock->method('getSearchEngineProvider')
                               ->willReturn($mysqlSearchEngineProviderMock);
        $mysqlInstantSearchMock->setSearchEngineProvider($mysqlSearchEngineProviderMock);

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

    public function testCategoriesAndManufacturersIgnoredWhenLimitIsZero(): void
    {
        define('INSTANT_SEARCH_PRODUCT_FIELDS_LIST', 'name');
        define('INSTANT_SEARCH_DROPDOWN_MAX_CATEGORIES', '0');
        define('INSTANT_SEARCH_DROPDOWN_MAX_MANUFACTURERS', '0');

        $mysqlSearchEngineProviderMock = $this->getMockBuilder(MysqlSearchEngineProvider::class)
                                              ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true'])
                                              ->onlyMethods([
                                                  'searchProducts', 'buildSqlProductName', 'buildSqlProductNameDescription',
                                                  'buildSqlProductCategory', 'buildSqlProductManufacturer',
                                                  'searchCategories', 'searchManufacturers'
                                              ])
                                              ->getMock();

        $mysqlInstantSearchMock = $this->getMockBuilder(MysqlInstantSearch::class)
                                       ->setConstructorArgs([INSTANT_SEARCH_MYSQL_USE_QUERY_EXPANSION === 'true', $mysqlSearchEngineProviderMock])
                                       ->onlyMethods([
                                           'getSearchEngineProvider', 'addEntryToSearchLog'
                                       ])
                                       ->getMock();
        $mysqlInstantSearchMock->method('getSearchEngineProvider')
                               ->willReturn($mysqlSearchEngineProviderMock);
        $mysqlInstantSearchMock->setSearchEngineProvider($mysqlSearchEngineProviderMock);

        $mysqlSearchEngineProviderMock->expects($this->never())
                                      ->method('searchCategories');

        $mysqlSearchEngineProviderMock->expects($this->never())
                                      ->method('searchManufacturers');
    }
}

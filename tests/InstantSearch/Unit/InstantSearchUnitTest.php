<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  4.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

namespace Tests\InstantSearch\Unit;

use Composer\Autoload\ClassLoader;
use Tests\Support\zcUnitTestCase;
use Zencart\Plugins\Catalog\InstantSearch\InstantSearch;
use Zencart\Plugins\Catalog\InstantSearch\SearchEngineProviders\SearchEngineProviderInterface;

abstract class InstantSearchUnitTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $classLoader = new ClassLoader();
        $classLoader->addPsr4("Zencart\\Plugins\\Catalog\\InstantSearch\\", "zc_plugins/InstantSearch/v4.0.0/classes/", true);
        $classLoader->register();

        require DIR_FS_CATALOG . DIR_WS_CLASSES . 'ajax/zcAjaxInstantSearch.php';

        define('INSTANT_SEARCH_DROPDOWN_MIN_WORDSEARCH_LENGTH', '3');
        define('INSTANT_SEARCH_DROPDOWN_MAX_WORDSEARCH_LENGTH', '30');
        define('INSTANT_SEARCH_DROPDOWN_MAX_PRODUCTS', '5');
        define('INSTANT_SEARCH_DROPDOWN_ADD_LOG_ENTRY', 'true');
        define('TEXT_SEARCH_LOG_ENTRY_DROPDOWN_PREFIX', '');
        define('INSTANT_SEARCH_PAGE_RESULTS_PER_PAGE', '5');
        define('INSTANT_SEARCH_PAGE_RESULTS_PER_SCREEN', '500');
        define('INSTANT_SEARCH_PAGE_ADD_LOG_ENTRY', 'true');
        define('TEXT_SEARCH_LOG_ENTRY_PAGE_PREFIX', '');
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
     */
    public function testKeywordReturnsEmpty(string $keyword, string $expectedOutput, string $scope = 'dropdown'): void
    {
        $instantSearchMock = $this->createMock(InstantSearch::class);

        $ajaxInstantSearchMock = $this->getMockBuilder('zcAjaxInstantSearch')
                                      ->setConstructorArgs([$instantSearchMock])
                                      ->onlyMethods(['formatDropdownResults', 'formatPageResults'])
                                      ->getMock();

        $instantSearchMock->expects($this->never())
                          ->method('runSearch');

        $ajaxInstantSearchMock->expects($this->never())
                              ->method('formatDropdownResults');

        $_POST['keyword'] = $keyword;
        $_POST['scope']   = $scope;
        $htmlOutput = $ajaxInstantSearchMock->instantSearch();
        $this->assertEquals($expectedOutput, $htmlOutput);
    }

    public function keywordProvider(): array
    {
        return [
            'empty (dropdown)'                    => ['', '{"count":0,"results":[]}'],
            'spaces only (dropdown)'              => ['            ', '{"count":0,"results":[]}'],
            'html tags (dropdown)'                => ['<p></p>', '{"count":0,"results":[]}'],
            'space as html entity (dropdown)'     => ['&nbsp;&nbsp;&nbsp;&nbsp;', '{"count":0,"results":[]}'],
            'length less than minimum (dropdown)' => ['ab', '{"count":0,"results":[]}'],
            'length more than maximum (dropdown)' => ['Lorem ipsum dolor sit amet erat justo invidunt odio et clita molestie eirmod dolore', '{"count":0,"results":[]}'],
            'empty (page)'                        => ['', '{"count":0,"results":[]}', 'page'],
            'spaces only (page)'                  => ['            ', '{"count":0,"results":[]}', 'page'],
            'html tags (page)'                    => ['<p></p>', '{"count":0,"results":[]}', 'page'],
            'space as html entity (page)'         => ['&nbsp;&nbsp;&nbsp;&nbsp;', '{"count":0,"results":[]}', 'page'],
        ];
    }


    public function testSaveSearchLogIfEnabled(): void
    {
        $searchEngineProviderMock = $this->getMockForAbstractClass(SearchEngineProviderInterface::class);

        $instantSearchMock = $this->getMockForAbstractClass(InstantSearch::class,
            mockedMethods: ['addEntryToSearchLog', 'searchCategories', 'searchManufacturers']);
        $instantSearchMock->method('getSearchEngineProvider')
                          ->willReturn($searchEngineProviderMock);

        $ajaxInstantSearchMock = $this->getMockBuilder('zcAjaxInstantSearch')
                                      ->setConstructorArgs([$instantSearchMock])
                                      ->onlyMethods(['formatDropdownResults', 'formatPageResults'])
                                      ->getMock();

        $instantSearchMock->expects($this->exactly(2))
                          ->method('addEntryToSearchLog');

        define('INSTANT_SEARCH_PRODUCT_FIELDS_LIST', 'whatever');
        define('INSTANT_SEARCH_DROPDOWN_MAX_CATEGORIES', '2');
        define('INSTANT_SEARCH_DROPDOWN_MAX_MANUFACTURERS', '2');
        $_POST['scope']   = 'dropdown';
        $_POST['keyword'] = 'whatever';
        $ajaxInstantSearchMock->instantSearch();

        $_POST['scope']   = 'dropdown';
        $ajaxInstantSearchMock->instantSearch();
    }

    public function testDontSaveSearchLogIfResultPageGreaterThan1(): void
    {
        $searchEngineProviderMock = $this->getMockForAbstractClass(SearchEngineProviderInterface::class);

        $instantSearchMock = $this->getMockForAbstractClass(InstantSearch::class,
            mockedMethods: ['addEntryToSearchLog', 'searchCategories', 'searchManufacturers']);
        $instantSearchMock->method('getSearchEngineProvider')
                          ->willReturn($searchEngineProviderMock);

        $ajaxInstantSearchMock = $this->getMockBuilder('zcAjaxInstantSearch')
                                      ->setConstructorArgs([$instantSearchMock])
                                      ->onlyMethods(['formatDropdownResults', 'formatPageResults'])
                                      ->getMock();

        $instantSearchMock->expects($this->never())
                          ->method('addEntryToSearchLog');

        define('INSTANT_SEARCH_PRODUCT_FIELDS_LIST', 'whatever');
        define('INSTANT_SEARCH_DROPDOWN_MAX_CATEGORIES', '2');
        define('INSTANT_SEARCH_DROPDOWN_MAX_MANUFACTURERS', '2');
        $_POST['scope']      = 'page';
        $_POST['keyword']    = 'whatever';
        $_POST['resultPage'] = '2';
        $ajaxInstantSearchMock->instantSearch();
    }

    public function testEmptyOutputAndZeroCountWhenNoResults(): void
    {
        $searchEngineProviderMock = $this->getMockForAbstractClass(SearchEngineProviderInterface::class);

        $instantSearchMock = $this->getMockForAbstractClass(InstantSearch::class,
            mockedMethods: ['addEntryToSearchLog', 'searchCategories', 'searchManufacturers']);
        $instantSearchMock->method('getSearchEngineProvider')
                          ->willReturn($searchEngineProviderMock);

        $ajaxInstantSearchMock = $this->getMockBuilder('zcAjaxInstantSearch')
                                      ->setConstructorArgs([$instantSearchMock])
                                      ->onlyMethods(['formatDropdownResults', 'formatPageResults'])
                                      ->getMock();

        define('INSTANT_SEARCH_PRODUCT_FIELDS_LIST', 'name-description');
        define('INSTANT_SEARCH_DROPDOWN_MAX_CATEGORIES', '2');
        define('INSTANT_SEARCH_DROPDOWN_MAX_MANUFACTURERS', '2');
        $_POST['scope']      = 'dropdown';
        $_POST['keyword']    = 'whatever';
        $htmlOutput = $ajaxInstantSearchMock->instantSearch();

        $this->assertEquals('{"count":0,"results":""}', $htmlOutput);
    }
}

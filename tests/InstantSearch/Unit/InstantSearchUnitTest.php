<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

declare(strict_types=1);

namespace Tests\InstantSearch\Unit;

use Composer\Autoload\ClassLoader;
use Tests\Support\zcUnitTestCase;

abstract class InstantSearchUnitTest extends zcUnitTestCase
{
    public function __construct(
        ?string $name = null,
        array $data = [],
        $dataName = '',
        public string $instantSearchClassName = ''
    ) {
        parent::__construct($name, $data, $dataName);
    }

    public function setUp(): void
    {
        parent::setUp();

        $classLoader = new ClassLoader();
        $classLoader->addPsr4("Zencart\\Plugins\\Catalog\\InstantSearch\\", "zc_plugins/InstantSearch/v3.0.0/classes/", true);
        $classLoader->register();

        define('PRODUCT_LIST_MODEL', '0');
        define('PRODUCT_LIST_NAME', '1');
        define('PRODUCT_LIST_MANUFACTURER', '2');
        define('PRODUCT_LIST_PRICE', '3');
        define('PRODUCT_LIST_QUANTITY', '4');
        define('PRODUCT_LIST_WEIGHT', '5');
        define('PRODUCT_LIST_IMAGE', '6');
        define('TEXT_INSTANT_SEARCH_CONFIGURATION_ERROR', 'Configuration error');
    }

    abstract public function keywordProvider(): array;

    /**
     * @dataProvider keywordProvider
     */
    public function testKeywordReturnsEmpty(string $keyword, string $expectedOutput): void
    {
        $instantSearchMock = $this->getMockBuilder($this->instantSearchClassName)
                                  ->onlyMethods(['searchDb', 'formatResults', 'addEntryToSearchLog'])
                                  ->getMock();

        $instantSearchMock->expects($this->never())
                          ->method('searchDb');

        $_POST['keyword'] = $keyword;
        $htmlOutput = $instantSearchMock->instantSearch();
        $this->assertEquals($expectedOutput, $htmlOutput);
    }

    public function testInvalidFieldNameSettingReturnsError(): void
    {
        define('INSTANT_SEARCH_DROPDOWN_FIELDS_LIST', 'gibberish,name-description,model-broad');
        define('INSTANT_SEARCH_PAGE_FIELDS_LIST', 'gibberish,name-description,model-broad');

        $instantSearchMock = $this->getMockBuilder($this->instantSearchClassName)
                                  ->onlyMethods(['execQuery', 'formatResults', 'addEntryToSearchLog'])
                                  ->getMock();

        $_POST['keyword'] = 'whatever';
        $htmlOutput = $instantSearchMock->instantSearch();
        $this->assertStringContainsString(TEXT_INSTANT_SEARCH_CONFIGURATION_ERROR, $htmlOutput);
    }

    public function testCommonFieldsValuesCallCorrespondingSql(bool $useQueryExpansion = true): void
    {
        define('INSTANT_SEARCH_DROPDOWN_FIELDS_LIST', 'name,model-exact,model-broad');
        define('INSTANT_SEARCH_PAGE_FIELDS_LIST', 'name,model-exact,model-broad');

        $instantSearchMock = $this->getMockBuilder($this->instantSearchClassName)
                                  ->onlyMethods([
                                      'execQuery', 'formatResults', 'addEntryToSearchLog',
                                      'buildSqlProductModel', 'buildSqlProductName', 'buildSqlProductNameDescriptionMatch'
                                  ])
                                  ->getMock();

        $_POST['keyword'] = 'whatever';

        $instantSearchMock->expects($this->exactly(2))
                          ->method('buildSqlProductModel')
                          ->withConsecutive([true], [false]);

        $instantSearchMock->expects($this->exactly(2))
                          ->method('buildSqlProductName')
                          ->withConsecutive([true], [false]);

        $instantSearchMock->expects($this->once())
                          ->method('buildSqlProductNameDescriptionMatch')
                          ->with(false, $useQueryExpansion);

        $instantSearchMock->instantSearch();
    }

    public function testNameWithDescriptionFieldCallsCorrespondingSql(bool $useQueryExpansion = true): void
    {
        define('INSTANT_SEARCH_DROPDOWN_FIELDS_LIST', 'name-description');
        define('INSTANT_SEARCH_PAGE_FIELDS_LIST', 'name-description');

        $instantSearchMock = $this->getMockBuilder($this->instantSearchClassName)
                                  ->onlyMethods(['execQuery', 'formatResults', 'addEntryToSearchLog', 'buildSqlProductNameDescriptionMatch'])
                                  ->getMock();

        $_POST['keyword'] = 'whatever';

        $instantSearchMock->expects($this->once())
                          ->method('buildSqlProductNameDescriptionMatch')
                          ->with(true, $useQueryExpansion);

        $instantSearchMock->instantSearch();
    }

    public function testSaveSearchLogIfEnabled(): void
    {
        define('INSTANT_SEARCH_DROPDOWN_FIELDS_LIST', 'name-description');
        define('INSTANT_SEARCH_PAGE_FIELDS_LIST', 'name-description');

        $instantSearchMock = $this->getMockBuilder($this->instantSearchClassName)
                                  ->onlyMethods(['searchDb', 'formatResults', 'addEntryToSearchLog'])
                                  ->getMock();

        $_POST['keyword'] = 'whatever';

        $instantSearchMock->expects($this->once())
                     ->method('addEntryToSearchLog');

        $instantSearchMock->instantSearch();
    }

    public function testEmptyOutputAndZeroCountWhenNoResults(): void
    {
        define('INSTANT_SEARCH_DROPDOWN_FIELDS_LIST', 'name-description');

        $instantSearchMock = $this->getMockBuilder($this->instantSearchClassName)
                                  ->onlyMethods(['searchDb', 'formatResults', 'addEntryToSearchLog'])
                                  ->getMock();

        $_POST['keyword'] = 'whatever';

        $htmlOutput = $instantSearchMock->instantSearch();

        $this->assertEquals('{"count":0,"results":""}', $htmlOutput);
    }
}

<?php
declare(strict_types=1);

namespace Tests\InstantSearch\Unit;

use zcAjaxInstantSearchPage;

class InstantSearchPageUnitTest extends InstantSearchUnitTest
{
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName, zcAjaxInstantSearchPage::class);
    }

    public function setUp(): void
    {
        parent::setUp();

        define('INSTANT_SEARCH_PAGE_MAX_RESULTS', '5');
        define('INSTANT_SEARCH_PAGE_USE_QUERY_EXPANSION', 'true');
        define('INSTANT_SEARCH_PAGE_ADD_LOG_ENTRY', 'true');
        define('INSTANT_SEARCH_PAGE_RESULTS_PER_PAGE', '5');
        define('TEXT_SEARCH_LOG_ENTRY_PAGE_PREFIX', '');
    }

    public function keywordProvider(): array
    {
        return [
            'empty'                => ['', ''],
            'spaces only'          => ['            ', ''],
            'html tags'            => ['<p></p>', ''],
            'space as html entity' => ['&nbsp;&nbsp;&nbsp;&nbsp;', ''],
        ];
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testNotAllowedFieldNameSettingReturnsError(): void
    {
        define('INSTANT_SEARCH_PAGE_FIELDS_LIST', 'category,name-description,model-broad');

        $pageMock = $this->getMockBuilder($this->instantSearchClassName)
                         ->onlyMethods(['execQuery', 'formatResults', 'addEntryToSearchLog'])
                         ->getMock();

        $_POST['keyword'] = 'whatever';
        $htmlOutput = $pageMock->instantSearch();
        $this->assertStringContainsString(TEXT_INSTANT_SEARCH_CONFIGURATION_ERROR, $htmlOutput);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCommonFieldsValuesCallCorrespondingSql(bool $useQueryExpansion = true): void {
        parent::testCommonFieldsValuesCallCorrespondingSql(INSTANT_SEARCH_PAGE_USE_QUERY_EXPANSION === 'true');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testNameWithDescriptionFieldCallsCorrespondingSql(bool $useQueryExpansion = true): void {
        parent::testCommonFieldsValuesCallCorrespondingSql(INSTANT_SEARCH_PAGE_USE_QUERY_EXPANSION === 'true');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDontSaveSearchLogIfResultPageGreaterThan1(): void
    {
        define('INSTANT_SEARCH_PAGE_FIELDS_LIST', 'name-description');

        $pageMock = $this->getMockBuilder($this->instantSearchClassName)
                         ->onlyMethods(['searchDb', 'formatResults', 'addEntryToSearchLog'])
                         ->getMock();

        $_POST['keyword'] = 'whatever';
        $_POST['resultPage'] = '2';

        $pageMock->expects($this->never())
                 ->method('addEntryToSearchLog');

        $pageMock->instantSearch();
    }
}

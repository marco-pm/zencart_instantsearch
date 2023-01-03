/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

const instantSearchParams                    = new URLSearchParams(window.location.search)
const instantSearchKeyword                   = instantSearchParams.get('keyword') ?? '';
const instantSearchAlphaFilterId             = instantSearchParams.get('alpha_filter_id') ?? '';
const instantSearchSort                      = instantSearchParams.get('sort') ?? '20a';
const instantSearchEndResultsSelector        = '#instantSearchResults__end';
const instantSearchListingDivSelector        = '#productListing';
const instantSearchLoadingDivSelector        = '#instantSearchResults__loadingWrapper';
const instantSearchFilterDivSelector         = '#instantSearchResults__sorterRow';
const instantSearchNoResultsFoundDivSelector = '#instantSearchResults__noResultsFoundWrapper';
let instantSearchResultPage                  = instantSearchParams.get('page') ?? 1;
let instantSearchIsLoadingResults            = false;
let instantSearchResultPageIsLast            = false;
let instantSearchPreviousResultCount         = 0;

document.addEventListener('DOMContentLoaded', async () => {
    if (!instantSearchIsLoadingResults) {
        await loadResults();
    }

    const observer = new IntersectionObserver(async function(entries) {
        if (entries[0].isIntersecting === true &&
            !instantSearchIsLoadingResults &&
            instantSearchResultPage > 1 &&
            !instantSearchResultPageIsLast
        ) {
            await loadResults();
        }
    }, { threshold: [0] });
    observer.observe(document.querySelector(instantSearchEndResultsSelector));
});

async function loadResults() {
    instantSearchIsLoadingResults = true;
    document.querySelector(instantSearchLoadingDivSelector).style.display = 'block';

    const data = new FormData();
    data.append('keyword', instantSearchKeyword);
    data.append('resultPage', instantSearchResultPage);
    data.append('alpha_filter_id', instantSearchAlphaFilterId);
    data.append('sort', instantSearchSort);
    data.append('securityToken', instantSearchResultSecurityToken);

    const response = await fetch('ajax.php?act=ajaxInstantSearchPage&method=instantSearch', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: data,
    });
    const responseData = await response.json();
    const responseDataJson = JSON.parse(responseData);

    document.querySelector(instantSearchFilterDivSelector).style.display = 'block';
    if (responseDataJson.results.length > 0 && responseDataJson.count !== instantSearchPreviousResultCount) {
        document.querySelector(instantSearchListingDivSelector).innerHTML = responseDataJson.results;
        instantSearchPreviousResultCount = responseDataJson.count;

        // Update URL page parameter
        const url = new URL(window.document.URL);
        url.searchParams.set('page', instantSearchResultPage);
        window.history.replaceState(null, '', url.toString());

        instantSearchResultPage++;
        instantSearchIsLoadingResults = false;
    } else {
        // The HTML response is empty or the number of products is the same as before,
        // so we have reached the end of results
        instantSearchResultPageIsLast = true;

        if (responseDataJson.results.length === 0 || responseDataJson.count === 0) {
            document.querySelector(instantSearchNoResultsFoundDivSelector).style.display = 'block';
        }
    }
    document.querySelector(instantSearchLoadingDivSelector).style.display = 'none';
}

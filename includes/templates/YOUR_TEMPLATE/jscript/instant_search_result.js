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
const instantSearchFilterDivSelector         = '.instantSearchResults__sorterRow';
const instantSearchNoResultsFoundDivSelector = '#instantSearchResults__noResultsFoundWrapper';
let instantSearchResultPage                  = instantSearchParams.get('page') ?? 1;
let instantSearchIsLoadingResults            = false;
let instantSearchResultPageIsLast            = false;
let instantSearchPreviousResultCount         = 0;

// Fetch the first page of results while the page is loading, then display it as soon as the DOM is loaded
(async () => {
    if (!instantSearchIsLoadingResults) {
        const jsonResults = await loadResults();

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            await displayResults(jsonResults);
            await fillViewportWithResults();
        } else {
            document.addEventListener('DOMContentLoaded', async () => {
                await displayResults(jsonResults);
                await fillViewportWithResults();
            });
        }
    }
})();

document.addEventListener('DOMContentLoaded', async () => {
    // When the user reaches the bottom of the current results page, load a new one
    const observer = new IntersectionObserver(async (entries) => {
        if (entries[0].isIntersecting === true &&
            !instantSearchIsLoadingResults &&
            instantSearchResultPage > 1 &&
            !instantSearchResultPageIsLast
        ) {
            document.querySelector(instantSearchLoadingDivSelector).style.display = 'block';
            const jsonResults = await loadResults();
            await displayResults(jsonResults);
        }
    }, { threshold: [0] });
    observer.observe(document.querySelector(instantSearchEndResultsSelector));
});

// If, on page load, the number of displayed results in the first page doesn't fill the viewport's height,
// load a new result page until it's filled (in order for the IntersectionObserver to be triggered on scroll)
async function fillViewportWithResults() {
    const observedDivBounding = (document.querySelector(instantSearchEndResultsSelector)).getBoundingClientRect();
    const isObservedDivInViewport =
        observedDivBounding.top >= 0 &&
        observedDivBounding.left >= 0 &&
        observedDivBounding.right <= (window.innerWidth || document.documentElement.clientWidth) &&
        observedDivBounding.bottom <= (window.innerHeight || document.documentElement.clientHeight);
    if (!instantSearchResultPageIsLast && isObservedDivInViewport) {
        const jsonResults = await loadResults();
        await displayResults(jsonResults);
        await fillViewportWithResults();
    }
}

async function loadResults() {
    instantSearchIsLoadingResults = true;

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
    return JSON.parse(responseData);
}

async function displayResults(jsonResults) {
    document.querySelector(instantSearchFilterDivSelector).style.display = 'block';

    if (jsonResults.results.length > 0 && jsonResults.count !== instantSearchPreviousResultCount) {
        document.querySelector(instantSearchListingDivSelector).innerHTML = jsonResults.results;
        instantSearchPreviousResultCount = jsonResults.count;

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

        if (jsonResults.results.length === 0 || jsonResults.count === 0) {
            document.querySelector(instantSearchNoResultsFoundDivSelector).style.display = 'block';
            document.querySelector(instantSearchFilterDivSelector).style.display = 'none';
        }
    }

    document.querySelector(instantSearchLoadingDivSelector).style.display = 'none';
}

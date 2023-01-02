/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

const instantSearchParams                    = new URLSearchParams(window.location.search)
const instantSearchKeyword                   = instantSearchParams.get('keyword') ?? '';
const instantSearchEndResultsSelector        = '#instantSearchResults__end';
const instantSearchListingDivSelector        = '#productListing';
const instantSearchLoadingDivSelector        = '#instantSearchResults__loadingWrapper';
const instantSearchFormFilterSelector        = '#instantSearchResultsDefault form[name=filter]';
const instantSearchNoResultsFoundDivSelector = '#instantSearchResults__noResultsFoundWrapper';
let instantSearchResultPage                  = instantSearchParams.get('page') ?? 1;
let instantSearchIsLoadingResults            = false;
let instantSearchResultPageIsLast            = false;
let instantSearchPreviousResult              = '';

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

    const response = await fetch('ajax.php?act=ajaxInstantSearchPage&method=instantSearch', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: data,
    });
    const responseData = await response.json();
    if (responseData.length > 0 && responseData !== instantSearchPreviousResult) {
        document.querySelector(instantSearchFormFilterSelector).style.display = 'block';
        document.querySelector(instantSearchListingDivSelector).innerHTML = responseData;
        instantSearchPreviousResult = responseData;

        // Update URL page parameter
        const url = new URL(window.document.URL);
        url.searchParams.set('page', instantSearchResultPage);
        window.history.replaceState(null, '', url.toString());

        instantSearchResultPage++;
        instantSearchIsLoadingResults = false;
    } else {
        // If the response HTML is empty or the same as before, it means that we have reached the end of results
        instantSearchResultPageIsLast = true;

        if (!responseData.length) {
            document.querySelector(instantSearchNoResultsFoundDivSelector).style.display = 'block';
        }
    }
    document.querySelector(instantSearchLoadingDivSelector).style.display = 'none';
}

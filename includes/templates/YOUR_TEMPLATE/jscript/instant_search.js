/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

const {slideDown, slideUp, slideToggle} = window.domSlider
const resultsContainerSelector  = 'instantSearchResultsDropdownContainer';
const instantSearchFormSelector = 'form[action*=search_result]:not([name=search]):not([name=advanced_search])';
let controller;
let instantSearchInputCurrent;
let inputTimer;

document.addEventListener('DOMContentLoaded', () => {

    if (instantSearchPageEnabled) {
        // Replace the search forms' action
        const instantSearchForms = document.querySelectorAll(instantSearchFormSelector);
        instantSearchForms.forEach(form => form.action = form.action.replace('search_result', 'instant_search_result'));

        const instantSearchFormPageInputs = document.querySelectorAll(`${instantSearchFormSelector} input[value="search_result"]`);
        instantSearchFormPageInputs.forEach(input => input.value = 'instant_search_result');

        const instantSearchFormSearchDescrInputs = document.querySelectorAll(`${instantSearchFormSelector} input[name="search_in_description"]`);
        instantSearchFormSearchDescrInputs.forEach(input => input.remove());
    }

    if (instantSearchDropdownEnabled) {
        // Add search suggestions on search inputs
        const instantSearchInputs = document.querySelectorAll(instantSearchDropdownInputSelector);

        for (let i = 0; i < instantSearchInputs.length; i++) {
            instantSearchInputs[i].setAttribute('autocomplete', 'off');

            // Hide the results container on blur
            instantSearchInputs[i].addEventListener('blur', async function () {
                await removeResultsDropdown();
            });

            ['input', 'focus'].forEach(event => instantSearchInputs[i].addEventListener(event, async function () {
                // Perform the search and shows the results dropdown
                instantSearchInputCurrent      = instantSearchInputs[i];
                const instantSearchQuery       = this.value;
                const instantSearchQueryParsed = instantSearchQuery.replace(/^\s+/, "").replace(/  +/g, ' ');

                if (instantSearchQueryParsed === "" || instantSearchQueryParsed.length < instantSearchDropdownInputMinLength) {
                    await removeResultsDropdown();
                } else {
                    if (controller) {
                        controller.abort();
                    }

                    clearTimeout(inputTimer);
                    inputTimer = setTimeout(async () => {
                        controller = new AbortController();
                        const signal = controller.signal;
                        const data = new FormData();
                        data.append('keyword', instantSearchQueryParsed);

                        try {
                            const response = await fetch('ajax.php?act=ajaxInstantSearchDropdown&method=instantSearch', {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: data,
                                signal
                            });
                            const responseData = await response.json();
                            if (responseData.length > 0 && instantSearchInputCurrent.value === instantSearchQuery) {
                                createAndPositionResultsDropdown(instantSearchInputCurrent, responseData);
                                await slideDown({
                                    element: document.querySelector(`#${resultsContainerSelector}`),
                                    slideSpeed: 200
                                });
                            } else {
                                await removeResultsDropdown();
                            }
                        } catch (e) {
                            if (e instanceof DOMException && e.name === "AbortError") {
                                // do nothing
                            } else {
                                console.error(e);
                            }
                        }
                    }, instantSearchDropdownInputWaitTime);
                }
            }));
        }
    }
});

// Create, populate and position the results container below the current input box
function createAndPositionResultsDropdown(inputBox, resultsData) {
    if (document.querySelector(`#${resultsContainerSelector}`)) {
        document.querySelector(`#${resultsContainerSelector}`).innerHTML = resultsData;
    } else {
        const resultsContainer = document.createElement('div');
        resultsContainer.innerHTML = resultsData;
        resultsContainer.setAttribute('id', resultsContainerSelector);
        resultsContainer.setAttribute('class', resultsContainerSelector);
        document.body.appendChild(resultsContainer);

        // set the min width of the container equals to the input box's
        resultsContainer.style.width = instantSearchInputCurrent.offsetWidth + 'px';
        resultsContainer.style.display = 'block';
        const containerWidth = resultsContainer.offsetWidth;
        resultsContainer.style.display = 'none';

        if (containerWidth > 250) {
            resultsContainer.classList.add('instantSearchResultsDropdownContainer--lg');
        } else {
            resultsContainer.classList.remove('instantSearchResultsDropdownContainer--lg');
        }

        const offsetInputBox  = inputBox.getBoundingClientRect();
        const inputBoxLeft    = offsetInputBox.left + window.scrollX;
        const inputBoxTop     = offsetInputBox.top + window.scrollY;
        const containerRightX = inputBoxLeft + containerWidth;
        const winWidth        = document.documentElement.clientWidth;
        let leftVal;

        if (containerRightX > winWidth) {
            // If the container overflows the screen horizontally, move it backward
            leftVal = Math.max(0, inputBoxLeft - containerRightX + winWidth);
        } else {
            leftVal = inputBoxLeft;
        }
        resultsContainer.style.left = leftVal + 'px';
        resultsContainer.style.top  = (inputBox.offsetHeight + inputBoxTop) + 'px';
    }
}

async function removeResultsDropdown() {
    const resultsContainer = document.querySelector(`#${resultsContainerSelector}`);

    if (instantSearchInputCurrent && resultsContainer) {
        await slideUp({element: resultsContainer, delay: 300, slideSpeed: 200});
        resultsContainer.remove();
    }
}

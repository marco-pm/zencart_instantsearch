/**
 * @package Instant Search Results
 * @copyright Copyright Ayoob G 2009-2011
 * @copyright Portions Copyright 2003-2006 The Zen Cart Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Instant Search 2.2.0
 */

const {slideDown, slideUp, slideToggle} = window.domSlider
const resultsContainerSelector = 'instantSearchResultsContainer';
let controller;
let inputBoxCurrent;
let inputTimer;

document.addEventListener('DOMContentLoaded', () => {
    const inputBox = document.querySelectorAll(searchBoxSelector);

    for (let i = 0; i < inputBox.length; i++) {
        inputBox[i].setAttribute('autocomplete', 'off');

        // Hide the results container on blur
        inputBox[i].addEventListener('blur', function() {
            removeResultsContainer();
        });

        // Perform the search and shows the results container on input and focus
        ['input', 'focus'].forEach(event => inputBox[i].addEventListener(event, function() {
            inputBoxCurrent = inputBox[i];

            const typedSearchWord = this.value;
            let searchWord = typedSearchWord.replace(/^\s+/, "").replace(/  +/g, ' ');

            if (searchWord === "" || searchWord.length < searchInputMinLength) {
                removeResultsContainer();
            } else {
                if (controller) {
                    controller.abort();
                }

                clearTimeout(inputTimer);
                inputTimer = setTimeout(async () => {
                    controller = new AbortController();
                    const signal = controller.signal;
                    let data = new FormData();
                    data.append('query', searchWord);

                    try {
                        const response = await fetch('ajax.php?act=ajaxInstantSearch&method=instantSearch', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: data,
                            signal
                        });
                        const responseData = await response.json();
                        if (responseData.length > 0 && inputBoxCurrent.value === typedSearchWord) {
                            createAndPositionResultsContainer(inputBoxCurrent, responseData);
                            await slideDown({element: document.querySelector(`#${resultsContainerSelector}`), slideSpeed: 200});
                        } else {
                            removeResultsContainer();
                        }
                    } catch (e) {
                        if (e instanceof DOMException && e.name === "AbortError") {
                            // do nothing
                        } else {
                            console.error(e);
                        }
                    }
                }, searchInputWaitTime);
            }
        }));
    }
});

// Create, populate and position the results container below the current input box
function createAndPositionResultsContainer(inputBox, resultsData) {
    if (document.querySelector(`#${resultsContainerSelector}`)) {
        document.querySelector(`#${resultsContainerSelector}`).innerHTML = resultsData
    } else {
        const resultsContainer = document.createElement('div');
        resultsContainer.innerHTML = resultsData;
        resultsContainer.setAttribute('id', resultsContainerSelector);
        resultsContainer.setAttribute('class', resultsContainerSelector);
        document.body.appendChild(resultsContainer);

        // set the min width of the container equals to the input box's
        resultsContainer.style.width = inputBoxCurrent.offsetWidth + 'px';
        resultsContainer.style.display = 'block';
        const containerWidth = resultsContainer.offsetWidth;
        resultsContainer.style.display = 'none';

        if (containerWidth > 250) {
            resultsContainer.classList.add('instantSearchResultsContainer--lg');
        } else {
            resultsContainer.classList.remove('instantSearchResultsContainer--lg');
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

async function removeResultsContainer() {
    const resultsContainer = document.querySelector(`#${resultsContainerSelector}`);

    if (inputBoxCurrent && resultsContainer) {
        await slideUp({element: resultsContainer, delay: 300, slideSpeed: 200});
        resultsContainer.remove();
    }
}

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
const searchBoxSelector = 'input[name="keyword"]';
let controller;
let inputBoxCurrent;
let inputTimer;

document.addEventListener('DOMContentLoaded', () => {
    const inputBox = document.querySelectorAll(searchBoxSelector);

    for (let i = 0; i < inputBox.length; i++) {
        let offset = inputBox[i].getBoundingClientRect();
        let resultsContainer = document.createElement('div');

        inputBox[i].setAttribute('autocomplete', 'off');

        // add a results container to the DOM for each search input
        resultsContainer.setAttribute('id', 'instantSearchResultsContainer' + i);
        resultsContainer.setAttribute('class', 'instantSearchResultsContainer');
        resultsContainer.style.left = offset.left + window.scrollX + 'px';
        resultsContainer.style.top = offset.top + window.scrollY + inputBox[i].clientHeight + 'px';
        document.body.appendChild(resultsContainer);

        // hide the results container on blur
        inputBox[i].addEventListener('blur', function() {
            if (inputBoxCurrent) {
                const resultsContainer = document.querySelector(`#instantSearchResultsContainer${i}`);
                slideUp({element: resultsContainer, delay: 300, slideSpeed: 200});
            }
        });

        // perform the search and shows the results container on input and focus
        ['input', 'focus'].forEach(event => inputBox[i].addEventListener(event, function() {
            inputBoxCurrent = inputBox[i];
            const resultsContainer = document.querySelector(`#instantSearchResultsContainer${i}`);
            const typedSearchWord = this.value;

            let searchWord = typedSearchWord.replace(/^\s+/, "").replace(/  +/g, ' ');
            if (searchWord === "" || searchWord.length < searchInputMinLength) {
                resultsContainer.style.display = 'none';
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
                                "X-Requested-With": "XMLHttpRequest",
                            },
                            body: data,
                            signal
                        });
                        const responseData = await response.json();
                        if (responseData.length > 0) {
                            resultsContainer.innerHTML = responseData;
                            if (inputBoxCurrent.value === typedSearchWord) {
                                autoPositionContainer(inputBoxCurrent, resultsContainer);
                                slideDown({element: resultsContainer, slideSpeed: 200});
                            }
                            resultsContainer.style.width = inputBoxCurrent.offsetWidth + 'px';
                            if (resultsContainer.offsetWidth > 250) {
                                resultsContainer.classList.add('instantSearchResultsContainer--lg');
                            } else {
                                resultsContainer.classList.remove('instantSearchResultsContainer--lg');
                            }
                        } else {
                            resultsContainer.style.display = 'none';
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

function autoPositionContainer(inputBox, container) {
    const offsetInputBox = inputBox.getBoundingClientRect();
    const inputBoxLeft = offsetInputBox.left + window.scrollX;
    const inputBoxTop = offsetInputBox.top + window.scrollY;
    const xOverflow = inputBoxLeft + container.offsetWidth;
    const winWidth = document.documentElement.clientWidth;

    let leftVal;
    if (xOverflow > winWidth) {
        let dif = xOverflow - winWidth;
        leftVal = (((inputBoxLeft - dif) < 0) ? 0 : (inputBoxLeft - dif));
    } else {
        leftVal = inputBoxLeft;
    }
    container.style.left = leftVal + 'px';
    container.style.top = (inputBox.offsetHeight + inputBoxTop) + 'px';
}

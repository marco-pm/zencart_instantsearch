/**
 * @package Instant Search Results
 * @copyright Copyright Ayoob G 2009-2011
 * @copyright Portions Copyright 2003-2006 The Zen Cart Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Instant Search 2.1.0
 */

const searchBoxSelector = 'input[name="keyword"]';
const resultsContainerSelector = '#resultsContainer';
let runningRequest = false;
let request;
let inputboxCurrent;
let inputTimer;

$(function() {
    const inputBox = $(searchBoxSelector);
    inputBox.attr('autocomplete', 'off');

    inputBox.each(function(index) {
        let offset = $(this).offset();
        $('body').append('<div id="resultsContainer' + index + '" class="resultsContainer"></div>');
        $(resultsContainerSelector + index).css('left', offset.left + 'px');
        $(resultsContainerSelector + index).css('top', ($(this).outerHeight(true) + offset.top) + 'px');
    });

    inputBox.on('blur', function() {
        if (inputboxCurrent) {
            const resultsContainer = $(`#resultsContainer${inputboxCurrent.index(searchBoxSelector)}`);
            resultsContainer.delay(300).slideUp(200);
        }
    });

    $(window).on('resize', function() {
        if (inputboxCurrent) {
            const resultsContainer = $(`#resultsContainer${inputboxCurrent.index(searchBoxSelector)}`);
            resultsContainer.hide();
        }
    });

    inputBox.on('input focus', function() {
        inputboxCurrent = $(this);
        const resultsContainer = $(`#resultsContainer${inputboxCurrent.index(searchBoxSelector)}`);
        const typedSearchWord = $(this).val();

        let searchWord = typedSearchWord.replace(/^\s+/, "").replace(/  +/g, ' ');
        if (searchWord === "" || searchWord.length < searchInputMinLength) {
            resultsContainer.hide();
        } else {
            if (runningRequest) {
                request.abort();
            }
            clearTimeout(inputTimer);
            inputTimer = setTimeout(() => {
                runningRequest = true;
                let data = new FormData();
                data.append('query', searchWord);
                request = $.ajax({
                    type: 'POST',
                    url: 'ajax.php?act=ajaxInstantSearch&method=instantSearch',
                    dataType: 'json',
                    contentType: false,
                    cache: false,
                    processData: false,
                    data: data,
                    success: function (data) {
                        if (data.length > 0) {
                            resultsContainer.html(data);
                            if (!resultsContainer.is(':visible') && $(inputboxCurrent).val() === typedSearchWord) {
                                autoPositionContainer(inputboxCurrent, resultsContainer);
                                resultsContainer.slideDown(200);
                            }
                            resultsContainer.outerWidth(inputboxCurrent.outerWidth());
                            if (resultsContainer.width() > 250) {
                                resultsContainer.addClass('resultsContainer--lg');
                            } else {
                                resultsContainer.removeClass('resultsContainer--lg');
                            }
                        } else {
                            resultsContainer.hide();
                        }
                        runningRequest = false;
                    }
                });
            }, searchInputWaitTime);
        }
    });
});

function autoPositionContainer(inputBoxCurr, resltsContainer) {
    const offsetInput = inputBoxCurr.offset();
    const overFlow = offsetInput.left + resltsContainer.outerWidth(true);
    const winWidth = $(document).width();

    let leftVal;
    if (overFlow > winWidth) {
        let dif = overFlow - winWidth;
        leftVal = (((offsetInput.left - dif) < 0) ? 0 : (offsetInput.left - dif));
    } else {
        leftVal = offsetInput.left;
    }
    resltsContainer.css('left', leftVal + 'px');
    resltsContainer.css('top', (inputBoxCurr.outerHeight(true) + offsetInput.top) + 'px');
}

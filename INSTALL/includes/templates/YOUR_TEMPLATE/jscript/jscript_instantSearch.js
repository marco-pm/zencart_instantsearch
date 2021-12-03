/**
 * @package Instant Search Results
 * @copyright Copyright Ayoob G 2009-2011
 * @copyright Portions Copyright 2003-2006 The Zen Cart Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: jscript_instantSearch.js 5 2018-09-01 18:34:47Z davewest $
 * 
 * Instant Search+ 1.0.4
 */

let runningRequest = false;
let request;
let inputboxCurrent;
const autoPosition = true;

$(function() {
    let inputBox = $('input[name="keyword"]');
    inputBox.before('<div class="resultsContainer"></div>');
    inputBox.attr('autocomplete', 'off');

    if (autoPosition === true) {
        inputBox.each(function(index) {
            let offset = $(this).offset();
            $(this).prev().css("left", offset.left + "px");
            $(this).prev().css("top", ($(this).outerHeight(true) + offset.top) + "px");
        });
    }

    inputBox.on('blur', function() {
        if (inputboxCurrent) {
            let resultsContainer = inputboxCurrent.prev();
            resultsContainer.delay(300).slideUp(200);
        }
    });

    inputBox.on('focus', function() {
        if (inputboxCurrent && $(inputboxCurrent).val() !== "") {
            let resultsContainer = inputboxCurrent.prev();
            resultsContainer.delay(200).slideDown(200);
        }
    });

    $(window).on('resize', function() {
        if (inputboxCurrent) {
            let resultsContainer = inputboxCurrent.prev();
            resultsContainer.hide();
        }
    });

    inputBox.on('input', function() {
        inputboxCurrent = $(this);
        const resultsContainer = $(this).prev();
        const typedSearchWord = $(this).val();

        searchWord = typedSearchWord.replace(/^\s+/, "").replace(/  +/g, ' ');
        if (searchWord === "") {
            resultsContainer.hide();
        } else {
            if (runningRequest) {
                request.abort();
            }
            runningRequest = true;
            let data = new FormData();
            data.append('query', searchWord);
            request = jQuery.ajax({
                type: 'POST',
                url: 'ajax.php?act=ajaxInstantSearch&method=instantSearch',
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                data: data,
                success: function(data) {
                    if (data.length > 0) {
                        let resultHtml = '';
                        $.each(data, function(i, item) {
                            resultHtml += `<a href="${item.link}">
                                             <div class="resultWrapper">
                                               <div>
                                                 ${item.img}
                                               </div>
                                               <div>
                                                 ${highlightWord('(' + item.srch + ')', item.name)}
                                                 <div class="productModel">
                                                   ${highlightWord('(' + item.srch + ')', item.model)}
                                                 </div>
                                               </div>
                                             </div>
                                           </a>`;
                        });
                        resultsContainer.html(resultHtml);
                        if (!resultsContainer.is(':visible') && $(inputboxCurrent).val() === typedSearchWord) {
                            if (autoPosition === true) {
                                autoPositionContainer(inputboxCurrent, resultsContainer);
                            }
                            resultsContainer.slideDown(200);
                        }
                        resultsContainer.outerWidth(inputboxCurrent.outerWidth());
                    } else {
                        resultsContainer.hide();
                    }
                    runningRequest = false;
                }
            });
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
    resltsContainer.css("left", leftVal + "px");
    resltsContainer.css("top", (inputBoxCurr.outerHeight(true) + offsetInput.top) + "px");
}

function highlightWord(findTxt, replaceTxt) {
    const re = new RegExp(findTxt, 'ig');
    return replaceTxt.replace(re, "<span class=\"boldFont\">$1</span>");
}
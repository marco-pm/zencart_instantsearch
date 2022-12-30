<?php
/**
 * Adaptation of tpl_search_result_default.php (responsive_classic template version)
 * for the Instant Search result page.
 *
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */
?>

<div class="centerColumn" id="instantSearchResultsDefault">

    <h1 id="searchResultsDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

    <div id="instantSearchResults__noResultsFoundWrapper">
        <?php echo TEXT_NO_PRODUCTS_FOUND; ?>
    </div>

    <?php if ($do_filter_list || PRODUCT_LIST_ALPHA_SORTER === 'true') {
        echo zen_draw_form('filter', zen_href_link(FILENAME_INSTANT_SEARCH_RESULT), 'get', 'style="display:none"');
        echo zen_post_all_get_params('currency');
        require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCT_LISTING_ALPHA_SORTER));
        echo '</form>';
    } ?>

    <div id="productListing" class="group">
    </div>

    <div id="instantSearchResults__loadingWrapper">
        <?php echo TEXT_LOADING_RESULTS; ?>
        <div class="spinner"></div>
    </div>

    <?php // don't remove this div ?>
    <div id="instantSearchResults__end"></div>

    <div class="buttonRow back">
        <?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?>
    </div>

</div>

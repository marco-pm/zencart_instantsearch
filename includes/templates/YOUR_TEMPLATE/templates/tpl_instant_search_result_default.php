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

<div class="centerColumn" id="instantSearchResultDefault">

    <h1 id="searchResultsDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

    <?php if ($do_filter_list || PRODUCT_LIST_ALPHA_SORTER === 'true') { ?>
        <div id="filter-wrapper" class="group instantSearchResults__sorterRow" style="display:none">
        <?php
            echo zen_draw_form('filter', zen_href_link(FILENAME_INSTANT_SEARCH_RESULT), 'get') . '<label class="inputLabel">' . TEXT_SHOW . '</label>';
            echo zen_post_all_get_params(['currency', 'alpha_filter_id']);
            require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCT_LISTING_ALPHA_SORTER));
            echo '</form>'; ?>
        </div>
    <?php } ?>

    <div id="instantSearchResults__noResultsFoundWrapper">
        <?php echo TEXT_NO_PRODUCTS_FOUND; ?>
    </div>

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

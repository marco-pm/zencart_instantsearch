<?php
/**
 * Adaptation of tpl_modules_product_listing.php (bootstrap template version)
 * for the Instant Search result page.
 *
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */
?>

<?php if ($show_top_submit_button == true) { // only show when there is something to submit and enabled ?>
    <div id="productsListing-btn-toolbarTop" class="btn-toolbar justify-content-end my-3" role="toolbar">
        <?php echo zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit1" name="submit1"'); ?>
    </div>
<?php } // show top submit ?>

<?php if (in_array($product_listing_layout_style, ['columns', 'fluid'])) {
    require($template->get_template_dir('tpl_columnar_display.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/tpl_columnar_display.php');
} else {
    require($template->get_template_dir('tpl_tabular_display.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/tpl_tabular_display.php');
} ?>

<?php if ($show_bottom_submit_button == true) { // only show when there is something to submit and enabled ?>
    <div id="productsListing-btn-toolbarBottom" class="btn-toolbar justify-content-end my-3" role="toolbar">
        <?php echo zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit2" name="submit1"'); ?>
    </div>
<?php } // show_bottom_submit_button ?>

<?php if ($show_top_submit_button == true || $show_bottom_submit_button == true) {
    echo '</form>';
} ?>

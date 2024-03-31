<?php
/**
 * Adaptation of tpl_modules_product_listing.php (bootstrap template version)
 * for the Instant Search result page.
 *
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  4.0.3
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */
?>

<?php
// -----
// v3.6.4 adds a configuration setting to override the "Add Selected to Cart"
// button's default positioning.  The default, if not yet configured, is 'Always'.
//
if (!defined('BS4_FLOAT_ADD_SELECTED')) {
    define('BS4_FLOAT_ADD_SELECTED', 'Always');
}
switch (BS4_FLOAT_ADD_SELECTED) {
    case 'Never':
        $top_button_extra_class = '';
        $bottom_button_extra_class = '';
        break;
    case 'Small Devices Only':
        $top_button_extra_class = 'bs4-button-float sm-only';
        $bottom_button_extra_class = 'bs4-button-hide-sm';
        break;
    default:
        $top_button_extra_class = 'bs4-button-float always';
        $bottom_button_extra_class = 'd-none';
        break;
}
?>

<?php if ($show_top_submit_button) { // only show when there is something to submit and enabled ?>
    <div id="productsListing-btn-toolbarTop" class="btn-toolbar justify-content-end my-3" role="toolbar">
        <?= zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit1" name="submit1"', $top_button_extra_class) ?>
    </div>
<?php } // show top submit ?>

<?php if (in_array($product_listing_layout_style, ['columns', 'fluid'])) {
    require($template->get_template_dir('tpl_columnar_display.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/tpl_columnar_display.php');
} else {
    require($template->get_template_dir('tpl_tabular_display.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/tpl_tabular_display.php');
} ?>

<?php if ($show_bottom_submit_button) { // only show when there is something to submit and enabled ?>
    <div id="productsListing-btn-toolbarBottom" class="btn-toolbar justify-content-end my-3" role="toolbar">
        <?= zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit2" name="submit1"', $bottom_button_extra_class) ?>
    </div>
<?php } // show_bottom_submit_button ?>

<?php if ($show_top_submit_button || $show_bottom_submit_button) {
    echo '</form>';
} ?>

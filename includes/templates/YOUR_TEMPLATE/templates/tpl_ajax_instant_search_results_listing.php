<?php
/**
 * Adaptation of tpl_modules_product_listing.php (responsive_classic template version)
 * for the Instant Search result page.
 *
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */
?>

<?php if ($listing_split->number_of_rows && (PREV_NEXT_BAR_LOCATION == '1' || PREV_NEXT_BAR_LOCATION == '3')) { ?>
    <div class="prod-list-wrap group">
<?php } ?>

<?php if ($show_top_submit_button == true) { // only show when there is something to submit and enabled
    if (PREV_NEXT_BAR_LOCATION === '2' && $listing_split->number_of_rows) { ?>
        <div class="prod-list-wrap group">
    <?php } ?>

    <div class="forward button-top">
        <?php echo zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit1" name="submit1"'); ?>
    </div>

    <?php if (PREV_NEXT_BAR_LOCATION == '2' && $listing_split->number_of_rows) { ?>
        </div>
    <?php } ?>

<?php } // show top submit ?>

<?php if ($listing_split->number_of_rows && (PREV_NEXT_BAR_LOCATION == '1' || PREV_NEXT_BAR_LOCATION == '3')) { ?>
    </div>
<?php } ?>


<?php if (in_array($product_listing_layout_style, ['columns', 'fluid'])) {
    require($template->get_template_dir('tpl_columnar_display.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/tpl_columnar_display.php');
} else {
    require($template->get_template_dir('tpl_tabular_display.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/tpl_tabular_display.php');
} ?>

<?php if ($listing_split->number_of_rows && (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3')) { ?>
    <div class="prod-list-wrap group">
<?php } ?>

<?php if ($show_bottom_submit_button == true) { // only show when there is something to submit and enabled ?>
        <?php if (PREV_NEXT_BAR_LOCATION == '1') { ?>
            <div class="prod-list-wrap group button-bottom">
        <?php } ?>

        <div class="forward button-top">
            <?php echo zen_image_submit(BUTTON_IMAGE_ADD_PRODUCTS_TO_CART, BUTTON_ADD_PRODUCTS_TO_CART_ALT, 'id="submit2" name="submit1"'); ?>
        </div>

        <?php if (PREV_NEXT_BAR_LOCATION == '1') { ?>
            </div>
        <?php } ?>

<?php } // show_bottom_submit_button?>

<?php if ($listing_split->number_of_rows && (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3')) { ?>
    </div>
<?php } ?>

<?php if ($how_many > 0 && PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0 and $show_submit == true and $listing_split->number_of_rows > 0) { ?>
    </form>
<?php } ?>

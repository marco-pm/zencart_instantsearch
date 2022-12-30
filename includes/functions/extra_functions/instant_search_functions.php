<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

/**
 * Returns the number of (enabled) products per manufacturer.
 *
 * @param int $manufacturers_id Manufacturer's id
 *
 * @return int Products count
 */
function zen_count_products_for_manufacturer(int $manufacturers_id): int
{
    global $db;

    $products = $db->Execute("
        SELECT COUNT(products_id) AS total
        FROM " . TABLE_PRODUCTS . "
        WHERE manufacturers_id = " . $manufacturers_id . "
        AND products_status = 1
    ");

    return (int)$products->fields['total'];
}

if (!function_exists('zen_get_products_model')) { // for ZC v1.5.7
    /**
     * lookup attributes model
     * @param int $product_id
     */
    function zen_get_products_model($product_id)
    {
        global $db;
        $check = $db->Execute("SELECT products_model
                    FROM " . TABLE_PRODUCTS . "
                    WHERE products_id=" . (int)$product_id, 1);
        if ($check->EOF) return '';
        return $check->fields['products_model'];
    }
}

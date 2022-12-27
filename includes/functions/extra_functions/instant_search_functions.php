<?php
/**
 * @package Instant Search Results
 * @copyright Copyright Ayoob G 2009-2011
 * @copyright Portions Copyright 2003-2006 The Zen Cart Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Instant Search 3.0.0
 */

/**
 * Returns the number of (enabled) products per manufacturer.
 *
 * @param int $manufacturers_id manufacturer's id
 * @return int products count
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

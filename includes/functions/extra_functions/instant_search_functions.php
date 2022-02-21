<?php
/**
 * @package Instant Search Results
 * @copyright Copyright Ayoob G 2009-2011
 * @copyright Portions Copyright 2003-2006 The Zen Cart Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

/**
 * Returns the number of products per manufacturer.
 *
 * @param $manufacturers_id
 * @param $include_inactive
 * @return int
 */
function zen_count_products_for_manufacturer($manufacturers_id, $include_inactive = false): int
{
    global $db;

    $products_count = 0;
    $products_query = "SELECT COUNT(products_id) AS total
                       FROM " . TABLE_PRODUCTS . "
                       WHERE manufacturers_id = " . (int)$manufacturers_id ."
                       " . ($include_inactive === true ? ' AND products_status = 1': '');

    $products = $db->Execute($products_query);
    $products_count += $products->fields['total'];

    return $products_count;
}

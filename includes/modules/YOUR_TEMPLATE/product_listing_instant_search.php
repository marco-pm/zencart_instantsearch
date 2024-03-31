<?php
/**
 * Adaptation of product_listing.php (responsive_classic template version)
 * for the Instant Search result page.
 *
 * @package   Instant Search Plugin for Zen Cart
 * @author    marco-pm
 * @version   4.0.3
 * @see       https://github.com/marco-pm/zencart_instantsearch
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$row = 0;
$col = 0;
$list_box_contents = [];
$title = '';
$show_top_submit_button = false;
$show_bottom_submit_button = false;
$error_categories = false;

$show_submit = zen_run_normal();

$columns_per_row = defined('PRODUCT_LISTING_COLUMNS_PER_ROW') ? PRODUCT_LISTING_COLUMNS_PER_ROW : 1;
$product_listing_layout_style = (int)$columns_per_row > 1 ? 'columns' : 'table';
if (empty($columns_per_row)) $product_listing_layout_style = 'fluid';
if ($columns_per_row === 'fluid') $product_listing_layout_style = 'fluid';

$max_results = (int)MAX_DISPLAY_PRODUCTS_LISTING;
if ($product_listing_layout_style === 'columns' && $columns_per_row > 1) {
    $max_results = ($columns_per_row * (int)($max_results / $columns_per_row));
}
if ($max_results < 1) $max_results = 1;

//$listing_split = new splitPageResults($listing_sql, $max_results, 'p.products_id', 'page'); // marcopm instant-search edit
$zco_notifier->notify('NOTIFY_MODULE_INSTANT_SEARCH_LISTING_RESULTCOUNT', $listing_split->number_of_rows); // marcopm instant-search edit

// counter for how many items on the page can use add-to-cart, so we can decide what kinds of submit-buttons to offer in the template
$how_many = 0;

// Begin Row Headings
if ($product_listing_layout_style === 'table') {
    $list_box_contents[0] = ['params' => 'class="productListing-rowheading"'];

    $zc_col_count_description = 0;
    for ($col = 0, $n = count($column_list); $col < $n; $col++) {
        $lc_align = '';
        $lc_text = '';
        switch ($column_list[$col]) {
            case 'PRODUCT_LIST_MODEL':
                $lc_text = TABLE_HEADING_MODEL;
                $lc_align = '';
                $zc_col_count_description++;
                break;
            case 'PRODUCT_LIST_NAME':
                $lc_text = TABLE_HEADING_PRODUCTS;
                $lc_align = '';
                $zc_col_count_description++;
                break;
            case 'PRODUCT_LIST_MANUFACTURER':
                $lc_text = TABLE_HEADING_MANUFACTURER;
                $lc_align = '';
                $zc_col_count_description++;
                break;
            case 'PRODUCT_LIST_PRICE':
                $lc_text = TABLE_HEADING_PRICE;
                $lc_align = 'right' . (PRODUCTS_LIST_PRICE_WIDTH > 0 ? '" width="' . PRODUCTS_LIST_PRICE_WIDTH : '');
                $zc_col_count_description++;
                break;
            case 'PRODUCT_LIST_QUANTITY':
                $lc_text = TABLE_HEADING_QUANTITY;
                $lc_align = 'right';
                $zc_col_count_description++;
                break;
            case 'PRODUCT_LIST_WEIGHT':
                $lc_text = TABLE_HEADING_WEIGHT;
                $lc_align = 'right';
                $zc_col_count_description++;
                break;
            case 'PRODUCT_LIST_IMAGE':
                $lc_text = '&nbsp;';
//                $lc_text = TABLE_HEADING_IMAGE;   //-Uncomment this line if you want the "Products Image" header title
                $lc_align = 'center';
                $zc_col_count_description++;
                break;
            default:
                break;
        }

        // Add clickable "sort" links to column headings
        if ($column_list[$col] !== 'PRODUCT_LIST_IMAGE') {
            $lc_text = zen_create_sort_heading(isset($_GET['sort']) ? $_GET['sort'] : '', $col + 1, $lc_text);
        }


        $list_box_contents[0][$col] = [
            'align' => $lc_align,
            'params' => 'class="productListing-heading"',
            'text' => $lc_text,
        ];
    }
}


// Build row/cell content

$num_products_count = $listing_split->number_of_rows;

$rows = 0;
$column = 0;
$extra_row = 0;
$skip_sort = false;

if ($num_products_count > 0) {

    // if in fixed-columns mode, calculate column width
    if ($product_listing_layout_style === 'columns') {
        $calc_value = $columns_per_row;
        if ($num_products_count < $columns_per_row || $columns_per_row == 0) {
            $calc_value = $num_products_count;
        }
        $col_width = floor(100 / $calc_value) - 0.5;
    }

    // $listing = $db->Execute($listing_split->sql_query); // marcopm instant-search edit

    // Retrieve all records into an array to allow for sorting and insertion of additional data if needed
    $records = [];
    // marcopm instant-search edit start
        /*while (!$listing->EOF) {
        $category_id = !empty($listing->fields['categories_id']) ? $listing->fields['categories_id'] : $listing->fields['master_categories_id'];
        $parent_category_name = trim(zen_get_categories_parent_name($category_id));
        $category_name = trim(zen_get_category_name($category_id, (int)$_SESSION['languages_id']));
        $records[] = array_merge($listing->fields,
            [
                'parent_category_name' => (!empty($parent_category_name)) ? $parent_category_name : $category_name,
                'category_name' => $category_name,
//                'products_name' => $listing->fields['products_name'],
//                'master_categories_id' => $listing->fields['master_categories_id'],
//                'products_sort_order' => $listing->fields['products_sort_order'],
            ]);
        $listing->MoveNext();
    }*/
    foreach ($listing as $result) {
        $category_id = !empty($result['categories_id']) ? $result['categories_id'] : $result['master_categories_id'];
        $parent_category_name = trim(zen_get_categories_parent_name($category_id));
        $category_name = trim(zen_get_category_name($category_id, (int)$_SESSION['languages_id']));
        $records[] = array_merge($result,
            [
                'parent_category_name' => (!empty($parent_category_name)) ? $parent_category_name : $category_name,
                'category_name' => $category_name,
            ]);
    }
    // marcopm instant-search edit end

    if (!empty($_GET['keyword'])) $skip_sort = true;
    // add additional criteria for sort exclusions here if needed

    // SORT ACCORDING TO SPECIAL NEEDS
    if (empty($skip_sort)) {
        // add custom array_multisort code here if needed; otherwise the sort is based on the db query, whose sort order is influenced by index_filters and $_GET parameters
    }
    foreach ($records as $record) {
        if ($product_listing_layout_style === 'table') {
            $rows++;
            // handle even/odd striping if not set already with CSS
            $list_box_contents[$rows] = ['params' => 'class="productListing-' . ((($rows - $extra_row) % 2 == 0) ? 'even' : 'odd') . '"'];
        }

//        if ($product_listing_layout_style !== 'table') {
//            // insert breaks when the category changes
//            if (empty($_GET['manufacturers_id']) || !in_array($current_page_base, ['advanced_search_result'])) {
//                if (!isset($listing_prev_cat)) $listing_prev_cat = '';
//                $listing_current_cat = $record['category_name'];
//                if ($listing_current_cat !== $listing_prev_cat) {
//                    $listing_prev_cat = $listing_current_cat;
//
//                    // category divider
//                    if ($product_listing_layout_style == 'columns') $column = 0;
//                    $rows++;
//                    $list_box_contents[$rows][] = [
//                        'params' => 'class="h3 categoryHeader row row-cols-1 text-left"',
//                        'text' => $listing_current_cat,
//                    ];
//                    $column = 0;
//                    $rows++;
//                }
//            }
//        }

        // set css classes for "row" wrapper, to allow for fluid grouping of cells based on viewport
        // these defaults are based on Bootstrap4, but can be customized to suit your own framework
        if ($product_listing_layout_style === 'fluid') {
            $grid_cards_classes = 'row-cols-1 row-cols-md-2 row-cols-lg-2 row-cols-xl-3';
            // this array is intentionally in reverse order, with largest index first
            $grid_classes_matrix = [
                '10' => 'row-cols-1 row-cols-md-2 row-cols-lg-4 row-cols-xl-5',
                '8' => 'row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4',
                '6' => 'row-cols-1 row-cols-md-2 row-cols-lg-2 row-cols-xl-3',
            ];
            // determine classes to use based on number of grid-columns used by "center" column
            if (isset($center_column_width)) {
                foreach ($grid_classes_matrix as $width => $classes) {
                    if ($center_column_width >= $width) {
                        $grid_cards_classes = $classes;
                        break;
                    }
                }
            }
            $list_box_contents[$rows]['params'] = 'class="row ' . $grid_cards_classes . ' text-center"';
        }

        $product_contents = [];

        $linkCpath = $record['master_categories_id'];
        if (!empty($_GET['cPath'])) $linkCpath = $_GET['cPath'];
        if (!empty($_GET['manufacturers_id']) && !empty($_GET['filter_id'])) $linkCpath = $_GET['filter_id'];

        for ($col = 0, $n = count($column_list); $col < $n; $col++) {
            $lc_align = '';
            $lc_text = '';

            $href = zen_href_link(zen_get_info_page($record['products_id']), 'cPath=' . zen_get_generated_category_path_rev($linkCpath) . '&products_id=' . $record['products_id']);
            $listing_product_name = (isset($record['products_name'])) ? $record['products_name'] : '';
            $listing_description = '';
            if ((int)PRODUCT_LIST_DESCRIPTION > 0) {
                $listing_description = zen_trunc_string(zen_clean_html(stripslashes(zen_get_products_description($record['products_id'], $_SESSION['languages_id']))), PRODUCT_LIST_DESCRIPTION);
                $lc_text .= '<div class="listingDescription">' . $listing_description . '</div>';
            }
            $listing_model = (isset($record['products_model'])) ? $record['products_model'] : '';
            $listing_mfg_name = (isset($record['manufacturers_name'])) ? $record['manufacturers_name'] : '';
            $listing_quantity = (isset($record['products_quantity'])) ? $record['products_quantity'] : 0;
            $listing_weight = (isset($record['products_weight'])) ? $record['products_weight'] : 0;
            $listing_mfg_link = zen_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . (int)$record['manufacturers_id']);
            $listing_price = zen_get_products_display_price($record['products_id']);
            $more_info_button = '<a class="moreinfoLink list-more" href="' . $href . '" title="' . $record['products_id'] . '">' . MORE_INFO_TEXT . '</a>';
            $buy_now_link = zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $record['products_id']);
            $buy_now_button = '<a class="" href="' . $buy_now_link . '">' . zen_image_button(BUTTON_IMAGE_BUY_NOW, BUTTON_BUY_NOW_ALT, 'class="listingBuyNowButton"') . '</a>';
            $listing_qty_input_form = zen_draw_form('cart_quantity', zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action')) . 'action=add_product&products_id=' . $record['products_id']), 'post', 'enctype="multipart/form-data"')
                . '<input class="" type="text" name="cart_quantity" value="' . (zen_get_buy_now_qty($record['products_id'])) . '" maxlength="6" size="4" aria-label="' . ARIA_QTY_ADD_TO_CART . '">'
                . '<br>'
                . zen_draw_hidden_field('products_id', $record['products_id'])
                . zen_image_submit(BUTTON_IMAGE_IN_CART, BUTTON_IN_CART_ALT)
                . '</form>';

            $lc_button = '';
            if (zen_requires_attribute_selection($record['products_id']) || PRODUCT_LIST_PRICE_BUY_NOW == '0') {
                // more info in place of buy now
                $lc_button = $more_info_button;
            } else {
                if (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0) {
                    if (
                        // not a hide qty box product
                        $record['products_qty_box_status'] != 0 &&
                        // product type can be added to cart
                        zen_get_products_allow_add_to_cart($record['products_id']) != 'N'
                        &&
                        // product is not call for price
                        $record['product_is_call'] == 0
                        &&
                        // product is in stock or customers may add it to cart anyway
                        ($listing_quantity > 0 || SHOW_PRODUCTS_SOLD_OUT_IMAGE == 0)
                    ) {
                        $how_many++;
                    }
                    // hide quantity box
                    if ($record['products_qty_box_status'] == 0) {
                        $lc_button = '';
                        $lc_button .= '<div class="cart-add">';
                        $lc_button .= $buy_now_button;
                        $lc_button .= '</div>';
                    } else {
                        $lc_button = '';
                        $lc_button .= '<div class="list-input">';
                        $lc_button .= '<span class="list-addtext">';
                        $lc_button .= TEXT_PRODUCT_LISTING_MULTIPLE_ADD_TO_CART;
                        $lc_button .= '</span>';
                        $lc_button .= '<input class="" type="text" name="products_id[' . $record['products_id'] . ']" value="0" size="4" aria-label="' . ARIA_QTY_ADD_TO_CART . '">';
                        $lc_button .= '</div>';
                    }
                } else {
                    // qty box with add to cart button
                    if (PRODUCT_LIST_PRICE_BUY_NOW == '2' && $record['products_qty_box_status'] != 0) {
                        $lc_button = '';
                        $lc_button .= '<div class="cart-add">';
                        $lc_button .= $listing_qty_input_form;
                        $lc_button .= '</div>';
                    } else {
                        $lc_button = '';
                        $lc_button .= '<div class="cart-add">';
                        $lc_button .= $buy_now_button;
                        $lc_button .= '</div>';
                    }
                }
            }
            $zco_notifier->notify('NOTIFY_MODULES_INSTANT_SEARCH_LISTING_PRODUCTS_BUTTON', [], $record, $lc_button); // marcopm instant-search edit


            switch ($column_list[$col]) {
                case 'PRODUCT_LIST_MODEL':
                    $lc_align = 'center';
                    if ($product_listing_layout_style === 'table') $lc_align = '';
                    $lc_text = '';
                    $lc_text .= '<div class="list-model">';
                    //if ($product_listing_layout_style === 'columns') $lc_text .= '<label>' . TABLE_HEADING_MODEL . '</label>';
                    $lc_text .= $listing_model;
                    $lc_text .= '</div>';
                    break;

                case 'PRODUCT_LIST_NAME':
                    $lc_align = 'center';
                    if ($product_listing_layout_style === 'table') $lc_align = '';
                    $lc_text = '<h3 class="itemTitle">
                        <a class="" href="' . $href . '">' . $listing_product_name . '</a>
                        </h3>';

                    if (!empty($listing_description)) {
                        $lc_text .= '<div class="listingDescription">' . $listing_description . '</div>';
                    }
                    break;

                case 'PRODUCT_LIST_MANUFACTURER':
                    $lc_align = 'center';
                    if ($product_listing_layout_style === 'table') $lc_align = '';
                    $lc_text = '';
                    //if ($product_listing_layout_style === 'columns') $lc_text .= '<label>' . TABLE_HEADING_MANUFACTURER . '</label>';
                    $lc_text .= '<a class="mfgLink list-man" href="' . $listing_mfg_link . '">' . $listing_mfg_name . '</a>';
                    break;

                case 'PRODUCT_LIST_PRICE':
                    $lc_align = 'center';
                    if ($product_listing_layout_style === 'table') $lc_align = 'right';
                    $lc_text = '';
                    // if ($product_listing_layout_style === 'columns') $lc_text .= '<label>' . TABLE_HEADING_PRICE . '</label>';
                    $lc_text .= '<div class="list-price">';
                    $lc_text .= $listing_price;
                    $lc_text .= '</div>';
//                    $lc_text .= '<br><br>';
                    $lc_text .= zen_get_buy_now_button($record['products_id'], $lc_button, $more_info_button);
//                    $lc_text .= '<br>';
                    $lc_text .= zen_get_products_quantity_min_units_display($record['products_id']);
//                    $lc_text .= '<br>';
                    if (zen_get_show_product_switch($record['products_id'], 'ALWAYS_FREE_SHIPPING_IMAGE_SWITCH')) {
                        if (zen_get_product_is_always_free_shipping($record['products_id'])) {
                            $lc_text .= '<br>';
                            $lc_text .= TEXT_PRODUCT_FREE_SHIPPING_ICON;
//                            $lc_text .= '<br>';
                        }
                    }
                    break;

                case 'PRODUCT_LIST_QUANTITY':
                    $lc_align = 'center';
                    if ($product_listing_layout_style === 'table') $lc_align = 'right';
                    $lc_text = '';
                    $lc_text .= '<div class="list-quantity">';
                    //if ($product_listing_layout_style === 'columns') $lc_text .= '<label>' . TABLE_HEADING_QUANTITY . '</label>';
                    $lc_text .= $listing_quantity;
                    $lc_text .= '</div>';
                    break;

                case 'PRODUCT_LIST_WEIGHT':
                    $lc_align = 'center';
                    if ($product_listing_layout_style === 'table') $lc_align = 'right';
                    $lc_text = '';
                    $lc_text .= '<div class="list-weight">';
                    //if ($product_listing_layout_style === 'columns') $lc_text .= '<label>' . TABLE_HEADING_WEIGHT . '</label>';
                    $lc_text .= $listing_weight;
                    $lc_text .= '</div>';
                    break;

                case 'PRODUCT_LIST_IMAGE':
                    $lc_align = 'center';
                    $lc_text = '';
                    if (!empty($record['products_image']) || PRODUCTS_IMAGE_NO_IMAGE_STATUS > 0) {
                        $lc_text .= '<div class="list-image">';
                        $lc_text .= '<a href="' . $href . '">';
                        $lc_text .= zen_image(DIR_WS_IMAGES . $record['products_image'], $listing_product_name, IMAGE_PRODUCT_LISTING_WIDTH, IMAGE_PRODUCT_LISTING_HEIGHT, 'class="listingProductImage"');
                        $lc_text .= '</a>';
                        $lc_text .= '</div>';
                    }
                    break;
            }

            $product_contents[] = $lc_text; // (used in column/fluid modes)

            if ($product_listing_layout_style === 'table') {
                $list_box_contents[$rows][] = [
                    'align' => $lc_align,
                    'params' => 'class="productListing-data"',
                    'category' => $record['master_categories_id'],
                    'parent_category_name' => $record['parent_category_name'],
                    'category_name' => $record['category_name'],
                    'manufacturers_id' => $record['manufacturers_id'],
                    'manufacturers_name' => $listing_mfg_name,
                    'text' => $lc_text,
                ];
//                // add description
//                if (!empty($listing_description)) {
//                    $rows++;
//                    // match alternating colors
//                    if ($extra_row == 1) {
//                        $tmp_class_name = "productListing-data-description-even";
//                        $extra_row = 0;
//                    } else {
//                        $tmp_class_name = "productListing-data-description-odd";
//                        $extra_row = 1;
//                    }
//                    $list_box_contents[$rows][] = [
//                        'params' => 'class="' . $tmp_class_name . '" colspan="' . $zc_col_count_description . '"',
//                        'text' => $listing_description
//                    ];
//                }
            }
        }

        if ($product_listing_layout_style === 'columns' || $product_listing_layout_style === 'fluid') {
            $lc_text = implode('<br>', $product_contents);
            $style = '';
            if ($product_listing_layout_style === 'columns') {
                $style = ' style="width:' . $col_width . '%;"';
            }
            $list_box_contents[$rows][] = [
                'params' => 'class="centerBoxContentsProducts centeredContent back gridlayout"' . $style,
                'text' => $lc_text,
                'wrap_with_classes' => '',
                'card_type' => $product_listing_layout_style,
                'category' => $record['master_categories_id'],
                'parent_category_name' => $record['parent_category_name'],
                'category_name' => $record['category_name'],
                'manufacturers_id' => $record['manufacturers_id'],
                'manufacturers_name' => $listing_mfg_name,
            ];
            if ($product_listing_layout_style === 'columns') {
                $column++;
                if ($column >= $columns_per_row) {
                    $column = 0;
                    $rows++;
                }
            }
        }
    }
} else {

    $list_box_contents = [];
    $list_box_contents[0][] = [
        'params' => 'class="productListing-data"',
        'text' => '', // marcopm instant-search edit
    ];
    $error_categories = true;
}

if (($how_many > 0 && $show_submit == true && $num_products_count > 0) && (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 1 || PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 3)) {
    $show_top_submit_button = true;
}
if (($how_many > 0 && $show_submit == true && $num_products_count > 0) && (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART >= 2)) {
    $show_bottom_submit_button = true;
}

$zco_notifier->notify('NOTIFY_INSTANT_SEARCH_LISTING_END', $current_page_base, $list_box_contents, $listing_split, $show_top_submit_button, $show_bottom_submit_button, $show_submit, $how_many); // marcopm instant-search edit

if ($how_many > 0 && PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0 && $show_submit == true && $num_products_count > 0) {
    // bof: multiple products
    echo zen_draw_form('multiple_products_cart_quantity', zen_href_link(FILENAME_INSTANT_SEARCH_RESULT, zen_get_all_get_params(array('action')) . 'action=multiple_products_add_product', $request_type), 'post', 'enctype="multipart/form-data"'); // marcopm instant-search edit
}


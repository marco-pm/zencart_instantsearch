<?php

namespace Tests\Support\DatabaseFixtures;

class ProductsDemoFixture extends DatabaseFixture implements FixtureContract
{
    public function createTable()
    {
        $sql = "
            DROP TABLE IF EXISTS products;
            CREATE TABLE products (
                products_id int(11) NOT NULL auto_increment,
                products_type int(11) NOT NULL default '1',
                products_quantity float NOT NULL default '0',
                products_model varchar(32) default NULL,
                products_image varchar(255) default NULL,
                products_price decimal(15,4) NOT NULL default '0.0000',
                products_virtual tinyint(1) NOT NULL default '0',
                products_date_added datetime NOT NULL default '0001-01-01 00:00:00',
                products_last_modified datetime default NULL,
                products_date_available datetime default NULL,
                products_weight float NOT NULL default '0',
                products_status tinyint(1) NOT NULL default '0',
                products_tax_class_id int(11) NOT NULL default '0',
                manufacturers_id int(11) default NULL,
                products_ordered float NOT NULL default '0',
                products_quantity_order_min float NOT NULL default '1',
                products_quantity_order_units float NOT NULL default '1',
                products_priced_by_attribute tinyint(1) NOT NULL default '0',
                product_is_free tinyint(1) NOT NULL default '0',
                product_is_call tinyint(1) NOT NULL default '0',
                products_quantity_mixed tinyint(1) NOT NULL default '0',
                product_is_always_free_shipping tinyint(1) NOT NULL default '0',
                products_qty_box_status tinyint(1) NOT NULL default '1',
                products_quantity_order_max float NOT NULL default '0',
                products_sort_order int(11) NOT NULL default '0',
                products_discount_type tinyint(1) NOT NULL default '0',
                products_discount_type_from tinyint(1) NOT NULL default '0',
                products_price_sorter decimal(15,4) NOT NULL default '0.0000',
                master_categories_id int(11) NOT NULL default '0',
                products_mixed_discount_quantity tinyint(1) NOT NULL default '1',
                metatags_title_status tinyint(1) NOT NULL default '0',
                metatags_products_name_status tinyint(1) NOT NULL default '0',
                metatags_model_status tinyint(1) NOT NULL default '0',
                metatags_price_status tinyint(1) NOT NULL default '0',
                metatags_title_tagline_status tinyint(1) NOT NULL default '0',
                PRIMARY KEY  (products_id),
                KEY idx_products_date_added_zen (products_date_added),
                KEY idx_products_status_zen (products_status),
                KEY idx_products_date_available_zen (products_date_available),
                KEY idx_products_ordered_zen (products_ordered),
                KEY idx_products_model_zen (products_model),
                KEY idx_products_price_sorter_zen (products_price_sorter),
                KEY idx_master_categories_id_zen (master_categories_id),
                KEY idx_products_sort_order_zen (products_sort_order),
                KEY idx_manufacturers_id_zen (manufacturers_id)
            ) ENGINE=MyISAM;
        ";

        $this->connection->query($sql);
    }

    public function seeder()
    {
        $sql = "
            INSERT INTO products (products_id, products_type, products_quantity, products_model, products_image, products_price, products_virtual, products_date_added, products_last_modified, products_date_available, products_weight, products_status, products_tax_class_id, manufacturers_id, products_ordered, products_quantity_order_min, products_quantity_order_units, products_priced_by_attribute, product_is_free, product_is_call, products_quantity_mixed, product_is_always_free_shipping, products_qty_box_status, products_quantity_order_max, products_sort_order, products_discount_type, products_discount_type_from, products_price_sorter, master_categories_id, products_mixed_discount_quantity) VALUES (1, 1, '31', 'MG200MMS', 'matrox/mg200mms.gif', '299.9900', 0, '2003-11-03 12:32:17', '2004-04-26 23:57:34', NULL, '23.00', 1, 1, 1, '1', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '299.9900', 4, 1),
                (2, 1, '31', 'MG400-32MB', 'matrox/mg400-32mb.gif', '499.9900', 0, '2003-11-03 12:32:17', NULL, NULL, '23.00', 1, 1, 1, '1', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '499.9900', 4, 1),
                (3, 1, '500', 'MSIMPRO', 'microsoft/msimpro.gif', '49.9900', 0, '2003-11-03 12:32:17', NULL, NULL, '7.00', 1, 1, 2, '1', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '39.9900', 9, 1),
                (4, 1, '12', 'DVD-RPMK', 'dvd/replacement_killers.gif', '42.0000', 0, '2003-11-03 12:32:17', NULL, NULL, '23.00', 1, 1, 3, '1', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '42.0000', 10, 1),
                (5, 1, '15', 'DVD-BLDRNDC', 'dvd/blade_runner.gif', '35.9900', 0, '2003-11-03 12:32:17', '2003-12-23 00:44:28', NULL, '7.00', 1, 1, 3, '2', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '30.0000', 11, 1),
                (6, 1, '8', 'DVD-MATR', 'dvd/the_matrix.gif', '39.9900', 0, '2003-11-03 12:32:17', '2003-12-23 00:48:28', NULL, '7.00', 1, 1, 3, '2', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '30.0000', 10, 1),
                (7, 1, '500', 'DVD-YGEM', 'dvd/youve_got_mail.gif', '34.9900', 0, '2003-11-03 12:32:17', '2004-04-27 14:53:17', NULL, '7.00', 1, 1, 3, '5', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '34.9900', 12, 1),
                (8, 1, '499', 'DVD-ABUG', 'dvd/a_bugs_life.gif', '35.9900', 0, '2003-11-03 12:32:17', '2004-04-27 14:52:54', NULL, '7.00', 1, 1, 3, '6', '1', '1', 0, 0, 0, 0, 0, 1, '0', 10, 1, 1, '35.9900', 13, 1),
                (9, 1, '10', 'DVD-UNSG', 'dvd/under_siege.gif', '29.9900', 0, '2003-11-03 12:32:17', '2004-05-17 13:35:27', NULL, '7.00', 1, 1, 3, '0', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '29.9900', 10, 1),
                (10, 1, '9', 'DVD-UNSG2', 'dvd/under_siege2.gif', '29.9900', 0, '2003-11-03 12:32:17', NULL, NULL, '7.00', 1, 1, 3, '1', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '29.9900', 10, 1),
                (11, 1, '10', 'DVD-FDBL', 'dvd/fire_down_below.gif', '29.9900', 0, '2003-11-03 12:32:17', '2003-12-23 00:43:40', NULL, '7.00', 1, 1, 3, '0', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '29.9900', 10, 1),
                (12, 1, '9', 'DVD-DHWV', 'dvd/die_hard_3.gif', '39.9900', 0, '2003-11-03 12:32:17', '2004-05-16 00:34:33', NULL, '7.00', 1, 1, 4, '6', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '39.9900', 10, 1),
                (13, 1, '10', 'DVD-LTWP', 'dvd/lethal_weapon.gif', '34.9900', 0, '2003-11-03 12:32:17', '2004-04-27 00:07:35', NULL, '7.00', 1, 1, 3, '0', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '34.9900', 10, 1),
                (14, 1, '9', 'DVD-REDC', 'dvd/red_corner.gif', '32.0000', 0, '2003-11-03 12:32:17', '2003-12-23 00:47:39', NULL, '7.00', 1, 1, 3, '1', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '32.0000', 15, 1),
                (15, 1, '9', 'DVD-FRAN', 'dvd/frantic.gif', '35.0000', 0, '2003-11-03 12:32:17', '2003-12-23 00:43:55', NULL, '7.00', 1, 1, 3, '1', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '35.0000', 14, 1),
                (16, 1, '9', 'DVD-CUFI', 'dvd/courage_under_fire.gif', '38.9900', 0, '2003-11-03 12:32:17', '2003-12-23 00:42:57', '2008-02-21 00:00:00', '7.00', 1, 1, 4, '1', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '29.9900', 15, 1),
                (17, 1, '10', 'DVD-SPEED', 'dvd/speed.gif', '39.9900', 0, '2003-11-03 12:32:17', '2003-12-23 00:47:51', NULL, '7.00', 1, 1, 4, '0', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '39.9900', 10, 1),
                (18, 1, '10', 'DVD-SPEED2', 'dvd/speed_2.gif', '42.0000', 0, '2003-11-03 12:32:17', NULL, NULL, '7.00', 1, 1, 4, '0', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '42.0000', 10, 1),
                (19, 1, '10', 'DVD-TSAB', 'dvd/theres_something_about_mary.gif', '49.9900', 0, '2003-11-03 12:32:17', '2003-12-23 00:49:00', NULL, '7.00', 1, 1, 4, '0', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '49.9900', 12, 1),
                (20, 1, '8', 'DVD-BELOVED', 'dvd/beloved.gif', '54.9900', 0, '2003-11-03 12:32:17', '2003-12-23 00:42:34', NULL, '7.00', 1, 1, 3, '2', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '54.9900', 15, 1),
                (21, 1, '16', 'PC-SWAT3', 'sierra/swat_3.gif', '79.9900', 0, '2003-11-03 12:32:17', '2004-04-27 14:51:00', NULL, '7.00', 1, 1, 7, '0', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '79.9900', 18, 1),
                (22, 1, '13', 'PC-UNTM', 'gt_interactive/unreal_tournament.gif', '89.9900', 0, '2003-11-03 12:32:17', '2003-12-23 00:49:29', NULL, '7.00', 1, 1, 8, '9', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '89.9900', 19, 1),
                (23, 1, '16', 'PC-TWOF', 'gt_interactive/wheel_of_time.gif', '99.9900', 0, '2003-11-03 12:32:17', '2003-12-23 00:48:50', NULL, '10.00', 1, 1, 8, '0', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '99.9900', 20, 1),
                (24, 1, '16', 'PC-DISC', 'gt_interactive/disciples.gif', '90.0000', 0, '2003-11-03 12:32:17', '2003-12-23 00:43:24', NULL, '8.00', 1, 1, 8, '1', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '90.0000', 20, 1),
                (25, 1, '16', 'MSINTKB', 'microsoft/intkeyboardps2.gif', '69.9900', 0, '2003-11-03 12:32:17', '2004-01-04 03:02:41', NULL, '8.00', 1, 1, 2, '0', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '69.9900', 8, 1),
                (26, 1, '9', 'MSIMEXP', 'microsoft/imexplorer.gif', '64.9500', 0, '2003-11-03 12:32:17', '2004-05-03 01:47:47', NULL, '8.00', 1, 1, 2, '17', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '64.9500', 9, 1),
                (27, 1, '70', 'HPLJ1100XI', 'hewlett_packard/lj1100xi.gif', '499.9900', 0, '2003-11-03 12:32:17', '2003-12-23 00:45:03', NULL, '45.00', 1, 1, 9, '1', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '499.9900', 5, 1),
                (28, 1, '999', 'GIFT005', 'gift_certificates/gv_5.gif', '5.0000', 1, '2003-11-03 12:32:17', '2004-01-10 02:57:18', NULL, '0.00', 1, 0, 0, '1', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '5.0000', 21, 1),
                (29, 1, '985', 'GIFT 010', 'gift_certificates/gv_10.gif', '10.0000', 1, '2003-11-03 12:32:17', '2003-12-28 14:51:36', NULL, '0.00', 1, 0, 0, '15', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '10.0000', 21, 1),
                (30, 1, '992', 'GIFT025', 'gift_certificates/gv_25.gif', '25.0000', 1, '2003-11-03 12:32:17', NULL, NULL, '0.00', 1, 0, 0, '8', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '25.0000', 21, 1),
                (31, 1, '997', 'GIFT050', 'gift_certificates/gv_50.gif', '50.0000', 1, '2003-11-03 12:32:17', NULL, NULL, '0.00', 1, 0, 0, '4', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '50.0000', 21, 1),
                (32, 1, '995', 'GIFT100', 'gift_certificates/gv_100.gif', '100.0000', 1, '2003-11-03 12:32:17', NULL, NULL, '0.00', 1, 0, 0, '5', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '100.0000', 21, 1),
                (34, 1, '796', 'DVD-ABUG', 'dvd/a_bugs_life.gif', '35.9900', 0, '2003-11-07 22:03:45', '2004-01-01 14:16:01', '2005-02-21 00:00:00', '7.00', 1, 1, 3, '5', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '35.9900', 22, 1),
                (36, 1, '700', 'HPLJ1100XI', 'hewlett_packard/lj1100xi.gif', '0.0000', 0, '2003-12-24 14:29:11', '2004-01-03 01:51:12', NULL, '45.00', 1, 1, 9, '0', '1', '1', 1, 0, 0, 0, 0, 1, '0', 0, 0, 0, '449.1000', 25, 1),
                (100, 1, '700', 'HPLJ1100XI', 'hewlett_packard/lj1100xi.gif', '0.0000', 0, '2004-01-08 14:06:13', '2004-01-08 14:06:50', NULL, '45.00', 1, 1, 9, '0', '1', '1', 1, 0, 0, 0, 0, 1, '0', 0, 0, 0, '336.8250', 25, 1),
                (39, 1, '997', 'TESTFREE', 'free.gif', '100.0000', 0, '2003-12-25 16:33:13', '2004-01-11 02:29:16', NULL, '1.00', 1, 1, 0, '3', '1', '1', 0, 1, 0, 1, 0, 1, '0', 0, 0, 0, '0.0000', 24, 1),
                (40, 1, '999', 'TESTCALL', 'call_for_price.jpg', '100.0000', 0, '2003-12-25 17:42:15', '2004-01-04 13:08:08', '2008-02-21 00:00:00', '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 1, 1, 0, 1, '0', 0, 0, 0, '100.0000', 24, 1),
                (41, 1, '999', 'TESTCALL', 'call_for_price.jpg', '100.0000', 0, '2003-12-25 19:13:35', '2004-09-27 13:33:33', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 1, 1, 0, 1, '0', 0, 0, 0, '81.0000', 28, 0),
                (42, 1, '998', 'TESTFREE', 'free.gif', '100.0000', 0, '2003-12-25 19:14:16', '2003-12-25 19:15:00', NULL, '1.00', 1, 1, 0, '1', '1', '1', 0, 1, 0, 1, 0, 1, '0', 0, 0, 0, '0.0000', 28, 1),
                (43, 1, '999', 'TESTFREEATTRIB', 'free.gif', '100.0000', 0, '2003-12-25 20:44:06', '2004-01-21 16:23:29', NULL, '0.00', 1, 1, 0, '0', '1', '1', 0, 1, 0, 1, 0, 1, '0', 0, 0, 0, '0.0000', 24, 1),
                (44, 1, '999', 'TESTMINUNITSNOMIX', 'sample_image.gif', '100.0000', 0, '2003-12-25 21:38:59', '2004-01-22 13:15:41', NULL, '1.00', 1, 1, 0, '0', '4', '2', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '90.0000', 22, 1),
                (46, 1, '981', 'TESTMINUNITSMIX', 'sample_image.gif', '100.0000', 0, '2003-12-25 21:53:07', '2003-12-29 02:00:50', NULL, '1.00', 1, 1, 0, '18', '4', '2', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '90.0000', 22, 1),
                (47, 1, '9996', 'GIFT', 'gift_certificates/gv.gif', '0.0000', 1, '2003-12-27 22:56:57', '2004-09-29 20:11:51', NULL, '0.00', 1, 0, 0, '4', '1', '1', 1, 0, 0, 1, 0, 1, '0', 0, 0, 0, '5.0000', 21, 1),
                (48, 1, '9990', 'TEST1', '1_small.jpg', '39.0000', 0, '2003-12-28 02:27:47', '2004-01-11 02:56:37', NULL, '1.00', 1, 1, 0, '10', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '39.0000', 23, 1),
                (49, 1, '900', 'TEST2', '2_small.jpg', '20.0000', 0, '2003-12-28 02:28:42', '2003-12-29 23:00:27', NULL, '0.50', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '20.0000', 23, 1),
                (50, 1, '1000', 'TEST3', '3_small.jpg', '75.0000', 0, '2003-12-28 02:29:37', '2003-12-29 23:01:04', NULL, '1.50', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '75.0000', 23, 1),
                (51, 1, '998', 'Free1', 'b_g_grid.gif', '25.0000', 0, '2003-12-28 11:51:05', '2004-01-21 17:03:32', NULL, '10.00', 1, 1, 0, '2', '1', '1', 0, 1, 0, 1, 1, 1, '0', 0, 0, 0, '0.0000', 24, 1),
                (52, 1, '997', 'Free2', 'b_p_grid.gif', '0.0000', 1, '2003-12-28 12:24:58', '2004-01-21 17:01:18', NULL, '2.00', 1, 1, 0, '2', '1', '1', 0, 1, 0, 1, 0, 1, '0', 0, 0, 0, '0.0000', 24, 1),
                (53, 1, '991', 'MINUNITSMIX', 'b_c_grid.gif', '25.0000', 0, '2003-12-28 23:26:44', '2004-01-11 02:22:35', NULL, '1.00', 1, 1, 0, '6', '6', '3', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '20.0000', 23, 1),
                (54, 1, '991', 'MINUNITSNOMIX', 'waybkgnd.gif', '25.0000', 0, '2003-12-29 23:19:13', '2004-01-11 02:23:08', NULL, '1.00', 1, 1, 0, '0', '6', '3', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '25.0000', 23, 1),
                (55, 1, '991', 'MINUNITSMIXSALE', 'b_b_grid.gif', '25.0000', 0, '2003-12-31 11:11:46', '2004-01-11 02:26:28', NULL, '1.00', 1, 1, 0, '0', '6', '3', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '22.5000', 28, 1),
                (56, 1, '991', 'MINUNITSNOMIXSALE', 'b_w_grid.gif', '25.0000', 0, '2003-12-31 11:13:08', '2004-01-11 02:26:49', NULL, '1.00', 1, 1, 0, '0', '6', '3', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '22.5000', 28, 1),
                (57, 1, '998', 'TESTFREEALL', 'free.gif', '0.0000', 0, '2003-12-31 11:36:09', '2004-01-21 16:55:19', NULL, '1.00', 1, 1, 0, '1', '1', '1', 0, 1, 0, 1, 1, 1, '0', 0, 0, 0, '0.0000', 24, 1),
                (59, 1, '700', 'HPLJ1100XI', 'hewlett_packard/lj1100xi.gif', '0.0000', 0, '2003-12-31 14:36:57', '2003-12-31 14:37:05', NULL, '45.00', 1, 1, 9, '0', '1', '1', 1, 0, 0, 0, 0, 1, '0', 0, 0, 0, '300.0000', 23, 1),
                (60, 1, '699', 'HPLJ1100XI', 'hewlett_packard/lj1100xi.gif', '499.7500', 0, '2004-01-02 01:34:55', '2004-01-02 01:41:37', NULL, '45.00', 1, 1, 9, '1', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '449.7750', 28, 1),
                (61, 1, '699', 'HPLJ1100XI', 'hewlett_packard/lj1100xi.gif', '499.7500', 0, '2004-01-02 01:44:09', '2004-01-02 01:45:45', NULL, '45.00', 1, 1, 9, '1', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 0, 0, '449.7750', 28, 1),
                (101, 1, '1000', 'Test120-90off-10', 'test_demo.jpg', '0.0000', 0, '2004-01-08 14:11:32', '2004-01-08 14:17:09', NULL, '1.00', 1, 1, 0, '0', '1', '1', 1, 0, 0, 1, 0, 1, '0', 0, 0, 0, '72.0000', 47, 1),
                (109, 1, '1000', 'HIDEQTYBOX', '1_small.jpg', '75.0000', 0, '2004-01-21 22:01:20', '2004-01-22 11:21:12', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '1', 0, 0, 0, '75.0000', 23, 1),
                (78, 1, '1000', 'Test25-10AttrAll', 'test_demo.jpg', '0.0000', 0, '2004-01-04 01:09:46', '2004-01-04 01:30:12', NULL, '0.00', 1, 1, 0, '0', '1', '1', 1, 0, 0, 1, 0, 1, '0', 0, 0, 0, '101.2500', 25, 1),
                (79, 1, '1000', 'Test25-AttrAll', 'test_demo.jpg', '0.0000', 0, '2004-01-04 01:28:52', '2004-01-04 01:33:55', NULL, '1.00', 1, 1, 0, '0', '1', '1', 1, 0, 0, 1, 0, 1, '0', 0, 0, 0, '150.0000', 23, 1),
                (74, 1, '700', 'HPLJ1100XI', 'hewlett_packard/lj1100xi.gif', '0.0000', 0, '2004-01-02 15:34:49', '2004-01-02 15:35:17', NULL, '45.00', 1, 1, 9, '0', '1', '1', 1, 0, 0, 0, 0, 1, '0', 0, 0, 0, '399.2000', 23, 1),
                (76, 1, '1000', 'Test25-10', 'test_demo.jpg', '100.0000', 0, '2004-01-03 23:08:33', NULL, NULL, '0.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '67.5000', 28, 1),
                (80, 1, '1000', 'Test25', 'test_demo.jpg', '100.0000', 0, '2004-01-04 01:31:06', '2004-01-04 13:35:47', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '100.0000', 23, 1),
                (84, 1, '999', 'Test120', 'test_demo.jpg', '120.0000', 0, '2004-01-04 15:05:10', '2004-01-06 15:27:39', NULL, '1.00', 1, 1, 0, '1', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '120.0000', 23, 1),
                (82, 1, '1000', 'Test120-5', 'test_demo.jpg', '120.0000', 0, '2004-01-04 14:50:38', '2004-01-04 17:09:03', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '115.0000', 27, 1),
                (83, 1, '1000', 'Test120-90-5', 'test_demo.jpg', '120.0000', 0, '2004-01-04 15:01:53', '2004-01-06 10:02:11', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '85.0000', 27, 1),
                (85, 1, '1000', 'Test90', 'test_demo.jpg', '120.0000', 0, '2004-01-04 15:19:00', '2004-01-06 10:00:35', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '90.0000', 23, 1),
                (88, 1, '1000', 'Test120-90-10-Skip', 'test_demo.jpg', '120.0000', 0, '2004-01-05 00:14:31', '2004-01-06 09:58:08', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '90.0000', 31, 1),
                (89, 1, '1000', 'Test120-90-10-Skip', 'test_demo.jpg', '120.0000', 0, '2004-01-05 00:41:40', '2004-01-06 09:57:42', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '108.0000', 31, 1),
                (95, 1, '1000', 'Test120-25-New100-Skip', 'test_demo.jpg', '120.0000', 0, '2004-01-07 02:35:44', '2004-01-07 02:37:27', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '90.0000', 51, 1),
                (90, 1, '999', 'Test120-90-10', 'test_demo.jpg', '120.0000', 0, '2004-01-05 23:55:18', '2004-01-06 00:08:58', NULL, '1.00', 1, 1, 0, '1', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '81.0000', 45, 1),
                (92, 1, '1000', 'Test120-90off-10', 'test_demo.jpg', '120.0000', 0, '2004-01-05 23:58:54', '2004-01-06 00:09:28', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '108.0000', 45, 1),
                (93, 1, '1000', 'Test120-New100', 'test_demo.jpg', '120.0000', 0, '2004-01-06 00:02:32', '2004-01-06 00:04:25', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '100.0000', 46, 1),
                (94, 1, '1000', 'Test120-25-New100', 'test_demo.jpg', '120.0000', 0, '2004-01-06 00:04:31', '2004-01-06 00:07:08', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '100.0000', 46, 1),
                (96, 1, '1000', 'Test120-New100-Off-Skip', 'test_demo.jpg', '120.0000', 0, '2004-01-07 02:36:52', '2004-01-07 02:37:29', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '100.0000', 51, 1),
                (97, 1, '1000', 'Test120-90-10-Price', 'test_demo.jpg', '120.0000', 0, '2004-01-07 11:26:34', '2004-01-07 11:27:24', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '108.0000', 32, 1),
                (98, 1, '1000', 'Test120-90off-10-Price', 'test_demo.jpg', '120.0000', 0, '2004-01-07 11:28:16', '2004-01-07 11:29:57', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '108.0000', 32, 1),
                (99, 1, '997', 'FreeShipping', 'small_00.jpg', '25.0000', 0, '2004-01-07 13:27:30', '2004-01-21 01:48:48', NULL, '5.00', 1, 1, 0, '3', '1', '1', 0, 0, 0, 1, 1, 1, '0', 0, 0, 0, '25.0000', 23, 1),
                (104, 1, '1000', 'HIDEQTY', '1_small.jpg', '75.0000', 0, '2004-01-11 03:02:51', '2004-01-22 11:21:36', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 0, '0', 0, 0, 0, '75.0000', 23, 1),
                (105, 1, '999', 'MAXSAMPLE-1', 'waybkgnd.gif', '50.0000', 0, '2004-01-11 14:10:59', '2004-01-11 14:36:00', NULL, '1.00', 1, 1, 0, '1', '1', '1', 0, 0, 0, 1, 0, 1, '1', 0, 0, 0, '50.0000', 22, 1),
                (106, 1, '1000', 'MAXSAMPLE-3', 'waybkgnd.gif', '50.0000', 0, '2004-01-11 14:36:08', '2004-01-11 15:32:56', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '3', 0, 0, 0, '50.0000', 22, 1),
                (107, 1, '995', 'FreeShippingNoWeight', 'small_00.jpg', '25.0000', 0, '2004-01-21 01:41:22', '2004-01-21 02:01:54', NULL, '0.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '25.0000', 23, 1),
                (108, 1, '0', 'SoldOut', 'small_00.jpg', '25.0000', 0, '2004-01-21 01:53:20', NULL, NULL, '3.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '25.0000', 23, 1),
                (110, 1, '1000', 'Test120-5SKIP', 'test_demo.jpg', '120.0000', 0, '2004-01-24 16:09:52', '2004-01-24 16:15:25', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '115.0000', 52, 1),
                (111, 1, '1000', 'Test120-90-5SKIP', 'test_demo.jpg', '120.0000', 0, '2004-01-24 16:10:12', '2004-01-24 16:15:27', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '90.0000', 52, 1),
                (112, 1, '998', 'Test2', '', '25.0000', 0, '2004-04-26 02:24:57', '2004-04-26 02:25:44', NULL, '1.00', 1, 1, 0, '2', '1', '1', 0, 0, 0, 1, 0, 1, '0', 2, 0, 0, '25.0000', 53, 1),
                (113, 1, '994', 'Test4', '', '25.0000', 0, '2004-04-26 02:25:03', '2004-04-26 02:25:35', NULL, '1.00', 1, 1, 0, '6', '1', '1', 0, 0, 0, 1, 0, 1, '0', 4, 0, 0, '25.0000', 53, 1),
                (114, 1, '998', 'Test5', '', '25.0000', 0, '2004-04-26 02:25:53', '2004-04-26 02:26:15', NULL, '1.00', 1, 1, 0, '2', '1', '1', 0, 0, 0, 1, 0, 1, '0', 5, 0, 0, '25.0000', 53, 1),
                (115, 1, '999', 'Test1', '', '25.0000', 0, '2004-04-26 02:26:23', '2004-05-06 21:50:19', NULL, '1.00', 1, 1, 0, '1', '1', '1', 0, 0, 0, 1, 0, 1, '0', 1, 0, 0, '25.0000', 53, 1),
                (116, 1, '997', 'Test8', '', '25.0000', 0, '2004-04-26 02:26:54', '2004-04-26 02:27:18', NULL, '1.00', 1, 1, 0, '3', '1', '1', 0, 0, 0, 1, 0, 1, '0', 8, 0, 0, '25.0000', 53, 1),
                (117, 1, '995', 'Test3', '', '25.0000', 0, '2004-04-26 02:27:24', '2004-10-03 12:20:14', NULL, '1.00', 1, 1, 0, '5', '1', '1', 0, 0, 0, 1, 0, 1, '0', 3, 0, 0, '25.0000', 53, 1),
                (118, 1, '999', 'Test10', '', '25.0000', 0, '2004-04-26 02:27:52', '2004-04-26 02:28:14', NULL, '1.00', 1, 1, 0, '1', '1', '1', 0, 0, 0, 1, 0, 1, '0', 10, 0, 0, '25.0000', 53, 1),
                (119, 1, '1000', 'Test6', '', '25.0000', 0, '2004-04-26 02:28:22', '2004-10-06 18:26:25', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 6, 0, 0, '25.0000', 53, 1),
                (120, 1, '1000', 'Test7', '', '25.0000', 0, '2004-04-26 02:29:03', '2004-04-26 02:29:23', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 7, 0, 0, '25.0000', 53, 1),
                (121, 1, '999', 'Test12', '', '25.0000', 0, '2004-04-26 02:29:36', '2004-04-28 13:02:47', NULL, '1.00', 1, 1, 0, '1', '1', '1', 0, 0, 0, 1, 0, 1, '0', 12, 0, 0, '25.0000', 53, 1),
                (122, 1, '998', 'Test9', '', '25.0000', 0, '2004-04-26 02:30:12', '2004-04-26 02:30:32', NULL, '1.00', 1, 1, 0, '2', '1', '1', 0, 0, 0, 1, 0, 1, '0', 9, 0, 0, '25.0000', 53, 1),
                (123, 1, '999', 'Test11', '', '25.0000', 0, '2004-04-26 02:30:41', '2004-04-26 02:31:04', NULL, '1.00', 1, 1, 0, '1', '1', '1', 0, 0, 0, 1, 0, 1, '0', 11, 0, 0, '25.0000', 53, 1),
                (130, 1, '1000', 'Special', '2_small.jpg', '15.0000', 0, '2004-04-28 02:19:53', '2004-10-06 00:05:34', NULL, '2.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 1, 1, '10.0000', 55, 1),
                (127, 1, '1000', 'Normal', 'small_00.jpg', '15.0000', 0, '2004-04-28 01:51:35', '2004-04-28 14:23:29', NULL, '2.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 1, 0, '15.0000', 55, 1),
                (131, 1, '1000', 'PERWORDREQ', '', '0.0000', 0, '2004-05-01 01:31:28', '2004-05-07 21:30:23', NULL, '1.00', 1, 1, 0, '0', '1', '1', 1, 0, 0, 1, 0, 1, '0', 0, 0, 0, '5.0000', 57, 1),
                (132, 1, '997', 'GolfClub', '9_small.jpg', '0.0000', 0, '2004-05-02 12:36:12', '2004-05-02 18:04:36', NULL, '1.00', 1, 1, 0, '3', '1', '1', 1, 0, 0, 1, 0, 1, '0', 0, 0, 0, '13.0050', 58, 1),
                (133, 1, '1000', 'DOWNLOAD2', '2_small.jpg', '49.9900', 0, '2004-05-02 23:51:33', '2004-05-03 00:06:58', NULL, '0.00', 1, 1, 0, '2', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '49.9900', 60, 1),
                (134, 1, '1000', 'PERLETTERREQ', '', '0.0000', 0, '2004-05-07 21:23:58', '2004-05-07 21:29:50', NULL, '1.00', 1, 1, 0, '0', '1', '1', 1, 0, 0, 1, 0, 1, '0', 0, 0, 0, '5.0000', 57, 1),
                (154, 1, '10000', 'ROPE', '9_small.jpg', '1.0000', 0, '2004-05-16 21:08:08', '2004-07-12 17:18:46', NULL, '0.00', 1, 1, 0, '0', '10', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '0.9000', 58, 0),
                (155, 1, '1000', 'PRICEFACTOR', 'sample_image.gif', '10.0000', 0, '2004-05-17 23:03:10', '2004-07-12 17:21:04', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '10.0000', 56, 1),
                (156, 1, '1000', 'PRICEFACTOROFF', 'sample_image.gif', '10.0000', 0, '2004-05-17 23:05:24', '2004-05-17 23:10:12', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '10.0000', 56, 1),
                (157, 1, '1000', 'PRICEFACTOROFFATTR', 'sample_image.gif', '10.0000', 0, '2004-05-17 23:10:18', '2004-05-17 23:13:48', NULL, '1.00', 1, 1, 0, '0', '1', '1', 1, 0, 0, 1, 0, 1, '0', 0, 0, 0, '10.0000', 56, 1),
                (158, 1, '1000', 'ONETIME', 'b_b_grid.gif', '45.0000', 0, '2004-05-17 23:22:08', NULL, NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '45.0000', 56, 1),
                (159, 1, '10000', 'ATTQTYPRICE', 'b_c_grid.gif', '25.0000', 0, '2004-05-17 23:29:31', '2004-05-17 23:49:56', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '25.0000', 56, 1),
                (160, 1, '997', 'GolfClub', '9_small.jpg', '0.0000', 0, '2004-05-18 10:14:35', '2004-05-18 10:15:16', NULL, '1.00', 1, 1, 0, '0', '1', '1', 1, 0, 0, 1, 0, 1, '0', 0, 0, 0, '14.4500', 61, 1),
                (165, 1, '10000', 'ROPE', '9_small.jpg', '1.0000', 0, '2004-05-18 10:42:50', '2004-07-12 17:18:12', NULL, '0.00', 1, 1, 0, '0', '10', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '1.0000', 61, 0),
                (166, 2, '10000', 'RTBHUNTER', 'sooty.jpg', '4.9900', 0, '2004-05-18 10:42:50', '2004-05-18 10:43:00', NULL, '3.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '3.0000', 62, 1),
                (167, 3, '0', '', '', '0.0000', 0, '2004-05-18 10:42:50', '2004-10-06 00:39:10', NULL, '0.00', 1, 0, 0, '0', '1', '1', 0, 0, 0, 0, 0, 0, '0', 0, 0, 0, '0.0000', 63, 1),
                (168, 1, '1000', 'PGT', 'samples/1_small.jpg', '3.9500', 0, '2004-07-12 15:25:32', '2004-07-12 16:26:08', NULL, '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 10, 0, 0, '3.9500', 64, 1),
                (169, 2, '1000', 'PMT', 'samples/2_small.jpg', '3.9500', 0, '2004-07-12 15:27:50', '2004-07-12 16:29:01', NULL, '1.00', 1, 1, NULL, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 20, 0, 0, '3.9500', 64, 1),
                (170, 3, '0', '', 'samples/3_small.jpg', '0.0000', 0, '2004-07-12 15:29:23', '2004-09-27 23:11:25', NULL, '0.00', 1, 0, 0, '0', '1', '1', 0, 0, 0, 0, 0, 0, '0', 30, 0, 0, '0.0000', 64, 1),
                (171, 4, '1000', 'DPT', 'samples/4_small.jpg', '0.9346', 0, '2004-07-12 15:32:40', '2004-07-12 17:46:49', NULL, '0.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 40, 0, 0, '0.9300', 64, 1),
                (172, 5, '1000', 'PFS', 'samples/5_small.jpg', '3.9500', 0, '2004-07-12 15:39:18', '2004-07-12 23:08:43', NULL, '5.00', 1, 0, 0, '0', '1', '1', 0, 0, 0, 1, 1, 1, '0', 50, 0, 0, '3.9500', 64, 1),
                (173, 1, '1000', 'Book', 'b_g_grid.gif', '0.0000', 0, '2004-09-24 23:54:34', '2004-09-26 02:50:59', NULL, '0.00', 1, 1, 0, '0', '1', '1', 1, 0, 0, 1, 0, 1, '0', 0, 0, 0, '52.5000', 61, 1),
                (174, 1, '999', 'TESTCALL', 'call_for_price.jpg', '0.0000', 0, '2004-09-27 13:25:44', '2004-09-27 13:28:54', '2008-02-21 00:00:00', '1.00', 1, 1, 0, '0', '1', '1', 0, 0, 1, 1, 0, 1, '0', 0, 0, 0, '0.0000', 24, 0),
                (175, 1, '1000', 'Normal', '1_small.jpg', '60.0000', 0, '2004-09-27 23:32:52', '2004-10-05 17:13:20', NULL, '2.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 1, 0, '60.0000', 55, 1),
                (176, 1, '1000', 'Normal', 'small_00.jpg', '100.0000', 0, '2004-10-05 16:45:25', '2004-10-05 16:47:22', NULL, '2.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 1, 0, '100.0000', 55, 1),
                (177, 1, '1000', 'Special', '2_small.jpg', '100.0000', 0, '2004-10-05 16:47:45', '2004-10-06 00:05:48', NULL, '2.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 1, 1, '75.0000', 55, 1),
                (179, 1, '1000', 'DOWNLOAD1', '1_small.jpg', '39.0000', 0, '2004-10-06 00:08:33', '2004-10-06 00:18:51', NULL, '0.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 0, 0, 0, '39.0000', 60, 1),
                (178, 1, '1000', 'Normal', '1_small.jpg', '60.0000', 0, '2004-10-05 16:54:52', '2004-10-05 17:15:02', NULL, '2.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 0, 0, 1, '0', 0, 1, 0, '50.0000', 55, 1),
                (180, 4, '1000', 'DPT', 'samples/4_small.jpg', '0.9346', 0, '2004-07-12 15:32:40', '2004-07-12 17:46:49', NULL, '0.00', 1, 1, 0, '0', '1', '1', 0, 0, 0, 1, 0, 1, '0', 40, 0, 0, '0.9300', 63, 1);
        ";

        $this->connection->query($sql);
    }
}

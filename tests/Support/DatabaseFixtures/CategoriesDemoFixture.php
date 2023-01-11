<?php

namespace Tests\Support\DatabaseFixtures;

class CategoriesDemoFixture extends DatabaseFixture implements FixtureContract
{
    public function createTable()
    {
        $sql = "
            DROP TABLE IF EXISTS categories;
            CREATE TABLE categories (
              categories_id int(11) NOT NULL auto_increment,
              categories_image varchar(255) default NULL,
              parent_id int(11) NOT NULL default '0',
              sort_order int(3) default NULL,
              date_added datetime default NULL,
              last_modified datetime default NULL,
              categories_status tinyint(1) NOT NULL default '1',
              PRIMARY KEY  (categories_id),
              KEY idx_parent_id_cat_id_zen (parent_id,categories_id),
              KEY idx_status_zen (categories_status),
              KEY idx_sort_order_zen (sort_order)
            ) ENGINE=MyISAM;
        ";

        $this->connection->query($sql);
    }

    public function seeder()
    {
        $sql = "
            INSERT INTO categories (categories_id, categories_image, parent_id, sort_order, date_added, last_modified, categories_status) VALUES (1, 'categories/category_hardware.gif', 0, 1, '2003-12-23 03:18:19', '2004-05-21 00:32:17', 1),
            (2, 'categories/category_software.gif', 0, 2, '2003-12-23 03:18:19', '2004-05-22 21:14:57', 1),
            (3, 'categories/category_dvd_movies.gif', 0, 3, '2003-12-23 03:18:19', '2004-05-21 00:22:39', 1),
            (4, 'categories/subcategory_graphic_cards.gif', 1, 0, '2003-12-23 03:18:19', NULL, 1),
            (5, 'categories/subcategory_printers.gif', 1, 0, '2003-12-23 03:18:19', NULL, 1),
            (6, 'categories/subcategory_monitors.gif', 1, 0, '2003-12-23 03:18:19', NULL, 1),
            (7, 'categories/subcategory_speakers.gif', 1, 0, '2003-12-23 03:18:19', NULL, 1),
            (8, 'categories/subcategory_keyboards.gif', 1, 0, '2003-12-23 03:18:19', NULL, 1),
            (9, 'categories/subcategory_mice.gif', 1, 0, '2003-12-23 03:18:19', '2004-05-21 00:34:10', 1),
            (10, 'categories/subcategory_action.gif', 3, 0, '2003-12-23 03:18:19', '2004-05-21 00:39:17', 1),
            (11, 'categories/subcategory_science_fiction.gif', 3, 0, '2003-12-23 03:18:19', NULL, 1),
            (12, 'categories/subcategory_comedy.gif', 3, 0, '2003-12-23 03:18:19', NULL, 1),
            (13, 'categories/subcategory_cartoons.gif', 3, 0, '2003-12-23 03:18:19', '2004-05-21 00:23:13', 1),
            (14, 'categories/subcategory_thriller.gif', 3, 0, '2003-12-23 03:18:19', NULL, 1),
            (15, 'categories/subcategory_drama.gif', 3, 0, '2003-12-23 03:18:19', NULL, 1),
            (16, 'categories/subcategory_memory.gif', 1, 0, '2003-12-23 03:18:19', NULL, 1),
            (17, 'categories/subcategory_cdrom_drives.gif', 1, 0, '2003-12-23 03:18:19', NULL, 1),
            (18, 'categories/subcategory_simulation.gif', 2, 0, '2003-12-23 03:18:19', NULL, 1),
            (19, 'categories/subcategory_action_games.gif', 2, 0, '2003-12-23 03:18:19', NULL, 1),
            (20, 'categories/subcategory_strategy.gif', 2, 0, '2003-12-23 03:18:19', NULL, 1),
            (21, 'categories/gv_25.gif', 0, 4, '2003-12-23 03:18:19', '2004-05-21 00:26:06', 1),
            (22, 'categories/box_of_color.gif', 0, 5, '2003-12-23 03:18:19', '2004-05-21 00:28:43', 1),
            (23, 'waybkgnd.gif', 0, 500, '2003-12-28 02:26:19', '2003-12-29 23:21:35', 1),
            (24, 'categories/category_free.gif', 0, 600, '2003-12-28 11:48:46', '2004-01-02 19:13:45', 1),
            (25, 'sample_image.gif', 0, 515, '2003-12-31 02:39:17', '2004-01-24 01:49:12', 1),
            (27, 'sample_image.gif', 49, 10, '2004-01-04 14:13:08', '2004-01-24 16:16:23', 1),
            (28, 'sample_image.gif', 0, 510, '2004-01-04 17:13:47', '2004-01-05 23:54:23', 1),
            (31, 'sample_image.gif', 48, 30, '2004-01-04 23:16:46', '2004-01-24 01:48:29', 1),
            (32, 'sample_image.gif', 48, 40, '2004-01-05 01:34:56', '2004-01-24 01:48:36', 1),
            (33, 'categories/subcategory.gif', 0, 700, '2004-01-05 02:08:31', '2004-05-20 10:35:31', 1),
            (34, 'categories/subcategory.gif', 33, 10, '2004-01-05 02:08:50', '2004-05-20 10:35:57', 1),
            (35, 'categories/subcategory.gif', 33, 20, '2004-01-05 02:09:01', '2004-01-24 00:07:33', 1),
            (36, 'categories/subcategory.gif', 33, 30, '2004-01-05 02:09:12', '2004-01-24 00:07:41', 1),
            (37, 'categories/subcategory.gif', 35, 10, '2004-01-05 02:09:28', '2004-01-24 00:22:39', 1),
            (38, 'categories/subcategory.gif', 35, 20, '2004-01-05 02:09:39', '2004-01-24 00:22:46', 1),
            (39, 'categories/subcategory.gif', 35, 30, '2004-01-05 02:09:49', '2004-01-24 00:22:53', 1),
            (40, 'categories/subcategory.gif', 34, 10, '2004-01-05 02:17:27', '2004-05-20 10:36:19', 1),
            (41, 'categories/subcategory.gif', 36, 10, '2004-01-05 02:21:02', '2004-01-24 00:23:04', 1),
            (42, 'categories/subcategory.gif', 36, 30, '2004-01-05 02:21:14', '2004-01-24 00:23:18', 1),
            (43, 'categories/subcategory.gif', 34, 20, '2004-01-05 02:21:29', '2004-01-24 00:21:37', 1),
            (44, 'categories/subcategory.gif', 36, 20, '2004-01-05 02:21:47', '2004-01-24 00:23:11', 1),
            (45, 'sample_image.gif', 48, 10, '2004-01-05 23:54:56', '2004-01-24 01:48:22', 1),
            (46, 'sample_image.gif', 50, 10, '2004-01-06 00:01:48', '2004-01-24 01:39:56', 1),
            (47, 'sample_image.gif', 48, 20, '2004-01-06 03:09:57', '2004-01-24 01:48:05', 1),
            (48, 'sample_image.gif', 0, 1000, '2004-01-07 02:24:07', '2004-01-07 02:44:26', 1),
            (49, 'sample_image.gif', 0, 1100, '2004-01-07 02:27:31', '2004-01-07 02:44:34', 1),
            (50, 'sample_image.gif', 0, 1200, '2004-01-07 02:28:18', '2004-01-07 02:47:19', 1),
            (51, 'sample_image.gif', 50, 20, '2004-01-07 02:33:55', '2004-01-24 01:40:05', 1),
            (52, 'sample_image.gif', 49, 20, '2004-01-24 16:09:35', '2004-01-24 16:16:33', 1),
            (53, 'categories/subcategory.gif', 0, 1500, '2004-04-25 23:07:41', NULL, 1),
            (54, 'categories/subcategory.gif', 0, 1510, '2004-04-26 12:02:35', '2004-05-20 11:45:20', 1),
            (55, 'categories/subcategory.gif', 54, 0, '2004-04-28 01:48:47', '2004-05-20 11:45:51', 1),
            (56, 'categories/subcategory.gif', 54, 0, '2004-04-28 01:49:16', '2004-04-28 01:53:14', 1),
            (57, 'categories/subcategory.gif', 54, 0, '2004-05-01 01:29:13', NULL, 1),
            (58, 'categories/subcategory.gif', 54, 110, '2004-05-02 12:35:02', '2004-05-18 10:46:13', 1),
            (60, 'categories/subcategory.gif', 54, 0, '2004-05-02 23:45:21', NULL, 1),
            (61, 'categories/subcategory.gif', 54, 100, '2004-05-18 10:13:46', '2004-05-18 10:46:02', 1),
            (62, 'sample_image.gif', 0, 1520, '2003-12-23 03:18:19', '2004-05-22 21:14:57', 1),
            (63, 'categories/subcategory.gif', 0, 1530, '2003-12-23 03:18:19', '2004-07-12 17:45:24', 1),
            (64, 'categories/subcategory.gif', 0, 1550, '2004-07-12 15:22:27', NULL, 1);
        ";

        $this->connection->query($sql);
    }
}

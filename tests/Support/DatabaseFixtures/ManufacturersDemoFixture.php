<?php

namespace Tests\Support\DatabaseFixtures;

class ManufacturersDemoFixture extends DatabaseFixture implements FixtureContract
{
    public function createTable()
    {
        $sql = "
            DROP TABLE IF EXISTS manufacturers;
            CREATE TABLE manufacturers (
              manufacturers_id int(11) NOT NULL auto_increment,
              manufacturers_name varchar(32) NOT NULL default '',
              manufacturers_image varchar(255) default NULL,
              date_added datetime default NULL,
              last_modified datetime default NULL,
              featured tinyint default 0,
              PRIMARY KEY  (manufacturers_id),
              KEY idx_mfg_name_zen (manufacturers_name)
            ) ENGINE=MyISAM;
        ";

        $this->connection->query($sql);
    }

    public function seeder()
    {
        $sql = "
            INSERT manufacturers (manufacturers_id, manufacturers_name, manufacturers_image, date_added, last_modified) VALUES (1, 'Matrox', 'manufacturers/manufacturer_matrox.gif', '2003-12-23 03:18:19', NULL),
            (2, 'Microsoft', 'manufacturers/manufacturer_microsoft.gif', '2003-12-23 03:18:19', NULL),
            (3, 'Warner', 'manufacturers/manufacturer_warner.gif', '2003-12-23 03:18:19', NULL),
            (4, 'Fox', 'manufacturers/manufacturer_fox.gif', '2003-12-23 03:18:19', NULL),
            (5, 'Logitech', 'manufacturers/manufacturer_logitech.gif', '2003-12-23 03:18:19', NULL),
            (6, 'Canon', 'manufacturers/manufacturer_canon.gif', '2003-12-23 03:18:19', NULL),
            (7, 'Sierra', 'manufacturers/manufacturer_sierra.gif', '2003-12-23 03:18:19', NULL),
            (8, 'GT Interactive', 'manufacturers/manufacturer_gt_interactive.gif', '2003-12-23 03:18:19', NULL),
            (9, 'Hewlett Packard', 'manufacturers/manufacturer_hewlett_packard.gif', '2003-12-23 03:18:19', NULL);
        ";

        $this->connection->query($sql);
    }
}

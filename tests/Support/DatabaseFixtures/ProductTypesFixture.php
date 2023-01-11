<?php

namespace Tests\Support\DatabaseFixtures;

class ProductTypesFixture extends DatabaseFixture implements FixtureContract
{
    public function createTable()
    {
        $sql = "
            DROP TABLE IF EXISTS product_types;
            CREATE TABLE product_types (
                type_id int(11) NOT NULL auto_increment,
                type_name varchar(255) NOT NULL default '',
                type_handler varchar(255) NOT NULL default '',
                type_master_type int(11) NOT NULL default '1',
                allow_add_to_cart char(1) NOT NULL default 'Y',
                default_image varchar(255) NOT NULL default '',
                date_added datetime NOT NULL default '0001-01-01 00:00:00',
                last_modified datetime NOT NULL default '0001-01-01 00:00:00',
                PRIMARY KEY  (type_id),
                KEY idx_type_master_type_zen (type_master_type)
            ) ENGINE=MyISAM;
        ";

        $this->connection->query($sql);
    }

    public function seeder()
    {
        $sql = "
            INSERT INTO product_types VALUES (1, 'Product - General', 'product', '1', 'Y', '', now(), now());
            INSERT INTO product_types VALUES (2, 'Product - Music', 'product_music', '1', 'Y', '', now(), now());
            INSERT INTO product_types VALUES (3, 'Document - General', 'document_general', '3', 'N', '', now(), now());
            INSERT INTO product_types VALUES (4, 'Document - Product', 'document_product', '3', 'Y', '', now(), now());
            INSERT INTO product_types VALUES (5, 'Product - Free Shipping', 'product_free_shipping', '1', 'Y', '', now(), now());
        ";

        $this->connection->query($sql);
    }
}

<?php

namespace Tests\Support\DatabaseFixtures;

class MetaTagsProductsDescriptionFixture extends DatabaseFixture implements FixtureContract
{
    public function createTable()
    {
        $sql = "
            DROP TABLE IF EXISTS meta_tags_products_description;
            CREATE TABLE meta_tags_products_description (
              products_id int(11) NOT NULL,
              language_id int(11) NOT NULL default '1',
              metatags_title varchar(255) NOT NULL default '',
              metatags_keywords text,
              metatags_description text,
              PRIMARY KEY  (products_id,language_id)
            ) ENGINE=MyISAM;
        ";

        $this->connection->query($sql);
    }

    public function seeder()
    {
        $sql = "
            INSERT INTO meta_tags_products_description VALUES (19, 1, '', 'incredible, top-rated', '');
            INSERT INTO meta_tags_products_description VALUES (699, 1, '', 'product of the year', '');
        ";

        $this->connection->query($sql);
    }
}

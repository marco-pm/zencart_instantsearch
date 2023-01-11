<?php

namespace Tests\Support\DatabaseFixtures;

class CountProductsViewsDemoFixture extends DatabaseFixture implements FixtureContract
{
    public function createTable()
    {
        $sql = "
            DROP TABLE IF EXISTS count_product_views;
            CREATE TABLE count_product_views (
                product_id int(11) NOT NULL default 0,
                language_id int(11) NOT NULL default 1,
                date_viewed date NOT NULL,
                views int(11) default NULL,
                PRIMARY KEY (product_id, language_id, date_viewed),
                KEY idx_pid_lang_date_zen (language_id, product_id, date_viewed),
                KEY idx_date_pid_lang_zen (date_viewed, product_id, language_id)
            ) ENGINE=MyISAM;
        ";

        $this->connection->query($sql);
    }

    public function seeder()
    {
        $sql = "
            INSERT INTO count_product_views (product_id, language_id, date_viewed, views) VALUES
            (160, 1, now(), 3),
            (168, 1, subdate(current_date, 3), 9),
            (168, 1, subdate(current_date, 2), 3),
            (168, 1, subdate(current_date, 1), 8),
            (168, 1, now(), 15),
            (169, 1, subdate(current_date, 1), 4),
            (169, 1, now(), 10),
            (171, 1, now(), 18),
            (172, 1, now(), 7),
            (174, 1, now(), 100),
            (105, 1, now(), 92);
        ";

        $this->connection->query($sql);
    }
}

<?php

namespace Tests\Support\DatabaseFixtures;

class CategoriesDescriptionDemoFixture extends DatabaseFixture implements FixtureContract
{
    public function createTable()
    {
        $sql = "
            DROP TABLE IF EXISTS categories_description;
            CREATE TABLE categories_description (
              categories_id int(11) NOT NULL default '0',
              language_id int(11) NOT NULL default '1',
              categories_name varchar(32) NOT NULL default '',
              categories_description text NOT NULL,
              PRIMARY KEY  (categories_id,language_id),
              KEY idx_categories_name_zen (categories_name)
            ) ENGINE=MyISAM;
        ";

        $this->connection->query($sql);
    }

    public function seeder()
    {
        $sql = "
            INSERT INTO categories_description (categories_id, language_id, categories_name, categories_description) VALUES (1, 1, 'Hardware', 'We offer a variety of Hardware from printers to graphics cards and mice to keyboards.'),
            (2, 1, 'Software', 'Select from an exciting list of software titles. <br /><br />Not seeing a title that you are looking for?'),
            (3, 1, 'DVD Movies', 'We offer a variety of DVD movies enjoyable for the whole family.<br /><br />Please browse the various categories to find your favorite movie today!'),
            (4, 1, 'Graphics Cards', ''),
            (5, 1, 'Printers', ''),
            (6, 1, 'Monitors', ''),
            (7, 1, 'Speakers', ''),
            (8, 1, 'Keyboards', ''),
            (9, 1, 'Mice', 'Pick the right mouse for your individual computer needs!<br /><br />Contact Us if you are looking for a particular mouse that we do not currently have in stock.'),
            (10, 1, 'Action', '<p>Get into the action with our Action collection of DVD movies!<br /><br />Don\'t miss the excitement and order your\'s today!<br /><br /></p>'),
            (11, 1, 'Science Fiction', ''),
            (12, 1, 'Comedy', ''),
            (13, 1, 'Cartoons', 'Something you can enjoy with children of all ages!'),
            (14, 1, 'Thriller', ''),
            (15, 1, 'Drama', ''),
            (16, 1, 'Memory', ''),
            (17, 1, 'CDROM Drives', ''),
            (18, 1, 'Simulation', ''),
            (19, 1, 'Action', ''),
            (20, 1, 'Strategy', ''),
            (60, 1, 'Downloads', ''),
            (58, 1, 'Real Sale', ''),
            (21, 1, 'Gift Certificates', 'Send a Gift Certificate today!<br /><br />Gift Certificates are good for anything in the store.'),
            (57, 1, 'Text Pricing', ''),
            (56, 1, 'Attributes', ''),
            (22, 1, 'Big Linked', 'All of these products are &quot;Linked Products&quot;.<br /><br />This means that they appear in more than one Category.<br /><br />However, you only have to maintain the product in one place.<br /><br />The Master Product is used for pricing purposes.'),
            (55, 1, 'Discount Qty', '<p>Discount Quantities can be set for Products or on the individual attributes.<br /><br />Discounts on the Product do NOT reflect on the attributes price.<br /><br />Only discounts based on Special and Sale Prices are applied to attribute prices.</p>'),
            (23, 1, 'Test Examples', ''),
            (24, 1, 'Free Call Stuff', ''),
            (25, 1, 'Test 10% by Attrib', ''),
            (27, 1, '$5.00 off', ''),
            (28, 1, 'Test 10%', ''),
            (31, 1, '10% off Skip', ''),
            (32, 1, '10% off Price', ''),
            (47, 1, '10% off Attrib', ''),
            (33, 1, 'A Top Level Cat', '<p>This is a top level category description.</p>'),
            (34, 1, 'SubLevel 2 A', 'This is a sublevel category description.'),
            (35, 1, 'SubLevel 2 B', ''),
            (36, 1, 'SubLevel 2 C', ''),
            (37, 1, 'Sub Sub Cat 2B1', ''),
            (38, 1, 'Sub Sub Cat 2B2', ''),
            (39, 1, 'Sub Sub Cat 2B3', ''),
            (40, 1, 'Sub Sub Cat 2A1', 'This is a sub-sub level category description.'),
            (41, 1, 'Sub Sub Cat 2C1', ''),
            (42, 1, 'Sub Sub Cat 2C3', ''),
            (43, 1, 'Sub Sub Cat 2A2', ''),
            (44, 1, 'Sub Sub Cat 2C2', ''),
            (45, 1, '10% off', ''),
            (46, 1, 'Set $100', ''),
            (48, 1, 'Sale Percentage', ''),
            (49, 1, 'Sale Deduction', ''),
            (50, 1, 'Sale New Price', ''),
            (51, 1, 'Set $100 Skip', ''),
            (52, 1, '$5.00 off Skip', ''),
            (53, 1, 'Big Unlinked', ''),
            (54, 1, 'New v1.2', '<p>The New Products show many of the newest features that have been added to Zen Cart.<br /><br />Take the time to review these and the other Demo Products to better understand all the options and features that Zen Cart has to offer.</p>'),
            (61, 1, 'Real', ''),
            (62, 1, 'Music', ''),
            (63, 1, 'Documents', 'Documents can now be added to the category tree. For example you may want to add servicing/Technical documents. Or use Documents as an integrated FAQ system on your site. The implemetation here is fairly spartan, but could be expanded to offer PDF downloads, links to purchaseable download files. The possibilities are endless and left to your imagination.'),
            (64, 1, 'Mixed Product Types', 'This is a category with mixed product types.\r\n\r\nThis includes both products and documents. There are two types of documents - Documents that are for reading and Documents that are for reading and purchasing.');
        ";

        $this->connection->query($sql);
    }
}

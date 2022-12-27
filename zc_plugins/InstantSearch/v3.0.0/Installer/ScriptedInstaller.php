<?php

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        // add fulltext indexes on products_description table
        $this->dbConn->Execute("ALTER TABLE " . TABLE_PRODUCTS_DESCRIPTION . " ENGINE = InnoDB");

        $index = $this->dbConn->Execute("
            SHOW INDEX
            FROM " . TABLE_PRODUCTS_DESCRIPTION . "
            WHERE column_name = 'products_name'
            AND index_type = 'FULLTEXT'
        ");
        if ($index->EOF) {
            $this->dbConn->Execute("
                CREATE FULLTEXT INDEX idx_products_name
                ON " . TABLE_PRODUCTS_DESCRIPTION . "(products_name)
            ");
        }

        $index = $this->dbConn->Execute("
            SHOW INDEX
            FROM " . TABLE_PRODUCTS_DESCRIPTION . "
            WHERE column_name = 'products_description'
            AND index_type = 'FULLTEXT'
        ");
        if ($index->EOF) {
            $this->dbConn->Execute("
                CREATE FULLTEXT INDEX idx_products_description
                ON " . TABLE_PRODUCTS_DESCRIPTION . "(products_description)
            ");
        }

        $this->dbConn->Execute("OPTIMIZE TABLE " . TABLE_PRODUCTS_DESCRIPTION);


        // add configuration group
        $configurationGroupTitle = 'Instant Search';
        $configuration = $this->dbConn->Execute("
            SELECT configuration_group_id
            FROM " . TABLE_CONFIGURATION_GROUP . "
            WHERE configuration_group_title = '$configurationGroupTitle'
            LIMIT 1
        ");
        if ($configuration->EOF) {
            $this->dbConn->Execute("
                INSERT INTO " . TABLE_CONFIGURATION_GROUP . " (configuration_group_title, configuration_group_description, sort_order, visible)
                VALUES ('$configurationGroupTitle', '$configurationGroupTitle', 1, 1);
            ");
            $cgi = $this->dbConn->Insert_ID();
            $this->dbConn->Execute("
                UPDATE " . TABLE_CONFIGURATION_GROUP . "
                SET sort_order = $cgi
                WHERE configuration_group_id = $cgi
            ");
        } else {
            $cgi = $configuration->fields['configuration_group_id'];
        }


        // register admin page
        zen_deregister_admin_pages(['configInstantSearch']);
        zen_register_admin_page('configInstantSearch', 'BOX_CONFIGURATION_INSTANT_SEARCH_OPTIONS', 'FILENAME_CONFIGURATION', "gID=$cgi", 'configuration', 'Y');


        // insert configuration settings
        $sql =
            "INSERT INTO " . TABLE_CONFIGURATION . "
                (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, date_added, sort_order, use_function, set_function)
                VALUES
                ('Enable Instant Search', 'INSTANT_SEARCH_ENABLE', 'true', 'Enable Instant Search', $cgi, now(), 0, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Maximum Number of Results', 'INSTANT_SEARCH_MAX_NUMBER_OF_RESULTS', '5', 'Maximum number of results displayed in the dropdown.', $cgi, now(), 50, NULL, NULL),
                ('Minimum Length of Search Query', 'INSTANT_SEARCH_MIN_WORDSEARCH_LENGTH', '3', 'Minimum number of characters that must be entered before Instant Search is initiated.', $cgi, now(), 100, NULL, NULL),
                ('Maximum Length of Search Query', 'INSTANT_SEARCH_MAX_WORDSEARCH_LENGTH', '100', 'Maximum string length allowed for Instant Search. If the search string length exceeds this value, the instant search will not be performed.', $cgi, now(), 150, NULL, NULL),
                ('Search Delay', 'INSTANT_SEARCH_INPUT_WAIT_TIME', '50', 'Delay the execution of the instant search query by this time period (in milliseconds), after a character is entered, to prevent unnecessary queries while the user is typing.', $cgi, now(), 200, NULL, NULL),
                ('Search Product Description', 'INSTANT_SEARCH_INCLUDE_PRODUCT_DESCRIPTION', 'true', 'Search also into the product descriptions. Set to false to improve Instant Search performance.', $cgi, now(), 220, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Search Product Model', 'INSTANT_SEARCH_INCLUDE_PRODUCT_MODEL', 'true', 'Search also into the product models.', $cgi, now(), 250, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Search Categories', 'INSTANT_SEARCH_INCLUDE_CATEGORIES', 'true', 'Search also into the category names.', $cgi, now(), 300, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Search Manufacturers', 'INSTANT_SEARCH_INCLUDE_MANUFACTURERS', 'true', 'Search also into the manufacturer names.', $cgi, now(), 350, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Display Images', 'INSTANT_SEARCH_DISPLAY_IMAGE', 'true', 'Display the product/category/manufacturer\'s image in the results.', $cgi, now(), 400, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Display Product Price', 'INSTANT_SEARCH_DISPLAY_PRODUCT_PRICE', 'true', 'Display the product\'s price in the results.', $cgi, now(), 450, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Display Product Model', 'INSTANT_SEARCH_DISPLAY_PRODUCT_MODEL', 'false', 'Display the product\'s model in the results.', $cgi, now(), 500, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Display Category Count', 'INSTANT_SEARCH_DISPLAY_CATEGORIES_COUNT', 'true', 'Display the number of products for the matched categories (only if <em>Search Categories</em> is set to true).', $cgi, now(), 550, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Display Manufacturer Count', 'INSTANT_SEARCH_DISPLAY_MANUFACTURERS_COUNT', 'true', 'Display the number of products for the matched manufacturers (only if <em>Search Manufacturers</em> is set to true).', $cgi, now(), 600, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Image Width', 'INSTANT_SEARCH_IMAGE_WIDTH', '100', 'Width of the product/category/manufacturer\'s image displayed in the results.', $cgi, now(), 650, NULL, NULL),
                ('Image Height', 'INSTANT_SEARCH_IMAGE_HEIGHT', '80', 'Height of the product/category/manufacturer\'s image displayed in the results.', $cgi, now(), 700, NULL, NULL),
                ('Higlight Search Terms in Bold', 'INSTANT_SEARCH_HIGHLIGHT_TEXT', 'suggestion', '<code>none</code>: no highlight<br><code>query</code>: highlight the user search terms<br><code>suggestion</code>: highlight the autocompleted text', $cgi, now(), 750, NULL, 'zen_cfg_select_option(array(\'none\', \'query\', \'suggestion\'),'),
                ('Input Box Selector', 'INSTANT_SEARCH_INPUT_BOX_SELECTOR', 'input[name=\"keyword\"]', 'CSS selector of the search input box(es). You might need to change it if you\'re using a custom template and the results dropdown is not showing. Default: <code>input[name=\"keyword\"]</code>', $cgi, now(), 800, NULL, NULL)
            ";
        $this->executeInstallerSql($sql);
    }

    protected function executeUninstall()
    {
        // deregister admin pae
        zen_deregister_admin_pages(['configInstantSearch']);


        // remove configuration settings
        $sql = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'INSTANT_SEARCH_%'";
        $this->executeInstallerSql($sql);


        // remove configuration group
        $sql = "DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_title = 'Instant Search'";
        $this->executeInstallerSql($sql);


        // remove fulltext indexes from products_description table
        $index = $this->dbConn->Execute("
            SHOW INDEX
            FROM " . TABLE_PRODUCTS_DESCRIPTION . "
            WHERE key_name = 'idx_products_name'
            AND column_name = 'products_name'
            AND index_type = 'FULLTEXT'
        ");
        if (!$index->EOF) {
            $this->dbConn->Execute("
                DROP INDEX idx_products_name
                ON " . TABLE_PRODUCTS_DESCRIPTION
            );
        }

        $index = $this->dbConn->Execute("
            SHOW INDEX
            FROM " . TABLE_PRODUCTS_DESCRIPTION . "
            WHERE key_name = 'idx_products_description'
            AND column_name = 'products_description'
            AND index_type = 'FULLTEXT'
        ");
        if (!$index->EOF) {
            $this->dbConn->Execute("
                DROP INDEX idx_products_description
                ON " . TABLE_PRODUCTS_DESCRIPTION
            );
        }

        $this->dbConn->Execute("ALTER TABLE " . TABLE_PRODUCTS_DESCRIPTION . " ENGINE = MyISAM");
    }
}

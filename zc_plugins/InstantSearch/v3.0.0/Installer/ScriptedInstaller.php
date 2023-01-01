<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        // Add FULLTEXT indexes on products_description table
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

        // Add configuration group
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

        // Register admin page
        zen_deregister_admin_pages(['configInstantSearch']);
        zen_register_admin_page('configInstantSearch', 'BOX_CONFIGURATION_INSTANT_SEARCH_OPTIONS', 'FILENAME_CONFIGURATION', "gID=$cgi", 'configuration', 'Y');

        // Insert configuration settings
        $sql = "
            INSERT INTO " . TABLE_CONFIGURATION . "
                (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, date_added, sort_order, use_function, set_function)
            VALUES
                ('Dropdown - Enable', 'INSTANT_SEARCH_DROPDOWN_ENABLED', 'true', 'Enable dropdown suggestions on search input boxes.', $cgi, now(), 0, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Listing Page - Enable', 'INSTANT_SEARCH_PAGE_ENABLED', 'true', 'Displays a listing page with the search results after the user submits a search input box. It does not replace the (formerly advanced) search and results page.', $cgi, now(), 50, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Dropdown - Search Delay', 'INSTANT_SEARCH_DROPDOWN_INPUT_WAIT_TIME', '50', 'Delay the execution of the Instant Search query by this time period (in milliseconds), after a character is entered, to prevent unnecessary queries while the user is typing.', $cgi, now(), 100, NULL, NULL),
                ('Dropdown - Maximum Number of Results', 'INSTANT_SEARCH_DROPDOWN_MAX_RESULTS', '5', 'Maximum number of results displayed in the dropdown.', $cgi, now(), 150, NULL, NULL),
                ('Dropdown - Minimum Length of Search Query', 'INSTANT_SEARCH_DROPDOWN_MIN_WORDSEARCH_LENGTH', '3', 'Minimum number of characters that must be entered before Instant Search is initiated.', $cgi, now(), 200, NULL, NULL),
                ('Dropdown - Maximum Length of Search Query', 'INSTANT_SEARCH_DROPDOWN_MAX_WORDSEARCH_LENGTH', '100', 'Maximum string length allowed for Instant Search. If the search string length exceeds this value, the Instant Search will not be performed.', $cgi, now(), 250, NULL, NULL),
                ('Dropdown - Fields to Search and Order', 'INSTANT_SEARCH_DROPDOWN_FIELDS_LIST', 'model-exact,name-description,model-broad,category,manufacturer', 'List of the fields to search, separated by comma. You can exclude a field from the search by not including in the list.<br>The list also determines the order in which fields are searched, and therefore the position in the results. E.g. putting <code>model-exact</code> before <code>name-description</code> will show results where the model is equal to the query first, and then products that have name or description that contains the query.<br><br>List of fields:<ul><li><b>name-description</b> (product name and description) OR <b>name</b> (product name only, don\\'t search in descriptions) – only ONE of the two</li><li><b>model-exact</b> (product model - exact match, the query contains only and exactly the model)</li><li><b>model-broad</b> (product model - broad match, the query contains also the model of part of it)</li><li><b>category</b></li><li><b>manufacturer</b></li></ul>Default: <code>model-exact,name-description,model-broad,category,manufacturer</code><br>', $cgi, now(), 300, NULL, NULL),
                ('Dropdown - Include Related Products in the Results (Query Expansion)', 'INSTANT_SEARCH_DROPDOWN_USE_QUERY_EXPANSION', 'true', 'Show also products with related name and/or description (Query Expansion function of MySQL Full-Text Search).<br><br>Example: one product has name <em>Logitech Wired Keyboard</em> and another product has name <em>Microsoft Keyboard and Mouse, wireless set</em>. The user searches for <em>logitech</em>. Without query expansion, only the first product is shown. With query expansion, the second product is shown too, because it contains the word <em>keyboard</em> that is present also in the matched product (so it could be relevant for the user).<br><br>Note: this option does NOT search for strings that differ by a letter or so (e.g. <em>Did you mean...?</em> feature).<br>', $cgi, now(), 350, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Dropdown - Display Images', 'INSTANT_SEARCH_DROPDOWN_DISPLAY_IMAGE', 'true', 'Display the product/category/manufacturer\'s image.', $cgi, now(), 400, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Dropdown - Display Product Price', 'INSTANT_SEARCH_DROPDOWN_DISPLAY_PRODUCT_PRICE', 'true', 'Display the product\'s price.', $cgi, now(), 450, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Dropdown - Display Product Model', 'INSTANT_SEARCH_DROPDOWN_DISPLAY_PRODUCT_MODEL', 'false', 'Display the product\'s model.', $cgi, now(), 500, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Dropdown - Display Category Count', 'INSTANT_SEARCH_DROPDOWN_DISPLAY_CATEGORIES_COUNT', 'true', 'Display the number of products for the matched categories.', $cgi, now(), 550, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Dropdown - Display Manufacturer Count', 'INSTANT_SEARCH_DROPDOWN_DISPLAY_MANUFACTURERS_COUNT', 'true', 'Display the number of products for the matched manufacturers.', $cgi, now(), 600, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Dropdown - Image Width', 'INSTANT_SEARCH_DROPDOWN_IMAGE_WIDTH', '100', 'Width of the product/category/manufacturer\'s image.', $cgi, now(), 650, NULL, NULL),
                ('Dropdown - Image Height', 'INSTANT_SEARCH_DROPDOWN_IMAGE_HEIGHT', '80', 'Height of the product/category/manufacturer\'s image.', $cgi, now(), 700, NULL, NULL),
                ('Dropdown - Higlight Search Terms in Bold', 'INSTANT_SEARCH_DROPDOWN_HIGHLIGHT_TEXT', 'suggestion', '<code>none</code>: no highlight<br><code>query</code>: highlight the user search terms<br><code>suggestion</code>: highlight the autocompleted text', $cgi, now(), 750, NULL, 'zen_cfg_select_option(array(\'none\', \'query\', \'suggestion\'),'),
                ('Dropdown - Input Box Selector', 'INSTANT_SEARCH_DROPDOWN_INPUT_BOX_SELECTOR', 'input[name=\"keyword\"]', 'CSS selector of the search input box(es). You might need to change it if you\'re using a custom template and the results dropdown is not showing. Default: <code>input[name=\"keyword\"]</code>', $cgi, now(), 800, NULL, NULL),
                ('Listing Page - Maximum Number of Results per Page', 'INSTANT_SEARCH_PAGE_MAX_RESULTS_PER_PAGE', '5', 'Maximum number of products per page displayed in the search results listing page.', $cgi, now(), 850, NULL, NULL),
                ('Listing Page - Fields to Search and Order', 'INSTANT_SEARCH_PAGE_FIELDS_LIST', 'model-exact,name-description,model-broad', 'List of the fields to search, separated by comma. You can exclude a field from the search by not including in the list.<br>The list also determines the order in which fields are searched, and therefore the position in the results. E.g. putting <code>model-exact</code> before <code>name-description</code> will show results where the model is equal to the query first, and then products that have name or description that contains the query.<br><br>List of fields:<ul><li><b>name-description</b> (product name and description) OR <b>name</b> (product name only, don\\'t search in descriptions) – only ONE of the two</li><li><b>model-exact</b> (product model - exact match, the query contains only and exactly the model)</li><li><b>model-broad</b> (product model - broad match, the query contains also the model of part of it)</li></ul>Default: <code>model-exact,name-description,model-broad</code><br>', $cgi, now(), 900, NULL, NULL),
                ('Listing Page - Include Related Products in the Results (Query Expansion)', 'INSTANT_SEARCH_PAGE_USE_QUERY_EXPANSION', 'true', 'Show also products with related name and/or description (Query Expansion function of MySQL Full-Text Search).<br><br>Example: one product has name <em>Logitech Wired Keyboard</em> and another product has name <em>Microsoft Keyboard and Mouse, wireless set</em>. The user searches for <em>logitech</em>. Without query expansion, only the first product is shown. With query expansion, the second product is shown too, because it contains the word <em>keyboard</em> that is present also in the matched product (so it could be relevant for the user).<br><br>Note: this option does NOT search for strings that differ by a letter or so (e.g. <em>Did you mean...?</em> feature).<br>', $cgi, now(), 950, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),')
        ";
        $this->executeInstallerSql($sql);
    }

    protected function executeUninstall()
    {
        // Deregister admin pae
        zen_deregister_admin_pages(['configInstantSearch']);

        // Remove configuration settings
        $sql = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'INSTANT_SEARCH_%'";
        $this->executeInstallerSql($sql);

        // Remove configuration group
        $sql = "DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_title = 'Instant Search'";
        $this->executeInstallerSql($sql);

        // Remove FULLTEXT indexes from products_description table
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

<?php

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        $configurationGroupTitle = 'Instant Search';
        $configuration = $this->dbConn->Execute("SELECT configuration_group_id FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_title = '$configurationGroupTitle' LIMIT 1");
        if ($configuration->EOF) {
            $this->dbConn->Execute("INSERT INTO " . TABLE_CONFIGURATION_GROUP . "
                     (configuration_group_title, configuration_group_description, sort_order, visible)
                     VALUES ('$configurationGroupTitle', '$configurationGroupTitle', 1, 1);");
            $cgi = $this->dbConn->Insert_ID();
            $this->dbConn->Execute("UPDATE " . TABLE_CONFIGURATION_GROUP . " SET sort_order = $cgi WHERE configuration_group_id = $cgi");
        } else {
            $cgi = $configuration->fields['configuration_group_id'];
        }

        zen_deregister_admin_pages(['configInstantSearch']);
        zen_register_admin_page('configInstantSearch', 'BOX_CONFIGURATION_INSTANT_SEARCH_OPTIONS', 'FILENAME_CONFIGURATION', "gID=$cgi", 'configuration', 'Y');

        $sql =
            "INSERT IGNORE INTO " . TABLE_CONFIGURATION . "
                (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, date_added, sort_order, use_function, set_function)
                VALUES
                ('Enable Instant Search', 'INSTANT_SEARCH_ENABLE', 'true', 'Enable Instant Search', $cgi, now(), 100, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Maximum number of results to display', 'INSTANT_SEARCH_MAX_NUMBER_OF_RESULTS', '5', 'Maximum number of results displayed in the dropdown.', $cgi, now(), 110, NULL, NULL),
                ('Minimum length of input text', 'INSTANT_SEARCH_MIN_WORDSEARCH_LENGTH', '3', 'Minimum number of characters that must be entered before Instant Search is initiated.', $cgi, now(), 120, NULL, NULL),
                ('Maximum length of input text', 'INSTANT_SEARCH_MAX_WORDSEARCH_LENGTH', '100', 'Maximum string length allowed for Instant Search. If the search string length exceeds this value, the instant search will not be performed.', $cgi, now(), 130, NULL, NULL),
                ('Search Delay', 'INSTANT_SEARCH_INPUT_WAIT_TIME', '150', 'Delay the execution of the instant search query by this time period (in milliseconds), after a character is entered, to prevent unnecessary queries while the user is typing.', $cgi, now(), 140, NULL, NULL),
                ('Display Images', 'INSTANT_SEARCH_DISPLAY_IMAGE', 'true', 'Display the product/category/manufacturer\'s image in the results.', $cgi, now(), 150, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Display the product\'s price', 'INSTANT_SEARCH_DISPLAY_PRODUCT_PRICE', 'true', 'Display the product\'s price in the results.', $cgi, now(), 160, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Search Product\'s model', 'INSTANT_SEARCH_INCLUDE_PRODUCT_MODEL', 'true', 'Include the product\'s model in the search. Set to false to improve Instant Search performance.', $cgi, now(), 170, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Display the product\'s model', 'INSTANT_SEARCH_DISPLAY_PRODUCT_MODEL', 'false', 'Display the product\'s model in the results.', $cgi, now(), 180, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Search Categories', 'INSTANT_SEARCH_INCLUDE_CATEGORIES', 'false', 'Include category names in the search/results. Set to false to improve Instant Search performance.', $cgi, now(), 190, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Display Categories count', 'INSTANT_SEARCH_DISPLAY_CATEGORIES_COUNT', 'true', 'Display the number of products in the matched categories results (only if <em>Search Categories</em> is set to true).', $cgi, now(), 200, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Search Manufacturers', 'INSTANT_SEARCH_INCLUDE_MANUFACTURERS', 'false', 'Include manufacturer names in the search/results.', $cgi, now(), 210, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Display Manufacturers count', 'INSTANT_SEARCH_DISPLAY_MANUFACTURERS_COUNT', 'true', 'Display the number of products for the matched manufacturers (only if <em>Search Manufacturers</em> is set to true).', $cgi, now(), 220, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Search product\'s attributes values', 'INSTANT_SEARCH_INCLUDE_OPTIONS_VALUES', 'false', 'Include product attribute values in the search. Set to false to improve Instant Search performance.', $cgi, now(), 230, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),')
            ";

        $this->executeInstallerSql($sql);
    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages(['configInstantSearch']);

        $sql = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'INSTANT_SEARCH_%'";
        $this->executeInstallerSql($sql);

        $sql = "DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_title = 'Instant Search'";
        $this->executeInstallerSql($sql);
    }
}

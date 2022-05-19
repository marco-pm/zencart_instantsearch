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
                ('Max # of results displayed', 'INSTANT_SEARCH_MAX_NUMBER_OF_RESULTS', '5', 'Maximum number of results displayed in the dropdown. Too many and it\'s useless.', $cgi, now(), 110, NULL, NULL),
                ('Min length of input text', 'INSTANT_SEARCH_MIN_WORDSEARCH_LENGTH', '3', 'Minimum length of text for Instant Search. If the text entered by the user has fewer characters than this value, the instant search won\'t be performed.', $cgi, now(), 120, NULL, NULL),
                ('Max length of input text', 'INSTANT_SEARCH_MAX_WORDSEARCH_LENGTH', '100', 'Maximum length of text for Instant Search. If the text entered by the user has more characters than this value, the instant search won\'t be performed.', $cgi, now(), 121, NULL, NULL),
                ('Wait time after typing', 'INSTANT_SEARCH_INPUT_WAIT_TIME', '150', 'Wait time (in milliseconds) after the user has stopped typing before the search is performed. Increase this value a little if you want to avoid too many AJAX requests (and therefore database queries) on your server in a short interval.', $cgi, now(), 130, NULL, NULL),
                ('Display images', 'INSTANT_SEARCH_DISPLAY_IMAGE', 'true', 'Display the product/category/manufacturer\'s image in the results.', $cgi, now(), 140, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Search also into product\'s model', 'INSTANT_SEARCH_INCLUDE_PRODUCT_MODEL', 'true', 'Search also into the product\'s model.', $cgi, now(), 150, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Display the product\'s model', 'INSTANT_SEARCH_DISPLAY_PRODUCT_MODEL', 'true', 'Display the product\'s model in the results.', $cgi, now(), 160, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Display the product\'s price', 'INSTANT_SEARCH_DISPLAY_PRODUCT_PRICE', 'true', 'Display the product\'s price in the results.', $cgi, now(), 170, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Search also into categories', 'INSTANT_SEARCH_INCLUDE_CATEGORIES', 'false', 'Search also into the categories name.', $cgi, now(), 180, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Display categories count', 'INSTANT_SEARCH_DISPLAY_CATEGORIES_COUNT', 'true', 'Display also the number of products for the matched categories (only if <em>Search also into categories</em> is set to True). Set to False to improve instant search performance.', $cgi, now(), 190, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Search also into manufacturers', 'INSTANT_SEARCH_INCLUDE_MANUFACTURERS', 'false', 'Search also into the manufacturers name.', $cgi, now(), 200, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Display manufacturers count', 'INSTANT_SEARCH_DISPLAY_MANUFACTURERS_COUNT', 'true', 'Display also the number of products for the matched manufacturers (only if <em>Search also into manufacturers</em> is set to True). Set to False to improve instant search performance.', $cgi, now(), 210, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
                ('Search also into product\'s attributes', 'INSTANT_SEARCH_INCLUDE_OPTIONS_VALUES', 'false', 'Search also into the product\'s attributes (option values). Set to False to improve instant search performance.', $cgi, now(), 220, NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),')
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

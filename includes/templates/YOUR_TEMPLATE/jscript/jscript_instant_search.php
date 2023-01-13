<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.1
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

if (defined('INSTANT_SEARCH_DROPDOWN_ENABLED') && defined('INSTANT_SEARCH_PAGE_ENABLED')) { ?>
    <script>
        const instantSearchSecurityToken          = '<?php echo $_SESSION['securityToken']; ?>';
        const instantSearchDropdownEnabled        = <?php echo INSTANT_SEARCH_DROPDOWN_ENABLED === 'true' ? 1 : 0; ?>;
        const instantSearchPageEnabled            = <?php echo INSTANT_SEARCH_PAGE_ENABLED === 'true' ? 1 : 0; ?>;
        const instantSearchDropdownInputWaitTime  = parseInt(<?php echo INSTANT_SEARCH_DROPDOWN_INPUT_WAIT_TIME; ?>);
        const instantSearchDropdownInputMinLength = parseInt(<?php echo INSTANT_SEARCH_DROPDOWN_MIN_WORDSEARCH_LENGTH; ?>);
        const instantSearchDropdownInputSelector  = '<?php echo str_replace("'", "\'", INSTANT_SEARCH_DROPDOWN_INPUT_BOX_SELECTOR); ?>';
        const instantSearchZcSearchPageName       = '<?php echo zen_get_zcversion() >= '1.5.8' ? FILENAME_SEARCH : FILENAME_ADVANCED_SEARCH; ?>';
        const instantSearchZcSearchResultPageName = '<?php echo zen_get_zcversion() >= '1.5.8' ? FILENAME_SEARCH_RESULT : FILENAME_ADVANCED_SEARCH_RESULT; ?>';
        const instantSearchResultPageName         = '<?php echo FILENAME_INSTANT_SEARCH_RESULT; ?>';
    </script>
    <script src="<?php echo $template->get_template_dir('instant_search.js', DIR_WS_TEMPLATE, $current_page_base, 'jscript') . '/' . 'instant_search.js'; ?>" defer></script>
<?php }

<?php
if (defined('INSTANT_SEARCH_ENABLE') && INSTANT_SEARCH_ENABLE === 'true') { ?>
    <script>
        const searchInputWaitTime = parseInt(<?php echo INSTANT_SEARCH_INPUT_WAIT_TIME; ?>);
        const searchInputMinLength = parseInt(<?php echo INSTANT_SEARCH_MIN_WORDSEARCH_LENGTH; ?>);
    </script>
    <script src="<?php echo $template->get_template_dir('instant_search.js', DIR_WS_TEMPLATE, $current_page_base, 'jscript') . '/' . 'instant_search.js'; ?>" defer></script>
<?php }

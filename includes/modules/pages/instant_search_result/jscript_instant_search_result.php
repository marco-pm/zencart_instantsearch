<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.1
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */
?>

<script>
    const instantSearchResultSecurityToken = '<?php echo $_SESSION['securityToken']; ?>';
</script>
<script src="<?php echo $template->get_template_dir('instant_search_result.js', DIR_WS_TEMPLATE, $current_page_base, 'jscript') . '/' . 'instant_search_result.js'; ?>"></script>

<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.0
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

$zco_notifier->notify('NOTIFY_HEADER_START_INSTANT_SEARCH_RESULTS_PAGE');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

require(zen_get_index_filters_directory('default_filter.php'));

$breadcrumb->add(NAVBAR_TITLE);

$zco_notifier->notify('NOTIFY_HEADER_END_INSTANT_SEARCH_RESULTS_PAGE');

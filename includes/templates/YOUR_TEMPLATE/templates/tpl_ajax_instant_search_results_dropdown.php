<?php
/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.1
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

$nameModelClass = '';
if (INSTANT_SEARCH_DROPDOWN_HIGHLIGHT_TEXT === 'query') {
    $nameModelClass = ' instantSearchResultsDropdownContainer__resultWrapper__infoWrapper__nameModelWrapper--highlightQuery';
} elseif (INSTANT_SEARCH_DROPDOWN_HIGHLIGHT_TEXT === 'autocomplete') {
    $nameModelClass = ' instantSearchResultsDropdownContainer__resultWrapper__infoWrapper__nameModelWrapper--highlightAutocomplete';
}

foreach ($dropdownResults as $result) { ?>
    <a href="<?php echo $result['link']; ?>">
        <div class="instantSearchResultsDropdownContainer__resultWrapper">
            <?php if (!empty($result['img'])) { ?>
                <div class="instantSearchResultsDropdownContainer__resultWrapper__img">
                    <?php echo $result['img']; ?>
                </div>
            <?php } ?>
            <div class="instantSearchResultsDropdownContainer__resultWrapper__infoWrapper">
                <div class="instantSearchResultsDropdownContainer__resultWrapper__infoWrapper__nameModelWrapper<?php echo $nameModelClass; ?>">
                    <?php echo $result['name']; ?>
                    <?php if (!empty($result['model'])) { ?>
                        <div class="instantSearchResultsDropdownContainer__resultWrapper__infoWrapper__nameModelWrapper__model">
                            <?php echo $result['model']; ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="instantSearchResultsDropdownContainer__resultWrapper__infoWrapper__priceCountWrapper">
                    <?php if (!empty($result['price'])) {
                        echo $result['price'];
                    } elseif (!empty($result['count'])) { ?>
                        <div class="instantSearchResultsDropdownContainer__resultWrapper__infoWrapper__priceCountWrapper__count">
                            <?php echo $result['count'] . ' ' . TEXT_INSTANT_SEARCH_PRODUCTS_TEXT; ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </a>
<?php } ?>

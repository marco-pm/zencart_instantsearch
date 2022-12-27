<?php
/**
 * Instant Search 3.0.0
 */

$nameModelClass = '';
if (INSTANT_SEARCH_HIGHLIGHT_TEXT === 'query') {
    $nameModelClass = ' resultWrapper__infoWrapper__nameModelWrapper--highlightQuery';
} elseif (INSTANT_SEARCH_HIGHLIGHT_TEXT === 'suggestion') {
    $nameModelClass = ' resultWrapper__infoWrapper__nameModelWrapper--highlightSuggestion';
}

foreach ($this->results as $result) { ?>
    <a href="<?php echo $result['link']; ?>">
        <div class="resultWrapper">
            <?php if (!empty($result['img'])) { ?>
                <div class="resultWrapper__img">
                    <?php echo $result['img']; ?>
                </div>
            <?php } ?>
            <div class="resultWrapper__infoWrapper">
                <div class="resultWrapper__infoWrapper__nameModelWrapper<?php echo $nameModelClass; ?>">
                    <?php echo $result['name']; ?>
                    <?php if (!empty($result['model'])) { ?>
                        <div class="resultWrapper__infoWrapper__nameModelWrapper__model">
                            <?php echo $result['model']; ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="resultWrapper__infoWrapper__priceCountWrapper">
                    <?php if (!empty($result['price'])) {
                        echo $result['price'];
                    } elseif (!empty($result['count'])) { ?>
                        <div class="resultWrapper__infoWrapper__priceCountWrapper__count">
                            <?php echo $result['count'] . ' ' . TEXT_INSTANT_SEARCH_PRODUCTS_TEXT; ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </a>
<?php } ?>

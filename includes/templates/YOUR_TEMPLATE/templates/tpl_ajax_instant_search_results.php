<?php

foreach ($instantSearchResults as $result) { ?>
    <a href="<?php echo $result['link']; ?>">
        <div class="resultWrapper">
            <div class="resultWrapper__img">
                <?php echo $result['img']; ?>
            </div>
            <div class="resultWrapper__infoWrapper">
                <div class="resultWrapper__infoWrapper__nameModelWrapper">
                    <?php echo $result['name']; ?>
                    <?php if ($result['model']) { ?>
                        <div class="resultWrapper__infoWrapper__nameModelWrapper__model">
                            <?php echo $result['model']; ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="resultWrapper__infoWrapper__priceCountWrapper">
                    <?php if ($result['price']) {
                        echo $result['price'];
                    } elseif ($result['count']) { ?>
                        <div class="resultWrapper__infoWrapper__priceCountWrapper__count">
                            <?php echo $result['count'] . ' ' . TEXT_INSTANT_SEARCH_PRODUCTS_TEXT; ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </a>
<?php } ?>
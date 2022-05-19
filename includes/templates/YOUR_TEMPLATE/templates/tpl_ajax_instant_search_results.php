<?php

foreach ($instantSearchResults as $result) { ?>
    <a href="<?php echo $result['link']; ?>">
        <div class="resultWrapper">
            <?php if (isset($result['img']) && $result['img'] !== '') { ?>
                <div class="resultWrapper__img">
                    <?php echo $result['img']; ?>
                </div>
            <?php } ?>
            <div class="resultWrapper__infoWrapper">
                <div class="resultWrapper__infoWrapper__nameModelWrapper">
                    <?php echo $result['name']; ?>
                    <?php if (isset($result['model']) && $result['model'] !== '') { ?>
                        <div class="resultWrapper__infoWrapper__nameModelWrapper__model">
                            <?php echo $result['model']; ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="resultWrapper__infoWrapper__priceCountWrapper">
                    <?php if (isset($result['price']) && $result['price'] !== '') {
                        echo $result['price'];
                    } elseif (isset($result['count']) && $result['count'] !== '') { ?>
                        <div class="resultWrapper__infoWrapper__priceCountWrapper__count">
                            <?php echo $result['count'] . ' ' . TEXT_INSTANT_SEARCH_PRODUCTS_TEXT; ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </a>
<?php } ?>

<?php

foreach ($instantSearchResults as $result) { ?>
    <a href="<?php echo $result['link']; ?>">
        <div class="resultWrapper" >
            <div>
                <?php echo $result['img']; ?>
            </div>
            <div>
                <?php echo $result['name'] . ($result['count'] ? ' (' . $result['count'] . ' ' . TEXT_INSTANT_SEARCH_PRODUCTS_TEXT . ')' : ''); ?>
                <?php if ($result['model']) { ?>
                    <div class="productModel">
                        <?php echo $result['model']; ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </a>
<?php } ?>
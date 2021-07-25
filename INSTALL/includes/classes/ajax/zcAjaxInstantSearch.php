<?php
/**
 * @package Instant Search Results
 * @copyright Copyright Ayoob G 2009-2011
 * @copyright Portions Copyright 2003-2006 The Zen Cart Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: searches.php 5 2018-09-01 18:34:47Z davewest $
 * 
 * Instant Search+ 1.0.2
 */

class zcAjaxInstantSearch extends base
{
    protected const MAX_WORDSEARCH_LENGTH = 100;
    protected const MAX_RESULTS           = 5;

    public function instantSearch()
    {
        global $db;

        $wordSearch = ($_POST['query'] ?? '');
        $results    = [];

        if ($wordSearch !== '' && strlen($wordSearch) < self::MAX_WORDSEARCH_LENGTH) { // if not empty and not too long

            $wordSearchPlus = preg_quote($wordSearch, '&');

            if (strlen($wordSearch) <= 2) {
                $wordSearchPlus = "^" . $wordSearchPlus;
                $wordSearchPlusArray = [$wordSearch];
            } else {
                $wordSearchPlus = trim(preg_replace('/\s+/', ' ', $wordSearchPlus));
                $wordSearchPlusArray = explode(' ', $wordSearchPlus);
                $wordSearchPlus = preg_replace('/\s/', '|', $wordSearchPlus);
            }

            $sqlProduct = "SELECT p.products_id, p.products_model, p.products_image, pd.products_viewed
                           FROM " . TABLE_PRODUCTS_DESCRIPTION . " as pd, " . TABLE_PRODUCTS . " as p 
                           WHERE p.products_id = pd.products_id
                           AND p.products_status <> 0
                           AND ((pd.products_name REGEXP :wordSearchPlus:) OR (p.products_model REGEXP :wordSearchPlus:) OR (LEFT(pd.products_name, LENGTH(:wordSearch:)) SOUNDS LIKE :wordSearch:))
                           AND language_id = '" . (int)$_SESSION['languages_id'] . "'";

            $this->notify('NOTIFY_INSTANT_SEARCH_QUERY', '', $sqlProduct);

            $sqlProduct = $db->bindVars($sqlProduct, ':wordSearch:', $wordSearch, 'string');
            $sqlProduct = $db->bindVars($sqlProduct, ':wordSearchPlus:', $wordSearchPlus, 'string');

            $dbProducts = $db->Execute($sqlProduct);

            if ($dbProducts->RecordCount() > 0) {

                foreach ($dbProducts as $dbProduct) {
                    $productId       = $dbProduct['products_id'];
                    $productName     = zen_get_products_name($dbProduct['products_id']);
                    $productModel    = $dbProduct['products_model'];
                    $productImg      = $dbProduct['products_image'];
                    $productViews    = $dbProduct['products_viewed'];
                    $totalMatches    = 0;
                    $findSum         = null; // sum of first occurrences of words in the product name

                    // check if product model is an exact match
                    if (strtolower(trim(preg_replace('/\s+/', ' ', $productModel))) === strtolower(trim(preg_replace('/\s+/', ' ', $wordSearch)))) {
                        $totalMatches++;
                    }

                    foreach ($wordSearchPlusArray as $word) {
                        $wordPos = stripos($productName, $word);
                        
                        if ($wordPos !== false) { // search for word anywhere in the product name
                            $totalMatches++;
                            $findSum += $wordPos;

                            if (preg_match("/\b$word\b/i", $productName)) { // exact words matches have a higher priority
                                $totalMatches++;
                            }
                        } elseif (stripos($productModel, $word) === 0) { // search for word at the beginning of the product model
                            $totalMatches++;
                        }
                    }

                    $result = [
                        'name'  => strip_tags($productName),
                        'link'  => zen_href_link(zen_get_info_page($productId), 'products_id=' . $productId),
                        'model' => $productModel,
                        'img'   => zen_image(DIR_WS_IMAGES . strip_tags($productImg), strip_tags($productName)),
                        'srch'  => $wordSearchPlus,
                        'mtch'  => $totalMatches,
                        'views' => $productViews,
                        'fsum'  => $findSum ?? self::MAX_WORDSEARCH_LENGTH
                    ];
                    $results[] = $result;
                }

                // order by number of matches (desc),
                // then by words that occur first in the title,
                // then by number of views (desc)
                usort($results, static function($prod1, $prod2) {
                    return
                        [$prod2['mtch'], $prod1['fsum'], $prod2['views']]
                        <=>
                        [$prod1['mtch'], $prod2['fsum'], $prod1['views']];
                }
                );
            }
        }

        return array_slice($results, 0, self::MAX_RESULTS);
    }

}

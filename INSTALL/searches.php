<?php
/**
 * @package Instant Search Results
 * @copyright Copyright Ayoob G 2009-2011
 * @copyright Portions Copyright 2003-2006 The Zen Cart Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: searches.php 5 2018-09-01 18:34:47Z davewest $
 */


//This PHP file is used to get the search results from our database. 

// I don't know if this is nessceary
header( 'Content-type: text/html; charset=utf-8' );


//need to add this
require('includes/application_top.php');
global $db;


//this gets the word we are searching for. Usually from instantSearch.js.
$wordSearch = (isset($_GET['query']) ? $_GET['query'] : '');


// we place or results into these arrays
//$results will hold data that has the search term in the begining of the word. This will yield a better search result but the number of results will be a few.
//$resultsAddAfter will hold data that has the search term anywhere in the word. This will yield a normal search result but the number of results will be a high.
//$results has first priority over $resultsAddAfter
$results=array();
$resultsAddAfter=array();
$prodResult;

//On-TRUE Off-FALSE switches for items within the search box
$ProdCount = false;
$CatCount = true;
$sqlLimit = 4; //change LIMIT to your max display idea.. too long and its useless

//the search word can not be empty
if (strlen($wordSearch) > 0) {
	
	//if the user enters less than 2 characters we would like match search results that beging with these characters
	//if the characters are greater than 2 then we would like to broaden our search results
	if (strlen($wordSearch) <= 2) {
		$wordSearchPlus =  $wordSearch . "%";
	}else{
		$wordSearchPlus =  "%" . $wordSearch . "%";
	}
	
	
	//first we would like to search for products that match our search word
	//we then order the search results with respect to the keyword found at the begining of each of the results
	$sqlProduct = "SELECT pd.products_name, pd.products_id, p.products_status, p.products_quantity
		      FROM " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p
		      WHERE p.products_id = pd.products_id
			AND p.products_status <> 0
			AND pd.language_id = :languagesID:
			AND ((products_name LIKE :wordSearchPlus:) OR (LEFT(pd.products_name,LENGTH(:wordSearch:)) SOUNDS LIKE :wordSearch:))
		     ORDER BY field(LEFT(pd.products_name,LENGTH(:wordSearch:)), :wordSearch:) DESC, pd.products_viewed DESC LIMIT " . $sqlLimit; 
		     
						
	//this protects use from sql injection - i think????		
	$sqlProduct = $db->bindVars($sqlProduct, ':languagesID:', $_SESSION['languages_id'], 'integer');					
	$sqlProduct = $db->bindVars($sqlProduct, ':wordSearch:', $wordSearch, 'string');
	$sqlProduct = $db->bindVars($sqlProduct, ':wordSearchPlus:', $wordSearchPlus, 'string');


	$dbProducts = $db->Execute($sqlProduct);
	
	//this takes each item that was found in the results and places it into 2 separate arrays
	if ($dbProducts->RecordCount() > 0) {
	  while (!$dbProducts->EOF) {
	        $prodquantity = ($ProdCount) ? $dbProducts->fields['products_quantity'] : "";

		$prodResult = strip_tags($dbProducts->fields['products_name']);
		$prodInfo = zen_get_info_page($dbProducts->fields['products_id']);
		
		if (strtolower(substr($prodResult,0,strlen($wordSearch))) == strtolower($wordSearch)){
			$results[] = array(
				//we have 5 seperate variables that will be passed on to instantSearch.js
				//'q' is the result thats been found
				//'c' is the number of item within a category search (we leave this empty for product search, look at the example bellow for category search)
				//'l' is used for creating a link to the product or category
				//'pt' is used to get the product info page by product type using type_handler + '_info'
				//'pc' lets us know if the word found is a product or a category
				'q'=>$prodResult,
				'c'=>$prodquantity,
				'l'=>$dbProducts->fields['products_id'],
				'pt'=>$prodInfo,
				'pc'=>"p"
			);
		}else{
			$resultsAddAfter[] = array(
				'q'=>$prodResult,
				'c'=>$prodquantity,
				'l'=>$dbProducts->fields['products_id'],
				'pt'=>$prodInfo,
				'pc'=>"p"
			);	
		}
		
		$dbProducts->MoveNext();
	  }
	}
	
	//similar to product search but now we search witin categories
	$sqlCategories = "SELECT cd.categories_name, cd.categories_id, c.categories_status
			  FROM " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c
			  WHERE  c.categories_id = cd.categories_id 
			    AND c.categories_status <> 0
			    AND cd.language_id = :languagesID:
			    AND (cd.categories_name  LIKE :wordSearchPlus:)
			  ORDER BY field(LEFT(cd.categories_name,LENGTH(:wordSearch:)), :wordSearch:) DESC LIMIT " . $sqlLimit;; 
			 
		
	$sqlCategories = $db->bindVars($sqlCategories, ':languagesID:', $_SESSION['languages_id'], 'integer');
	$sqlCategories = $db->bindVars($sqlCategories, ':wordSearch:', $wordSearch, 'string');
	$sqlCategories = $db->bindVars($sqlCategories, ':wordSearchPlus:', $wordSearchPlus, 'string');

	$dbCategories = $db->Execute($sqlCategories); 	
	
	if ($dbCategories->RecordCount() > 0) {
	  while (!$dbCategories->EOF) {
		//this searches for the number of products within a category
		$products_count = ($CatCount) ? zen_count_products_in_category($dbCategories->fields['categories_id']) : ""; 
		$prodResult = strip_tags($dbCategories->fields['categories_name']);
		if (strtolower(substr($prodResult,0,strlen($wordSearch))) == strtolower($wordSearch)){
			$results[] = array(
				'q'=>$prodResult,
				'c'=>$products_count,
				'l'=>$dbCategories->fields['categories_id'],
				'pc'=>"c"
			);
		}else{
			$resultsAddAfter[] = array(
				'q'=>$prodResult,
				'c'=>$products_count,
				'l'=>$dbCategories->fields['categories_id'],
				'pc'=>"c"
			);	
		}
		
		
		$dbCategories->MoveNext();
	  }
	}
	
}


//we now re-sort the results so that $results has first priority over $resultsAddAfter
//if pt is null we need to not sort it or it will kill the link.
foreach ($resultsAddAfter as &$value) {
     if ($value["pt"] == '') {
	$results[] = array(
		'q'=>$value["q"],
		'c'=>$value["c"],
		'l'=>$value["l"],
		'pc'=>$value["pc"]
	);
    }else{
       $results[] = array(
		'q'=>$value["q"],
		'c'=>$value["c"],
		'l'=>$value["l"],
		'pt'=>$value["pt"],
		'pc'=>$value["pc"]
	);
    }
}

unset($value);

//the results are now passed onto instantSearch.js
echo json_encode($results);


?>

# Instant Search plugin for Zen Cart
Shows search results as you type.

The support thread on the Zen Cart forums is located [here](https://www.zen-cart.com/showthread.php?189289-Instant-Search).

# Prerequisites
* Zen Cart 1.5.7 or higher
* PHP 7.0 or higher
* Tested on Responsive Classic template and Bootstrap template

# Installation and use
See the [readme.html](readme.html).

# Changelog
## v2.1.0
* Improved search performance
* Added option for searching into product's attributes
* Added option for minimum search term length
* Bug fixes

## v2.0.1
* Added debouncing with configurable wait time
* Removed old css rules

## v2.0.0
* Added configuration options in the Admin
  * enable/disable plugin
  * max # of results to be displayed
  * enable/disable display of images, model, price
  * enable/disable search on product's model, category, manufacturer
* Optional search on categories and manufacturers
* Code refactoring
* Various code and visual improvements

## v1.1.0
* Now uses a ZC AJAX class
* Searches only into the product name and the product model (for now)
* Improved word search and results sorting
* Displays the product image, name and model on the results



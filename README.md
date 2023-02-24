# Instant Search plugin 4.0 for Zen Cart 1.5.7 and 1.5.8
Show autocomplete search results while the user is typing. Show relevant search results in a listing page with 
infinite scroll.

# Features
This plugin uses a combination of MySQL Full-Text Search and LIKE/REGEXP queries to quickly find and sort products, 
brands, and categories based on their relevance to the user query.

With the [Typesense add-on](https://github.com/marco-pm/zencart_typesense), Typesense can be used as a search
engine in place of MySQL.

The results can be displayed in an autocomplete dropdown as the user types in a search box and/or as a search 
results page with a sortable product list and infinite scroll once the user submits the search form, providing an 
alternative to the classic Zen Cart search results page.

Version 4.0 of the plugin brings new and improved features:
- Refactor of the search class to allow the use of different search engines (with automatic fallback to MySQL 
  if the search engine is unavailable)
- Support for using Typesense as a search engine (with the [Typesense add-on](https://github.com/marco-pm/zencart_typesense))
- Improved display of categories and brands in the dropdown
- Search in the product category and brand
- Complete rewrite of the JavaScript code of dropdown and results page with React and TypeScript
- Improved dropdown accessibility and keyboard navigation
- Various bug fixes and improvements

These add to the features and improvements of version 3.0:
- Faster and better searches, with MySQL Full-Text search and Query Expansion
- New, additional search results page with sortable product list and infinite scroll
- New admin settings, including control of which product fields to search and their order
- More robust dropdown auto-positioning
- Support for Zen Cart 1.5.8 language files and plugin upgrade functionality
- Integration with Search Log plugin
- Responsive Classic and Bootstrap template files included
- Code almost entirely rewritten, removed jQuery dependency, and many other improvements

There are no modifications to Zen Cart core files.

# Prerequisites
- Zen Cart 1.5.7 or 1.5.8
- PHP 7.4 through 8.2

# Installation, use and FAQs
See the [readme.html](https://htmlpreview.github.io/?https://github.com/marco-pm/zencart_instantsearch/blob/main/readme.html).

# Troubleshooting
[Zen Cart Forum Support Thread](https://www.zen-cart.com/showthread.php?189289-Instant-Search)

See also the FAQS & troubleshooting section in the [readme.html](https://htmlpreview.github.io/?https://github.com/marco-pm/zencart_instantsearch/blob/main/readme.html).

# Development
To build the `.js` files from the `.tsx` sources, install Node.js and NPM, then run:
```
npm install

# dropdown:
npm run build-instant_search_dropdown

# results page:
npm run build-instant_search_results
```

# Testing
Unit and integration tests use the [Test Framework](https://docs.zen-cart.com/dev/testframework/) of Zen Cart 1.5.8. 
Place the content of the `tests` directory of this repo under `/not_for_release/testFramework`.

Run the tests with:
```
php phpunit --configuration phpunit_instantsearch.xml
```
# Instant Search v1.0.2
Just like googles instant search feature I’ve made one for zen cart. Instant search is a new search enhancement that shows results as you type. 

Tried and tested and works on all major browsers including smart phones.

This contribution is subject to version 2.0 of the GPL license, that is bundled with this package in the file LICENSE, and is available here.

These files are submitted for public distribution via the Zen Cart forum. It would be great if you can provide your feedback or support via the support thread at the Zen Cart forum where it can benefit all users of this add-on module.

How It Works
 * When the page loads up jscript_instantSearch.js adds a keyup listeners to the search boxes.
 * When the user starts to key in a few letters jscript_instantSearch.js sends the data to searches.php.
 * searches.php uses sql to gather matching search results.
 * searches.php sends these results back to jscript_instantSearch.js.
 * jscript_instantSearch.js then creates the instant search box with the results on the web page.

 * Instant search uses the jQuery JavaScript Library. 
 * You can change the style and layout of the instant search via stylesheet_instantSearch.css.
 
 
Unmodified upload..  Still planning on adding and editing.

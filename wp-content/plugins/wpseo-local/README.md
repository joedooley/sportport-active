Local SEO
=========
Requires at least: 4.0<br/>
Tested up to: 4.4<br/>
Stable tag: 3.0
Depends: wordpress-seo

Description
-----------

Local Search module for Yoast SEO plugin of yoast.com.

Changelog
=========

Trunk
------------
### 3.0: November 18th, 2015

* Synchronized plugin version with all other Yoast SEO plugins for WordPress.

* Bugfixes
	* Fixes deprecation warnings for filters that have been removed in Yoast SEO 3.0
	* Fixed content error for Local admin page (iframe of lseo.com)
	* Fixed mixed content issue for XSL

* Enhancements
	* Makes sure location specific content analysis checks work well with the Real Time content analysis tool in Yoast SEO 3.0.

### 1.3.8
* Bugfix:
    * Fixed bug where widgets no longer showed up when using a single location

### 1.3.7:
* Bugfixes:
	* Don't show widget contents/title when no location has been entered
    * Updated Widget constructor
    * Fixed a bug where radius was display wrong in storelocator shortcode
    * Fixed a bug where e-mail was no longer shown in address shortcode
    * Hidden location category dropdown when there are no categories to select
    * Fixed issue where page analysis did not recognize city in URL
    * Map in storlocator is once again shown before a search
    * Added (hidden) radius to store locator widget, so search will work properly again

* Enhancements:
    * Changed naming from WordPress SEO to Yoast SEO
    * Phonenumbers are no longer formatted in <a href="tel:">
    * Added option to center the map on a specific location
    * Separated dragging and scrolling options for maps
    * Added 3 note fields to locations and the import function
    * Added option to upload a logo per location. The company logo can now be added to your address widget, the address shortcode or by the use of a newly added shortcode [wpseo_local_show_logo]. This shortcode accepts id as attribute.
    * Added VAT, Tax and Chamber of Commerce ID fields
    * Enhanced search by extending it with address, zip code and city parameters
    * Locations found in search now display address details

### 1.3.6:
* Bugfixes:
	* Removed condition shorthand due to POT file problems
    * Fixed issue where allowing to scroll and zoom the map did not work as intended
    * Removed some PHP notices
    * Fixed issue where a custom 'Show route' label was not correctly shown
    * Route calculation is now based on lat/long
    * Slide toggle for opening hours in admin reversed
    * Missing space between input field and button
    * Removed dash after one-line address if no other options are selected
    * Extra span for telephone number, so Google picks it up
  
* Enhancements:
	* Added option to show email address in Google Maps info window
    * Email address is no longer copied when copying data from other location
    * Added a warning when a custom marker of larger than 100 x 100 pixels is used
    * Added option to change the URL when using a single location.
    * Added a 'wpseo_local_contact_details' filter to change the labels and order of contact details
    * Locations can now be shown per category
    * If geocoding limit is reached, a notice will be shown.
    * Added placeholder for "Show route" field


### 1.3.5: January 9th, 2015
* Bugfixes:
	* Changed a <? into <?php. Yep, really...
    * Resolved some PHP 5.2 and 5.3 compatibilty issues
    * Fixed the url for locations in locations.kml
    * Added the + in url's for phonenumbers
    * Added map styles to the storelocator
* Enhancements:
	* Added support for Jetpack's Omnisearch
	* Added support for Publicize and Markdown
    * Added label fields. You can now determine your own labels for locations.


### 1.3.4.1: January 2nd, 2015

* Bugfixes:
	* Fixes a JS bug that was caused by a merge conflict

### 1.3.4: December 22nd, 2014

* Bugfixes:
    * Wrong opening hours were added to meta-data when location is closed
    * Category sitemap was created when there were no location categories
    * Accept both "value" and "nice name" for Business Type in import
* Enhancements:
    * Added option for hiding (not using) opening hours
    * Added option for entering a Google Maps API key
    * We added a tab in the Options section, wehere we've listed some great Local SEO tools

1.3.3
------------
Release date: November 11th, 2014
* Enhancements:
    * Updated translations for 10 languages


1.3.2
------------
Release date: October 8th, 2014

* Bugfixes:
    * When selecting quarters in opening hours, it isn't displayed as "closed" anymore
    * Copying data from an existing location now works properly
* Enhancements:
    * Custom markers for Google Maps
    * Custom markers per category (when using multiple locations)
    * In the locations post edit screen, you can now drag the Google Maps pin to a different location
    * Added an option to the store locator to show the nearest location if no locations are found in the searchradius
    * Removed the current location from the 'copy data from another location' drop down
    * Expanded the importer with opening hours
    * Added Business Type and URL to CSV import
    * Added a nonce check to import    
    * Caching for sitemaps

### 1.3.1: July 2nd, 2014

* Bugfixes:
    * Fixed unability to save 2 sets of openinghours
    * Fixed issue where multiple locations or change of slug gave a 404.
    * Fixed issue where business URL input field did not contain the correct URL
    * Don't display number of results in store locator when no search is performed yet
    * Fixed attachment upload when adding URL's for images in CSV import
    * Fixed: Checkbox to use 24h format in metabox doesn't work when using multiple locations
    * Fixed: business url was not being saved
    * show_email wasn't being set to false, when not selecting it in the shortcode popup
    * Business types were not saved correctly in metabox (musing ultiple locations)
* Enhancements:
    * Hide import options when not using multiple locations
    * Change link in Address (and store locator results) into "Business URL" option, with fallback to permalink
    * Add option to make maps scrollable (or not)
    * Added several new Schema.org markups (Residences, Governent Buildings, Chruches etc.)
    * Added radius to "Show store locator" popup

### 1.3.0.3: March 31th, 2014

* Bugfixes:
    * 24-h format for opening hours works again
    * Fixes sitemap URL's for servers which needs index.php in permalink structure
    * Option added for hiding business name (remember doing this will break Schema.org markup, since itemprop="name" is a required attribute)
    * Hide "Show route" when not selected from popup.
* Enhancements:
    * Properly minify admin CSS scripts.
* i18n
    * Updated es_ES, nl_NL and ru_RU
    * Added de_DE, fr_FR and tr_TK
    
### 1.3.0.2: March 17th, 2014

* Bugfixes:
	* Fix error that prevented properly recognizing current version.

### 1.3.0.1: March 13th, 2014

* Bugfixes:
    * Fixed fatal error when saving single location
    * Fixed "Non-static method" notice

### 1.3.0: March 11th, 2014

* Bugfixes:
    * Mismatched itemprop="email" for URL's now changed to itemprop="url"
    * Manually changing lat/long coordinates is working again
* Enhancements:
    * Add {zipcode} {city}, {state} address format
    * Added html elements to address lines
    * Added possibility to change author of location
    * Added "all locations" option to Address button in edit-pages to show all locations
* Code changes
    * Classes and instances of classes renamed to be more consistent with WP SEO
    * Separated some funciotnality in different classes

### 1.2.2.2: February 14th, 2014

* Bugfixes:
    * Video sitemap was breaking after update 1.2.2.1. We fixed that now.

### 1.2.2.1: February 5th, 2014

* Bugfixes:
    * Due to changes in sitemaps to be more in line with other WordPress SEO sitemaps, geo_sitemap.xml was not working anymore. Added now a redirect to redirect geo_sitemap.xml to geo-sitemap.xml

### 1.2.2: January 31st, 2014

* Bugfixes:
    * Fixes fatal error in metabox when having no internet connection
    * Updates lat.long coordinates after changing address of location
    * Force slug for locations CPT, even when blank in admin bug
    * Notice fix in widget when location has no lat/long coordinates
* Enhancements:
    * Possibility to add default country to imporve searches from store locator (it adds the country to the search query)
    * Show meesage when route cannot be calculated
    * Pre-select location when adding short codes via popup
    * Add filter to time-frame in Opening Hours
    * Added parameter to shortcode that prevents mouse scrolling

### 1.2.1: December 10th, 2013

* Bugfixes:
    * Fixed: Store locator routing function was broke
    * Some addresses were not reverse geocoded well by the route planner.
* Enhancements:
    * Load text domain through filter now, so you can overrule standard translations. (Thanks to Timo Leiniö and http://geertdedeckere.be/article/loading-wordpress-language-files-the-right-way)


1.2.1
-----
* Bugfixes:
    * Fixed: Store locator gave unexpected results with a lot of locations
    * Store locator popup checkboxes didn't work correctly. Now they do. The scrollbar is gone too.
    * Fixed: When some locations don't have geo locations, map with all locations fails
    * Fixed: Map failed when some locations don't have lat/long coordinates
* Enhancements:
    * Added documentation for CSV import
    * Routeplanner on mobile phones opens now in maps.google.com, which results in opening in the Google Maps app (if installed)
* i18n
    * Updated .pot file
    * Updated ru_RU translation


1.2
-----
* Bugfixes:
    * Checkbox 'Hide closed days' in widget-admin now works.
    * Added filter 'wpseo_local_location_route_title_name' for title 'route' of widget and shortcode
    * Added esc_html to filter 'wpseo_local_location_title_tag_name'
    * Replaced WPSEO_LOCAL_URL constants by using plugins_url() so that it can be filtered (where needed)
* Enhancements:
    * Added a store locator. Gives you the possibility to let people search for the neirest store/office
    * Added a custom taxonomy for categorizing your locations
    * You can enter custom URL's for your locations now
    * Better icons for adding shortcodes
    * Better UI for selecting the map style when adding a map shortcode
    * Added possibility to add comma separated ID's to wpseo_map shortcode for selectively showing locations on a map
    * Added a second field for a phone number (office, mobile etc.)
    * Allow HTML in the "Extra comment" field in the Address and Opening Hours widgets
* i18n:
    * Updated .pot file

1.1.7
-----
* Bugfixes:
    * When outputting opening hours on its own, don't add schema.org
    * When using the "insert address" button it inserts the entire address with phone, country, fax, ect whether or not it's checked.
    * When "hide closing days" isn't checked it still hides them.
    * Added page layout options for Genesis themes
    * Added quarters for the opening hours
    * Added shortcode ( [wpseo_all_locations] ) to display all your locations at once.
* Enhancements:
    * Added icons to shortcode buttons
    * Added opening hours shortcode button
		* Allow license key to be set by constant WPSEO_LOCAL_LICENSE. Key will be hidden if valid.
    * Created option to show URL in address detail and in info-box in Google Map
    * Deleted unnecessary files
* i18n:
    * Updated hu_HU & ru_RU
    * Updated .pot file

1.1.6
-----
* Bugfixes:
    * Apostrophe in company name created issues. Not anymore
    * Setting Unit system works again
    * When specifying a business type and saving, the chosen business type is now selected.
    * Opening hours now display correctly if the opening hours are set to two sets, and only one set is used
* Enhancements:
    * Shortcodes can now be inserted visually (button opens popup with settings)
    * Google Maps is now responsive (fluid width)
    * Hide link in popup box (Google Map) when there's just one location
    * Added comment box in the address and opening hours widgets, for extra (optional) comments.

1.1.5
-----
* Bugfixes:
	* Make sure maps work on https.
	* Improve JS output.
	* Fix several widget bugs.
* Enhancements:
	* Remove jQuery dependency.
	* Move JS to external file.
* i18n:
	* Updated ru_RU translation.
	* Added Swedish and Polish.

1.1.4
-----
* Bugfixes:
    * Allow more values in shortcodes to set stuff to false.
    * Fix bounds for Maps.
* Enhancements:
    * Make maps output search engine indexable links too.
* i18n:
    * Added ru_RU translation.

1.1.3
-----
* Bugfixes:
    * Fix activation hook to work on add_option instead of just update_option, so activation works immediately.
    * Multiple maps embedded on one page now work properly.
    * Dropdowns with chosen script now line out properly.
    * Google Maps geocoder script + maps embed scripts now properly enqueued and outputted in footer instead of within content.
    * Maps shortcode output bug fixed.
* Inline documentation:
    * Added link to FAQ entry about schema.org business types.
* Enhancements:
    * Added back LocalBusiness business type to top of business type select.
    * You can now use "Current location" for widgets, so you can use them on the locations pages. They'll output nothing outside of locations.

1.1.2
-----
* i18n
    * Added da_DK, hu_HU, it_IT and nl_NL translations.
* Bugfixes
    * Fix `class_exists` check to actually check for the right class (props [Ryan McCue](http://ryanmccue.info/)).
    * Make both front and backend classes global so methods can be used outside the plugin (props [Ryan McCue](http://ryanmccue.info/)).
    * Fix overwriting of `$args` variable which broke widgets.

1.1.1
-----
* Bugfixes:
    * Make updater actually work...

1.1
---
* Enhancements:
    * Added hide_closed option to opening hours shortcode and widgets.
    * Added option to show fax number and email address in both shortcode and widget.
    * Improved UI for opening hours.
    * Switched to a better endpoint for Google Maps Geocode API.
    * Added state to KML file output.
* Bugfixes:
    * "undefined" URL in maps shortcode and widgets.
    * Fixed several notices.
    * Values "off" and "no" now properly work for shortcodes.

1.0
---
* Initial version.


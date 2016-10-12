Video SEO
=========
Requires at least: 4.3<br/>
Tested up to: 4.6<br/>
Stable tag: 3.7.0<br/>
Depends: wordpress-seo

Video SEO adds Video SEO capabilities to WordPress SEO.

Description
-----------

This plugin adds Video XML Sitemaps as well as the necessary OpenGraph markup, Schema.org videoObject markup and mediaRSS for your videos.

Installation
------------

1. Go to Plugins -> Add New.
2. Click "Upload" right underneath "Install Plugins".
3. Upload the zip file that this readme was contained in.
4. Activate the plugin.
5. Go to SEO -> Extensions and enter your license key.
6. Save settings, your license key will be validated. If all is well, you should now see the XML Video Sitemap settings.
7. Make sure to hit the "Re-index videos" button if you have videos in old posts.

Frequently Asked Questions
--------------------------

You can find the [Video SEO FAQ](https://kb.yoast.com/kb/category/video-seo/) in our knowledge base.

Changelog
=========

### 3.7: October 11th, 2016

* Enhancements
    * Added iframe-based support for uStudio videos.
    * Added missing index.php files.

### 3.6: September 27th, 2016

* Changes
    * Updated translations.

### 3.5: September 7th, 2016 

* Changes
    * Adds support for Featured Video Plugin, props [ahoereth](https://github.com/ahoereth)


### 3.4: July 19th, 2016

* Changes
	* Updated translations.

### 3.3: June 14th, 2016

* Enhancements
	* Adds the Yoast i18n module to the Yoast SEO Video settings page, which informs users the plugin isn't available in their language and what they can do about it.

* Bugfixes
    * Fixes a bug where the support beacon for Yoast SEO Video was added to all Yoast SEO settings pages.
    * Fixes a bug where updates were not working reliably when multiple paid Yoast plugins were active.

### 3.2: April 20th, 2016

* Fixes a bug where the video sitemap cache wasn't cleared on activation. 
* Fixes a bug where video specific checks that were added to the content analysis would no longer work in combination with Yoast SEO 3.2 and higher.
* Fixes a bug where clicking the 'Update now' button on the plugin page didn't update correctly.

### 3.1: March 1st, 2016

* Bug fixes
	* Fixes a JS error on the post edit page causing the content analysis to break in combination with Yoast SEO versions higher than 3.0.7.
	* Fixes a bug where our license manager could sometimes not reach our licensing system due to problems with ssl.

* Enhancements
	* Makes sure users don't have to reactivate their license after updating or disabling/enabling the plugin.
	* Adds a support beacon on the Video SEO settings page enabling users to ask for support from the WordPress backend.

### 3.0: November 18th, 2015

* Synchronized plugin version with all other Yoast SEO plugins for WordPress.

* Bug fixes
	* Fixes a fatal error that could occur while reïndexing the video sitemap.
	* Fixes the video metabox that was broken in combination with Yoast SEO 3.0.
	* Fixes deprecation warnings for filters that have been removed in Yoast SEO 3.0

* Enhancements
	* Made sure video specific content analysis checks work well with the Real Time content analysis tool in Yoast SEO 3.0.


== Upgrade Notice ==

1.6
---
* Please make sure you also upgrade the WordPress SEO plugin to version 1.5 for compatibility.

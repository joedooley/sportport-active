=== Yoast ACF Analysis ===
Contributors: marcusforsberg, joostdevalk, atimmer, omarreiss, jipmoors
Tags: yoast, seo, acf, advanced custom fields, analysis, search engine optimization, seo score
Donate link: https://forsberg.ax
Requires at least: 4.3.1
Tested up to: 4.5
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Version: 1.2.0

Adds the content of all ACF fields to the Yoast SEO content analysis.

== Description ==
[Yoast SEO for WordPress](https://yoast.com/wordpress/plugins/) content and SEO analysis does not take in to account the content of a post's [Advanced Custom Fields](http://www.advancedcustomfields.com/). This plugin uses the plugin system of Yoast SEO for WordPress 3.1+ to hook into the analyser in order to add ACF content to the SEO analysis.

This had previously been done by the [WordPress SEO ACF Content Analysis](https://wordpress.org/plugins/wp-seo-acf-content-analysis/) plugin but that no longer works with Yoast 3.0. Kudos to [ryuheixys](https://profiles.wordpress.org/ryuheixys/), the author of that plugin, for the original idea.

Please note that the plugin does not work with WYSIWYG fields for ACF yet, we're working on this. 

> If you have issues, please [submit them on GitHub](https://github.com/Yoast/yoast-acf-analysis/issues)

== Changelog ==

= 1.2.0 =

Released June 30th, 2016

* Bugfixes:
	* Fixes an incompatibility issue with Yoast SEO version 3.2+ where the assets are registered with a new prefix.

* Internationalization:
	* Improved text in notifications when dependencies are missing.

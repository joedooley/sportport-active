Video SEO
=========
Requires at least: 4.3<br/>
Tested up to: 4.5<br/>
Stable tag: 3.2<br/>
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

You can find the FAQ [online here](http://kb.yoast.com/category/8-video-seo).

Changelog
=========

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

### 2.0.4: June 23rd, 2015

* Bug fixes
    * Fixes a bug where https YouTube URLs weren't recognized.
    * Fixes a bug where the sitemap cache wouldn't be cleared when saving options. 
    * Changed to the YouTube v3 API, making the YouTube integration work again. 

### 2.0.3: March 25th, 2015

* Bug fixes
	* Fixes a bug where the video sitemap could contain wrongly formatted date times.
	* Fixes an undefined index notice for the $post global that was fired when creating a new product in WooCommerce.
	* Fixes a bug where title variables weren't parsed well in the Video Sitemap.
	* Fixes a bug where the video thumbnails were saved without an extension.
* Enhancement
	* Added 5 new languages: en_GB, es_MX, fr_FR, nl_NL and tr_TR.


### 2.0.2: Dec 17th, 2014

* Bug fixes:
	* Fix for notice on the snippet preview

* Enhancements:
	* Showing progressbar on re-indexing the video sitemap

### 2.0.1: Nov 11th, 2014

* Bug fixes:
    * Fixed: Vixxy shortcode/url combi not recognized
    * Fixed: Missing stylesheet error
    * Fixed: Limiting issue on sitemap

* Enhancements:
    * Added translations for Persian and Brazilian Portuguese
    * Removed translations for French, Dutch and Swedish. If you would like to help translate these languages, please sign up at translate.yoast.com!
    * Improved translations for Danish, German, Hungarian and Italian

### 2.0: Oct 7th, 2014

* Bug fixes:
    * Fixed: shortcode list would often not be reset properly.
    * Fixed: escaped shortcodes would still be searched for video.
    * Fixed: no name shortcode attributes wouldn't always be recognized.
    * Fixed: Flickr video detail retrieval was failing, SSL now required.
    * Fixed: Compatibility issue between support for the [JW Player](http://wordpress.org/extend/plugins/jw-player-plugin-for-wordpress/) plugin and fitvids.js.
    * Fixed: only the first shortcode found would be checked to see if it was a video shortcode, then it would fall back to other methods, now all shortcodes are checked until a video shortcode is found. If none is found, it will still fall back to other methods of finding video.
    * Fixed: small regression where wordpress.tv video details would not always be retrieved.
    * Fixed: `[videopress]` shortcode - while supported by plugins - was still not recognized.
    * Fixed: regression where numeric video ids would sometimes prevent video detail retrieval.
    * Fixed: most of vidyard detail retrieval failed.
    * Fixed: bug where content of the last recognized meta field would overrule earlier found information.
    * Fixed: bug where a meta field containing a mixture of html/text and a url at the end could be accepted as content_loc.
    * Fixed: the VideoSEO plugin would auto-de-activate on an upgrade of WPSEO. This should no longer happen.

* Enhancements:
    * Added support for recognizing video attachments without additional plugins.
    * Added support for recognizing `.ogv` files as video files.
    * Added support for custom Wistia domains.
    * A lot more video URLs will be recognized as such.
    * Better support for protocol-less urls all round.
    * Add Video SEO menu item to the admin bar
    * WP 4.0 removes oembed support for Viddler videos as [Viddler no longer supports free personal accounts](https://gigaom.com/2014/02/07/viddler-gets-ready-to-delete-personal-videos/). For those users who still use Viddler, Video SEO will continue to support both the OEmbedding as well as - of course - the SEO aspect.

* Supported Services:
    * Added support for 23Video videos (retrieval of video details).
    * Added support for Archive.org videos (retrieval of video details).
    * Added support for CollegeHumor.com videos (retrieval of video details).
    * Added support for Funnyordie.com videos (retrieval of video details - unfortunately this does not (yet) work for short urls).
    * Added support for Hulu.com videos (retrieval of video details).
    * Added support for Revision3 videos (retrieval of video details).
    * Added support for TED videos (retrieval of video details).
    * Added support for VideoJug videos (retrieval of video details).
    * Added support for Snotr videos via Embedly (limited video details).
    * Added support for Spike.com/IFilm videos via Embedly (retrieval of video details).
    * Added support for Vine videos via Embedly (retrieval of video details).
    * If no video detail retrieval is available, Embedly will be used to try and retrieve details anyway.
    * Much improved support for uploaded/locally hosted videos (retrieval of video details).
    * Improved support for YouTube (country) sub-domains and alternative protocols (httpvhd, httpvhp, youtube::). Removed support for audio-only embeds as, well, audio is not video.
    * Improved support for Animoto videos (recognition of urls).
    * Improved support for Blip.tv videos (improved recognition leading to better retrieval of video details).
    * Improved support for Dailymotion.com videos (recognition of short urls).
    * Improved support for Flickr videos (recognition of short urls and better retrieval of video details).
    * Improved support for Viddler videos (retrieval of video details).
    * Improved support for VideoPress and WordPress.tv (retrieval of video details).
    * Improved support for Vimeo videos (url recognition and retrieval of video details).
    * Improved support for Vzaar videos (url recognition and retrieval of video details).
    * Improved support for Wistia videos (recognition of urls and retrieval of video details).
    * Slightly improved support for YouTube videos (retrieval of video details).

* Supported Plugins:
    * Added support for the [Flowplayer HTML5](http://wordpress.org/plugins/flowplayer5/) plugin.
    * Added support for the [JetPack](http://wordpress.org/plugins/jetpack/) plugin shortcodes module.
    * Added support for the [VideoPress](http://wordpress.org/plugins/video/) plugin.
    * Added support for the [YouTube Embed Plus](http://wordpress.org/plugins/youtube-embed-plus/) plugin.
    * Improved support for the [Advanced Responsive Video Embedder](http://wordpress.org/plugins/advanced-responsive-video-embedder/) plugin - a large number of shortcodes were not recognized.
    * Improved support for the [IFrame Embed for YouTube](http://wordpress.org/extend/plugins/iframe-embed-for-youtube/) plugin - shortcode was not recognized.
    * Improved support for the [Simple Video Embedder](http://wordpress.org/plugins/simple-video-embedder/) plugin - shortcode was recognized, better handling of custom fields.
    * Improved support for the [Sublime Video](http://wordpress.org/extend/plugins/sublimevideo-official/) plugin - not all possible video sources were recognized.
    * Improved support for the [TubePress](http://wordpress.org/extend/plugins/tubepress/) plugin - added Vimeo support.
    * Improved support for the [Viper Video Quicktags](http://wordpress.org/extend/plugins/vipers-video-quicktags/) plugin - a large number of shortcodes were not recognized.
    * Improved support for the [WP Video Lightbox](http://wordpress.org/extend/plugins/wp-video-lightbox/) plugin - thumbnail image was not supported.
    * Improved support for the [WP YouTube Player](http://wordpress.org/extend/plugins/wp-youtube-player/) plugin - added support for id instead of url and for width, height attributes.
    * Improved support for the [YouTuber](http://wordpress.org/extend/plugins/youtuber/) plugin - shortcode was not supported.
    * Improved support for the [YouTube Embed](http://wordpress.org/extend/plugins/youtube-embed/) plugin - alternative protocols recognition.
    * Improved support for the [YouTube with Style](http://wordpress.org/extend/plugins/youtube-with-style/) plugin - playlist syntax would break support.
    * Removed support for the [Better Youtube Embeds](http://wordpress.org/extend/plugins/dirtysuds-embed-youtube-iframe/) plugin as the plugin functionality is now included in WP core and the plugin is no longer active.
    * Removed support for the [Instabuilder](http://instabuilder.com/) plugin.
    * Removed explicit support for the [Premise](http://getpremise.com/) plugin.
    * Removed explicit support for the [Youtube Brackets](http://wordpress.org/extend/plugins/youtube-brackets/) plugin as the plugin hasn't been updated in eight years.

* Other:
    * Minimum requirement for WP now 3.6.
    * Added license information
    * Applied some best practices

### 1.7.2: July 17th, 2014

Fix added whitespace after content cause in 1.7 update.

### 1.7.1: July 15th, 2014

Fix error in update caused by missing the version number update in 1.7.

### 1.7: July 14th, 2014

* Bug fixes:
	* Fixed: bug where `$content` would be empty for an `mrss_item`.
	* Fixed: minor bug in upgrade routine.
	* Fixed: bugs in Animoto and Screenr oembed provider addition.
	* Fixed: issue with sitemap errors when conflicting http protocols were given.
	* Fixed: video sitemap could show in sitemap index even when no posts with videos were found.
	* Fixed: video description generated from content could break off in the middle of a word or html entity.
	* Fixed: error on plugin activation.
	* Fixed: sitemap conflict when a custom post type named 'video' would exist.
	* Fixed: issue where durations would not be shown correctly in the metabox.

* Enhancements:
	* Add oembed support for wistia.net domain and wistia protocol-relative urls.
	* Moved language file loading to the init hook to allow for translation overloading.
	* Improved clean-up of uploaded files.
	* Update snippet preview to use latest Google design changes in line with the earlier update to WP SEO. This fixes the javascript error some people were experiencing.
	* Auto-deactivate plugin in circumstances that it can't work.
	* Increased size of YouTube thumbnail image being retrieved.

### 1.6.3: March 31st, 2014

* Bug fixes:
	* Fixed a warning for a missing variable in sanitize_rating.

### 1.6.2: March 17th, 2014

* Bug fixes:
	* Fixed a warning for a missing variable.
	* Updated Fitvids.js to fix some issues with it.

* Enhancements:
	* Fitvids will now be included un-minified when `SCRIPT_DEBUG` is on.

### 1.6.1: March 11th, 2014

Fix wrong boolean check.

### 1.6: March 11th, 2014

Compatibility with WPSEO 1.5 and implementation of the same options & meta philosophy

* Bug fixes
	* Fixed: Non-static methods should not be called statically
	* Fixed: noindex setting wasn't being respected properly
	* Fixed: some inconsistent admin form texts
	* Fixed: Warning when loading new post.
    * Fixed: Always re-validate license key on change.

* i18n
    * Updated .pot file
    * Updated it_IT

### 1.5.5.1

* Bug fixes
	* Make sure thumbnail image is available.
	* Move initialisation of plugin to earlier hook to make sure it's there when XML sitemap is generated.

### 1.5.5

* Bug fixes
	* Remove dependency on `WPSEO_URL` constant.
	* Fix use of wrong image in OpenGraph and Schema.org output when a thumbnail is manually selected.
	* Restore $shortcode_tags to original after `index_content()`.

* Enhancements
	* Use media uploader to change video thumbnail.
	* Add setting to allow video playback directly on Facebook (defaults to on).

### 1.5.4.6

* Bug fixes
    * Prevent warning on line 4169, for unset video taxonomies.
    * Prevent issues with custom fields that have spaces in their keys.
    * Added support for more Dailymotion URLs.

* Enhancements
    * Remove CDATA in favor of proper encoding of entities.
    * Force 200 status codes and proper caching on both video sitemap XML and XSL.
    * Add support for [WP YouTube Lyte](http://wordpress.org/extend/plugins/wp-youtube-lyte/) shortcode.

* i18n
    * Renamed wpseo-video.pot to yoast-video-seo.pot
    * Updated fr_FR
    * Added hu_HU


### 1.5.4.5

* To make best use of the new features in this update, please reindex your videos.

* Bug fixes
    * Several i18n namespace fixes.
    * Make videos in taxonomy descriptions pick up properly again.
    * Fix for Wistia popover embeds and Wistia https URLs.
    * Prevent output of hd attribute for videos in XML Video sitemap.
    * Make sure opengraph image is always set to "full" size.
    * Add width and height for Youtube videos.
    * Prevent notice in sitemap when video from taxonomy term is displayed.
    * Prevent wrong or empty dates in XML video sitemap.
* Enhancements
    * Add option to manually add tags per video.
    * Add option to override video category (normally defaults to first post category).
    * Order videos in XML video sitemap by date modified, ascending.
    * Add "proper" Facebook video integration.
    * Added support for [Advanced Responsive Video Embedder](http://wordpress.org/plugins/advanced-responsive-video-embedder/).
    * Added support for muzu.tv.
    * Allow for custom fields that hold arrays to be detected too.
    * Add support for custom Vimeo URLs. (eg http://vimeo.com/yoast/video-seo)
    * Make sure the video thumbnail is always put out as an og:image too.
	* Added support for Instabuilder video shortcodes
	* Added support for Vidyard
	* Set license key with a constant
	* Added support for Cincopa
	* Added support for Brightcove
	* Added support for videos in the 'Archive Intro Text' (Genesis) in the video sitemap
	* Added support for [WP OS FLV plugin](http://wordpress.org/plugins/wp-os-flv/)
	* Added support for [Wordpress Automatic Youtube Video Post] (http://wordpress.org/plugins/automatic-youtube-video-posts/)

### 1.5.4.4

* Bug fixes
    * Spaces in custom fields settings are now properly trimmed.
    * Fix for Vzaar URLs.
    * Wistia embed with extra classes now properly detected.
* Enhancements
    * Video sitemap now adheres to same pagination as post sitemap.
    * Video XML Sitemap date now properly retrieved from last modified post with movie.

### 1.5.4.3

* Enhancements
    * Add support for `fvplayer` shortcode.
    * Add option to manually change or enter duration.

### 1.5.4.2

* Bug fixes:
    * Properly allow normal meta description length when video has been disabled for post.
* Enhancements:
    * Added option to disable RSS enhancements, to prevent clashes with podcasting plugins.

### 1.5.4.1

* Move loading of the plugin to prio 20, in line with upgrades of the core WordPress SEO plugin.

### 1.5.4

* Enhancements:
    * Added support for [fitvids.js](http://fitvidsjs.com/), enable it in the Video SEO settings to make your Youtube / Vimeo / Blip.tv / Viddler / Wistia videos responsive, meaning they'll become fluid. This might not work with all embed codes, let us know when it doesn't work for a particular one.
    * Removed the ping functionality as that's fixed within the core plugin.
    * Added code that forces you to update WordPress to 3.4 or higher and the WordPress SEO plugin to 1.4 or higher to use the plugin.
* Bug fixes:
    * Fixed a bug that would prevent the time last modified of the video sitemap to update.

### 1.5.3

* Enhancements:
    * Improved defaults: now enables all public post-types by default on install.
    * Option to change the basename of the video sitemap, from video-sitemap.xml to whatever-sitemap.xml by setting the `YOAST_VIDEO_SITEMAP_BASENAME` constant.
    * If post meta values are encoded, the plugin now decodes them.
* Bug fixes:
    * No longer override opengraph image when one has already been set.
    * Add extra newlines before video schema to allow oEmbed to work.
    * No longer depends on response from Vzaar servers to create sitemap, properly uses the referrer to authenticate requests and adds option in settings to add your Vzaar CNAME.
    * When there's a post-type with the slug `video`, the plugin now automatically changes the basename to `yoast-video`.
    * No longer print empty `<p>` for empty description in meta box.
    * Improve logic whereby "this image" link is shown correctly and only when the video thumb is not overridden.

### 1.5.2

* Enhancements:
    * Added support for Vzaar videos, embedded with either iframe, object embed or shortcode through 1 of 2 plugins.
    * Added [TubePress](https://wordpress.org/plugins/tubepress/) support.
* Bug fixes
    * Wistia.net support added (not just .com).
    * Fixed bug in parsing youtube_sc shortcodes.

### 1.5.1

* Bug fixes:
    * Improved activation.
* Enhancements:
    * Add support for titan lightbox.
    * Prevented some notices.

### 1.5

* Bug fixes:
    * Make `mrss_gallery_lookup` public to prevent notices.
    * Fix some forms of object detection for youtube and others.
    * Fix detection of [video] shortcodes.
* Enhancements:
    * Allow deactivation of license key so it can be used on another domain.
    * Add link to detected thumbnail on video tab.
    * Changed text-domain from `wordpress-seo` to `yoast-video-seo`.
    * Made sure all the strings are translatable.
    * Touch up admin sections styling.
* i18n:
    * You can now translate the plugin to your native language should you need a translation, check [translate.yoast.com](http://translate.yoast.com/projects/yoast-video-seo) for details.
    * Changed text-domain from `wordpress-seo` to `yoast-video-seo`.
    * Added .pot file to repository.
    * Added Dutch translation.

== Upgrade Notice ==

1.6
---
* Please make sure you also upgrade the WordPress SEO plugin to version 1.5 for compatibility.

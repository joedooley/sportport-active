=== WPSiteSync for Content ===
Contributors: serverpress, spectromtech, davejesch, Steveorevo
Donate link: http://wpsitesync.com
Tags: attachments, content, content sync, data migration, desktopserver, export, import, migrate content, moving data, staging, synchronization, taxonomies
Requires at least: 3.5
Tested up to: 4.5
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Provides features for synchronizing content between two WordPress sites.

== Description ==

WPSiteSync for Content helps Designers, Developers and Content Creators Synchronize Blog Post and Page Content between WordPress installs, in Real Time, with a simple Click of a button!

* Local -&gt; Staging
* Staging -&gt; Live
* Local -&gt; Staging -&gt; Live

[youtube https://www.youtube.com/watch?v=KpeiTMbdj_Y]

><strong>Support Details:</strong> We are happy to provide support and help troubleshoot issues. Visit our Contact page at <a href="http://serverpress.com/contact/" target="_blank">http://serverpress.com/contact/</a>. Users should know however, that we check the WordPress.org support forums once a week on Fridays from 10am to 12pm PST (UTC -8).

The <em>WPSiteSync for Content</em> plugin was specifically designed to ease your workflow when creating content between development, staging and live servers. The tool removes the need to migrate an entire database, potentially overwriting new content on the live site, just to update a few pages or posts. Now you can easily move your content from one install to another with the click of a button, reducing errors and saving you time.

While WPSiteSync for Content is optimized to work with local development tools such as DesktopServer, it is designed to be fully functional in any WordPress environment.


<strong>This benefits the Development Workflow in more ways than one:</strong>

* Real-Time Sync eliminates data loss such as Comments.
* Saving development time with No files to backup, download and upload.
* Limit mistakes copying and pasting.
* Client Approval on Staging site is now Faster and Easier than ever.
* Getting paid before Project Delivery is even Easier!

<strong>In the Free Version, WPSiteSync for Contents synchronizes the following:</strong>

* Blog Post Text Content
* Page Text Content
* Content Images
* Featured Images
* PDF Attachements
* Meta-Data (including Advanced Custom Fields)
* Taxonomy such as Tags and Categories
* And much much more

<strong>In our Early Adopter Add-On Program, you will also Receive:</strong>

* WPSiteSync for Bi-Directional Pull (Syncing from Live to Staging)
* WPSiteSync for Custom Post Types
* WPSiteSync for Author Attribution
* WPSiteSync for Comments (coming soon!)
* FULL access to ALL future Premium Extensions

<strong>For more perks such as Early Access</strong> and <strong>Exclusive Preview</strong> of upcoming Features, please visit us at <a href="https://wpsitesync.com">WPSiteSync.com</a>

<strong>ServerPress, LLC is not responsible for any loss of data that may occur as a result of WPSiteSync for Content's use.</strong> However, should you experience such an issue, we want to know about it right away.

== Installation ==

Installation instructions: To install, do the following:

1. From the dashboard of your site, navigate to Plugins --&gt; Add New.
2. Select the "Upload Plugin" button.
3. Click on the "Choose File" button to upload your file.
3. When the Open dialog appears select the wpsitesynccontent.zip file from your desktop.
4. Follow the on-screen instructions and wait until the upload is complete.
5. When finished, activate the plugin via the prompt. A confirmation message will be displayed.

or, you can upload the files directly to your server.

1. Upload all of the files in `wpsitesynccontent.zip` to your  `/wp-content/plugins/wpsitesynccontent` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

You will need to Install and Activate the WPSiteSync for Content plugin on your development website (the Source) as well as the Target web site (where the Content is being moved to).

Once activated, you can use the Configuration page found at Settings -&gt; WPSiteSync, on the Source website to set the URL of the Target and the login credentials to use when sending data. This will allow the WPSiteSync for Content plugin to communicate with the Target website, authenticate, and then move the data between the websites. You do not need to Configure WPSiteSync for Content on the Target website as this will only be receiving Synchronization requests from the Source site.

== Frequently Asked Questions ==

= Do I need to Install WPSiteSync for Content on both sites? =

Yes! The WPSiteSync for Content needs to be installed on the local or Staging server (the website you're moving the data from - the Source), as well as the Live server (the website you're moving the data to - the Target).

= Does this plugin Synchronize all of my content at once? =

No. WPSiteSync for Content will only synchronize the Page or Post content that you are editing. And it will only Synchronize the content when you tell it to. This allows you to control exactly what content is moved between sites and when it will be moved.

= Will this overwrite data while I am editing? =

No. WPSiteSync checks to see if the Content is being edited by someone else on the Target web site. If it is, it will not update the Content, allowing you to coordinate with the users on the Target web site.

In addition, each time Content is updated or Synchronized on the Target web site, a Post Revision is created (using the Post Revision settings). This allows you to recover Content to a previous version.

= Does WPSiteSync only update Page and Posts Content? =

Yes. Support for Custom Post Types is coming very soon. Additional plugins for User Attribution, Synchronizing Comments and Pulling content are in the testing stage and will be released soon as well.

More complex data, such as WooCommerce products, Forms (like Gravity Forms or Ninja Forms), and other plugins that use custom database tables will be supported by additional plugins that work with those products.

== Screenshots ==

1. Configuration page.
2. WPSiteSync for Content metabox.

== Changelog ==
= 1.1.1 - Jul 20, 2016 =
* Fix for authentication issues that sometimes occur after initial credentials are entered.

= 1.1 - Jul 8, 2016 =
* Add features to Settings page and extensibility of APIs in preparation for add-on functionality.
* Fixed several bugs in Settings, data migration after update, admin UI and runtime errors.
* Add response object to API filters; allows display of API warnings in UI.

= 1.0 - Jun 29, 2016 =
* Official Release.
* UI improvements.
* Image attachments sync title, caption and alt content.
* Allow PDF attachments.
* Updates to support Pull operations.
* Change name of API endpoint to ensure uniqueness.
* Add Target Site Key to settings and change database structure.
* Turn on checks for Strict Mode.
* Small bug fixes.

= 0.9.7 - Jun 17, 2016 =
* Release Candidate 2
* Fix some authentication issues on some hosts.
* Improve mechanism for detecting and syncing embedded image references within content.
* Fix duplicated messages in Settings.
* Check mime types of images to ensure valid images are being sent.
* Optionally remove settings/tables on plugin deactivation.
* Other minor bug fixes, improvements and cleanup.

= 0.9.6 - May 20, 2016 =
* Release Candidate 1
* Add authentication token rather than storing passwords.
* Fix issue with not removing Favorite Image on Target when image removed on Source.

= 0.9.5 - May 2, 2016 =
* Fix CSS conflict with wp-product-feed-manager plugin; load CSS/JS only on needed pages and make CSS rules more specific.

= 0.9.4 - Apr 29, 2016 =
* Fix media upload for images referenced within the Content and for featured images.

= 0.9.3 - Apr 26, 2016 =
* Fix taxonomy sync issue when taxonomy does not exist in some conditions on Target.

= 0.9.2 - Apr 22, 2016 =
* Fix runtime error when embeded images are in content.

= 0.9.1 - Apr 20, 2016 =
* Work around for missing apache_request_headers() on SiteGround; fix misnamed header constant.

= 0.9 - Apr 18, 2016 =
* First release - BETA

== Upgrade Notice ==

= 0.9 =
First release.

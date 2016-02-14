=== Hide My Site ===
Contributors: clevelandwebdeveloper
Donate link: http://www.clevelandwebdeveloper.com/wordpress-plugins/donate.php
Tags: password, password protection, password protect, hide, hide site, hide from search engines, hide from google
Requires at least: 2.9
Tested up to: 4.4
Stable tag: 1.6.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Choose a single password to protect your entire wordpress site from the public and search engines

== Description ==

Choose a single password to protect your entire wordpress site. Only visitors who know the password will be able to access your wordpress site. This is a great tool for someone setting up a development version of a wordpress site or anyone else looking to hide their site from the public, search engines, etc...Set your site-wide password by going to <strong>Settings > Hide My Site > Set Your Password</strong>. If you want to disable password protection uncheck the box at <strong>Settings > Hide My Site > Enable Password Protection</strong>.

= Duration =

You can choose how many days you want the user to stay logged in by going to <strong>Settings > Hide My Site > Duration</strong>.

<h3>New in Version 1.6</h3>

<ul>
<li>You can choose to automatically grant access to specific ip addresses</li>
</ul>

<h3>New in Version 1.5</h3>

<ul>
<li>You can set and customize the title tag for the login page</li>
<li>You can choose to discourage search engines from indexing your login page via Settings > Reading > Search Engine Visibility</li>
</ul>

<h3>New in Version 1.4</h3>

<h4>For Everyone</h4>
<ul>
<li><strong>Brute force protection </strong> Blocks access to users after repeated failed login attempts. This protects you from hackers trying to guess your password using "Brute Force" methods. Protection can be toggled via Settings > Hide My Site > Brute Force Protection</li>
<li>You can choose to automatically grant access to admin users</li>
<li>Preview login page option - See your login page as a logged-out visitor would see it. Helpful if you want to see what your login page looks like even if you are already logged in</li>
</ul>

<h3>Version 1.3</h3>

<h4>For Everyone</h4>
<ul>
<li><strong>'Password request for every page' bug fixed.</strong>This will resolve the issue for the vast majority of the subset of users who were experiencing the bug that site visitors were receiving password requests on every page. Also, users can now log in from any page - you no longer have to log in from specifically the homepage in order to stay logged in.</li>
<li>Conflict with polylang plugin fixed</li>
<li>Password characters now hidden when entered on front end</li>
<li>Select a notification message to appear at the top of your login page</li>
</ul>

<h4>For Premium Users</h4>
<ul>
<li>Set background image size, position, repeat, and color via plugin settings page</li>
<li>Customize notification message text which appears at the top of your login page</li>
</ul>

<h3>Older Versions</h3>

<ul>
<li>See Changelog</li>
</ul>

<blockquote>
  <p>Note, this plugin does not currently work with WP Engine hosting because they automatically cache your pages for you. If you use WP Engine or another host that auto-caches, try contacting them directly and ask them if they can deactivate the auto page caching on your site. If you are using a caching plugin and you notice that it conflicts with Hide My Site, try deactivating the plugin and see if that resolves it. Note that deactivating a caching plugin does not always purge the cache. Before you deactivate the caching plugin, first go to the plugin admin page and turn off caching from there.</p>
</blockquote>

== Installation ==

1. From WP admin > Plugins > Add New
1. Search "Hide My Site" under search and hit Enter
1. Click "Install Now"
1. Click the Activate Plugin link

== Frequently asked questions ==

= Where do I set the password? =

Settings > Hide My Site > Set Your Password

= How do I set the duration that a user stays logged in? =

Settings > Hide My Site > Duration

= How do I override a login page template within my theme folder? =

Choose one of the login page styles you want to use as your baseline. For example, let's say you choose the 'Ice' style. Select the 'Ice' theme on the plugin settings page in Settings > Hide My Site > Select a theme for your login page. After you save changes, locate the associated php template file within the plugin subfolder called 'templates'. In this case, it would be the 'hmsice.php' file. Copy and paste this file directly into your theme folder. You can now edit the file as you please, and when you update the plugin in the future, you will not lose the changes you have made to the template. Note this feature is only available to premium users.

== Screenshots ==

1. Hide My Site in action
1. Setting the password and login duration
1. Setting password hint and RSS access
1. Displaying password hint on login page
1. 1 of 5 new sleek login page designs available to premium users
1. Choosing custom login page template, custom background image, and entering custom css (for premium users)
1. Premium users can now choose custom background images and enter custom css for their login pages

== Changelog ==

= 1.6.2 =
* resolved upgrade-functions.php bug. restored login page preview link

= 1.6.1 =
* resolved Undefined index: class bug

= 1.5 =
* set and customize login page title tag
* discourage search engines from indexing login page

= 1.4.1 =
* fixed preview page bug which prevented users from viewing post previews

= 1.4 =
* brute force protection
* auto log in for admins
* preview login page

= 1.3.2 =
* change in code to ensure compliance with WordPress repository rules

= 1.3.1 =
* 'hmsclassic' notification message bug fixed

= 1.3 =
* 'Password request for every page' bug fixed
* Conflict with polylang plugin fixed
* Password characters now hidden when entered on front end
* Select a notification message to appear at the top of your login page
* Premium users: Set background image size, position, repeat, and color via plugin settings page
* Premium users: Customize notification message text

= 1.2 =
* Free feature added: Add a password hint
* Premium features added: Five additional custom login page templates to choose from, Custom background image, Custom CSS, Create your own login page templates within your theme folder

= 1.1 =
* Enabled XML-RPC access

= 1.0 =
* Initial version

== Upgrade Notice ==

= 1.6.2 =
resolves upgrade-functions.php bug. restores login page preview link
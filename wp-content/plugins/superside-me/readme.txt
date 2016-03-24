=== SuperSide Me ===

Contributors: littler.chicken
Donate link: http://robincornett.com/downloads/superside-me
Tags: mobile menu, navigation, sidr
Requires at least: 4.0
Tested up to: 4.4
Stable tag: 1.9.0
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

SuperSide Me is a super app-style mobile navigation plugin for WordPress.

== Description ==

SuperSide Me adds an awesome mobile menu panel to your website. It automagically builds the panel from your existing registered menus. All you have to do, if you want to, is pick the colors you want to use and you're ready to go!

Optionally, you can add a small widget or two to your panel as well, but this widget area is best suited for small/minor widgets, such as a search box.

== Installation ==

1. Upload the entire `superside-me` folder to your `/wp-content/plugins` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Optionally, visit the Settings > Media page to change the default behavior of the plugin.

== Frequently Asked Questions ==

= I activate the plugin and nothing happens. What gives? =

This is likely because your site is using a custom menu in a widget area for your main navigation. The plugin scans the theme's registered menu locations and uses whatever menus are assigned there--if you don't have one assigned, then there is nothing for the plugin to grab on to.

There are two ways to work with this kind of setup: 1) _SuperSide Me_ registers a new menu location, which shows only in the side panel. So you can assign any menu to that, and it will work as your mobile menu, either in addition to other registered menus, or alone if you are not using them at all. 2) You can add a custom menu widget to the _SuperSide Me_ widget area.

= How can I add a widget to my SuperSide Me navigation panel? =

There is a new _SuperSide Me_ widget area under Appearance > Widgets. You can add any widget you like there, but it helps to be reasonable about it, and keep it small.

= Can I change the icons used by the plugin? =

Yes! The plugin has many filters available, including one to change the glyphs, or icons, used for the _SuperSide_ panel. You might change the icons like this (the plugin uses Font Awesome for icons, so use any of those you like):

    add_filter( 'supersideme_default_glyphs', 'prefix_change_superside_glyphs', 10, 2 );
    function prefix_change_superside_glyphs( $glyphs ) {
        $glyphs['slide-nav-link']       = '\f100';
        $glyphs['slide-nav-link-open']  = '\f101';
        $glyphs['menu-close']           = '\f00d';
        $glyphs['sub-menu-toggle']      = '\f107';
        $glyphs['sub-menu-toggle-open'] = '\f106';

        return $glyphs;
    }


= I want to change things about how the menu behaves or is output. =

The plugin has some default CSS, but as little as possible so that you can style your menu the way you want. You can also change a lot of the default behaviors with this filter--use what you want and ignore the rest:

    add_filter( 'supersideme_navigation_options', 'prefix_modify_supersideme_options' );
    function prefix_modify_supersideme_options( $side_tweaks ) {
    	$side_tweaks['background']   = '#000000'; // change background color for side and menu button
    	$side_tweaks['button_color'] = ''; // empty by default (so it will use the panel background color), but there are cases where you might want to style this independently of the main menu panel
    	$side_tweaks['close']        = ''; // removes the close button from the SuperSide panel
    	$side_tweaks['closeevent']   = '.menu-close, .menu-item'; // change what causes the SuperSide Me panel to close (useful mostly if you have on page anchor links in your menu)
    	$side_tweaks['displace']     = false; // the side panel will slide over the site instead of pushing it
    	$side_tweaks['link_color']   = '#fefefe'; // change the link color
    	$side_tweaks['location']     = '.site-header'; // the menu button will hook into the .site-header div instead of being added to the body (default)
    	$side_tweaks['maxwidth']     = '50em'; // change the width at which the mobile menu activates (default 800px)
    	$side_tweaks['outline']      = 'dotted'; // for accessibility support, there is an outline added to the side panel on focus. change the outline style
    	$side_tweaks['panel_width']  = '260px'; // change the width of the side panel
    	$side_tweaks['position']     = 'absolute'; // absolute position instead of relative
    	$side_tweaks['side']         = 'right'; // will override what is set on the settings page
    	$side_tweaks['speed']        = 200; // open/close speed for menu panel (in milliseconds)
    	$side_tweaks['width']        = 'auto'; // main menu button width

    	return $side_tweaks;
    }

= My theme has a responsive menu script in it already. How can I make sure that only one script loads? =

SuperSide Me has a function which lets you easily check whether the plugin is active and can create a menu. Here's how I use it in my starter theme:

    function leaven_load_scripts() {

        // Google Fonts
        wp_enqueue_style( 'google-fonts', '//fonts.googleapis.com/css?family=Oxygen:300,400,700|Lora:400italic,700italic', array(), PARENT_THEME_VERSION );

        // FontAwesome
        wp_enqueue_style( 'fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', array(), '4.3.0' );

        // Responsive Navigation
        wp_enqueue_script( 'leaven-globaljs', get_stylesheet_directory_uri() . '/js/global.js', array( 'jquery' ), false, PARENT_THEME_VERSION );
        if ( function_exists( 'supersideme_has_content' ) && supersideme_has_content() ) {
            return;
        }
        wp_enqueue_script( 'leaven-responsive-menu', get_stylesheet_directory_uri() . '/js/responsive-menu.js', array( 'jquery' ), '1.0.0', true );

        $output = array(
            'mainMenu' => 'Menu',
            'subMenu'  => 'Menu',
        );
        wp_localize_script( 'leaven-responsive-menu', 'LeavenL10n', $output );

    }

Basically, my theme's responsive menu is the last script I load up, and I do an early return if my plugin is running. If it's not, then the theme menu loads.

= Is it possible to deactivate the menu entirely, say, on a landing page template? =

Yes, you can just add this to your template file:

    // Remove Mobile Menu
    add_filter( 'supersideme_override_output', '__return_false' );

= What if my theme's menu still displays? How can I hide it? =

Since theme authors can name their menus and site elements anything they want, it's not possible to account for every single theme in the plugin. There is an easy filter to hide your specific menus, though. Here's how you might hide the navigation toggle button for the WordPress default theme Twenty Fourteen:

    add_filter( 'supersideme_hide_elements', 'twentyfourteen_hide_button' );
    function twentyfourteen_hide_button( $hidden ) {
        $hidden[] = '.menu-toggle'; // append your specific element to an array of general navigation elements
        return $hidden;
    }

If you want even more control over SuperSide Me's inline CSS, which hides other navigation elements and displays the main menu button, here's a filter for you:

    add_filter( 'supersideme_modify_display_css', 'altitude_full_width_supersideme', 10, 3 );
    function altitude_full_width_supersideme( $display_css, $side_tweaks, $hidden ) {
        $display_css =
            $hidden . ' { display: none; }
            .slide-nav-link { display: block; }';
        return $display_css;
    }

`$side_tweaks` are the navigation options passed by the settings page and `supersideme_navigation_options` filter; `$hidden` is the pre-existing array of hidden [navigation] elements.

== Upgrade Notice ==
1.9.0 new setting to automatically resize the menu button; new versions of Font Awesome, Sidr, TouchSwipe

== Changelog ==

= 1.9.0 =
* added: plugin setting to automatically size the menu button
* updated: Font Awesome 4.5.0
* updated: Sidr 2.1.0 (menu script)
* updated: TouchSwipe 1.6.15
* bugfix: some server configurations not recognizing we're in the Customizer

= 1.8.2 =
* bugfix: fixed javascript to check for customizer (again)

= 1.8.1 =
* bugfix: fixed javascript to check for customizer

= 1.8.0 =
* added: plugin settings are now available in the customizer! (developers, you can disable this)
* added: panel width as a plugin setting
* added: developer friendly filters to disable customizer/settings
* fixed: overactive submenu opening/closing (thanks David Kenzik for reporting)

= 1.7.2 =
* added: filter to retrieve menus for panel
* fixed: inconvienently closing panel on android

= 1.7.1 =
* added: filter to modify just the list of hidden [navigation] elements
* added: filter to force search input to display
* improved: moved navigation options filter to a central location, made DRY
* improved: on multisite, plugin activation shows only on main site

= 1.7.0 =
* added: filter to modify rules for menus (specifically for Genesis Simple Menus)
* added: ability to modify the speed of the side panel open/close (thanks to Mike Z for requesting)
* added: ability to change the panel source (for @jivedig). use at your own risk
* changed: optional menu sidebar build/output improved--can handle more robust widgets
* updated: EDD software licensing update script
* improved: panel source--no more duplicate divs
* improved: check for whether panel can be successfully output (thanks to David W for reporting)
* bugfix: if Genesis Simple Menus is active, correctly uses that menu
* bugfix: no longer hides Woo breadcrumbs
* deprecated: panel builder. search, menus, and sidebar all added separately now due to errors from complex widgets

= 1.6.1 =
* added: support for changing Genesis skip links to point to mobile menu button

= 1.6.0 =
* added: optionally add support for swiping the menu open/closed
* bettered: SuperSide Me now pays attention to whether it's "enabled"
* updated: now sporting Font Awesome 4.4
* improved: theme navigation is not hidden (unless the theme has hidden it), menu button does not show, if js is disabled
* bugfix: improve helper function `supersideme_has_content`
* bugfix: CSS for mysteriously hidden menu items
* cleaned up js

= 1.5.0 =
* Add search input option!
* bug fixes for settings

= 1.4.2 =
* Added link to the settings page to the plugins table
* Fix validation of settings before localization

= 1.4.1 =
* Added checkmark for valid license
* bugfix: fixed settings check for pre PHP5.5

= 1.4.0 =
* Renamed plugin.
* Implemented licensing.
* Implemented checks and warnings to avoid an empty menu panel.
* Mapped deprecated filters

= 1.3.2 =
* bugfix: side panel still works even with zero registered menus
* bugfix: side panel compiled only on front end

= 1.3.0 =
* add maxwidth as a plugin setting, instead of limiting to filter

= 1.2.2 =
* close menu on window resize
* tweak CSS for theme capability and Mike

= 1.2.1 =
* Added CSS filters

= 1.2.0 =
* Registered a new optional menu location for the widget to use.

= 1.1.0 =
* A11y work on focus, etc.

= 1.0.0 =
* Initial commit.

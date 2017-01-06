<?php

/**
 * Helper functions for SuperSide Me.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2017 Robin Cornett
 * @license   GPL-2.0+
 */

/**
 * helper function for themes to use to check whether SuperSide Me can perform.
 * defined in class SuperSide_Me
 *
 * @return bool
 *
 * @since 1.4.0
 */
function supersideme_has_content() {
	return apply_filters( 'supersideme_panel_has_content', false );
}

/**
 * helper function to get the plugin settings, with defaults
 *
 * @return array
 *
 * @since 1.5.1
 */
function supersideme_get_settings() {
	return apply_filters( 'supersideme_get_plugin_setting', false );
}

/**
 * helper function to get the navigation options, with defaults
 *
 * @return array
 * @since 1.7.1
 */
function supersideme_get_navigation_options() {
	return apply_filters( 'supersideme_get_navigation_options', false );
}

/**
 * Optionally disable the settings page. Cannot disable licensing though.
 * @return bool
 * @since 2.0.0
 */
function supersideme_disable_settings_page() {
	return (bool) apply_filters( 'supersideme_disable_settings_page', false );
}

/**
 * Determine if the settings page can be output at all.
 * @return bool
 * @since 2.0.0
 */
function supersideme_do_settings_page() {
	$disabled = supersideme_disable_settings_page();
	if ( ( is_multisite() && is_main_site() ) || ! is_multisite() ) {
		return true;
	}

	return (bool) ! $disabled;
}

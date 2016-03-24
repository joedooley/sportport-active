<?php

/**
 * Helper functions for SuperSide Me.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2016 Robin Cornett
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
if ( ! function_exists( 'supersideme_has_content' ) ) {
	function supersideme_has_content() {
		return apply_filters( 'supersideme_panel_has_content', false );
	}
}

/**
 * helper function to get the plugin settings, with defaults
 *
 * @return array
 *
 * @since 1.5.1
 */
if ( ! function_exists( 'supersideme_get_settings' ) ) {
	function supersideme_get_settings() {
		return apply_filters( 'supersideme_get_plugin_setting', false );
	}
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

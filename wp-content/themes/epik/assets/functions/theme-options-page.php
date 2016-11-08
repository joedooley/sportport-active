<?php
/**
 * This file contains functions for theme-options-page.php
 *
 * @author          Joe Dooley - Developing Designs | hello@developingdesigns.com
 * @package         SportPort Active Theme
 * @subpackage      Customizations
 *
 * @link            https://github.com/joedooley/sportport-active
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'genesis_before', 'spa_google_tag_manager' );
/**
 * Add required Google Tag Manager script
 * after the opening <body> tag. Needed for
 * Google Tag Manager for WordPress plugin.
 *
 * @return        void
 * @author        Joe Dooley
 *
 */
function spa_google_tag_manager() {
	if ( function_exists( 'gtm4wp_the_gtm_tag' ) ) {
		gtm4wp_the_gtm_tag();
	}
}

add_action( 'init', 'spa_add_acf_options_page' );
/**
 * ACF Theme Options Page
 */
function spa_add_acf_options_page() {
	if ( function_exists( 'acf_add_options_page' ) && is_admin() ) {

		$acf_theme_settings = acf_add_options_page( array(
			'page_title' => 'Theme General Settings',
			'menu_title' => 'Theme Settings',
			'menu_slug'  => 'theme-general-settings',
			'capability' => 'edit_posts',
			'redirect'   => false,
		) );

	}
}


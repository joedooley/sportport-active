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


// ACF Theme Options Page
if ( function_exists( 'acf_add_options_page' ) || is_admin() ) {

	$acf_theme_settings = acf_add_options_page( array(
		'page_title' => 'Theme General Settings',
		'menu_title' => 'Theme Settings',
		'menu_slug'  => 'theme-general-settings',
		'capability' => 'edit_posts',
		'redirect'   => false,
	) );

}


add_action( 'genesis_before', 'spa_free_shipping_before_header' );
/**
 * Output Free Shipping Notification into before header hook
 *
 * @since   1.0.0
 *
 * @return  null if the free_shipping_notification is empty
 */
function spa_free_shipping_before_header() {

	echo '<section class="before-header"><div class="wrap">';

	genesis_widget_area( 'before-header-left', array(
		'before' => '<div class="before-header-left-container">',
		'after'  => '</div>',
	) );

	if ( get_field( 'free_shipping_notification', 'option' ) ) {

		echo '<div class="before-header-left-container"><div class="one-third before-header-middle">';

		the_field( 'free_shipping_notification', 'option' );

		echo '</div></div>';

	}

	genesis_widget_area( 'before-header-right', array(
		'before' => '<div class="before-header-right-container">',
		'after'  => '</div>',
	) );

	echo '</div></section>';
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



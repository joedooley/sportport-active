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

	if ( get_field( 'free_shipping_notification', 'option' ) ) {
		the_field( 'free_shipping_notification', 'option' );
	}

	echo '</div></section>';
}




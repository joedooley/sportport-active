<?php
/**
 * This file contains all Genesis specific functions
 *
 * @author     Joe Dooley
 * @package    SportPort Active Theme
 * @subpackage Customizations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'spa_add_theme_support' );
/**
 * Add theme support features on after-theme-setup hook
 *
 * @author Joe Dooley
 *
 */
function spa_add_theme_support() {

	add_theme_support( 'html5' );
	add_theme_support( 'genesis-responsive-viewport' );
	add_theme_support( 'custom-background' );
	add_theme_support( 'genesis-footer-widgets', 3 );
	add_theme_support( 'genesis-connect-woocommerce' );
	add_theme_support( 'woocommerce' );


	add_theme_support( 'genesis-structural-wraps', array(
		'header',
		'nav',
		'subnav',
		'inner',
		'footer-widgets',
		'footer',
	) );


	add_action( 'wp_enqueue_scripts', 'spa_scripts_styles' );

	//* Remove the site description
	remove_action( 'genesis_site_description', 'genesis_seo_site_description' );

	//* Remove header right widget area
	unregister_sidebar( 'header-right' );

	//* Reposition secondary navigation menu
	remove_action( 'genesis_after_header', 'genesis_do_nav' );
	add_action( 'genesis_header', 'genesis_do_nav', 12 );

}

//* Remove the entry meta in the entry header (requires HTML5 theme support)
remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );


add_action( 'init', 'spa_register_custom_image_sizes' );
/**
 * Register custom image sizes
 */
function spa_register_custom_image_sizes() {

	add_image_size( 'featured-img', 730, 420, true );
	add_image_size( 'featured-page', 341, 173, true );
	add_image_size( 'portfolio-thumbnail', 264, 200, true );

}


// Customize search form input box text
add_filter( 'genesis_search_text', 'custom_search_text' );
function custom_search_text( $text ) {
	return esc_attr( 'Search...' );
}


<?php
/**
 * Add WooCommerce 3.0 support
 *
 * This file adds the required WooCommerce setup functions to SportPort Active child theme
 *
 * @package SportPort
 * @author  Developing Designs
 * @link    https://www.developingdesigns.com/
 */


add_action( 'after_setup_theme', 'spa_wc_add_theme_support' );
/**
 * Add product gallery support
 */
function spa_wc_add_theme_support() {
	if ( class_exists( 'WooCommerce' ) ) {

		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-zoom' );
		//add_theme_support( 'wc-product-gallery-slider' );

	}
}



add_action( 'wp_enqueue_scripts', 'spa_genesis_sample_products_match_height', 99 );
/**
 * Print an inline script to the footer to keep products the same height.
 *
 */
function spa_genesis_sample_products_match_height() {

	// If Woocommerce is not activated, or a product page isn't showing, exit early.
	if ( ! class_exists( 'WooCommerce' ) || ! is_shop() && ! is_product_taxonomy() && ! is_front_page() ) {
		return;
	}

	wp_enqueue_script( 'spa-match-height', CHILD_VENDOR_JS_DIR . '/match-height.js', array( 'jquery' ), CHILD_THEME_VERSION, true );
	wp_add_inline_script( 'spa-match-height', "jQuery(document).ready( function() { jQuery( '.product .woocommerce-LoopProduct-link').matchHeight(); });" );

}



add_filter( 'woocommerce_style_smallscreen_breakpoint', 'spa_genesis_sample_woocommerce_breakpoint' );
/**
 * Modify the WooCommerce breakpoints.
 *
 * @since 2.3.0
 *
 * @return string Pixel width of the theme's breakpoint.
 */
function spa_genesis_sample_woocommerce_breakpoint() {

	$current = genesis_site_layout();
	$layouts = array(
		'one-sidebar' => array(
			'content-sidebar',
			'sidebar-content',
		),
		'two-sidebar' => array(
			'content-sidebar-sidebar',
			'sidebar-content-sidebar',
			'sidebar-sidebar-content',
		),
	);

	if ( in_array( $current, $layouts['two-sidebar'] ) ) {
		return '2000px'; // Show mobile styles immediately.
	} elseif ( in_array( $current, $layouts['one-sidebar'] ) ) {
		return '1200px';
	} else {
		return '860px';
	}

}



add_filter( 'genesiswooc_products_per_page', 'spa_genesis_sample_default_products_per_page' );
/**
 * Set the default products per page.
 *
 * @since 2.3.0
 *
 * @return int Number of products to show per page.
 */
function spa_genesis_sample_default_products_per_page() {
	return 8;
}




add_filter( 'woocommerce_pagination_args', 	'spa_genesis_sample_woocommerce_pagination' );
/**
 * Update the next and previous arrows to the default Genesis style.
 *
 * @since 2.3.0
 *
 * @return string New next and previous text string.
 */
function spa_genesis_sample_woocommerce_pagination( $args ) {

	$args['prev_text'] = sprintf( '&laquo; %s', __( 'Previous Page', 'spa' ) );
	$args['next_text'] = sprintf( '%s &raquo;', __( 'Next Page', 'spa' ) );

	return $args;

}

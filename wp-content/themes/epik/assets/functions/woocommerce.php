<?php
/**
 * This file contains all WooCommerce specific functions
 *
 * @package    YourMembership
 * @author     Developing Designs - Joe Dooley
 * @link       https://www.developingdesigns.com
 * @copyright  Joe Dooley, Developing Designs
 * @license    GPL-2.0+
 */

//* Remove WooCommerce Order By Dropdown
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );


add_action( 'template_redirect', 'remove_sidebar_shop' );
/**
 * Remove Sidebar from Shop and Single Product pages
 *
 * @return      void
 * @author      Joe Dooley
 */
function remove_sidebar_shop() {

	if ( is_product() || is_shop() ) {
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar' );
	}

}



add_action( 'wp_enqueue_scripts', 'spa_conditionally_load_woc_js_css' );
/**
 * Dequeue WooCommerce Scripts and Styles for pages that don't need them
 */
function spa_conditionally_load_woc_js_css() {

	if ( function_exists( 'spa_conditionally_load_woc_js_css' ) ) {

		if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {

			wp_dequeue_script( 'woocommerce' );
			wp_dequeue_script( 'wc-add-to-cart' );
			wp_dequeue_script( 'wc-cart-fragments' );

			wp_dequeue_style( 'woocommerce-general' );
			wp_dequeue_style( 'woocommerce-layout' );
			wp_dequeue_style( 'woocommerce-smallscreen' );
		}

		if ( is_product() ) {
			wp_dequeue_style( 'pac-styles-css' );
			wp_dequeue_style( 'pac-layout-styles-css' );
		}
	}

}


// Remove WooCommerce breadcrumbs, using Genesis crumbs instead.
add_action( 'get_header', function() {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
});

add_filter( 'woocommerce_product_tabs', 'spa_woo_remove_product_tabs', 98 );
/**
 * Delete WooCommerce Product Tabs.
 *
 * @param $tabs
 * @return mixed
 */
function spa_woo_remove_product_tabs( $tabs ) {

	unset( $tabs['description'] );
	unset( $tabs['reviews'] );
	unset( $tabs['additional_information'] );

	return $tabs;

}


//Full Width Pages on WooCommerce
function themeprefix_cpt_layout() {
	if ( is_page( array( 'cart', 'checkout' ) ) || is_shop() || 'product' == get_post_type() ) {
		return 'full-width-content';
	}
}

add_filter( 'genesis_site_layout', 'themeprefix_cpt_layout' );

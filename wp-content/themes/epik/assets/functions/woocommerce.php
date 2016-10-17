<?php
/**
 * This file contains all WooCommerce specific functions
 *
 * @package    SportPort Active
 * @author     Developing Designs - Joe Dooley
 * @link       https://www.developingdesigns.com
 * @copyright  Joe Dooley, Developing Designs
 * @license    GPL-2.0+
 */


add_action( 'template_redirect', 'remove_sidebar_shop' );
/**
 * Remove Sidebar from Single Product pages
 *
 * @return  void
 */
function remove_sidebar_shop() {
	if ( is_product() ) {
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar' );
	}
}


/**
 * Replace primary sidebar with shop-sidebar on WooCommerce archives.
 *
 * @uses spa_do_shop_sidebar()
 */
add_action( 'get_header', function () {
	if ( is_shop() || is_product_taxonomy() ) {

		remove_action( 'get_header', 'gencwooc_ss_handler', 12 );
		remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );

		add_action( 'genesis_sidebar', 'spa_do_shop_sidebar' );
	}
}, 12 );


/**
 * Output shop-sidebar.
 */
function spa_do_shop_sidebar() {
	dynamic_sidebar( 'shop-sidebar' );
}


add_filter( 'genesis_site_layout', 'spa_wc_force_full_width' );
/**
 * Force full width layout on WooCommerce pages
 * @return string
 */
function spa_wc_force_full_width() {
	if ( is_page( array( 'cart', 'checkout' ) ) || 'product' === get_post_type() ) {
		return 'full-width-content';
	}
}


/**
 * Remove WooCommerce orderby dropdown.
 */
add_action( 'get_header', function() {
	if ( is_woocommerce() ) {
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	}
} );


/**
 * Remove WooCommerce breadcrumbs, using Genesis crumbs instead.
 */
add_action( 'get_header', function () {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
} );



add_action( 'wp_enqueue_scripts', 'spa_conditionally_load_woc_js_css' );
/**
 * Dequeue WooCommerce Scripts and Styles for pages that don't need them.
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


/**
 * Move product price to just before add to cart button.
 */
add_action( 'get_header', function () {
	if ( is_front_page() || is_shop() || is_product_taxonomy()  || is_product() ) {
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
		add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_price', 6 );
	}
} );


/**
 * Truncate product title to one line or 3 words
 * on everything but is_product() pages.
 *
 * @param $title string
 * @return $title string
 */
add_filter( 'the_title', function( $title ) {
	return is_shop() || is_front_page() || is_product_taxonomy() ? wp_trim_words( $title, 3 ) : $title;
} );



/**
 * Change WC add to cart button text on all pages
 * except single-product.php,
 */
add_filter( 'woocommerce_product_add_to_cart_text', function() {
	return __( 'Shop Now', 'woocommerce' );
} );


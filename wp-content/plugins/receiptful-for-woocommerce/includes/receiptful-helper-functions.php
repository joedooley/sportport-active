<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( ! function_exists( 'wc_get_coupon_by_code' ) ) {

	/**
	 * Coupon by code.
	 *
	 * Get the coupon ID by the coupon code.
	 *
	 * @param	string		$coupon_code	Code that is used as coupon code.
	 * @return	int|bool					WP_Post ID if coupon is found, otherwise False.
	 */
	function wc_get_coupon_by_code( $coupon_code ) {

		global $wpdb;

		$coupon_id = $wpdb->get_var( $wpdb->prepare( apply_filters( 'woocommerce_coupon_code_query', "
			SELECT ID
			FROM $wpdb->posts
			WHERE post_title = %s
			AND post_type = 'shop_coupon'
			AND post_status = 'publish'
		" ), $coupon_code ) );

		if ( ! $coupon_id ) {
			return false;
		} else {
			return $coupon_id;
		}

	}

}


if ( ! function_exists( 'wc_get_random_products' ) ) {

	/**
	 * Random products.
	 *
	 * Get random WC product IDs.
	 *
	 * @param	int		$limit	Number of products to return
	 * @return	array			List of random product IDs.
	 */
	function wc_get_random_products( $limit = 2 ) {

		$product_args = array(
			'fields'			=> 'ids',
			'post_type'			=> 'product',
			'post_status'		=> 'publish',
			'posts_per_page'	=> $limit,
			'orderby'			=> 'rand',
			'meta_query'		=> array(
				array(
					'meta_key'	=> '_thumbnail_id',
					'compare'	=> 'EXISTS',
				),
			)
		);
		$products = get_posts( $product_args );

		return $products;

	}

}


/************************************************
 * Compatibility functions.
 *
 * @author		Receiptful
 * @version		1.0.0
 * @since		1.0.1
 ***********************************************/


if ( ! function_exists( 'wc_get_product' ) ) {

	/**
	 * Define function to make plugin compatible with WooCommerce 2.1.x
	 *
	 * @codeCoverageIgnore
	 */
	function wc_get_product( $product_id ) {

		return get_product( $product_id );
	}

}


if ( ! function_exists( 'wc_get_order' ) ) {

	/**
	 * Define function to make plugin compatible with WooCommerce 2.1.x
	 *
	 * @codeCoverageIgnore
	 */
	function wc_get_order( $order ) {

		$order_id = 0;

		if ( $order instanceof WP_Post ) {
			$order_id = $order->ID;
		} elseif ( $order instanceof WC_Order ) {
			$order_id = $order->id;
		} elseif ( is_numeric( $order ) ) {
			$order_id = $order;
		}

		return new WC_Order( $order_id );
	}

}


if ( ! function_exists( 'wc_tax_enabled' ) ) {

	/**
	 * Define function to make plugin compatible with WooCommerce 2.2.x
	 *
	 * @codeCoverageIgnore
	 *
	 * Are store-wide taxes enabled?
	 * @return bool
	 */
	function wc_tax_enabled() {
		return get_option( 'woocommerce_calc_taxes' ) === 'yes';
	}

}


/**
 * Clear unused, expired coupons.
 *
 * Clear the *Receiptful* coupons that are expired / unused for at least
 *
 * @since 1.2.2
 */
function receiptful_clear_unused_coupons() {

	$expired_coupons = new WP_Query( array(
		'post_type' => 'shop_coupon',
		'fields' => 'ids',
		'posts_per_page' => 1000,
		'meta_query' => array(
			array(
				'key' => 'receiptful_coupon',
				'compare' => '=',
				'value' => 'yes',
			),
			array(
				'key' => 'expiry_date',
				'compare' => '<',
				'type' => 'DATE',
				'value' => date_i18n( 'Y-m-d', strtotime( '-7 days' ) ),
			),
			array(
				'key' => 'usage_count',
				'compare' => '=',
				'value' => '0',
			),
		),
	) );

	// Trash expired coupons
	foreach ( $expired_coupons->get_posts() as $post_id ) {
		wp_trash_post( $post_id );
	}

}


/**
 * Add Receiptful version endpoint.
 *
 * Adds a simple Receiptful version check endpoint, allowing
 * to check if Receiptful is active and which version.
 *
 * @since 1.2.5
 */
function receiptful_add_active_endpoint() {

	if ( isset( $_GET['receiptful_version'] ) ) {
		wp_send_json( Receiptful()->version );
	}

}
if ( isset( $_GET['receiptful_version'] ) ) {
	add_action( 'init', 'receiptful_add_active_endpoint' );
}
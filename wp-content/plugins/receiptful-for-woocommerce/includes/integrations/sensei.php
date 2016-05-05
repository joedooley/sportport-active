<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WooThemes Sensei compatibility functions.
 *
 * @author		Receiptful
 * @version		1.0.0
 * @since		1.2.3
 */


/**
 * Check if sensei is active.
 *
 * @since 1.2.3
 *
 * @return bool True when WooThemes Sensei is active, false otherwise.
 */
function receiptful_is_sensei_active() {

	$active = false;
	if ( in_array( 'woothemes-sensei/woothemes-sensei.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		$active = true;
	} elseif ( is_plugin_active_for_network( 'woothemes-sensei/woothemes-sensei.php' ) ) {
		$active = true;
	}

	return $active;

}

/**
 * Add course links to receipt.
 *
 * Add Sensei course links to the receipt when the Sensei plugin is used.
 *
 * @param $urls
 * @param $item
 * @param $order_id
 * @return array
 */
function receiptful_sensei_add_course_links( $urls, $item, $order_id ) {

	$order = wc_get_order( $order_id );
	$order_items = $order->get_items();

	// exit early if not wc-completed or wc-processing
	if ( 'wc-completed' != $order->post_status && 'wc-processing' != $order->post_status ) {
		return $urls;
	}

	if ( empty( $order_items ) ) {
		return $urls;
	}

	$product_id = ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];
	$user_id = get_post_meta( $order->id, '_customer_user', true );

	if ( ! $user_id ) {
		return $urls;
	}

	// Get all courses for product
	$args = array(
		'posts_per_page' => -1,
		'post_type' => 'course',
		'meta_query' => array(
			array(
				'key' => '_course_woocommerce_product',
				'value' => $product_id
			)
		),
		'orderby' => 'menu_order date',
		'order' => 'ASC',
	);
	$courses = get_posts( $args );

	if ( empty( $courses) ) {
		return $urls;
	}

	foreach( $courses as $course ) {

		$title = $course->post_title;
		$urls[] = array(
			'key' => sprintf( __( 'View course: %1$s', 'receiptful-for-woocommerce' ), $title ),
			'value' => esc_url( get_permalink( $course->ID ) ),
		);
	}

	return $urls;

}
if ( receiptful_is_sensei_active() ) {
	add_filter( 'receiptful_get_download_urls', 'receiptful_sensei_add_course_links', 10, 3 );
}

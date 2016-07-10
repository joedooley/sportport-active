<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * CRON events.
 *
 * @author		Receiptful
 * @version		1.1.1
 * @since		1.0.0
 */


/**
 * 15 minute interval.
 *
 * Add a 15 minute interval to the cron schedules.
 *
 * @since 1.0.0
 *
 * @param	array $schedules	List of current CRON schedules.
 * @return	array				List of modified CRON schedules.
 */
function receiptful_add_quarter_schedule( $schedules ) {

	$schedules['quarter_hour'] = array(
		'interval'	=> 60 * 15, // 60 seconds * 15 minutes
		'display'	=> __( 'Every quarter', 'receiptful-for-woocommerce' ),
	);

	return $schedules;

}
add_filter( 'cron_schedules', 'receiptful_add_quarter_schedule' );


/**
 * Schedule events.
 *
 * Schedule the resend of receipts to fire every 15 minutes
 * Scheduled outside class because working with objects isn't
 * perfect while doing events.
 *
 * @since 1.0.0
 */
function receiptful_schedule_event() {

	// Resend queue
	if ( ! wp_next_scheduled( 'receiptful_check_resend' ) ) {
		wp_schedule_event( 1407110400, 'quarter_hour', 'receiptful_check_resend' ); // 1407110400 is 08 / 4 / 2014 @ 0:0:0 UTC
	}

	// Initial product sync
	if ( ! wp_next_scheduled( 'receiptful_initial_product_sync' ) && 1 != get_option( 'receiptful_completed_initial_product_sync', 0 ) ) {
		wp_schedule_event( 1407110400, 'quarter_hour', 'receiptful_initial_product_sync' ); // 1407110400 is 08 / 4 / 2014 @ 0:0:0 UTC
	} elseif ( wp_next_scheduled( 'receiptful_initial_product_sync' ) && 1 == get_option( 'receiptful_completed_initial_product_sync', 0 ) ) {
		// Remove CRON when we're done with it.
		wp_clear_scheduled_hook( 'receiptful_initial_product_sync' );
	}

	// Initial receipt sync
	if ( ! wp_next_scheduled( 'receiptful_initial_receipt_sync' ) && 1 != get_option( 'receiptful_completed_initial_receipt_sync', 0 ) ) {
		wp_schedule_event( 1407110400, 'quarter_hour', 'receiptful_initial_receipt_sync' ); // 1407110400 is 08 / 4 / 2014 @ 0:0:0 UTC
	} elseif ( wp_next_scheduled( 'receiptful_initial_receipt_sync' ) && 1 == get_option( 'receiptful_completed_initial_receipt_sync', 0 ) ) {
		wp_clear_scheduled_hook( 'receiptful_initial_receipt_sync' ); // Remove CRON when we're done with it.
	}

}
add_action( 'init', 'receiptful_schedule_event' );


/**
 * Resend queue.
 *
 * Function is called every 15 minutes by a CRON job.
 * This fires the resend of Receipts and data that should be synced.
 *
 * @since 1.0.0
 */
function receiptful_check_resend() {

	// Receipt queue
	Receiptful()->email->resend_queue();

	// Products queue
	Receiptful()->products->process_queue();

	// Orders queue
	Receiptful()->order->process_queue();

}
add_action( 'receiptful_check_resend', 'receiptful_check_resend' );


/**
 * Sync data.
 *
 * Sync data with the Receiptful API, this contains products for now.
 * The products are synced with Receiptful to give the best product recommendations.
 * This is a initial product sync, the process should be completed once.
 *
 * @since 1.1.1
 */
function receiptful_initial_product_sync() {

	$product_ids = get_posts( array(
		'fields'			=> 'ids',
		'posts_per_page'	=> '225',
		'post_type'			=> 'product',
		'has_password'		=> false,
		'post_status'		=> 'publish',
		'meta_query'		=> array(
			'relation' 		=> 'OR',
			// @since 1.2.5 - This is for a re-sync that should be initialised
			array(
				'key'		=> '_receiptful_last_update',
				'compare'	=> '<',
				'value'		=> strtotime( '2016-05-06' ),
			),
			array(
				array(
					'key'		=> '_receiptful_last_update',
					'compare'	=> 'NOT EXISTS',
					'value'		=> '',
				),
				array(
					'key'		=> '_visibility',
					'compare'	=> '!=',
					'value'		=> 'hidden',
				),
			),
		),
	) );

	// Update option so the system knows it should stop syncing
	if ( empty( $product_ids ) ) {
		update_option( 'receiptful_completed_initial_product_sync', 1 );
		return;
	}

	// Get product args
	$args = array();
	foreach ( $product_ids as $product_id ) {
		$args[] = Receiptful()->products->get_formatted_product( $product_id );
	}

	// Update products
	$response = Receiptful()->api->update_products( $args );

	// Process response
	if ( is_wp_error( $response ) ) {

		return false;

	} elseif ( in_array( $response['response']['code'], array( '400' ) ) ) {

		// Set empty update time, so its not retried at next CRON job
		foreach ( $product_ids as $product_id ) {
			update_post_meta( $product_id, '_receiptful_last_update', '' );
		}

	} elseif ( in_array( $response['response']['code'], array( '200', '202' ) ) ) { // Update only the ones without error - retry the ones with error

		$failed_ids = array();
		$body 		= json_decode( $response['body'], 1 );
		foreach ( $body['errors'] as $error ) {
			$failed_ids[] = isset( $error['error']['product_id'] ) ? $error['error']['product_id'] : null;
		}

		// Set empty update time, so its not retried at next CRON job
		foreach ( $product_ids as $product_id ) {
			if ( ! in_array( $product_id, $failed_ids ) ) {
				update_post_meta( $product_id, '_receiptful_last_update', time() );
			} else {
				update_post_meta( $product_id, '_receiptful_last_update', '' );
			}
		}

	} elseif ( in_array( $response['response']['code'], array( '401', '500', '503' ) ) ) { // Retry later - keep meta unset
	}

}
add_action( 'receiptful_initial_product_sync', 'receiptful_initial_product_sync' );


/**
 * Sync Receipt data.
 *
 * Sync data with the Receiptful API, this contains products for now.
 * The products are synced with Receiptful to give the best product recommendations.
 * This is a initial product sync, the process should be completed once.
 *
 * @since 1.1.2
 */
function receiptful_initial_receipt_sync() {

	$receipt_ids = get_posts( array(
		'fields'			=> 'ids',
		'posts_per_page'	=> '225',
		'post_type'			=> 'shop_order',
		'post_status'		=> array_keys( wc_get_order_statuses() ),
		'meta_query'		=> array(
			'relation' => 'OR',
			array(
				'key'		=> '_receiptful_last_update',
				'compare'	=> 'NOT EXISTS',
				'value'		=> '',
			),
			// @since 1.1.9 - This is for a re-sync that should be initialised
			array(
				'key'		=> '_receiptful_last_update',
				'compare'	=> '<',
				'value'		=> strtotime( '2015-07-15' ),
			),
		),
	) );

	// Update option so the system knows it should stop syncing
	if ( empty( $receipt_ids ) ) {
		update_option( 'receiptful_completed_initial_receipt_sync', 1 );
		return;
	}

	// Prepare product args
	$args = array();
	foreach ( $receipt_ids as $receipt_id ) {

		$order		= wc_get_order( $receipt_id );
		$items 		= WC()->mailer->emails['WC_Email_Customer_Completed_Order']->api_args_get_items( $order );
		$subtotals 	= WC()->mailer->emails['WC_Email_Customer_Completed_Order']->api_args_get_subtotals( $order );
		$order_args	= WC()->mailer->emails['WC_Email_Customer_Completed_Order']->api_args_get_order_args( $order, $items, $subtotals, $related_products = array() );
		$order_args['status'] = $order->get_status();

		$args[] = $order_args;

	}

	// Update products
	$response = Receiptful()->api->upload_receipts( $args );

	// Process response
	if ( is_wp_error( $response ) ) {

		return false;

	} elseif ( in_array( $response['response']['code'], array( '400' ) ) ) {

		// Set empty update time, so its not retried at next CRON job
		foreach ( $receipt_ids as $receipt_id ) {
			update_post_meta( $receipt_id, '_receiptful_last_update', '' );
		}

	} elseif ( in_array( $response['response']['code'], array( '200', '202' ) ) ) { // Update only the ones without error - retry the ones with error

		$failed_ids = array();
		$body 		= json_decode( $response['body'], 1 );
		foreach ( $body['errors'] as $error ) {
			$failed_ids[] = isset( $error['error']['reference'] ) ? $error['error']['reference'] : null;
		}

		// Set empty update time, so its not retried at next CRON job
		foreach ( $receipt_ids as $receipt_id ) {
			if ( ! in_array( $receipt_id, $failed_ids ) ) {
				update_post_meta( $receipt_id, '_receiptful_last_update', time() );
			} else {
				update_post_meta( $receipt_id, '_receiptful_last_update', '' );
			}
		}

	} elseif ( in_array( $response['response']['code'], array( '401', '500', '503' ) ) ) { // Retry later - keep meta unset
	}

}
add_action( 'receiptful_initial_receipt_sync', 'receiptful_initial_receipt_sync' );

<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Receiptful_Order.
 *
 * Class to manage all order stuff.
 *
 * @class		Receiptful_Order
 * @since		1.1.6
 * @version		1.1.6
 * @author		Receiptful
 */
class Receiptful_Order {


	/**
	 * Constructor.
	 *
	 * @since 1.1.6
	 */
	public function __construct() {

		// Save Receiptful user token on checkout
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'order_save_user_token' ), 10, 2 );

		// Check product stock, if empty update product
		add_action( 'woocommerce_reduce_order_stock', array( $this, 'maybe_update_products' ), 10, 1 );

	}


	/**
	 * Save user token.
	 *
	 * Save the user token from the receiptful cookie at checkout.
	 * After save it will immediately be deleted. When deleted it will
	 * automatically re-generate a new one to track the new purchase flow.
	 *
	 * @since 1.1.6
	 *
	 * @param int 	$order_id	ID of the order that is being processed.
	 * @param array	$posted		List of $_POST values.
	 */
	public function order_save_user_token( $order_id, $posted ) {

		if ( isset( $_COOKIE['receiptful-token'] ) ) {
			update_post_meta( $order_id, '_receiptful_token', $_COOKIE['receiptful-token'] );
		}

	}


	/**
	 * Update products.
	 *
	 * Maybe send a update to Receiptful. Check if the product is out-of-stock,
	 * when it is, a update will be send to Receiptful to make sure the product
	 * is set to 'hidden'.
	 *
	 * @since 1.1.9
	 *
	 * @param	WC_Order	$order	Order object.
	 */
	public function maybe_update_products( $order ) {

		foreach ( $order->get_items() as $item ) {

			if ( $item['product_id'] > 0 ) {
				$_product = $order->get_product_from_item( $item );

				if ( $_product && $_product->exists() && $_product->managing_stock() ) {
					if ( ! $_product->is_in_stock() ) {
						Receiptful()->products->update_product( $_product->id );
					}
				}

			}

		}

	}



	/**
	 * Process orders queue.
	 *
	 * Process the orders that are in the queue. Only bulk upload should be in this queue /
	 * get a 'status' parameter.
	 *
	 * @since 1.1.13
	 */
	public function process_queue() {

		$queue = get_option( '_receiptful_queue', array() );

		if ( isset( $queue['orders'] ) && is_array( $queue['orders'] ) ) {

			$upload_args = array();
			$receipt_ids = array_slice( $queue['orders'], 0, 225, true );
			foreach ( $receipt_ids as $key => $order ) {

				if ( 'upload' == $order['action'] ) {

					$order		= wc_get_order( $order['id'] );
					$items 		= WC()->mailer->emails['WC_Email_Customer_Completed_Order']->api_args_get_items( $order );
					$subtotals 	= WC()->mailer->emails['WC_Email_Customer_Completed_Order']->api_args_get_subtotals( $order );
					$order_args	= WC()->mailer->emails['WC_Email_Customer_Completed_Order']->api_args_get_order_args( $order, $items, $subtotals, $related_products = array() );
					$order_args['status'] = $order->get_status(); // Give bulk uploads a order status

					$upload_args[] = $order_args;

				}

			}

			if ( ! empty( $upload_args ) ) {
				// Update products
				$response = Receiptful()->api->upload_receipts( $upload_args );

				// Process response
				if ( ! is_wp_error( $response ) && in_array( $response['response']['code'], array( '400' ) ) ) {

					// Set empty update time, so its not retried at next CRON job
					foreach ( $receipt_ids as $key => $order ) {
						unset( $queue['orders'][ $order['id'] ] );
					}

				} elseif ( ! is_wp_error( $response ) && in_array( $response['response']['code'], array( '200', '202' ) ) ) { // Update only the ones without error - retry the ones with error

					$failed_ids = array();
					$body 		= json_decode( $response['body'], 1 );
					foreach ( $body['errors'] as $error ) {
						$failed_ids[] = isset( $error['error']['reference'] ) ? $error['error']['reference'] : null;
					}

					// Set empty update time, so its not retried at next CRON job
					foreach ( $receipt_ids as $key => $order ) {
						if ( ! in_array( $order['id'], $failed_ids ) ) {
							update_post_meta( $order['id'], '_receiptful_last_update', time() );
							unset( $queue['orders'][ $order['id'] ] );
						}
					}

				}

			}

		}

		update_option( '_receiptful_queue', $queue );

	}


}

<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Receiptful_Front_End.
 *
 * Class to manage all front-end stuff.
 *
 * @class		Receiptful_Front_End
 * @since		1.1.4
 * @version		1.1.6
 * @author		Receiptful
 */
class Receiptful_Front_End {


	/**
	 * Constructor.
	 *
	 * @since 1.1.4
	 */
	public function __construct() {

		// Track pageviews
		add_action( 'wp_footer', array( $this, 'page_tracking' ) );

		// Receiptful search
		if ( get_option( 'receiptful_enable_search' ) == 'yes' ) {
			add_action( 'wp_footer', array( $this, 'receiptful_search' ) );
		}

		// Delete user token
		add_action( 'woocommerce_thankyou', array( $this, 'reset_user_token_cookie' ) );

	}


	/**
	 * Product page tracking.
	 *
	 * Track the product pageview for better product recommendations.
	 *
	 * @since 1.1.4
	 * @deprecated 1.1.6
	 */
	public function product_page_tracking() {
		return _deprecated_function( __METHOD__, '1.1.6', 'page_tracking' );
	}


	/**
	 * Page tracking.
	 *
	 * Track the pageviews for better product recommendations.
	 *
	 * @since 1.1.6
	 */
	public function page_tracking() {

		$public_user_key 	= Receiptful()->api->get_public_user_key();
		$product_id 		= 'product' == get_post_type( get_the_ID() ) ? get_the_ID() : null;
		$customer 			= is_user_logged_in() ? get_current_user_id() : '';
		$cart				= WC()->cart->get_cart();
		$product_ids		= array_values( wp_list_pluck( $cart, 'product_id' ) );

		// Bail if public user key is empty/invalid
		if ( ! $public_user_key ) {
			return false;
		}

		?><script type='text/javascript'>
			document.addEventListener('DOMContentLoaded', function(event) {
				if ( typeof Receiptful !== 'undefined' ) {
					Receiptful.init({
						user: '<?php echo esc_js( $public_user_key ); ?>',
						product: '<?php echo esc_js( $product_id ); ?>',
						cart: '<?php echo esc_js( implode( ',', $product_ids ) ); ?>',
						customer: '<?php echo esc_js( $customer ); ?>',
						recommend: <?php echo 'yes' == get_option( 'receiptful_enable_recommendations', false ) ? '1' : '0'; ?>,
						feedback: <?php echo 'yes' == get_option( 'receiptful_enable_feedback_widgets', false ) ? '1' : '0'; ?>
					});
				}
			});
		</script><?php

	}


	/**
	 * Search.
	 *
	 * Initialize the Receiptful search feature.
	 *
	 * @since 1.2.3
	 */
	public function receiptful_search() {

		$public_user_key = Receiptful()->api->get_public_user_key();

		// Bail if public user key is empty/invalid
		if ( ! $public_user_key ) {
			return false;
		}

		$search_field_selector = apply_filters( 'receiptful_search_selector', 'input[name=s]' );

		?><script type='text/javascript'>
			document.addEventListener('DOMContentLoaded', function(event) {
				if ( typeof ReceiptfulSearch !== 'undefined' ) {
					ReceiptfulSearch.init({
						user: '<?php echo esc_js( $public_user_key ); ?>',
						searchFieldSelector: '<?php echo esc_js( $search_field_selector ); ?>'
					});
				}
			});
		</script><?php

	}


	/**
	 * Delete user token.
	 *
	 * Delete the receiptful user token cookie after checkout. When deleted
	 * it will automatically re-generate a new one to track the new purchase flow.
	 *
	 * @since 1.1.6
	 */
	public function reset_user_token_cookie( $order_id ) {

		?><script type='text/javascript'>
			document.addEventListener('DOMContentLoaded', function(event) {
				if ( typeof Receiptful !== 'undefined' ) {
					Receiptful.docCookies.removeItem('receiptful-token', '/');
				}
			});
		</script><?php

	}


}

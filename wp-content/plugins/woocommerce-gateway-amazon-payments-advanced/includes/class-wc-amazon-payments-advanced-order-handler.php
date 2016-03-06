<?php
/**
 * Handle admin orders interface + status transitions
 */
class WC_Amazon_Payments_Advanced_Order_Handler {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'meta_box' ) );
		add_action( 'wp_ajax_amazon_order_action', array( $this, 'order_actions' ) );
	}

	/**
	 * Perform order actions for amazon
	 */
	public function order_actions() {
		check_ajax_referer( 'amazon_order_action', 'security' );

		$order_id = absint( $_POST['order_id'] );
		$id       = isset( $_POST['amazon_id'] ) ? woocommerce_clean( $_POST['amazon_id'] ) : '';
		$action   = sanitize_title( $_POST['amazon_action'] );

		switch ( $action ) {
			case 'refresh' :
				$this->clear_stored_states( $order_id );
			break;
			case 'authorize' :
				// Delete old
				delete_post_meta( $order_id, 'amazon_authorization_id' );
				delete_post_meta( $order_id, 'amazon_capture_id' );

				$this->authorize_payment( $order_id, $id, false );
				$this->clear_stored_states( $order_id );
			break;
			case 'authorize_capture' :
				// Delete old
				delete_post_meta( $order_id, 'amazon_authorization_id' );
				delete_post_meta( $order_id, 'amazon_capture_id' );

				$this->authorize_payment( $order_id, $id, true );
				$this->clear_stored_states( $order_id );
			break;
			case 'close_authorization' :
				$this->close_authorization( $order_id, $id );
				$this->clear_stored_states( $order_id );
			break;
			case 'capture' :
				$this->capture_payment( $order_id, $id );
				$this->clear_stored_states( $order_id );
			break;
			case 'refund' :
				$amazon_refund_amount = floatval( woocommerce_clean( $_POST['amazon_refund_amount'] ) );
				$amazon_refund_note   = woocommerce_clean( $_POST['amazon_refund_note'] );

				$this->refund_payment( $order_id, $id, $amazon_refund_amount, $amazon_refund_note );
				$this->clear_stored_states( $order_id );
			break;
		}

		die();
	}

	/**
	 * Wipe states so the value is refreshed
	 */
	public function clear_stored_states( $order_id ) {
		delete_post_meta( $order_id, 'amazon_reference_state' );
		delete_post_meta( $order_id, 'amazon_capture_state' );
		delete_post_meta( $order_id, 'amazon_authorization_state' );
	}

	/**
	 * Get auth state from amazon API
	 * @param  string $id
	 * @return string or false on failure
	 */
	public function get_reference_state( $order_id, $id ) {
		if ( $state = get_post_meta( $order_id, 'amazon_reference_state', true ) )
			return $state;

		$amazon = new WC_Gateway_Amazon_Payments_Advanced();

		$response = $amazon->api_request( array(
			'Action'                 => 'GetOrderReferenceDetails',
			'AmazonOrderReferenceId' => $id,
		) );

		if ( is_wp_error( $response ) || isset( $response['Error']['Message'] ) )
			return false;

		$state = $response['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['OrderReferenceStatus']['State'];

		update_post_meta( $order_id, 'amazon_reference_state', $state );

		return $state;
	}

	/**
	 * Get auth state from amazon API
	 * @param  string $id
	 * @return string or false on failure
	 */
	public function get_authorization_state( $order_id, $id ) {
		if ( $state = get_post_meta( $order_id, 'amazon_authorization_state', true ) )
			return $state;

		$amazon = new WC_Gateway_Amazon_Payments_Advanced();

		$response = $amazon->api_request( array(
			'Action'                => 'GetAuthorizationDetails',
			'AmazonAuthorizationId' => $id,
		) );

		if ( is_wp_error( $response ) || isset( $response['Error']['Message'] ) )
			return false;

		$state = $response['GetAuthorizationDetailsResult']['AuthorizationDetails']['AuthorizationStatus']['State'];

		update_post_meta( $order_id, 'amazon_authorization_state', $state );

		return $state;
	}

	/**
	 * Get capture state from amazon API
	 * @param  string $id
	 * @return string or false on failure
	 */
	public function get_capture_state( $order_id, $id ) {
		if ( $state = get_post_meta( $order_id, 'amazon_capture_state', true ) )
			return $state;

		$amazon = new WC_Gateway_Amazon_Payments_Advanced();

		$response = $amazon->api_request( array(
			'Action'          => 'GetCaptureDetails',
			'AmazonCaptureId' => $id,
		) );

		if ( is_wp_error( $response ) || isset( $response['Error']['Message'] ) )
			return false;

		$state = $response['GetCaptureDetailsResult']['CaptureDetails']['CaptureStatus']['State'];

		update_post_meta( $order_id, 'amazon_capture_state', $state );

		return $state;
	}

	/**
	 * meta_box function.
	 *
	 * @access public
	 * @return void
	 */
	function meta_box() {
		global $post, $wpdb;

		$order_id = absint( $post->ID );
		$order    = new WC_Order( $order_id );

		if ( $order->payment_method == 'amazon_payments_advanced' ) {
			add_meta_box( 'woocommerce-amazon-payments-advanced', __( 'Amazon Payments Advanced', 'woocommerce-gateway-amazon-payments-advanced' ), array( $this, 'authorization_box' ), 'shop_order', 'side' );
		}
	}

	/**
	 * pre_auth_box function.
	 *
	 * @access public
	 * @return void
	 */
	function authorization_box() {
		global $post, $wpdb, $theorder, $woocommerce;

		$actions  = array();
		$order_id = absint( $post->ID );

		if ( ! is_object( $theorder ) )
			$theorder = new WC_Order( $order_id );

		// Get ids
		$amazon_authorization_id = get_post_meta( $order_id, 'amazon_authorization_id', true );
		$amazon_reference_id     = get_post_meta( $order_id, 'amazon_reference_id', true );
		$amazon_capture_id       = get_post_meta( $order_id, 'amazon_capture_id', true );
		$amazon_refund_ids       = get_post_meta( $order_id, 'amazon_refund_id', false );

		if ( $amazon_capture_id ) {

			$amazon_capture_state = $this->get_capture_state( $order_id, $amazon_capture_id );

			switch ( $amazon_capture_state ) {
				case 'Pending' :

					echo wpautop( sprintf( __( 'Capture Reference %s is <strong>%s</strong>.', 'woocommerce-gateway-amazon-payments-advanced' ), $amazon_capture_id, $amazon_capture_state ) . ' <a href="#" data-action="refresh" class="refresh">' . __( 'Refresh', 'woocommerce-gateway-amazon-payments-advanced' ) . '</a>' );

					// Admin will need to re-check this, so clear the stored value
					$this->clear_stored_states( $order_id );
				break;
				case 'Declined' :

					echo wpautop( __( 'The capture was declined.', 'woocommerce-gateway-amazon-payments-advanced' ) );

					$actions['authorize'] = array(
						'id' => $amazon_reference_id,
						'button' => __( 'Re-authorize?', 'woocommerce-gateway-amazon-payments-advanced' )
					);

				break;
				case 'Completed' :

					echo wpautop( sprintf( __( 'Capture Reference %s is <strong>%s</strong>.', 'woocommerce-gateway-amazon-payments-advanced' ), $amazon_capture_id, $amazon_capture_state ) . ' <a href="#" class="toggle_refund">' . __( 'Make a refund?', 'woocommerce-gateway-amazon-payments-advanced' ) . '</a>' );

					// Refund form
					?>
					<p class="refund_form" style="display:none">
						<input type="number" step="any" style="width:100%" class="amazon_refund_amount" value="<?php echo $theorder->get_total(); ?>" />
						<input type="text" style="width:100%" class="amazon_refund_note" placeholder="<?php _e( 'Add a note about this refund', 'woocommerce-gateway-amazon-payments-advanced' ); ?>" /><br/>
						<a href="#" class="button" data-action="refund" data-id="<?php echo $amazon_capture_id; ?>"><?php _e( 'Refund', 'woocommerce-gateway-amazon-payments-advanced' ); ?></a>
					</form>
					<?php

				break;
				case 'Closed' :

					echo wpautop( sprintf( __( 'Capture Reference %s is <strong>%s</strong>.', 'woocommerce-gateway-amazon-payments-advanced' ), $amazon_capture_id, $amazon_capture_state ) );

				break;
			}

			// Display refunds
			if ( $amazon_refund_ids ) {
				$amazon = new WC_Gateway_Amazon_Payments_Advanced();

				$refunds = (array) get_post_meta( $order_id, 'amazon_refunds', true );

				foreach ( $amazon_refund_ids as $amazon_refund_id ) {

					if ( isset( $refunds[ $amazon_refund_id ] ) ) {
						echo wpautop( sprintf( __( 'Refund %s of %s is <strong>%s</strong> (%s).', 'woocommerce-gateway-amazon-payments-advanced' ), $amazon_refund_id, woocommerce_price( $refunds[ $amazon_refund_id ]['amount'] ), $refunds[ $amazon_refund_id ]['state'], $refunds[ $amazon_refund_id ]['note'] ) );
					} else {

						$response = $amazon->api_request( array(
							'Action'         => 'GetRefundDetails',
							'AmazonRefundId' => $amazon_refund_id,
						) );

						if ( ! is_wp_error( $response ) && ! isset( $response['Error']['Message'] ) ) {

							$note   = $response['GetRefundDetailsResult']['RefundDetails']['SellerRefundNote'];
							$state  = $response['GetRefundDetailsResult']['RefundDetails']['RefundStatus']['State'];
							$amount = $response['GetRefundDetailsResult']['RefundDetails']['RefundAmount']['Amount'];

							echo wpautop( sprintf( __( 'Refund %s of %s is <strong>%s</strong> (%s).', 'woocommerce-gateway-amazon-payments-advanced' ), $amazon_refund_id, woocommerce_price( $amount ), $state, $note ) );

							if ( $state == 'Completed' ) {
								$refunds[ $amazon_refund_id ] = array(
									'state'  => $state,
									'amount' => $amount,
									'note'   => $note
								);
							}
						}

					}
				}

				update_post_meta( $order_id, 'amazon_refunds', $refunds );
			}
		}

		elseif ( $amazon_authorization_id ) {

			$amazon_authorization_state = $this->get_authorization_state( $order_id, $amazon_authorization_id );

			echo wpautop( sprintf( __( 'Auth Reference %s is <strong>%s</strong>.', 'woocommerce-gateway-amazon-payments-advanced' ), $amazon_reference_id, $amazon_authorization_state ) . ' <a href="#" data-action="refresh" class="refresh">' . __( 'Refresh', 'woocommerce-gateway-amazon-payments-advanced' ) . '</a>' );

			switch ( $amazon_authorization_state ) {
				case 'Open' :

					$actions['capture'] = array(
						'id' => $amazon_authorization_id,
						'button' => __( 'Capture funds', 'woocommerce-gateway-amazon-payments-advanced' )
					);

					$actions['close_authorization'] = array(
						'id' => $amazon_authorization_id,
						'button' => __( 'Close Authorization', 'woocommerce-gateway-amazon-payments-advanced' )
					);

				break;
				case 'Pending' :

					echo wpautop( __( 'You cannot capture funds whilst the authorization is pending. Try again later.', 'woocommerce-gateway-amazon-payments-advanced' ) );

					// Admin will need to re-check this, so clear the stored value
					$this->clear_stored_states( $order_id );

				break;
				case 'Closed' :
				case 'Declined' :
					$actions['authorize'] = array(
						'id' => $amazon_reference_id,
						'button' => __( 'Authorize again', 'woocommerce-gateway-amazon-payments-advanced' )
					);
				break;
			}
		}

		elseif ( $amazon_reference_id ) {

			$amazon_reference_state = $this->get_reference_state( $order_id, $amazon_reference_id );

			echo wpautop( sprintf( __( 'Order Reference %s is <strong>%s</strong>.', 'woocommerce-gateway-amazon-payments-advanced' ), $amazon_reference_id, $amazon_reference_state ) . ' <a href="#" data-action="refresh" class="refresh">' . __( 'Refresh', 'woocommerce-gateway-amazon-payments-advanced' ) . '</a>' );

			switch ( $amazon_reference_state ) {
				case 'Open' :

					$actions['authorize'] = array(
						'id' => $amazon_reference_id,
						'button' => __( 'Authorize', 'woocommerce-gateway-amazon-payments-advanced' )
					);

					$actions['authorize_capture'] = array(
						'id' => $amazon_reference_id,
						'button' => __( 'Authorize &amp; Capture', 'woocommerce-gateway-amazon-payments-advanced' )
					);

				break;
				case 'Suspended' :

					echo wpautop( __( 'The reference has been suspended. Another form of payment is required.', 'woocommerce-gateway-amazon-payments-advanced' ) );

				break;
				case 'Canceled' :
				case 'Suspended' :

					echo wpautop( __( 'The reference has been cancelled/closed. No authorizations can be made.', 'woocommerce-gateway-amazon-payments-advanced' ) );

				break;
			}
		}

		if ( ! empty( $actions ) ) {

			echo '<p class="buttons">';

			foreach ( $actions as $action_name => $action ) {
				echo '<a href="#" class="button" data-action="' . $action_name . '" data-id="' . $action['id'] . '">' . $action['button'] . '</a> ';
			}

			echo '</p>';

		}

		$js = "

			jQuery('#woocommerce-amazon-payments-advanced').on( 'click', 'a.button, a.refresh', function(){

				jQuery('#woocommerce-amazon-payments-advanced').block({ message: null, overlayCSS: { background: '#fff url(" . $woocommerce->plugin_url() . "/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

				var data = {
					action: 		'amazon_order_action',
					security: 		'" . wp_create_nonce( "amazon_order_action" ) . "',
					order_id: 		'" . $order_id . "',
					amazon_action: 	jQuery(this).data('action'),
					amazon_id: 		jQuery(this).data('id'),
					amazon_refund_amount: jQuery('.amazon_refund_amount').val(),
					amazon_refund_note: jQuery('.amazon_refund_note').val(),
				};

				// Ajax action
				jQuery.ajax({
					url: '" . admin_url( 'admin-ajax.php' ) . "',
					data: data,
					type: 'POST',
					success: function( result ) {
						location.reload();
					}
				});

				return false;
			});

			jQuery('#woocommerce-amazon-payments-advanced').on( 'click', 'a.toggle_refund', function(){
				jQuery('.refund_form').slideToggle();
				return false;
			});

		";

		if ( function_exists( 'wc_enqueue_js' ) ) {
			wc_enqueue_js( $js );
		} else {
			$woocommerce->add_inline_js( $js );
		}
	}

    /**
     * Authorize payment
     */
    public function authorize_payment( $order_id, $amazon_reference_id, $capture_now = false ) {
		$order = new WC_Order( $order_id );

		if ( $order->payment_method == 'amazon_payments_advanced' ) {

			$amazon = new WC_Gateway_Amazon_Payments_Advanced();

			$response = $amazon->api_request( array(
				'Action'                           => 'Authorize',
				'AmazonOrderReferenceId'           => $amazon_reference_id,
				'AuthorizationReferenceId'         => $order->id . '-' . current_time( 'timestamp', true ),
				'AuthorizationAmount.Amount'       => $order->get_total(),
				'AuthorizationAmount.CurrencyCode' => strtoupper( get_woocommerce_currency() ),
				'CaptureNow'                       => $capture_now,
				'TransactionTimeout'               => 0
			) );

			if ( is_wp_error( $response ) ) {

				$order->add_order_note( __( 'Unable to authorize funds with amazon:', 'woocommerce-gateway-amazon-payments-advanced' ) . ' ' . $response->get_error_message() );

				return false;

			} elseif ( isset( $response['Error']['Message'] ) ) {

				$order->add_order_note( $response['Error']['Message'] );

				return false;

			} else {

				if ( isset( $response['AuthorizeResult']['AuthorizationDetails']['AmazonAuthorizationId'] ) ) {
					$auth_id = $response['AuthorizeResult']['AuthorizationDetails']['AmazonAuthorizationId'];
				} else {
					return false;
				}

				if ( isset( $response['AuthorizeResult']['AuthorizationDetails']['AuthorizationStatus']['State'] ) ) {
					$state = strtolower( $response['AuthorizeResult']['AuthorizationDetails']['AuthorizationStatus']['State'] );
				} else {
					$state = 'pending';
				}

				update_post_meta( $order_id, 'amazon_authorization_id', $auth_id );

				if ( 'declined' == $state ) {
					// Payment was not authorized
					return false;
				}

				if ( $capture_now ) {
					update_post_meta( $order_id, 'amazon_capture_id', str_replace( '-A', '-C', $auth_id ) );

					$order->add_order_note( sprintf( __( 'Captured (Auth ID: %s)', 'woocommerce-gateway-amazon-payments-advanced' ), str_replace( '-A', '-C', $auth_id ) ) );
				} else {
					$order->add_order_note( sprintf( __( 'Authorized (Auth ID: %s)', 'woocommerce-gateway-amazon-payments-advanced' ), $auth_id ) );
				}

				return true;
			}
		}

		return false;
    }

    /**
     * Close auth
     * @param  int $order_id
     * @param  string $amazon_authorization_id
     */
    public function close_authorization( $order_id, $amazon_authorization_id ) {
		$order = new WC_Order( $order_id );

		if ( $order->payment_method == 'amazon_payments_advanced' ) {

			$amazon = new WC_Gateway_Amazon_Payments_Advanced();

			$response = $amazon->api_request( array(
				'Action'                => 'CloseAuthorization',
				'AmazonAuthorizationId' => $amazon_authorization_id
			) );

			if ( is_wp_error( $response ) ) {

				// Don't add a note
				//
			} elseif ( isset( $response['Error']['Message'] ) ) {

				$order->add_order_note( $response['Error']['Message'] );

			} else {

				delete_post_meta( $order_id, 'amazon_authorization_id' );

				$order->add_order_note( sprintf( __( 'Authorization closed (Auth ID: %s)', 'woocommerce-gateway-amazon-payments-advanced' ), $amazon_authorization_id ) );

			}
		}
    }

    /**
     * Capture payment
     *
     * @param  int $order_id
     */
    public function capture_payment( $order_id, $amazon_authorization_id ) {
		$order = new WC_Order( $order_id );

		if ( $order->payment_method == 'amazon_payments_advanced' ) {

			$amazon = new WC_Gateway_Amazon_Payments_Advanced();

			$response = $amazon->api_request( array(
				'Action'                     => 'Capture',
				'AmazonAuthorizationId'      => $amazon_authorization_id,
				'CaptureReferenceId'         => $order->id . '-' . current_time( 'timestamp', true ),
				'CaptureAmount.Amount'       => $order->get_total(),
				'CaptureAmount.CurrencyCode' => strtoupper( get_woocommerce_currency() )
			) );

			if ( is_wp_error( $response ) ) {

				$order->add_order_note( __( 'Unable to authorize funds with amazon:', 'woocommerce-gateway-amazon-payments-advanced' ) . ' ' . $response->get_error_message() );

			} elseif ( isset( $response['Error']['Message'] ) ) {

				$order->add_order_note( $response['Error']['Message'] );

			} else {
				$capture_id = $response['CaptureResult']['CaptureDetails']['AmazonCaptureId'];

				$order->add_order_note( sprintf( __( 'Capture Attempted (Capture ID: %s)', 'woocommerce-gateway-amazon-payments-advanced' ), $capture_id ) );

				update_post_meta( $order_id, 'amazon_capture_id', $capture_id );
			}
		}
    }

    /**
     * Refund a payment
     * @param  int $order_id
     * @param  string $capture_id
     * @param  float $amount
     */
    public function refund_payment( $order_id, $capture_id, $amount, $note ) {
    	global $woocommerce;
    	
		$order = new WC_Order( $order_id );

		if ( $order->payment_method == 'amazon_payments_advanced' ) {

			if ( 'US' == $woocommerce->countries->get_base_country() && $amount > $order->get_total() ) {
				$order->add_order_note( __( 'Unable to refund funds via amazon:', 'woocommerce-gateway-amazon-payments-advanced' ) . ' ' . __( 'Refund amount is greater than order total.', 'woocommerce-gateway-amazon-payments-advanced' ) );

				return;
			} elseif ( $amount > min( ( $order->get_total() * 1.15 ), ( $order->get_total() + 75 ) ) ) {
				$order->add_order_note( __( 'Unable to refund funds via amazon:', 'woocommerce-gateway-amazon-payments-advanced' ) . ' ' . __( 'Refund amount is greater than the max refund amount.', 'woocommerce-gateway-amazon-payments-advanced' ) );

				return;
			}

			$amazon = new WC_Gateway_Amazon_Payments_Advanced();

			$response = $amazon->api_request( array(
				'Action'                    => 'Refund',
				'AmazonCaptureId'           => $capture_id,
				'RefundReferenceId'         => $order->id . '-' . current_time( 'timestamp', true ),
				'RefundAmount.Amount'       => $amount,
				'RefundAmount.CurrencyCode' => strtoupper( get_woocommerce_currency() ),
				'SellerRefundNote'          => $note
			) );

			if ( is_wp_error( $response ) ) {

				$order->add_order_note( __( 'Unable to refund funds via amazon:', 'woocommerce-gateway-amazon-payments-advanced' ) . ' ' . $response->get_error_message() );

			} elseif ( isset( $response['Error']['Message'] ) ) {

				$order->add_order_note( $response['Error']['Message'] );

			} else {
				$refund_id = $response['RefundResult']['RefundDetails']['AmazonRefundId'];

				$order->add_order_note( sprintf( __( 'Refunded %s (%s)', 'woocommerce-gateway-amazon-payments-advanced' ), woocommerce_price( $amount ), $note ) );

				add_post_meta( $order_id, 'amazon_refund_id', $refund_id );
			}
		}
    }
}

$GLOBALS['wc_amazon_pa_order_handler'] = new WC_Amazon_Payments_Advanced_Order_Handler();
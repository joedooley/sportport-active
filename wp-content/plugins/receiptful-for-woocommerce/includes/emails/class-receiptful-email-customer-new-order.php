<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Receiptful_Email_Customer_New_Order' ) ) {

	/**
	 * Customer New Order Email
	 *
	 * Emails are sent to the customer when the order is placed and usual indicates that the order has been shipped.
	 *
	 * @class		Receiptful_Email_Customer_New_Order
	 * @version		1.0.0
	 * @package		Receiptful/Classes/Emails
	 * @author		Conversio
	 * @extends		WC_Email
	 */
	class Receiptful_Email_Customer_New_Order extends WC_Email {


		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			$this->id				= 'customer_new_order';
			$this->title			= __( 'Conversio New Order', 'receiptful-for-woocommerce' );
			$this->description		= __( 'Conversio will send a new order receipt when the order is placed.', 'receiptful-for-woocommerce' );

			// Triggers for this email
			add_action( 'receiptful_order_status_processing_notification', array( $this, 'trigger' ) );

			// Call parent constructor
			parent::__construct();

		}


		/**
		 * Email settings.
		 *
		 * Initialize email settings.
		 *
		 * @since 1.0.0
		 */
		public function init_form_fields() {

			$this->form_fields = array(
				'enabled' => array(
					'title'		=> __( 'Enable/Disable', 'receiptful-for-woocommerce' ),
					'type'		=> 'checkbox',
					'label'		=> __( 'Enable this email notification', 'receiptful-for-woocommerce' ),
					'default'	=> 'yes'
				),
			);

		}


		/**
		 * trigger function.
		 *
		 * This is the big function of Conversio. When this email is triggered
		 * it will send data (API call) to Conversio to send the actual receipt.
		 *
		 * @since 1.0.0
		 *
		 * @param	int|WC_Order	$order_id	ID or object of the order being processed.
		 * @return	array|WP_Error				WP_Error when the API call failed, otherwise the API response.
		 */
		public function trigger( $order_id ) {

			$order = wc_get_order( $order_id );

			// Bail if the order is invalid
			if ( ! $order || ! $order instanceof WC_Order ) {
				return false;
			}

			// Resend receipt when it already has a Conversio ID
			if ( '' != $receiptful_id = get_post_meta( $order_id, '_receiptful_receipt_id', true ) ) {
				$response = $this->resend_receipt( $order, $receiptful_id );
				return $response;
			}


			// Order items
			$items = $this->api_args_get_items( $order );

			// get all the subtotals that can include shipping, tax, discount
			$subtotals = $this->api_args_get_subtotals( $order );

			// Related products
			$related_products = $this->api_args_get_related_products( $items );

			// Order args
			$order_args = $this->api_args_get_order_args( $order, $items, $subtotals, $related_products );

			// API Response
			$response = Receiptful()->api->receipt( $order_args );

			// Handle response
			if ( is_wp_error( $response ) ) {

				$order->add_order_note( sprintf( __( 'Error sending customer receipt via Conversio. <br/> Error Message: %1$s. Receipt added to resend queue.', 'receiptful-for-woocommerce' ), implode( ', ', $response->get_error_messages() ) ) );

				// queue the message for sending via cron
				$resend_queue	= get_option( '_receiptful_resend_queue' );
				$resend_queue[ $order_id ]	= $order_id;
				update_option( '_receiptful_resend_queue', $resend_queue );

			} elseif ( $response['response']['code'] == '201' ) {

				$order->add_order_note( 'Customer receipt sent via Conversio.' );
				$body = json_decode( $response ['body'], true);

				add_post_meta( $order_id, '_receiptful_web_link', $body['_meta']['links'] );
				add_post_meta( $order_id, '_receiptful_receipt_id', $body['_id'] );

				$upsell = $body['upsell'];
				if ( isset( $upsell['couponCode'] ) ) {
					do_action( 'receiptful_add_upsell', $upsell, $order_id );
				}

			} elseif ( in_array( $response['response']['code'], array( '401', '500', '503' ) ) ) {

				$order->add_order_note( sprintf( __( 'Error sending customer receipt via Conversio. <br/> Error Code: %1$s <br/> Error Message: %2$s. Receipt added to resend queue.', 'receiptful-for-woocommerce' ), $response['response']['code'], $response['response']['message'] ) );

				// queue the message for sending via cron
				$resend_queue	= get_option( '_receiptful_resend_queue' );
				$resend_queue[ $order_id ]	= $order_id;
				update_option( '_receiptful_resend_queue', $resend_queue );

			} else {

				$order->add_order_note( sprintf( __( 'Error sending customer receipt via Conversio. <br/> Error Code: %1$s <br/> Error Message: %2$s.', 'receiptful-for-woocommerce' ), $response['response']['code'], $response['response']['message'] ) );

			}

			// Set time when receipt was send
			update_post_meta( $order_id, '_receiptful_last_update', time() );

			do_action( 'receiptful_after_mail_send', $order_id, $response );

			return $response;

		}


		/**
		 * Resend receipt.
		 *
		 * Resend the order receipt based on the receipt ID.
		 *
		 * @since 1.1.0
		 *
		 * @param	int|WC_Order	$order	ID or object of the order to get the subtotals for.
		 * @return	array|WP_Error			WP_Error when the API call failed, otherwise the API response.
		 */
		public function resend_receipt( $order, $receiptful_id ) {

			$order		= wc_get_order( $order );
			$order_id   = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
			$response	= Receiptful()->api->resend_receipt( $receiptful_id );

			if ( is_wp_error( $response ) ) {

				$order->add_order_note( sprintf( __( 'Error resending customer receipt via Conversio. <br/> Error Message: %1$s. <br/> Receipt added to resend queue.', 'receiptful-for-woocommerce' ), implode( ', ', $response->get_error_messages() ) ) );

				// queue the message for sending via cron
				$resend_queue	= get_option( '_receiptful_resend_queue' );
				$resend_queue[ $order_id ]	= $order_id;
				update_option( '_receiptful_resend_queue', $resend_queue );

			} elseif ( $response['response']['code'] == '200' ) {

				$order->add_order_note( 'Customer receipt resent via Conversio.' );

			} elseif ( in_array( $response['response']['code'], array( '401', '500', '503' ) ) ) {

				$order->add_order_note( sprintf( __( 'Error resending customer receipt via Conversio. <br/> Error Code: %1$s <br/> Error Message: %2$s. <br/> Receipt added to resend queue.', 'receiptful-for-woocommerce' ), $response['response']['code'], $response['response']['message'] ) );

				// queue the message for sending via cron
				$resend_queue	= get_option( '_receiptful_resend_queue' );
				$resend_queue[ $order_id ]	= $order_id;
				update_option( '_receiptful_resend_queue', $resend_queue );

			} else {

				$order->add_order_note( sprintf( __( 'Error resending customer receipt via Conversio. <br/> Error Code: %1$s <br/> Error Message: %2$s.', 'receiptful-for-woocommerce' ), $response['response']['code'], $response['response']['message'] ) );

			}

			do_action( 'receiptful_after_mail_resend', $order_id, $response );

			return $response;

		}


		/**
		 * Get Items.
		 *
		 * Get the order items required for the API call.
		 *
		 * @since 1.1.0
		 *
		 * @param	int|WC_Order	$order	Order object or ID to get the subtotals for.
		 * @return	array					List of items that are in the order.
		 */
		public function api_args_get_items( $order ) {

			global $wpdb;

			$order	= wc_get_order( $order );
			$order_id   = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
			$items	= array();

			// Setting order item meta
			foreach ( $order->get_items() as $key => $item ) {

				// Get Item Meta Data
				$meta_data	= array();
				$metadata	= method_exists( $item, 'get_meta_data' ) ? $item->get_meta_data() : $order->has_meta( $key );

				foreach ( $metadata as $meta ) {

					$key = is_array( $meta ) ? $meta['meta_key'] : ( is_object( $meta ) ? $meta->key  : '' );
					$value = is_array( $meta ) ? $meta['meta_value'] : ( is_object( $meta ) ? $meta->value : '' );

					// Skip hidden core fields - Double filter to keep WC and integration compatibility
					if ( in_array( $key, apply_filters( 'receiptful_hidden_order_itemmeta', apply_filters( 'woocommerce_hidden_order_itemmeta', array(
						'_qty',
						'_tax_class',
						'_product_id',
						'_variation_id',
						'_line_subtotal',
						'_line_subtotal_tax',
						'_line_total',
						'_line_tax',
					) ) ) ) ) {
						continue;
					}

					// Skip serialised meta
					if ( is_serialized( $value ) ) {
						continue;
					}

					// Get attribute data
					if ( taxonomy_exists( $key ) ) {
						$term			= get_term_by( 'slug', $value, $key );
						$attribute_name	= str_replace( 'pa_', '', wc_clean( $key ) );
						$attribute		= $wpdb->get_var(
							$wpdb->prepare( "
									SELECT attribute_label
									FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
									WHERE attribute_name = %s;
								",
								$attribute_name
							)
						);

						$key	= ( ! is_wp_error( $attribute ) && $attribute ) ? $attribute : $attribute_name;
						$value	= ( isset( $term->name ) ) ? $term->name : $value;
					}

					$meta_data[] = array(
						'key'	=> wp_kses_post( urldecode( $key ) ),
						'value'	=> wp_kses_post( urldecode( $value ) ),
					);

				}

				// Product notes
				if ( $purchase_note = get_post_meta( $item['product_id'], '_purchase_note', true ) ) {
					$meta_data[] = array(
						'key'	=> __( 'Note', 'woocommerce' ),
						'value'	=> wp_kses_post( $purchase_note ),
					);
				}

				// Product image
				$product_id = $item['variation_id'] > 0 ? $item['variation_id'] : $item['product_id'];
				if ( has_post_thumbnail( $product_id ) ) {
					list( $img_src, $width, $height ) = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), array( 90, 900 ) );
				} elseif ( ( $parent_id = wp_get_post_parent_id( $product_id ) ) && has_post_thumbnail( $parent_id ) ) {
					list( $img_src, $width, $height ) = wp_get_attachment_image_src( get_post_thumbnail_id( $parent_id ), array( 90, 900 ) );
				} else {
					$img_src = wc_placeholder_img_src();
				}

				$tax_display    = get_option( 'woocommerce_tax_display_cart' );
				$inc_tax 		= 'incl' == $tax_display ? true : false;
				$product_amount = $order->get_line_subtotal( $item, $inc_tax, false ) / $item['qty'];

				$items[] = array(
					'reference'		=> $item['product_id'],
					'description'	=> $item['name'],
					'quantity'		=> $item['qty'],
					'amount'		=> number_format( (float) $product_amount, 2, '.', '' ),
					'downloadUrls'	=> $this->maybe_get_download_urls( $item, $order_id ),
					'metas'			=> $meta_data,
					'image'			=> $img_src,
				);

			}

			return apply_filters( 'receiptful_api_args_items', $items, $order );

		}


		/**
		 * Get subtotals.
		 *
		 * Get the subtotals required for the API call.
		 *
		 * @since 1.1.0
		 *
		 * @param	int|WC_Order	$order	Order object or ID to get the subtotals for.
		 * @return	array					List of subtotals to display on the Receipt.
		 */
		public function api_args_get_subtotals( $order ) {

			$order			= wc_get_order( $order );
			$subtotals		= array();
			$tax_display	= get_option( 'woocommerce_tax_display_cart' );;

			// Subtotal
			$subtotal 	= 0;
			$inc_tax	= 'incl' == $tax_display ? true : false;
			foreach ( $order->get_items() as $key => $item ) {
				$subtotal += $order->get_line_subtotal( $item, $inc_tax, true );
			}
			$subtotals[] = array( 'description' => __( 'Subtotal', 'woocommerce' ), 'amount' => number_format( (float) $subtotal, 2, '.', '' ) );

			// Discount
			if ( $order->get_total_discount() > 0 ) {
				$subtotals[] = array( 'description' => __( 'Discount', 'woocommerce' ), 'amount' => '-' . number_format( (float) $order->get_total_discount( false ), 2, '.', '' ) );
			}

			// Shipping
			$order_shipping = method_exists( $order, 'get_shipping_total' ) ? $order->get_shipping_total() : $order->order_shipping;
			if ( $order_shipping > 0 ) {
				$shipping_total = 'excl' == $tax_display ? $order_shipping : ( $order_shipping + $order->get_shipping_tax() );
				$subtotals[] = array( 'description' => $order->get_shipping_method(), 'amount' => number_format( (float) $shipping_total, 2, '.', '' ) );
			}

			// Fees
			if ( $fees = $order->get_fees() ) {

				foreach ( $fees as $id => $fee ) {

					if ( apply_filters( 'woocommerce_get_order_item_totals_excl_free_fees', $fee['line_total'] + $fee['line_tax'] == 0, $id ) ) {
						continue;
					}

					if ( 'excl' == $tax_display ) {
						$subtotals[] = array( 'description' => $fee['name'], 'amount' => number_format( (float) $fee['line_total'], 2, '.', '' ) );
					} else {
						$subtotals[] = array( 'description' => $fee['name'], 'amount' => number_format( (float) $fee['line_total'] + $fee['line_tax'], 2, '.', '' ) );
					}
				}

			}

			// Tax for tax exclusive prices
			if ( 'excl' == $tax_display ) {

				if ( get_option( 'woocommerce_tax_total_display' ) == 'itemized' ) {
					foreach ( $order->get_tax_totals() as $code => $tax ) {
						$subtotals[] = array( 'description' => $tax->label, 'amount' => number_format( (float) $tax->amount, 2, '.', '' ) );
					}
				} else {
					$subtotals[] = array( 'description' => WC()->countries->tax_or_vat(), 'amount' => number_format( (float) $order->get_total_tax(), 2, '.', '' ) );
				}

			}

			return apply_filters( 'receiptful_api_args_subtotals', $subtotals, $order );

		}


		/**
		 * Get related products.
		 *
		 * Get the related products based on the order items.
		 *
		 * @since 1.1.0
		 *
		 * @param	array $items	List of items in the order, list contains data equal to $this->api_args_get_items().
		 * @return	array			List of related products data.
		 */
		public function api_args_get_related_products( $items ) {

			$order_item				= reset( $items );
			$first_item_id			= $order_item['reference'];
			$product				= wc_get_product( $first_item_id );
			$related_products		= array();
			$related_product_ids	= function_exists( 'wc_get_related_products' ) ? wc_get_related_products( $product->get_id(), 2 ) : ( $product ? $product->get_related( 2 ) : array() );

			// Fallback to random products when no related were found.
			if ( empty( $related_product_ids ) ) {
				$related_product_ids = wc_get_random_products( 2 );
			}

			if ( ! empty( $related_product_ids ) ) {
				foreach ( $related_product_ids as $related_id ) {

					$product		= wc_get_product( $related_id );
					$product_image	= wp_get_attachment_image_src( $product->get_image_id(), array( 450, 450 ) );
					$content		= method_exists( $product, 'get_description' ) ? $product->get_description() : $product->post->post_content;
					$content		= strip_tags( $content );
					$description	= strlen( $content ) <= 100 ? $content : substr( $content, 0, strrpos( $content, ' ', -( strlen( $content ) - 100 ) ) );

					$related_products[] = array(
						'title'			=> $product->get_title(),
						'actionUrl'		=> get_permalink( $product->get_id() ),
						'image'			=> $product_image[0],
						'description'	=> $description,
					);

				}
			}

			return apply_filters( 'receiptful_api_args_related_products', $related_products, $items );

		}


		/**
		 * Order args.
		 *
		 * Get the order args required for the API call.
		 *
		 * @since 1.1.0
		 *
		 * @param	int|WC_Order	    $order				Order object or ID to get the subtotals for.
		 * @param	array				$items				List of items to send to the API.
		 * @param	array				$subtotals			List of subtotals to send to the API.
		 * @param	array				$related_products	List of related products to send to the API.
		 * @return	array									Complete list of arguments to send to the API.
		 */
		public function api_args_get_order_args( $order, $items, $subtotals, $related_products ) {

			$order = wc_get_order( $order );
			$order_id   = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;

			// These values are added to the order at checkout if available.
			// If not recorded then empty string will be sent.
			$payment_method	= get_post_meta( $order_id, '_payment_method_title', true );
			$token			= get_post_meta( $order_id, '_receiptful_token', true );
			$order_date		= new DateTime( get_post_field( 'post_date', $order_id ), new DateTimeZone( wc_timezone_string() ) );

			$order_args = array(
				'date'			=> $order_date->format( 'c' ),
				'reference'		=> ltrim( $order->get_order_number(), _x( '#', 'hash before order number', 'receiptful-for-woocommerce' ) ),
				'currency'		=> method_exists( $order, 'get_currency' ) ? $order->get_currency(): $order->get_order_currency(),
				'amount'		=> number_format( (float) $order->get_total(), 2, '.', '' ),
				'to'			=> method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email,
				'from'			=> $this->get_from_address(),
				'payment'		=> array(
					'type'	=> $payment_method,
				),
				'items'			=> $items,
				'subtotals'		=> $subtotals,
				'upsell'		=> array( 'products' => $related_products ),
				'customerIp'	=> '',
				'billing'		=> array(
					'address'	=> array(
						'firstName'		=> method_exists( $order, 'get_billing_first_name' ) ? $order->get_billing_first_name() : $order->billing_first_name,
						'lastName'		=> method_exists( $order, 'get_billing_last_name' ) ? $order->get_billing_last_name() : $order->billing_last_name,
						'company'		=> method_exists( $order, 'get_billing_company' ) ? $order->get_billing_company() : $order->billing_company,
						'addressLine1'	=> method_exists( $order, 'get_billing_address_1' ) ? $order->get_billing_address_1() : $order->billing_address_1,
						'addressLine2'	=> method_exists( $order, 'get_billing_address_2' ) ? $order->get_billing_address_2() : $order->billing_address_2,
						'city'			=> method_exists( $order, 'get_billing_city' ) ? $order->get_billing_city() : $order->billing_city,
						'state'			=> method_exists( $order, 'get_billing_state' ) ? $order->get_billing_state() : $order->billing_state,
						'postcode'		=> method_exists( $order, 'get_billing_postcode' ) ? $order->get_billing_postcode() : $order->billing_postcode,
						'country'		=> method_exists( $order, 'get_billing_country' ) ? $order->get_billing_country() : $order->billing_country,
					),
					'phone'		=> method_exists( $order, 'get_billing_phone' ) ? $order->get_billing_phone() : $order->billing_phone,
					'email'		=> method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email
				),
				'shipping'		=> array(
					'firstName'		=> method_exists( $order, 'get_shipping_first_name' ) ? $order->get_shipping_first_name() : $order->shipping_first_name,
					'lastName'		=> method_exists( $order, 'get_shipping_last_name' ) ? $order->get_shipping_last_name() : $order->shipping_last_name,
					'company'		=> method_exists( $order, 'get_shipping_company' ) ? $order->get_shipping_company() : $order->shipping_company,
					'addressLine1'	=> method_exists( $order, 'get_shipping_address_1' ) ? $order->get_shipping_address_1() : $order->shipping_address_1,
					'addressLine2'	=> method_exists( $order, 'get_shipping_address_2' ) ? $order->get_shipping_address_2() : $order->shipping_address_2,
					'city'			=> method_exists( $order, 'get_shipping_city' ) ? $order->get_shipping_city() : $order->shipping_city,
					'state'			=> method_exists( $order, 'get_shipping_state' ) ? $order->get_shipping_state() : $order->shipping_state,
					'postcode'		=> method_exists( $order, 'get_shipping_postcode' ) ? $order->get_shipping_postcode() : $order->shipping_postcode,
					'country'		=> method_exists( $order, 'get_shipping_country' ) ? $order->get_shipping_country() : $order->shipping_country,
				),
				'notes'				=> method_exists( $order, 'get_customer_note' ) ? $order->get_customer_note() : $order->customer_note,
				'token'				=> $token,
				'coupons'			=> $order->get_used_coupons(),
			);

			// Amount notes
			$tax_display = get_option( 'woocommerce_tax_display_cart' );
			if ( wc_tax_enabled() && 'incl' == $tax_display ) {

				$tax_string_array = array();
				if ( 'itemized' == get_option( 'woocommerce_tax_total_display' ) ) {

					foreach ( $order->get_tax_totals() as $code => $tax ) {
						$tax_string_array[] = sprintf( '%s %s', number_format( (float) $tax->amount, 2, '.', '' ), $tax->label );
					}

				} else {
					$tax_string_array[] = sprintf( '%s %s', $order->get_total_tax(), WC()->countries->tax_or_vat() );
				}

				if ( ! empty( $tax_string_array ) ) {
					$order_args['amountNotes'] = str_replace( array( '(', ')' ), '', sprintf( __( '(Includes %s)', 'woocommerce' ), implode( ', ', $tax_string_array ) ) );
				}

			}

			return apply_filters( 'receiptful_api_args_order_args', $order_args, $order, $items, $subtotals, $related_products );

		}


		/**
		 * Download urls.
		 *
		 * Get the download url(s) for the products that are downloadable.
		 *
		 * @param	array|WC_Order_item_Product	$item		Item list param as returned from $order->get_items().
		 * @param	int							$order_id	Order ID to get the download url for.
		 * @return	array									List of download URLs based on a key / value structure.
		 */
		public function maybe_get_download_urls( $item, $order_id ) {

			$urls = null;

			if ( ! is_array( $item ) && ! is_object( $item ) ) {
				return null;
			}

			$product_id		= $item['variation_id'] > 0 ? $item['variation_id'] : $item['product_id'];
			$product 		= wc_get_product( $product_id );
			$order			= wc_get_order( $order_id );

			// Extra check to prevent trashed (non-existing) products from executing '$product->get_item_downloads()'.
			if ( ! $product ) {
				return null;
			}

			$download_ids = method_exists( $item, 'get_item_downloads' ) ? $item->get_item_downloads() : $order->get_item_downloads( $item );
			foreach (  $download_ids as $download ) {
				$urls[] = array( 'key' => sprintf( __( 'Download %s', 'receiptful-for-woocommerce' ), $download['name'] ), 'value' => $download['download_url'] );
			}

			$urls = apply_filters( 'receiptful_get_download_urls', $urls, $item, $order_id );

			return is_array( $urls ) ? $urls : null;

		}


	}


}

return new Receiptful_Email_Customer_New_Order();

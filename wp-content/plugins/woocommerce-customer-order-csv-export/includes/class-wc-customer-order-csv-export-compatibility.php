<?php
/**
 * WooCommerce Customer/Order CSV Export
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Customer/Order CSV Export to newer
 * versions in the future. If you wish to customize WooCommerce Customer/Order CSV Export for your
 * needs please refer to http://docs.woothemes.com/document/ordercustomer-csv-exporter/
 *
 * @package     WC-Customer-Order-CSV-Export/Generator
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2015, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customer/Order CSV Export Generator
 *
 * Converts customer/order data into CSV
 *
 * @since 3.0
 */
class WC_Customer_Order_CSV_Export_Compatibility {


	/** @var string order export format */
	private $order_format;

	/** @var string customer export format */
	private $customer_format;


	/**
	 * Check if using import or legacy export format and add filters as needed
	 *
	 * @since 3.0
	 * @return \WC_Customer_Order_CSV_Export_Compatibility
	 */
	public function __construct() {

		$this->order_format    = get_option( 'wc_customer_order_csv_export_order_format' );
		$this->customer_format = get_option( 'wc_customer_order_csv_export_customer_format' );

		if ( ! in_array( $this->order_format, array( 'default', 'default_one_row_per_item' ) ) ) {

			add_filter( 'wc_customer_order_csv_export_order_headers', array( $this, 'modify_order_headers' ), 0, 2 );
			add_filter( 'wc_customer_order_csv_export_order_row', array( $this, 'modify_order_row' ), 0, 2 );
		}

		if ( 'default' !== $this->customer_format ) {

			add_filter( 'wc_customer_order_csv_export_customer_headers', array( $this, 'modify_customer_headers' ), 0, 2 );
			add_filter( 'wc_customer_order_csv_export_customer_row', array( $this, 'modify_customer_row' ), 0, 2 );
		}
	}


	/**
	 * Modify the order export column headers to match the chosen format
	 *
	 * @since 3.0
	 * @param array $_ unused
	 * @param \WC_Customer_Order_CSV_Export_Generator the generator instance
	 * @return array modified column headers
	 */
	public function modify_order_headers( $_, $generator ) {

		if ( 'import' == $this->order_format ) {

			// CSV import format
			$headers = array(
				'order_id'               => 'order_id',
				'order_number_formatted' => 'order_number_formatted',
				'order_number'           => 'order_number',
				'order_date'             => 'date',
				'status'                 => 'status',
				'shipping_total'         => 'order_shipping',
				'shipping_tax_total'     => 'order_shipping_tax',
				'fee_total'              => 'order_fees',
				'fee_tax_total'          => 'order_fee_tax',
				'tax_total'              => 'order_tax',
				'cart_discount'          => 'cart_discount',
				'order_discount'         => 'order_discount',
				'order_total'            => 'order_total',
				'payment_method'         => 'payment_method',
				'shipping_method'        => 'shipping_method',
				'customer_id'            => 'customer_user',
				'billing_first_name'     => 'billing_first_name',
				'billing_last_name'      => 'billing_last_name',
				'billing_email'          => 'billing_email',
				'billing_phone'          => 'billing_phone',
				'billing_address_1'      => 'billing_address_1',
				'billing_address_2'      => 'billing_address_2',
				'billing_postcode'       => 'billing_postcode',
				'billing_city'           => 'billing_city',
				'billing_state'          => 'billing_state',
				'billing_country'        => 'billing_country',
				'billing_company'        => 'billing_company',
				'shipping_first_name'    => 'shipping_first_name',
				'shipping_last_name'     => 'shipping_last_name',
				'shipping_address_1'     => 'shipping_address_1',
				'shipping_address_2'     => 'shipping_address_2',
				'shipping_postcode'      => 'shipping_postcode',
				'shipping_city'          => 'shipping_city',
				'shipping_state'         => 'shipping_state',
				'shipping_country'       => 'shipping_country',
				'shipping_company'       => 'shipping_company',
				'customer_note'          => 'customer_note',
			);

			// line items
			for ( $i = 1; $i <= $this->get_max_line_items( $generator->ids ); $i++ ) {

				$headers[ "order_item_{$i}" ] = "order_item_{$i}";
			}

			$headers['download_permissions'] = 'download_permissions_granted';
			$headers['order_notes']          = 'order_notes';

			// shipping line items
			for ( $i = 1; $i <= $this->get_max_shipping_line_items( $generator->ids ); $i++ ) {

				$headers[ "shipping_method_{$i}" ] = "shipping_method_{$i}";
				$headers[ "shipping_cost_{$i}" ] = "shipping_cost_{$i}";
			}

		} else {

			// legacy format
			$headers = array(
				'order_id'            => __( 'Order ID', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'order_date'          => __( 'Date', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'status'              => __( 'Order Status', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'shipping_total'      => __( 'Shipping', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'shipping_tax_total'  => __( 'Shipping Tax', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'fee_total'           => __( 'Fees', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'fee_tax_total'       => __( 'Fee Tax', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'tax_total'           => __( 'Tax', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'cart_discount'       => __( 'Cart Discount', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'order_discount'      => __( 'Order Discount', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'order_total'         => __( 'Order Total', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'payment_method'      => __( 'Payment Method', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'shipping_method'     => __( 'Shipping Method', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_first_name'  => __( 'Billing First Name', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_last_name'   => __( 'Billing Last Name', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_email'       => __( 'Billing Email', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_phone'       => __( 'Billing Phone', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_address_1'   => __( 'Billing Address 1', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_address_2'   => __( 'Billing Address 2', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_postcode'    => __( 'Billing Post code', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_city'        => __( 'Billing City', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_state'       => __( 'Billing State', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_country'     => __( 'Billing Country', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_company'     => __( 'Billing Company', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'shipping_first_name' => __( 'Shipping First Name', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'shipping_last_name'  => __( 'Shipping Last Name', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'shipping_address_1'  => __( 'Shipping Address 1', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'shipping_address_2'  => __( 'Shipping Address 2', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'shipping_postcode'   => __( 'Shipping Post code', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'shipping_city'       => __( 'Shipping City', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'shipping_state'      => __( 'Shipping State', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'shipping_country'    => __( 'Shipping Country', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'shipping_company'    => __( 'Shipping Company', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'customer_note'       => __( 'Customer Note', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
			);

			// legacy - one row per line item
			if ( 'legacy_one_row_per_item' == $this->order_format ) {

				$headers['line_item_sku']       = __( 'Item SKU', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
				$headers['line_item_name']      = __( 'Item Name', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
				$headers['line_item_variation'] = __( 'Item Variation', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
				$headers['line_item_amount']    = __( 'Item Amount', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
				$headers['line_item_price']     = __( 'Row Price', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );

			} else {

				// legacy - one column for all line items
				$headers['order_items'] = __( 'Order Items', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
			}

			// additional legacy columns that must go after line item
			$headers['download_permissions'] = __( 'Download Permissions Granted', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
			$headers['order_notes']          = __( 'Order notes', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
			$headers['coupon_items']         = __( 'Coupons', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
		}

		return $headers;
	}


	/**
	 * Modify the order export row to match the chosen format
	 *
	 * @since 3.0
	 * @param array $order_data an array of order data for the given order
	 * @param WC_Order $order the WC_Order object
	 * @return array modified order data
	 */
	public function modify_order_row( $order_data, $order ) {

		if ( 'import' == $this->order_format ) {

			return $this->get_import_one_column_per_line_item( $order_data, $order );

		} elseif ( 'legacy_one_row_per_item' == $this->order_format ) {

			return $this->get_legacy_one_row_per_line_item( $order_data, $order );

		} else {

			return $this->get_legacy_single_column_line_item( $order_data, $order );
		}
	}


	/**
	 * Get the order data format for a single column per line item, compatible with the CSV Import Suite plugin
	 *
	 * @since 3.0
	 * @param array $order_data an array of order data for the given order
	 * @param WC_Order $order the WC_Order object
	 * @return array modified order data
	 */
	private function get_import_one_column_per_line_item( $order_data, WC_Order $order ) {

		$count = 1;

		// add line items
		foreach( $order->get_items() as $_ => $item ) {

			// sku/qty/price
			$product = $order->get_product_from_item( $item );

			if ( ! is_object( $product ) ) {
				$product = new WC_Product( 0 );
			}

			$sku = $product->get_sku();

			// note that product ID must be prefixed with `product_id:` so the importer can properly parse it vs. the SKU
			$product_id = $product->is_type( 'variation' ) ? $product->variation_id : $product->id;

			$line_item = array(
				$sku ? $sku : "product_id:{$product_id}",
				$item['qty'],
				$order->get_line_total( $item )
			);

			// Add item meta
			$item_meta = new WC_Order_Item_Meta( SV_WC_Plugin_Compatibility::is_wc_version_gte_2_4() ? $item : $item['item_meta'] );
			$formatted_meta = $item_meta->get_formatted();

			if ( ! empty( $formatted_meta ) ) {

				foreach ( $formatted_meta as $meta_key => $meta ) {

					// remove newlines
					$label = str_replace( array( "\r", "\r\n", "\n" ), '', $meta['label'] );
					$value = str_replace( array( "\r", "\r\n", "\n" ), '', $meta['value'] );

					// escape reserved chars (:;|)
					$label = str_replace( array( ': ', ':', ';', '|' ), array( '\: ', '\:', '\;', '\|' ), $meta['label'] );
					$value = str_replace( array( ': ', ':', ';', '|' ), array( '\: ', '\:', '\;', '\|' ), $meta['value'] );

					$line_item[] = wp_kses_post( $label . ': ' . $value );
				}
			}

			$order_data[ "order_item_{$count}" ] = implode( '|', $line_item );

			$count++;
		}

		$count = 1;

		foreach ( $order->get_items( 'shipping' ) as $_ => $shipping_item ) {

			$order_data[ "shipping_method_{$count}" ] = $shipping_item['method_id'];
			$order_data[ "shipping_cost_{$count}" ]   = wc_format_decimal( $shipping_item['cost'], 2 );

			$count++;
		}

		// fix order numbers
		$order_data['order_id']               = $order->id;
		$order_data['order_number_formatted'] = get_post_meta( $order->id, '_order_number_formatted', true );
		$order_data['order_number']           = get_post_meta( $order->id, '_order_number', true );

		// fix customer user
		$user = new WP_User( $order_data['customer_id'] );
		$order_data['customer_id'] = $user->user_email;

		return $order_data;

	}


	/**
	 * Get the order data format for a new row per line item, compatible with the legacy (pre 3.0) CSV Export format
	 *
	 * Note this code was adapted from the old code to maintain compatibility as close as possible, so it should
	 * not be modified unless absolutely necessary
	 *
	 * @since 3.0
	 * @param array $order_data an array of order data for the given order
	 * @param WC_Order $order the WC_Order object
	 * @return array modified order data
	 */
	private function get_legacy_one_row_per_line_item( $order_data, WC_Order $order ) {

		$data = array();

		foreach ( $order->get_items() as $_ => $item ) {

			$product = $order->get_product_from_item( $item );

			if ( ! is_object( $product ) ) {
				$product = new WC_Product( 0 );
			}

			$item_meta = new WC_Order_Item_Meta( SV_WC_Plugin_Compatibility::is_wc_version_gte_2_4() ? $item : $item['item_meta'] );
			$variation = $item_meta->display( true, true );

			if ( $variation ) {
				$variation = str_replace( array( "\r", "\r\n", "\n" ), '', $variation );
			}

			$order_data['line_item_sku']       = $product->get_sku();
			$order_data['line_item_name']      = str_replace( array( '&#8220;', '&#8221;'), '', $item['name'] );
			$order_data['line_item_variation'] = $variation;
			$order_data['line_item_amount']    = $item['qty'];
			$order_data['line_item_price']     = $order->get_line_total( $item );

			// convert country codes to full name
			if ( isset( WC()->countries->countries[ $order->billing_country ] ) ) {
				$order_data['billing_country'] = WC()->countries->countries[ $order->billing_country ];
			}
			if ( isset( WC()->countries->countries[ $order->shipping_country ] ) ) {
				$order_data['shipping_country'] = WC()->countries->countries[ $order->shipping_country ];
			}

			// set order ID to order number
			$order_data['order_id'] = ltrim( $order->get_order_number(), _x( '#', 'hash before the order number', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );

			$data[] = $order_data;
		}

		// handle orders with no line items
		if ( empty( $data ) ) {

			$data = $order_data;
		}

		return $data;
	}


	/**
	 * Get the order data format for a single column for all line items, compatible with the legacy (pre 3.0) CSV Export format
	 *
	 * Note this code was adapted from the old code to maintain compatibility as close as possible, so it should
	 * not be modified unless absolutely necessary
	 *
	 * @since 3.0
	 * @param array $order_data an array of order data for the given order
	 * @param WC_Order $order the WC_Order object
	 * @return array modified order data
	 */
	private function get_legacy_single_column_line_item( $order_data, WC_Order $order ) {

		$line_items = array();

		foreach ( $order->get_items() as $_ => $item ) {

			$product = $order->get_product_from_item( $item );

			if ( ! is_object( $product ) ) {
				$product = new WC_Product( 0 );
			}

			$line_item = $item['name'];

			if ( $product->get_sku() ) {
				$line_item .= ' (' . $product->get_sku() . ')';
			}

			$line_item .= ' x' . $item['qty'];

			$item_meta = new WC_Order_Item_Meta( SV_WC_Plugin_Compatibility::is_wc_version_gte_2_4() ? $item : $item['item_meta'] );
			$variation = $item_meta->display( true, true );

			if ( $variation ) {
				$line_item .= ' - ' . str_replace( array( "\r", "\r\n", "\n" ), '', $variation );
			}


			$line_items[] = str_replace( array( '&#8220;', '&#8221;' ), '', $line_item );
		}

		$order_data['order_items'] = implode( '; ', $line_items );

		// convert country codes to full name
		if ( isset( WC()->countries->countries[ $order->billing_country ] ) ) {
			$order_data['billing_country'] = WC()->countries->countries[ $order->billing_country ];
		}
		if ( isset( WC()->countries->countries[ $order->shipping_country ] ) ) {
			$order_data['shipping_country'] = WC()->countries->countries[ $order->shipping_country ];
		}

		// set order ID to order number
		$order_data['order_id'] = ltrim( $order->get_order_number(), _x( '#', 'hash before the order number', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );

		return $order_data;
	}


	/**
	 * Get the maximum number of line items for the given set of order IDs in order to generate the proper
	 * number of order line item columns for use with the CSV Import Suite format
	 *
	 * @since 3.0
	 * @param array $order_ids
	 * @return array modified order data
	 */
	private function get_max_line_items( $order_ids ) {

		$max_line_items = 0;

		foreach ( $order_ids as $order_id ) {

			$order = wc_get_order( $order_id );

			$line_items_count = count( $order->get_items() );

			if ( $line_items_count >= $max_line_items ) {
				$max_line_items = $line_items_count;
			}
		}

		return $max_line_items;
	}


	/**
	 * Get the maximum number of shipping line items for the given set of order IDs in order to generate the proper
	 * number of shipping line item columns for use with the CSV Import Suite format
	 *
	 * @since 3.0
	 * @param array $order_ids
	 * @return array modified order data
	 */
	private function get_max_shipping_line_items( $order_ids ) {

		$max_line_items = 0;

		foreach ( $order_ids as $order_id ) {

			$order = wc_get_order( $order_id );

			$line_items_count = count( $order->get_items( 'shipping' ) );

			if ( $line_items_count >= $max_line_items ) {
				$max_line_items = $line_items_count;
			}
		}

		return $max_line_items;
	}


	/**
	 * Modify the order export column headers to match the chosen format
	 *
	 * @since 3.0
	 * @param array $_ unused
	 * @param \WC_Customer_Order_CSV_Export_Generator the generator instance
	 * @return array modified column headers
	 */
	public function modify_customer_headers( $_, $generator ) {

		if ( 'import' == $this->customer_format ) {

			$headers = array(
				'user_login'          => 'username',
				'email'               => 'email',
				'date_registered'     => 'date_registered',
				'billing_first_name'  => 'billing_first_name',
				'billing_last_name'   => 'billing_last_name',
				'billing_company'     => 'billing_company',
				'billing_address_1'   => 'billing_address_1',
				'billing_address_2'   => 'billing_address_2',
				'billing_city'        => 'billing_city',
				'billing_state'       => 'billing_state',
				'billing_postcode'    => 'billing_postcode',
				'billing_country'     => 'billing_country',
				'billing_email'       => 'billing_email',
				'billing_phone'       => 'billing_phone',
				'shipping_first_name' => 'shipping_first_name',
				'shipping_last_name'  => 'shipping_last_name',
				'shipping_company'    => 'shipping_company',
				'shipping_address_1'  => 'shipping_address_1',
				'shipping_address_2'  => 'shipping_address_2',
				'shipping_city'       => 'shipping_city',
				'shipping_state'      => 'shipping_state',
				'shipping_postcode'   => 'shipping_postcode',
				'shipping_country'    => 'shipping_country',
			);

		} else {

			// legacy format
			$headers = array(
				'customer_id'        => __( 'ID', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_first_name' => __( 'First Name', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_last_name'  => __( 'Last Name', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_email'      => __( 'Email', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_phone'      => __( 'Phone', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_address_1'  => __( 'Address', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_address_2'  => __( 'Address 2', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_postcode'   => __( 'Post code', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_city'       => __( 'City', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_state'      => __( 'State', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_country'    => __( 'Country', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'billing_company'    => __( 'Company', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
			);
		}

		return $headers;
	}


	/**
	 * Modify the order export row to match the chosen format
	 *
	 * @since 3.0
	 * @param array $customer_data an array of customer data for the given user
	 * @param WP_User $user the WP User object
	 * @return array modified customer data
	 */
	public function modify_customer_row( $customer_data, $user ) {

		if ( 'import' == $this->customer_format ) {

			$customer_data['user_login'] = isset( $user->user_login ) ? $user->user_login : '';
		}

		return $customer_data;
	}


} //end \WC_Customer_Order_CSV_Export_Compatibility class

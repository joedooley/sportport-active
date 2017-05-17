<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Receiptful_Abandoned_Cart.
 *
 * Class to manage all abandoned cart stuff.
 *
 * @class		Receiptful_Abandoned_Cart
 * @since		1.2.0
 * @version		1.2.0
 * @author		Conversio
 */
class Receiptful_Abandoned_Cart {


	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {

		// Add to cart
		add_action( 'woocommerce_add_to_cart', array( $this, 'cart_update' ), 10 );

		// Remove from cart
		add_action( 'woocommerce_cart_item_removed', array( $this, 'cart_update' ), 10 );

		// Restore cart item
		add_action( 'woocommerce_cart_item_restored', array( $this, 'cart_update' ), 10 );

		// Quantity update
		add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'cart_update' ), 10 );

		// Recover the cart
		if ( isset( $_GET['rfcart'] ) && ! empty( $_GET['rfcart'] ) ) {
			add_action( 'template_redirect', array( $this, 'recover_cart' ), 20 );
		}

	}


	/**
	 * Cart update.
	 *
	 * Send a cart update to Conversio when a product is added, removed, changed or restored.
	 *
	 * @since 1.2.0
	 */
	public function cart_update() {

		if ( ! isset( $_COOKIE['receiptful-token'] ) ) {
			return false;
		}

		$cart_args = array(
			'token'    => $_COOKIE['receiptful-token'],
			'amount'   => WC()->cart->cart_contents_total + WC()->cart->shipping_total + WC()->cart->get_taxes_total( false, false ),
			'currency' => get_woocommerce_currency(),
			'items'    => $this->prep_cart_items( WC()->cart->get_cart() ),
		);

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$cart_args['customer'] = $current_user->user_email;
		}

		$response = Receiptful()->api->post_cart_update( $cart_args );

		return $response;

	}


	/**
	 * Prepare cart items.
	 *
	 * Prepare the cart items list to be passed along to the API.
	 *
	 * @since 1.2.0
	 *
	 * @param	array		$cart_items	List of cart items.
	 * @return	array|bool				False when no cart items. Formatted cart item list otherwise.
	 */
	public function prep_cart_items( $cart_items ) {

		if ( ! is_array( $cart_items ) ) {
			return false;
		}

		$cart_items_args = array();
		foreach ( $cart_items as $cart_item_key => $cart_item ) {

			/** @var WC_Product $product */
			$product = $cart_item['data'];
			$price = $product->get_price();
			// Taxable
			if ( $product->is_taxable() ) {
				if ( get_option( 'woocommerce_tax_display_cart' ) == 'excl' ) {
					$price = function_exists( 'wc_get_price_excluding_tax' ) ? wc_get_price_excluding_tax( $product ) : $product->get_price_excluding_tax();
				} else {
					$price = function_exists( 'wc_get_price_including_tax' ) ? wc_get_price_including_tax( $product ) : $product->get_price_including_tax();
				}
			}

			$cart_items_args[] = array(
				'reference'   => $cart_item['product_id'],
				'variant'     => $cart_item['variation_id'],
				'description' => $product->get_title(),
				'quantity'    => $cart_item['quantity'],
				'amount'      => $price,
				'attributes'  => $this->prep_cart_item_attributes( $cart_item )
			);

		}

		return $cart_items_args;

	}


	/**
	 * Get cart item attributes.
	 *
	 * Get the attributes for a cart item when its a variable product.
	 *
	 * @since 1.2.0
	 *
	 * @param	array	$cart_item	List of cart item data.
	 * @return	array				List of cart item variable attributes.
	 */
	public function prep_cart_item_attributes( $cart_item ) {

		if ( ! isset( $cart_item['variation'] ) ) {
			return null;
		}

		$attributes = array();
		if ( isset( $cart_item['variation'] ) && is_array( $cart_item['variation'] ) ) {
			foreach ( $cart_item['variation'] as $key => $value ) {
				$attributes[] = array(
					'key'   => $key,
					'value' => $value,
				);
			}
		}

		return $attributes;

	}


	/**
	 * Recover the cart items.
	 *
	 * Recover the cart items when a recover ID is set in the URL.
	 *
	 * @since 1.2.0
	 *
	 * @return bool false when the ID is not recognized.
	 */
	public function recover_cart() {

		$token = isset( $_GET['rfcart'] ) ? $_GET['rfcart'] : null;

		$response = Receiptful()->api->get_abandoned_cart( $token );

		// Bail if the cart could not be retrieved
		if ( false == $response ) {
			return false;
		}

		// Clear the cart
		WC()->cart->empty_cart();

		if ( isset( $response['items'] ) ) {
			foreach ( $response['items'] as $key => $values ) {

				if ( ! in_array( get_post_type( $values['reference'] ), array( 'product', 'product_variation' ) ) ) {
					continue;
				}

				$product_id		= $values['reference'];
				$quantity		= isset( $values['quantity'] ) ? $values['quantity'] : '1';
				$variation_id	= isset( $values['variant'] )  ? $values['variant']  : '';
				$var			= '';

				if ( empty( $var ) && ! empty( $values['attributes'] ) ) {
					foreach ( $values['attributes'] as $key => $value ) {
						$var[ $value['key'] ] = $value['value'];
					}
				}

				WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $var );

			}
		}

		if ( ! is_cart() ) {
			// Redirect after add to cart
			wp_redirect( esc_url_raw( remove_query_arg( 'rfcart', WC()->cart->get_cart_url() ) ) );
			die;
		}


	}

}

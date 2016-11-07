<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Receiptful_Api.
 *
 * @class		Receiptful_Api
 * @version		1.0.0
 * @author		Receiptful
 */
class Receiptful_Api {


	/**
	 * Receiptful API key.
	 *
	 * @since 1.0.0
	 * @var $api_key
	 */
	public $api_key;


	/**
	 * URL for Receiptful.
	 *
	 * @since 1.0.0
	 * @var $url
	 */
	public $url = 'https://app.conversio.com/api/v1';


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->api_key = get_option( 'receiptful_api_key' );
	}


	/**
	 * Send receipt.
	 *
	 * Send the Receiptful receipt based on $args.
	 *
	 * @since 1.0.0
	 *
	 * @param	array	$args	API call arguments.
	 * @return	array			API response.
	 */
	public function receipt( $args = array() ) {

		$response = $this->api_call( '/receipts', $args );

		return $response;

	}


	/**
	 * Resend receipt.
	 *
	 * Resend the previously send Receiptful receipt.
	 *
	 * @since 1.0.0
	 *
	 * @param	int				$receipt_id		Receiptful receipt ID, as retrieved from original API call.
	 * @return	array|WP_Error					WP_Error when the API call fails, otherwise the API response.
	 */
	public function resend_receipt( $receipt_id ) {

		$response = $this->api_call( '/receipts/' . $receipt_id . '/send' );

		return $response;

	}


	/**
	 * Update product.
	 *
	 * When a product is created/updated, send it to the Receiptful API.
	 *
	 * @since 1.1.1
	 *
	 * @param	int				$product_id		Product ID to update in Receiptful.
	 * @param	array			$args			Product arguments to update.
	 * @return	array|WP_Error					WP_Error when the API call fails, otherwise the API response.
	 */
	public function update_product( $product_id, $args ) {

		$response = $this->api_put( '/products/' . $product_id, $args );

		return $response;

	}


	/**
	 * Update products.
	 *
	 * When a product is created/updated, send it to the Receiptful API.
	 *
	 * @since 1.1.1
	 *
	 * @param	array			$args	List of items arguments to update..
	 * @return	array|WP_Error			WP_Error when the API call fails, otherwise the API response.
	 */
	public function update_products( $args ) {

		$response = $this->api_call( '/products', $args );

		return $response;

	}


	/**
	 * Delete product.
	 *
	 * When a product is delete, also delete is from Receiptful.
	 *
	 * @since 1.1.1
	 *
	 * @param	int				$product_id		Product ID to delete from Receiptful.
	 * @return	array|WP_Error					WP_Error when the API call fails, otherwise the API response.
	 */
	public function delete_product( $product_id ) {

		$response = $this->api_delete( '/products/' . $product_id );

		return $response;

	}


	/**
	 * Upload receipts.
	 *
	 * Bulk upload old receipts to sync with Receiptful. This ensures
	 * better quality recommendations for similar products.
	 *
	 * @since 1.1.2
	 *
	 * @param	int				$args	List of formatted receipts according the API specs.
	 * @return	array|WP_Error			WP_Error when the API call fails, otherwise the API response.
	 */
	public function upload_receipts( $args ) {

		$response = $this->api_call( '/receipts/bulk', $args );

		return $response;

	}


	/**
	 * Update cart.
	 *
	 * Send a update of the cart. When the cart is abandoned, Receiptful will be able
	 * to send a cart abandoned email.
	 *
	 * @since 1.2.0
	 *
	 * @param	array			$args	List of arguments to pass to the endpoint.
	 * @return	array|WP_Error			WP_Error when the API call fails, otherwise the API response.
	 */
	public function post_cart_update( $args ) {

		$response = $this->api_call( '/abandoned-carts/', $args, array( 'blocking' => false ) );

		return $response;

	}


	/**
	 * Get abandoned cart arguments.
	 *
	 * Get the abandoned cart arguments from the Receiptful API (contains cart items).
	 *
	 * @since 1.2.0
	 *
	 * @param	string	$token	Abandoned cart token.
	 * @param	array	$args	List of arguments (unused).
	 * @return	mixed			False when API call is not valid. RAW API response otherwise.
	 */
	public function get_abandoned_cart( $token, $args = array() ) {

		$response = $this->api_get( '/abandoned-carts/' . $token, $args );

		if ( is_wp_error( $response ) || '200' != $response['response']['code'] ) {
			$cart = false;
		} else {
			$response_body 	= json_decode( $response['body'], 1 );
			$cart = $response_body;
		}

		return $cart;

	}


	/**
	 * Public user key.
	 *
	 * Get the current user key based on the API key used.
	 *
	 * @since 1.1.4
	 *
	 * @return array|WP_Error WP_Error when the API call fails, otherwise the API response.
	 */
	public function get_public_user_key() {

		$public_key = '';

		if ( ! $public_key = get_option( 'receiptful_public_user_key' ) ) {

			$response = $this->api_get( '/users/current' );

			if ( is_wp_error( $response ) || '200' != $response['response']['code'] ) {
				$public_key = '';
			} else {
				$response_body 	= json_decode( $response['body'], 1 );
				$public_key 	= isset( $response_body['publicKey'] ) ? $response_body['publicKey'] : '';
				update_option( 'receiptful_public_user_key', $public_key );
			}

		}

		return $public_key;

	}


	/**
	 * API GET.
	 *
	 * Send a GET request to the Receiptful API call.
	 *
	 * @since 1.1.4
	 * @since 1.2.0 Add $request_args param.
	 *
	 * @param	string	$method				API method to call.
	 * @param	array	$args				Arguments to pass in the API call.
	 * @param	array	$request_args		List of arguments to override default request arguments.
	 * @return	array|WP_Error	$response	API response.
	 */
	protected function api_get( $method, $args = array(), $request_args = array() ) {

		$headers = array( 'Content-Type' => 'application/json', 'X-ApiKey' => $this->api_key );

		$api_response = wp_remote_get( $this->url . $method, wp_parse_args( $request_args, array(
				'timeout'		=> 5,
				'redirection'	=> 5,
				'httpversion'	=> '1.0',
				'blocking'		=> true,
				'headers'		=> $headers,
				'body'			=> $args,
				'cookies'		=> array()
			)
		) );

		if ( is_wp_error( $api_response ) ) {
			return $api_response;
		} else {
			$response['response']	= $api_response['response'];
			$response['body']		= $api_response['body'];
			return $response;
		}

	}


	/**
	 * API Call.
	 *
	 * Send a Receiptful API call based on method and arguments.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Add $request_args param.
	 *
	 * @param	string	$method				API method to call.
	 * @param	array	$args				Arguments to pass in the API call.
	 * @param	array	$request_args		List of arguments to override default request arguments.
	 * @return	array|WP_Error	$response	API response.
	 */
	protected function api_call( $method, $args = array(), $request_args = array() ) {

		$headers = array( 'Content-Type' => 'application/json', 'X-ApiKey' => $this->api_key );

		$api_response = wp_remote_post( $this->url . $method, wp_parse_args( $request_args, array(
				'method'		=> 'POST',
				'timeout'		=> 45,
				'redirection'	=> 5,
				'httpversion'	=> '1.0',
				'blocking'		=> true,
				'headers'		=> $headers,
				'body'			=> json_encode( $args ),
				'cookies'		=> array()
			)
		) );

		if ( is_wp_error( $api_response ) ) {
			return $api_response;
		} else {
			$response['response']	= $api_response['response'];
			$response['body']		= $api_response['body'];
			return $response;
		}

	}


	/**
	 * API PUT.
	 *
	 * Send a Receiptful PUT API call based on method and arguments.
	 *
	 * @since 1.1.1
	 * @since 1.2.0 Add $request_args param.
	 *
	 * @param	string	$method				API method to call.
	 * @param	array	$args				Arguments to pass in the API call.
	 * @param	array	$request_args		List of arguments to override default request arguments.
	 * @return	array|WP_Error	$response	API response.
	 */
	protected function api_put( $method, $args = array(), $request_args = array() ) {

		$headers = array( 'Content-Type' => 'application/json', 'X-ApiKey' => $this->api_key );

		$api_response = wp_remote_post( $this->url . $method, wp_parse_args( $request_args, array(
				'method'		=> 'PUT',
				'timeout'		=> 45,
				'redirection'	=> 5,
				'httpversion'	=> '1.0',
				'blocking'		=> true,
				'headers'		=> $headers,
				'body'			=> json_encode( $args ),
				'cookies'		=> array()
			)
		) );

		if ( is_wp_error( $api_response ) ) {
			return $api_response;
		} else {
			$response['response']	= $api_response['response'];
			$response['body']		= $api_response['body'];
			return $response;
		}

	}


	/**
	 * API DELETE.
	 *
	 * Send a Receiptful DELETE API call based on method and arguments.
	 *
	 * @since 1.1.1
	 * @since 1.2.0 Add $request_args param.
	 *
	 * @param	string	$method				API method to call.
	 * @param	array	$request_args		List of arguments to override default request arguments.
	 * @return	array|WP_Error	$response	API response.
	 */
	protected function api_delete( $method, $request_args = array() ) {

		$headers = array( 'Content-Type' => 'application/json', 'X-ApiKey' => $this->api_key );

		$api_response = wp_remote_post( $this->url . $method, wp_parse_args( $request_args, array(
			'method'		=> 'DELETE',
			'timeout'		=> 45,
			'redirection'	=> 5,
			'httpversion'	=> '1.0',
			'blocking'		=> true,
			'headers'		=> $headers,
			'body'			=> array(),
			'cookies'		=> array()
		) ) );

		if ( is_wp_error( $api_response ) ) {
			return $api_response;
		} else {
			$response['response']	= $api_response['response'];
			$response['body']		= $api_response['body'];
			return $response;
		}

	}


}

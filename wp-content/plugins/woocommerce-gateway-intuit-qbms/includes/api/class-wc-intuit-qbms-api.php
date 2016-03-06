<?php
/**
 * WooCommerce Intuit QBMS
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Intuit QBMS to newer
 * versions in the future. If you wish to customize WooCommerce Intuit QBMS for your
 * needs please refer to http://docs.woothemes.com/document/intuit-qbms/
 *
 * @package   WC-Intuit-QBMS/API
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Intuit QBMS API Class
 *
 * Handles sending/receiving/parsing of Intuit QBMS XML, this is the main API
 * class responsible for communication with the Intuit QBMS API
 *
 * @since 1.0
 */
class WC_Intuit_QBMS_API implements SV_WC_Payment_Gateway_API {

	/** @var string API id */
	private $id;

	/** @var string API URL endpoint */
	private $endpoint;

	/** @var string the application login value */
	private $application_login;

	/** @var string the application id value */
	private $application_id;

	/** @var string the connection ticket value */
	public $connection_ticket;

	/** @var SV_WC_Payment_Gateway_API_Request most recent request */
	private $request;

	/** @var SV_WC_Payment_Gateway_API_Response most recent response */
	private $response;

	/** @var \WC_Order|null order associated with the request, if any */
	protected $order;


	/**
	 * Constructor - setup request object and set endpoint
	 *
	 * @since 1.0
	 * @param string $id API id
	 * @param string $api_endpoint API URL endpoint
	 * @param string $application_login application login value
	 * @param string $application_id application id value
	 * @param string $connection_ticket connection ticket value
	 */
	public function __construct( $id, $api_endpoint, $application_login, $application_id, $connection_ticket ) {

		$this->id                = $id;
		$this->endpoint          = $api_endpoint;
		$this->application_login = $application_login;
		$this->application_id    = $application_id;
		$this->connection_ticket = $connection_ticket;

	}


	/**
	 * Create a new cc charge transaction using Intuit QBMS XML API
	 *
	 * This request, if successful, causes a charge to be incurred by the
	 * specified credit card. Notice that the authorization for the charge is
	 * obtained when the card issuer receives this request. The resulting
	 * authorization code is returned in the response to this request.
	 *
	 * Notice that voice authorizations cannot be handled by this request. For
	 * voice authorizations, use the CustomerCreditCardVoiceAuth request
	 * followed by a CustomerCreditCardCapture request.
	 *
	 * Note: It's important that these elements appear in the expected order,
	 * otherwise there will be parsing errors returned from the QBMS API
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway_API::credit_card_charge()
	 * @param WC_Order $order the order
	 * @return WC_Intuit_QBMS_API_Credit_Card_Charge_Response Intuit QBMS API credit card charge response object
	 * @throws Exception network timeouts, etc
	 */
	public function credit_card_charge( WC_Order $order ) {

		$this->order = $order;

		$request = $this->get_new_request();
		$request->credit_card_charge( $order );

		return $this->perform_request( $request, 'WC_Intuit_QBMS_API_Credit_Card_Charge_Response' );

	}


	/**
	 * Create a new cc auth transaction using Intuit QBMS XML API
	 *
	 * This request is used for a transaction in which the merchant needs
	 * authorization of a charge, but does not wish to actually make the charge
	 * at this point in time. For example, if a customer orders merchandise to
	 * be shipped, you could issue this request at the time of the order to
	 * make sure the merchandise will be paid for by the card issuer. Then at
	 * the time of actual merchandise shipment, you perform the actual charge
	 * using the request CustomerCreditCardCaptureRq.
	 *
	 * It is very important to save the CreditCardTransID from the response to
	 * this request, because this is required for the subsequent
	 * CustomerCreditCardCapture request.
	 *
	 * Note: The authorization is valid only for a fixed amount of time, which
	 * may vary by card issuer, but which is usually several days. QBMS imposes
	 * its own maximum of 30 days after the date of the original authorization,
	 * but most issuers are expected to have a validity period significantly
	 * less than this.
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway_API::credit_card_authorization()
	 * @param WC_Order $order the order
	 * @return WC_Intuit_QBMS_API_Credit_Card_Authorization_Response Intuit QBMS API credit card auth response object
	 * @throws Exception network timeouts, etc
	 */
	public function credit_card_authorization( WC_Order $order ) {

		$this->order = $order;

		$request = $this->get_new_request();
		$request->credit_card_auth( $order );

		return $this->perform_request( $request, 'WC_Intuit_QBMS_API_Credit_Card_Authorization_Response' );
	}


	/**
	 * Capture funds for a credit card authorization using Intuit QBMS XML API
	 *
	 * This request can be made only after a previous and successful
	 * CustomerCreditCardAuth request, where the card issuer has authorized a
	 * charge to be made against the specified credit card in the future. The
	 * CreditCardTransID from that prior transaction must be used in this
	 * subsequent and related transaction. This request actually causes that
	 * authorized charge to be incurred against the customer's credit card.
	 *
	 * Notice that you cannot have multiple capture requests against a single
	 * CustomerCreditCardAuth request. Each CustomerCreditCardAuth request must
	 * have one and only one capture request.
	 *
	 * Note: The authorization to be captured is valid only for a fixed amount
	 * of time, which may vary by card issuer, but which is usually several
	 * days. QBMS imposes its own maximum of 30 days after the date of the
	 * original authorization, but most issuers are expected to have a validity
	 * period significantly less than this.
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway_API::credit_card_capture()
	 * @param WC_Order $order the order
	 * @return SV_WC_Payment_Gateway_API_Response credit card capture response
	 * @throws Exception network timeouts, etc
	 */
	public function credit_card_capture( WC_Order $order ) {

		$this->order = $order;

		$request = $this->get_new_request();
		$request->credit_card_capture( $order );

		return $this->perform_request( $request, 'WC_Intuit_QBMS_API_Credit_Card_Capture_Response' );
	}


	/**
	 * Perform a customer check debit transaction using the Intuit QBMS XML API
	 *
	 * An amount will be debited from the customer's account to the merchant's account.
	 *
	 * @since 1.0
	 * @param WC_Order $order the order
	 * @return SV_WC_Payment_Gateway_API_Response check debit response
	 * @throws Exception network timeouts, etc
	 */
	public function check_debit( WC_Order $order ) {

		$this->order = $order;

		$request = $this->get_new_request();
		$request->customer_check_debit( $order );

		return $this->perform_request( $request, 'WC_Intuit_QBMS_API_Customer_Check_Debit_Response' );

	}


	/**
	 * Perform a refund for the given order
	 *
	 * If the gateway does not support refunds, this method can be a no-op.
	 *
	 * @since 1.6.0
	 * @see SV_WC_Payment_Gateway_API::refund()
	 * @param WC_Order $order order object
	 * @return SV_WC_Payment_Gateway_API_Response refund response
	 * @throws SV_WC_Payment_Gateway_Exception network timeouts, etc
	 */
	public function refund( WC_Order $order ) {
		// TODO
	}


	/**
	 * Perform a void for the given order
	 *
	 * If the gateway does not support voids, this method can be a no-op.
	 *
	 * @since 1.6.0
	 * @see SV_WC_Payment_Gateway_API::void()
	 * @param WC_Order $order order object
	 * @return SV_WC_Payment_Gateway_API_Response void response
	 * @throws SV_WC_Payment_Gateway_Exception network timeouts, etc
	 */
	public function void( WC_Order $order ) {
		// TODO
	}


	/**
	 * Store sensitive payment information for a particular customer.
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway_API::tokenize_payment_method()
	 * @param WC_Order $order the order with associated payment and customer info
	 * @return WC_Intuit_QBMS_API_Wallet_Add_Response Intuit QBMS API wallet add response
	 * @throws Exception network timeouts, etc
	 */
	public function tokenize_payment_method( WC_Order $order ) {

		$this->order = $order;

		$request = $this->get_new_request();
		$request->wallet_add( $order );

		return $this->perform_request( $request, 'WC_Intuit_QBMS_API_Wallet_Add_Response' );

	}


	/**
	 * Removes the tokenized payment method
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway_API::remove_tokenized_payment_method()
	 * @param string $token the payment method token
	 * @param string $customer_id unique Inuit QBMS customer id
	 * @return WC_Intuit_QBMS_API_Response remove tokenized payment method response
	 * @throws Exception network timeouts, etc
	 */
	public function remove_tokenized_payment_method( $token, $customer_id ) {

		$request = $this->get_new_request();
		$request->wallet_del( $token, $customer_id );

		return $this->perform_request( $request, 'WC_Intuit_QBMS_API_Wallet_Delete_Response' );

	}


	/**
	 * Returns all tokenized payment methods for the user
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway_API::get_tokenized_payment_methods()
	 * @param string $customer_id unique Inuit QBMS customer id
	 * @return SV_WC_API_Get_Tokenized_Payment_Methods_Response get tokenized payment methods response
	 * @throws Exception network timeouts, etc
	 */
	public function get_tokenized_payment_methods( $customer_id ) {

		$request = $this->get_new_request();
		$request->wallet_query( $customer_id );

		return $this->perform_request( $request, 'WC_Intuit_QBMS_API_Wallet_Query_Response' );

	}


	/**
	 * Retrieves merchant account info including:
	 *
	 * * merchant convenience fee amount
	 * * credit card types accepted ie array( 'JCB', 'DinersClub', 'Visa', 'MasterCard', 'Discover', 'AmericanExpress' )
	 * * is check accepted (according to Intuit this will always be false)
	 * * batch hour close
	 *
	 * @since 1.0
	 * @return WC_Intuit_QBMS_API_Merchant_Account_Query_Response merchant account info response
	 * @throws Exception network timeouts, etc
	 */
	public function merchant_account_query() {

		$request = $this->get_new_request();
		$request->merchant_account_query();

		return $this->perform_request( $request, 'WC_Intuit_QBMS_API_Merchant_Account_Query_Response' );

	}


	/**
	 * Returns true, as Intuit QBMS supports a tokenized payment method query request
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway_API::supports_get_tokenized_payment_methods()
	 * @return boolean true
	 */
	public function supports_get_tokenized_payment_methods() {

		return true;

	}


	/**
	 * Returns true, as Intuit QBMS supports a tokenized payment method remove request
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway_API::supports_remove_tokenized_payment_method()
	 * @return boolean true
	 */
	public function supports_remove_tokenized_payment_method() {

		return true;

	}


	/**
	 * Perform the request post to the active endpoint
	 *
	 * @since 1.0
	 * @param WC_Intuit_QBMS_API_Request $request the request object
	 * @param string $response_class_name the class name of the response
	 * @return WC_Intuit_QBMS_API_Response response object
	 * @throws Exception network timeouts
	 */
	private function perform_request( $request, $response_class_name ) {

		// save the request object
		$this->request = $request;

		$method = 'POST';

		// perform the request
		$wp_http_args = array(
			'method'      => $method,
			'timeout'     => 30, // seconds
			'redirection' => 0,
			'httpversion' => '1.0',
			'sslverify'   => true,
			'blocking'    => true,
			'user-agent'  => "WooCommerce/" . WC_VERSION,
			'headers'     => array(
				'accept'       => 'application/x-qbmsxml',
				'content-type' => 'application/x-qbmsxml',
			),
			'body'        => trim( $request->to_xml() ),
			'cookies'     => array(),
		);

		$start_time = microtime( true );
		$response = wp_safe_remote_post( $this->endpoint, $wp_http_args );
		$time = round( microtime( true ) - $start_time, 5 );

		// prepare the request/response data for the request performed action
		$request_data  = array( 'method' => $method, 'uri' => $this->endpoint, 'body' => $request->to_string_safe(), 'time' => $time );
		$response_data = null;

		// Check for Network timeout, etc.
		if ( is_wp_error( $response ) ) {

			do_action( 'wc_' . $this->id . '_api_request_performed', $request_data, $response_data );

			throw new SV_WC_Payment_Gateway_Exception( $response->get_error_message() );
		}

		// now we know the response isn't an error
		$response_data = array( 'code' => ( isset( $response['response']['code'] ) ) ? $response['response']['code'] : '', 'body' => ( isset( $response['body'] ) ) ? $response['body'] : '' );

		// Status Codes:
		// 200 - success
		// 400 - invalid request XML
		// For others, perhaps we should be treating as QBMS Error Recovery situations as outlined https://developer.intuit.com/docs/030_qbms/0060_documentation/error_handling#QBMS_Error_Recovery
		//  and re-trying the transaction with the same TransRequestID, but I just don't know
		if ( 200 != $response['response']['code'] ) {

			// response will include the http status code/message
			$message = sprintf( "HTTP %s: %s", $response['response']['code'], $response['response']['message'] );

			// the body (if any)
			if ( trim( $response['body'] ) )
				$message .= ' - ' . $response['body'];

			do_action( 'wc_' . $this->id . '_api_request_performed', $request_data, $response_data );

			throw new SV_WC_Payment_Gateway_Exception( $message );
		}

		// return blank XML document if response body doesn't exist
		$response = ( isset( $response[ 'body' ] ) ) ? $response[ 'body' ] : '<?xml version="1.0" encoding="utf-8"?>';

		// create the response and tie it to the request
		$response = $this->parse_response( $response_class_name, $request, $response );

		// full response object
		$response_data['body'] = $response->to_string_safe();

		do_action( 'wc_' . $this->id . '_api_request_performed', $request_data, $response_data );

		return $response;
	}


	/**
	 * Return a new WC_Intuit_QBMS_API_Response object from the response XML
	 *
	 * @since 1.0
	 * @param string $response_class_name the class name of the response
	 * @param WC_Intuit_QBMS_API_Request $request the request
	 * @param string $response xml response
	 * @return WC_Intuit_QBMS_API_Response API response object
	 */
	private function parse_response( $response_class_name, $request, $response ) {

		// save the most recent response object
		return $this->response = new $response_class_name( $request, $response );

	}


	/**
	 * Builds and returns a new API request object
	 *
	 * @since 1.0
	 * @return WC_Intuit_QBMS_API_Request API request object
	 */
	private function get_new_request() {
		return new WC_Intuit_QBMS_API_Request( $this->application_login, $this->application_id, $this->connection_ticket );
	}


	/**
	 * Returns the most recent request object
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway_API::get_request()
	 * @return SV_WC_Payment_Gateway_API_Request the most recent request object
	 */
	public function get_request() {

		return $this->request;
	}


	/**
	 * Returns the most recent response object
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway_API::get_response()
	 * @return SV_WC_Payment_Gateway_API_Response the most recent response object
	 */
	public function get_response() {

		return $this->response;
	}


	/**
	 * Return the order associated with the request, if any
	 *
	 * @since 1.7.1
	 * @return \WC_Order|null
	 */
	public function get_order() {

		return $this->order;
	}


}

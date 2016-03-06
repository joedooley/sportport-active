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
 * Intuit QBMS API Request Class
 *
 * Generates XML required by API specs to perform an API request
 *
 * @link https://member.developer.intuit.com/qbSDK-current/Common/newOSR/index.html
 *
 * @since 1.0
 */
class WC_Intuit_QBMS_API_Request extends XMLWriter implements SV_WC_Payment_Gateway_API_Request {

	/** QBMS SDK Version number */
	const QBMSXML_VERSION = 4.5;

	/** @var string the request xml */
	private $request_xml;

	/** @var string the application login value */
	private $application_login;

	/** @var string the application id value */
	private $application_id;

	/** @var string the connection ticket value */
	private $connection_ticket;

	/** @var WC_Order optional order object if this request was associated with an order */
	protected $order;


	/**
	 * Construct an Intuit QBMS request object
	 *
	 * @since 1.0
	 * @param string $application_login application login value
	 * @param string $application_id application id value
	 * @param string $connection_ticket connection ticket value
	 */
	public function __construct( $application_login, $application_id, $connection_ticket ) {

		$this->application_login = $application_login;
		$this->application_id    = $application_id;
		$this->connection_ticket = $connection_ticket;

	}


	/**
	 * Creates a credit card auth request for the payment method/
	 * customer associated with $order
	 *
	 * @since 1.0
	 * @param WC_Order $order the order object
	 */
	public function credit_card_auth( $order ) {

		$request_type = isset( $order->payment->token ) && $order->payment->token ? 'CustomerCreditCardWalletAuthRq' : 'CustomerCreditCardAuthRq';
		$this->credit_card_charge_auth_request( $request_type, $order );

	}


	/**
	 * Creates a credit card charge request for the payment method/
	 * customer associated with $order
	 *
	 * @since 1.0
	 * @param WC_Order $order the order object
	 */
	public function credit_card_charge( $order ) {

		$request_type = isset( $order->payment->token ) && $order->payment->token ? 'CustomerCreditCardWalletChargeRq' : 'CustomerCreditCardChargeRq';
		$this->credit_card_charge_auth_request( $request_type, $order );

	}


	/**
	 * Capture funds for a credit card authorization
	 *
	 * @since 1.1
	 * @param WC_Order $order the order object
	 */
	public function credit_card_capture( $order ) {

		// store the order object for later use
		$this->order = $order;

		$this->init_document();

		// <QBMSXMLMsgsRq>
		$this->startElement( 'QBMSXMLMsgsRq' );

		// this is the only difference between cc charge/auth requests
		// <CustomerCreditCardCaptureRq>
		$this->startElement( 'CustomerCreditCardCaptureRq' );

		$this->writeElement( 'TransRequestID',    substr( $order->trans_request_id, 0, 50 ) );

		$this->writeElement( 'CreditCardTransID', get_post_meta( $order->id, '_wc_intuit_qbms_trans_id', true ) );

		$this->writeElement( 'Amount',            $order->capture_total );

		// root element <GeoLocationInfo>
		$this->startElement( 'GeoLocationInfo' );

		$this->writeElement( 'IPAddress', substr( $_SERVER['REMOTE_ADDR'], 0, 15 ) );

		// </GeoLocationInfo>
		$this->endElement();

		// </CustomerCreditCardCaptureRq>
		$this->endElement();

		// </QBMSXMLMsgsRq>
		$this->endElement();

		$this->close_document();
	}


	/**
	 * Creates a customer check debit request for the given $order
	 *
	 * Note: It's important that these elements appear in the expected order,
	 * otherwise there will be parsing errors returned from the QBMS API
	 *
	 * @since 1.0
	 * @param WC_Order $order the order object
	 */
	public function customer_check_debit( $order ) {

		// store the order object for later use
		$this->order = $order;

		$this->init_document();

		// <QBMSXMLMsgsRq>
		$this->startElement( 'QBMSXMLMsgsRq' );

		$request_type = isset( $order->payment->token ) && $order->payment->token ? 'CustomerCheckWalletDebitRq' : 'CustomerCheckDebitRq';

		// <CustomerCheckDebit> | <CustomerCheckWalletDebitRq>
		$this->startElement( $request_type );

		$this->writeElement( 'TransRequestID', substr( $order->trans_request_id, 0, 50 ) );

		if ( isset( $order->payment->token ) && $order->payment->token ) {
			// tokenized

			$this->writeElement( 'WalletEntryID', $order->payment->token );
			$this->writeElement( 'CustomerID',    $order->customer_id );

		} else {
			// non-tokenized

			// <KeyEnteredCheckInfo>
			$this->startElement( 'KeyEnteredCheckInfo' );

			$this->writeElement( 'RoutingNumber', $order->payment->routing_number );
			$this->writeElement( 'AccountNumber', $order->payment->account_number );

			// optional check number
			if ( isset( $order->payment->check_number ) && $order->payment->check_number )
				$this->writeElement( 'CheckNumber',   $order->payment->check_number );

			// <PersonalPaymentInfo>
			$this->startElement( 'PersonalPaymentInfo' );

			$this->writeElement( 'PersonalDebitAccountType', 'savings' == $order->payment->account_type ? 'Savings' : 'Checking' ); // Checking/Savings
			$this->writeElement( 'PayorFirstName',           $order->billing_first_name );
			$this->writeElement( 'PayorLastName',            $order->billing_last_name );

			// </PersonalPaymentInfo>
			$this->endElement();

			// </KeyEnteredCheckInfo>
			$this->endElement();

			// optional phone number
			if ( isset( $order->billing_phone ) && $order->billing_phone )
				$this->writeElement( 'PayorPhoneNumber',   $order->billing_phone );

		}

		if ( isset( $order->payment->driver_license_number ) && $order->payment->driver_license_number )
			$this->writeElement( 'PayorDriverLicenseNumber',   $order->payment->driver_license_number );

		// two-character state code
		if ( isset( $order->payment->driver_license_state ) && $order->payment->driver_license_state )
			$this->writeElement( 'PayorDriverLicenseState',   $order->payment->driver_license_state );

		$this->writeElement( 'Amount', $order->payment_total );

		$this->writeElement( 'PaymentMode', 'Internet' );

		$this->writeElement( 'InvoiceID', ltrim( $order->get_order_number(), _x( '#', 'hash before order number', 'woocommerce-gateway-intuit-qbms' ) ) );

		// customer identifier for non-guest transactions
		if ( isset( $order->customer_id ) && $order->customer_id ) {
			$this->writeElement( 'UserID', $order->customer_id );
		}

		// Set the request comment to the order note by default
		$comment = $order->customer_note;

		/**
		 * Filter the request comment sent to Intuit.
		 *
		 * @since 1.7.2
		 * @param string $comment The request comment.
		 * @param object $order The WooCommerce order.
		 */
		$comment = (string) apply_filters( 'wc_payment_gateway_intuit_qbms_check_request_comment', $comment, $order );

		// Optionally add the comment to the request
		if ( $comment ) {

			// Format the comment for the API
			$comment = SV_WC_Helper::str_truncate( SV_WC_Helper::str_to_ascii( $comment ), 4000 );

			$this->writeElement( 'Comment', $comment );
		}

		// root element <GeoLocationInfo>
		$this->startElement( 'GeoLocationInfo' );

		$this->writeElement( 'IPAddress', $_SERVER['REMOTE_ADDR'] );

		// </GeoLocationInfo>
		$this->endElement();

		// </CustomerCheckDebitRq> | </CustomerCheckWalletDebitRq>
		$this->endElement();

		// </QBMSXMLMsgsRq>
		$this->endElement();

		$this->close_document();

	}


	/**
	 * Creates the wallet add request for the payment method/
	 * customer associated with $order
	 *
	 * @since 1.0
	 * @param WC_Order $order the order object
	 */
	public function wallet_add( $order ) {

		// store the order object for later use
		$this->order = $order;

		$this->init_document();

		// <QBMSXMLMsgsRq>
		$this->startElement( 'QBMSXMLMsgsRq' );

		if ( 'credit_card' == $order->payment->type ) {
			// credit card transaction

			// <CustomerCreditCardWalletAddRq>
			$this->startElement( 'CustomerCreditCardWalletAddRq' );

			$this->writeElement( 'CustomerID', substr( $order->customer_id, 0, 40 ) );

			$this->writeElement( 'CreditCardNumber', substr( $order->payment->account_number, 0, 19 ) );
			$this->writeElement( 'ExpirationMonth',  substr( $order->payment->exp_month, 0, 12 ) );
			$this->writeElement( 'ExpirationYear',   $order->payment->exp_year );

			$this->writeElement( 'NameOnCard',           substr( SV_WC_Helper::str_to_ascii( $order->billing_first_name . ' ' . $order->billing_last_name ), 0, 30 ) );
			$this->writeElement( 'CreditCardAddress',    substr( SV_WC_Helper::str_to_ascii( empty( $order->billing_address_2 ) ? $order->billing_address_1 : $order->billing_address_1 . ' ' . $order->billing_address_2 ), 0, 30 ) );
			$this->writeElement( 'CreditCardCity',       substr( SV_WC_Helper::str_to_ascii( $order->billing_city ), 0, 50 ) );
			$this->writeElement( 'CreditCardState',      substr( SV_WC_Helper::str_to_ascii( $order->billing_state ), 0, 20 ) );
			$this->writeElement( 'CreditCardPostalCode', substr( SV_WC_Helper::str_to_ascii( str_replace( '-', '', $order->billing_postcode ) ), 0, 9 ) );

		} else {
			// check transaction (checking/savings)

			// <CustomerCheckWalletAddRq>
			$this->startElement( 'CustomerCheckWalletAddRq' );

			$this->writeElement( 'CustomerID', $order->customer_id );

			$this->writeElement( 'AccountNumber', $order->payment->account_number );
			$this->writeElement( 'RoutingNumber', $order->payment->routing_number );

			// <PersonalPaymentInfo>
			$this->startElement( 'PersonalPaymentInfo' );

			$this->writeElement( 'PersonalDebitAccountType', 'savings' == $order->payment->account_type ? 'Savings' : 'Checking' ); // Checking/Savings
			$this->writeElement( 'PayorFirstName',           SV_WC_Helper::str_to_ascii( $order->billing_first_name ) );
			$this->writeElement( 'PayorLastName',            SV_WC_Helper::str_to_ascii( $order->billing_last_name ) );

			// </PersonalPaymentInfo>
			$this->endElement();

			$this->writeElement( 'PayorPhoneNumber', SV_WC_Helper::str_to_ascii( $order->billing_phone ) );

		}

		// </CustomerCreditCardWalletAdRq> | </CustomerCheckWalletAddRq>
		$this->endElement();

		// </QBMSXMLMsgsRq>
		$this->endElement();

		$this->close_document();

	}


	/**
	 * Creates the wallet delete request for the identified user/token
	 *
	 * @since 1.0
	 * @param string $token the token
	 * @param string $customer_id unique Intuit QBMS customer identifier
	 */
	public function wallet_del( $token, $customer_id ) {

		$this->init_document();

		// <QBMSXMLMsgsRq>
		$this->startElement( 'QBMSXMLMsgsRq' );

		// credit card (or in the future check with CustomerCheckWalletDelRq)
		$request_type = 'CustomerCreditCardWalletDelRq';

		// <CustomerCreditCardWalletDelRq> | <CustomerCheckWalletDel>
		$this->startElement( $request_type );

		$this->writeElement( 'WalletEntryID', $token );
		$this->writeElement( 'CustomerID',    $customer_id );

		// </CustomerCreditCardWalletDelRq> | </CustomerCheckWalletDelRq>
		$this->endElement();

		// </QBMSXMLMsgsRq>
		$this->endElement();

		$this->close_document();

	}


	/**
	 * Performs a wallet query to return matching tokenized payment methods
	 *
	 * Credit card transactions only, at least as of version 4.5 of the Intuit QBMS API
	 *
	 * @since 1.0
	 * @param string $customer_id unique Intuit QBMS customer identifier
	 */
	public function wallet_query( $customer_id ) {

		$this->init_document();

		// <QBMSXMLMsgsRq>
		$this->startElement( 'QBMSXMLMsgsRq' );

		// <CustomerCreditCardWalletQueryRq>
		$this->startElement( 'CustomerCreditCardWalletQueryRq' );

		// <RelativeExpr>
		$this->startElement( 'RelativeExpr' );

		// CustomerID = $customer_id
		$this->writeElement( 'RelativeOp', 'Equals' );
		$this->writeElement( 'Name',       'CustomerID' );
		$this->writeElement( 'Value',      $customer_id );

		// <RelativeExpr>
		$this->endElement();

		// </CustomerCreditCardWalletQueryRq>
		$this->endElement();

		// </QBMSXMLMsgsRq>
		$this->endElement();

		$this->close_document();

	}


	/**
	 * MerchantAccountQuery is used to query information about the current
	 * merchant account. The query returns with the credit card types (Visa,
	 * MasterCard, Discover, AmericanExpress, JCB, DinersClub) that the
	 * merchant account accepts. (That is, there will be a separate
	 * CreditCardType element in the response for each supported card.) If the
	 * merchant account has a set convenience fee value, the ConvenienceFees
	 * element will also be returned in the response.
	 *
	 * If the merchant account cannot be identified or is not subscribed to
	 * QBMS, the status code 10202 and the status message "An error occurred
	 * during account validation" are returned.
	 *
	 * Note: Convenience fee based accounts are accounts that charge customers
	 * a fixed fee per transaction regardless of the size of the transaction,
	 * for the convenience of using a credit card.
	 *
	 * @since 1.0
	 */
	public function merchant_account_query() {

		$this->init_document();

		// <QBMSXMLMsgsRq>
		$this->startElement( 'QBMSXMLMsgsRq' );

		// <MerchantAccountQueryRq />
		$this->writeElement( 'MerchantAccountQueryRq' );

		// </QBMSXMLMsgsRq>
		$this->endElement();

		$this->close_document();

	}


	/**
	 * Helper to return completed XML document
	 *
	 * @since 1.0
	 * @return string XML
	 */
	public function to_xml() {
		return $this->request_xml;
	}


	/**
	 * Returns the string representation of this request
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway_API_Request::to_string()
	 * @return string request XML
	 */
	public function to_string() {

		$string = $this->to_xml();

		$dom = new DOMDocument();

		// suppress errors for invalid XML syntax issues
		if ( @$dom->loadXML( $string ) ) {
			$dom->formatOutput = true;
			$string = $dom->saveXML();
		}

		return $string;

	}


	/**
	 * Returns the string representation of this request with any and all
	 * sensitive elements masked or removed
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway_API_Request::to_string_safe()
	 * @return string the request XML, safe for logging/displaying
	 */
	public function to_string_safe() {

		$request = $this->to_string();

		// replace merchant authentication
		if ( preg_match( '/<ConnectionTicket>(.*)<\/ConnectionTicket>/', $request, $matches ) ) {
			$request = preg_replace( '/<ConnectionTicket>.*<\/ConnectionTicket>/', '<ConnectionTicket>' . str_repeat( '*', strlen( $matches[1] ) ) . '</ConnectionTicket>', $request );
		}

		// replace real card number
		if ( preg_match( '/<CreditCardNumber>([0-9]*)<\/CreditCardNumber>/', $request, $matches ) ) {
			$request = preg_replace( '/<CreditCardNumber>[0-9]*<\/CreditCardNumber>/', '<CreditCardNumber>' . str_repeat( '*', strlen( $matches[1] ) - 4 ) . substr( $matches[1], -4 ) . '</CreditCardNumber>', $request );
		}

		// replace real CSC code
		$request = preg_replace( '/<CardSecurityCode>[0-9]*<\/CardSecurityCode>/', '<CardSecurityCode>***</CardSecurityCode>', $request );

		return $request;

	}


	/** Helper Methods ******************************************************/


	/**
	 * Initialize the document by opening memory, adding doc encoding, qbmsxml
	 * version, opening the QBMSXML root element, and adding the auth element
	 *
	 * @since 1.0
	 */
	private function init_document() {

		// Create XML document in memory
		$this->openMemory();

		// Set XML version & encoding
		$this->startDocument( '1.0', 'UTF-8' );

		// include the current qbmsxml version number
		$this->writePi( 'qbmsxml', 'version="' . self::QBMSXML_VERSION . '"' );

		// root element <QBMSXML>
		$this->startElement( 'QBMSXML' );

		// add the common authentication element
		$this->add_auth_element( $this->application_login, $this->application_id, $this->connection_ticket );

	}


	/**
	 * Closes the XML document and saves the request XML
	 *
	 * @since 1.0
	 */
	private function close_document() {

		// </QBMSXML>
		$this->endElement();

		$this->endDocument();

		// save the request xml
		$this->request_xml = $this->outputMemory();

	}


	/**
	 * Adds the authentication information to the request
	 *
	 * @since 1.0
	 * @param string $application_login application login value
	 * @param string $application_id application id value
	 * @param string $connection_ticket connection ticket value
	 */
	private function add_auth_element( $application_login, $application_id, $connection_ticket ) {

		// root auth element <SignonMsgsRq>
		$this->startElement( 'SignonMsgsRq' );

		// <SignonDesktopRq>
		$this->startElement( 'SignonDesktopRq' );

		$this->writeElement( 'ClientDateTime',   gmdate( 'Y-m-d\TH:i:s' ) );
		$this->writeElement( 'ApplicationLogin', $application_login );
		$this->writeElement( 'ConnectionTicket', $connection_ticket );
		$this->writeElement( 'Language',         'English' );
		$this->writeElement( 'AppID',            $application_id );
		$this->writeElement( 'AppVer',           WC_Intuit_QBMS::VERSION );

		// </SignonDesktopRq>
		$this->endElement();

		// </SignonMsgsRq>
		$this->endElement();

	}


	/**
	 * Add the credit credit card charge or auth elements
	 *
	 * Note: It's important that these elements appear in the expected order,
	 * otherwise there will be parsing errors returned from the QBMS API
	 *
	 * @since 1.0
	 * @param string $request_type one of CustomerCreditCardChargeRq or CustomerCreditCardAuthRq
	 * @param WC_Order $order the order object
	 */
	private function credit_card_charge_auth_request( $request_type, $order ) {

		// store the order object for later use
		$this->order = $order;

		$this->init_document();

		// <QBMSXMLMsgsRq>
		$this->startElement( 'QBMSXMLMsgsRq' );

		// this is the only difference between cc charge/auth requests
		// <CustomerCreditCardChargeRq>|<CustomerCreditCardAuthRq>
		$this->startElement( $request_type );

		$this->writeElement( 'TransRequestID',   $order->trans_request_id );

		if ( isset( $order->payment->token ) && $order->payment->token ) {

			$this->writeElement( 'WalletEntryID', $order->payment->token );
			$this->writeElement( 'CustomerID',    $order->customer_id );

			$this->writeElement( 'Amount',        $order->payment_total );

			$this->writeElement( 'IsECommerce',   true );

		} else {

			$this->writeElement( 'CreditCardNumber',     substr( $order->payment->account_number, 0, 19 ) );
			$this->writeElement( 'ExpirationMonth',      substr( $order->payment->exp_month, 0, 12 ) );
			$this->writeElement( 'ExpirationYear',       $order->payment->exp_year );

			$this->writeElement( 'IsECommerce',          true );

			$this->writeElement( 'Amount',               $order->payment_total );

			$this->writeElement( 'NameOnCard',           substr( isset( $order->intuit_qbms_test_condition ) ? 'configid=' . $order->intuit_qbms_test_condition : SV_WC_Helper::str_to_ascii( $order->billing_first_name . ' ' . $order->billing_last_name ), 0, 30 ) );
			$this->writeElement( 'CreditCardAddress',    substr( SV_WC_Helper::str_to_ascii( empty( $order->billing_address_2 ) ? $order->billing_address_1 : $order->billing_address_1 . ' ' . $order->billing_address_2 ), 0, 30 ) );
			$this->writeElement( 'CreditCardCity',       substr( SV_WC_Helper::str_to_ascii( $order->billing_city ), 0, 50 ) );
			$this->writeElement( 'CreditCardState',      substr( SV_WC_Helper::str_to_ascii( $order->billing_state ), 0, 20 ) );
			$this->writeElement( 'CreditCardPostalCode', substr( SV_WC_Helper::str_to_ascii( str_replace( '-', '', $order->billing_postcode ) ), 0, 9 ) );

		}

		$this->writeElement( 'SalesTaxAmount',       number_format( $order->get_total_tax(), 2, '.', '' ) );

		if ( isset( $order->payment->csc ) ) {
			$this->writeElement( 'CardSecurityCode', substr( $order->payment->csc, 0, 4 ) );
		}

		$this->writeElement( 'InvoiceID', substr( SV_WC_Helper::str_to_ascii( ltrim( $order->get_order_number(), _x( '#', 'hash before order number', 'woocommerce-gateway-intuit-qbms' ) ) ), 0, 100 ) );

		// customer identifier for non-guest transactions
		if ( isset( $order->customer_id ) && $order->customer_id ) {
			$this->writeElement( 'UserID', substr( $order->customer_id, 0, 100 ) );
		}

		// Set the request comment to the order note by default
		$comment = $order->customer_note;

		/**
		 * Filter the request comment sent to Intuit.
		 *
		 * @since 1.7.2
		 * @param string $comment The request comment.
		 * @param object $order The WooCommerce order.
		 */
		$comment = (string) apply_filters( 'wc_payment_gateway_intuit_qbms_credit_card_request_comment', $comment, $order );

		// Optionally add the comment to the request
		if ( $comment ) {

			// Format the comment for the API
			$comment = SV_WC_Helper::str_truncate( SV_WC_Helper::str_to_ascii( $comment ), 4000 );

			$this->writeElement( 'Comment', $comment );
		}

		// root element <GeoLocationInfo>
		$this->startElement( 'GeoLocationInfo' );

		$this->writeElement( 'IPAddress', substr( $_SERVER['REMOTE_ADDR'], 0, 15 ) );

		// </GeoLocationInfo>
		$this->endElement();

		// </CustomerCreditCardChargeRq> | </CustomerCreditCardAuthRq>
		$this->endElement();

		// </QBMSXMLMsgsRq>
		$this->endElement();

		$this->close_document();

	}


	/**
	 * Returns the method for this request. Intuit QBMS uses the API default
	 * (POST)
	 *
	 * @since 1.7.0
	 * @return null
	 */
	public function get_method() { }


	/**
	 * Returns the request path for this request. Intuit QBMS request paths
	 * do not vary per request.
	 *
	 * @since 1.7.0
	 * @return string
	 */
	public function get_path() {
		return '';
	}


	/**
	 * Returns the order associated with this request, if there was one
	 *
	 * @since 1.7.0
	 * @return WC_Order the order object
	 */
	public function get_order() {

		return $this->order;

	}


}

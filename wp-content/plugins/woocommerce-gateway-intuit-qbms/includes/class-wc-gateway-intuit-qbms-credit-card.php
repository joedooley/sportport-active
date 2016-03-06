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
 * @package   WC-Intuit-QBMS/Gateway
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Intuit QBMS Credit Card Payment Gateway
 *
 * Handles all purchases, displaying saved cards, etc
 *
 * This is a direct credit card gateway that supports card types, charge,
 * authorization, tokenization, subscriptions and pre-orders.
 *
 * This gateway modifies the standard framework payment gateway with the
 * addition of a "Require Card Verification" option which requires the CSC
 * even for tokenized transactions (to support Intuit QBMS strict Fraud setting
 * option).  With "Require Card Verification" enabled subscription/pre-order
 * support is sacrified.
 *
 * @since 1.0
 */
class WC_Gateway_Intuit_QBMS_Credit_Card extends WC_Gateway_Intuit_QBMS {


	/** @var string whether CSC is required for *all* (including tokenized) transactions, 'yes' or 'no' */
	protected $require_csc;


	/**
	 * Initialize the gateway
	 *
	 * @since 1.0
	 */
	public function __construct() {

		parent::__construct(
			WC_Intuit_QBMS::CREDIT_CARD_GATEWAY_ID,
			wc_intuit_qbms(),
			array(
				'method_title'       => __( 'Intuit QBMS', 'woocommerce-gateway-intuit-qbms' ),
				'method_description' => __( 'Allow customers to securely pay using their credit cards with Intuit QBMS.', 'woocommerce-gateway-intuit-qbms' ),
				'supports'           => array(
					self::FEATURE_PRODUCTS,
					self::FEATURE_CARD_TYPES,
					self::FEATURE_TOKENIZATION,
					self::FEATURE_CREDIT_CARD_CHARGE,
					self::FEATURE_CREDIT_CARD_AUTHORIZATION,
					self::FEATURE_CREDIT_CARD_CAPTURE,
					self::FEATURE_DETAILED_CUSTOMER_DECLINE_MESSAGES,
					self::FEATURE_CUSTOMER_ID,
				 ),
				'payment_type'       => 'credit-card',
				'environments'       => array( 'production' => __( 'Production', 'woocommerce-gateway-intuit-qbms' ), 'test' => __( 'Test', 'woocommerce-gateway-intuit-qbms' ) ),
				// 'shared_settings'    => $this->shared_settings_names, // Commented out until/if QBMS really supports echeck
			)
		);
	}


	/**
	 * Adds the enable Card Security Code form fields.  Intuit QBMS has a
	 * stringent CSC fraud setting which when enabled will decline any
	 * credit card transactions that are missing the CSC, including tokenized
	 * transaction.  Since not everyone will necessarily have that enabled,
	 * we add a "Require CSC" form field for those who do.
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway::add_csc_form_fields()
	 * @param array $form_fields gateway form fields
	 * @return array $form_fields gateway form fields
	 */
	protected function add_csc_form_fields( $form_fields ) {

		$form_fields = parent::add_csc_form_fields( $form_fields );

		$form_fields['require_csc'] = array(
			'title'    => __( 'Require Card Verification', 'woocommerce-gateway-intuit-qbms' ),
			'label'    => __( 'Require the Card Security Code (CV2) for all transactions', 'woocommerce-gateway-intuit-qbms' ),
			'desc_tip' => __( 'Enabling this field will require the CSC even for tokenized transactions, and will disable support for WooCommerce Subscriptions and WooCommerce Pre-Orders.  Enable this if you have configured your merchant settings to Reject Transaction if CSC is not available.', 'woocommerce-gateway-intuit-qbms' ),
			'type'     => 'checkbox',
			'default'  => 'no',
		);

		return $form_fields;

	}


	/**
	 * Display settings page with some additional javascript for hiding
	 * conditional fields.  The "Require CSC" field will be shown only when
	 * the "Enable CSC" and "Tokenization" are enabled
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway::admin_options()
	 */
	public function admin_options() {

		parent::admin_options();

		// add inline javascript to show the "require csc" field when the "enable csc" and "tokenization" fields are both checked
		ob_start();
		?>
			$( '#woocommerce_<?php echo $this->get_id(); ?>_enable_csc, #woocommerce_<?php echo $this->get_id(); ?>_tokenization' ).change( function() {

				if ( $( '#woocommerce_<?php echo $this->get_id(); ?>_enable_csc' ).is( ':checked' ) && $( '#woocommerce_<?php echo $this->get_id(); ?>_tokenization' ).is( ':checked' ) ) {
					$( '#woocommerce_<?php echo $this->get_id(); ?>_require_csc' ).closest( 'tr' ).show();
				} else {
					$( '#woocommerce_<?php echo $this->get_id(); ?>_require_csc' ).closest( 'tr' ).hide();
				}

			} ).change();
		<?php

		wc_enqueue_js( ob_get_clean() );

	}


	/**
	 * Returns an array of javascript script params to localize for the
	 * checkout/pay page javascript.  Mostly used for i18n purposes
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway::get_js_localize_script_params()
	 * @return array associative array of param name to value
	 */
	protected function get_js_localize_script_params() {

		// add the "require_csc" param, which is needed to properly handle the checkout page tokenization logic
		return array_merge( parent::get_js_localize_script_params(), array( 'require_csc' => $this->csc_required() ) );

	}


	/**
	 * Validate the payment fields when processing the checkout
	 *
	 * @since 1.0
	 * @see WC_Payment_Gateway::validate_fields()
	 * @return bool true if fields are valid, false otherwise
	 */
	public function validate_fields() {

		$is_valid = parent::validate_fields();

		// tokenized transaction with CSC required
		if ( $this->supports_tokenization() && SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-payment-token' ) && $this->csc_required() ) {

			$csc = SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-csc' );
			$is_valid = $this->validate_csc( $csc ) && $is_valid;

		}

		return $is_valid;
	}


	/**
	 * Display the payment fields on the checkout page
	 *
	 * @since 1.0
	 * @see WC_Payment_Gateway::payment_fields()
	 */
	public function payment_fields() {

		woocommerce_intuit_qbms_payment_fields( $this );

	}


	/**
	 * Returns true if tokenization takes place prior authorization/charge
	 * transaction.
	 *
	 * Defaults to false but can be overridden by child gateway class
	 *
	 * @since 1.2.1-1
	 * @see SV_WC_Payment_Gateway_Direct::tokenize_before_sale()
	 * @return boolean true if there is a tokenization request that is issued
	 *         before a authorization/charge transaction
	 */
	public function tokenize_before_sale() {
		return true;
	}


	/**
	 * Add any Intuit QBMS specific payment and transaction information as
	 * class members of WC_Order instance.  Added members can include:
	 *
	 * $order->trans_request_id           - an application-supplied value that identifies the transaction
	 * $order->intuit_qbms_test_condition - a convenience for testing error conditions while in test mode
	 *
	 * @since 1.0
	 * @see WC_Gateway_Intuit_QBMS::get_order()
	 * @param int $order_id order ID being processed
	 * @return WC_Order object with payment and transaction information attached
	 */
	public function get_order( $order_id ) {

		// add common order members
		$order = parent::get_order( $order_id );

		// add intuit credit card-specific order members

		// a convenience for testing error conditions while in test mode, this is passed as the NameOnCard
		if ( $this->is_environment( 'test' ) && SV_WC_Helper::get_post( 'wc-intuit-qbms-test-condition' ) ) {
			$order->intuit_qbms_test_condition = SV_WC_Helper::get_post( 'wc-intuit-qbms-test-condition' );
		}

		return $order;
	}


	/**
	 * Adds any gateway-specific transaction data to the order
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway::add_payment_gateway_transaction_data()
	 * @param WC_Order $order the order object
	 * @param WC_Intuit_QBMS_API_Response $response the transaction response
	 */
	public function add_payment_gateway_transaction_data( $order, $response ) {

		// transaction results (CustomerCreditCardWalletAuthRs doesn't return a client trans id)
		if ( $response->get_client_trans_id() ) {
			$this->update_order_meta( $order->id, 'client_trans_id',                $response->get_client_trans_id() );
		}

		if ( $this->perform_credit_card_charge() ) {
			// performing a cc charge returns a number of more response elements than a simple auth
			$this->update_order_meta( $order->id, 'merchant_account_number', $response->get_merchant_account_number() );
			$this->update_order_meta( $order->id, 'recon_batch_id',          $response->get_recon_batch_id() );
			$this->update_order_meta( $order->id, 'payment_grouping_code',   $response->get_payment_grouping_code() );
			$this->update_order_meta( $order->id, 'txn_authorization_stamp', $response->get_txn_authorization_stamp() );
		}

	}


	/**
	 * Add payment and transaction information as class members of WC_Order
	 * instance for use in credit card capture transactions.  Standard information
	 * can include:
	 *
	 * $order->trans_request_id - an application-supplied value that identifies the transaction
	 *
	 * @since 1.1
	 * @see SV_WC_Payment_Gateway_Direct::get_order_for_capture()
	 * @param int $order_id order ID being processed
	 * @return WC_Order object with payment and transaction information attached
	 */
	protected function get_order_for_capture( $order ) {

		$order = parent::get_order_for_capture( $order );

		// this is used to identify the transaction and prevent duplicate transactions
		//  as might occur during a network outage.  Not really making use of this at
		//  the moment since there's no real way to test.  For further info:
		//  https://developer.intuit.com/docs/030_qbms/0060_documentation/error_handling#QBMS_Error_Recovery
		$order->trans_request_id = $order->id . '-' . rand();

		return $order;
	}


	/**
	 * Handle authorization errors
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway_Direct::do_credit_card_capture()
	 * @param $order WC_Order the order
	 * @return SV_WC_Payment_Gateway_API_Response the response of the capture attempt
	 */
	public function do_credit_card_capture( $order ) {

		$response = parent::do_credit_card_capture( $order );

		if ( $response && ! $response->transaction_approved() && '10406' == $response->get_status_code() ) {
			// mark the capture as invalid
			$this->update_order_meta( $order->id, 'auth_can_be_captured', 'no' );
		}

		return $response;
	}


	/**
	 * Adds any gateway-specific data to the order after a capture is performed
	 *
	 * @since 1.1
	 * @see SV_WC_Payment_Gateway_Direct::add_payment_gateway_capture_data()
	 * @param WC_Order $order the order object
	 * @param WC_Intuit_QBMS_API_Credit_Card_Capture_Response $response the transaction response
	 */
	protected function add_payment_gateway_capture_data( $order, $response ) {

		// capture fields
		$this->update_order_meta( $order->id, 'merchant_account_number', $response->get_merchant_account_number() );
		$this->update_order_meta( $order->id, 'recon_batch_id',          $response->get_recon_batch_id() );
		$this->update_order_meta( $order->id, 'payment_grouping_code',   $response->get_payment_grouping_code() );
		$this->update_order_meta( $order->id, 'txn_authorization_stamp', $response->get_txn_authorization_stamp() );

		$this->update_order_meta( $order->id, 'capture_trans_id',           $response->get_transaction_id() );
		$this->update_order_meta( $order->id, 'capture_authorization_code', $response->get_authorization_code() );
		$this->update_order_meta( $order->id, 'capture_client_trans_id',    $response->get_client_trans_id() );

	}


	/** Subscriptions/Pre-Orders **********************************************/


	/**
	 * Tweak the labels shown when editing the payment method for a Subscription
	 *
	 * @hooked from SV_WC_Payment_Gateway_Integration_Subscriptions
	 *
	 * @since 1.7.1
	 * @see SV_WC_Payment_Gateway_Integration_Subscriptions::admin_add_payment_meta()
	 * @param array $meta payment meta
	 * @param \WC_Subscription $subscription subscription being edited, unused
	 * @return array
	 */
	public function subscriptions_admin_add_payment_meta( $meta, $subscription ) {

		if ( isset( $meta[ $this->get_id() ] ) ) {

			$meta[ $this->get_id() ]['post_meta'][ $this->get_order_meta_prefix() . 'payment_token' ]['label'] = __( 'Wallet Token ID', 'woocommerce-gateway-intuit-qbms' );
		}

		return $meta;
	}


	/**
	 * Validate the payment meta for a Subscription by ensuring the wallet token
	 * ID is numeric
	 *
	 * @since 1.7.1
	 * @see SV_WC_Payment_Gateway_Integration_Subscriptions::admin_validate_payment_meta()
	 * @param array $meta payment meta
	 * @throws \Exception if payment profile/customer profile IDs are not numeric
	 */
	public function subscriptions_admin_validate_payment_meta( $meta ) {

		// wallet token ID (payment_token) must be numeric
		if ( ! ctype_digit( (string) $meta['post_meta'][ $this->get_order_meta_prefix() . 'payment_token' ]['value'] ) ) {
			throw new Exception( __( 'Wallet Token ID must be numeric.', 'woocommerce-gateway-intuit-qbms' ) );
		}
	}


	/**
	 * Returns meta keys to be excluded when copying over meta data when:
	 *
	 * + a renewal order is created from a subscription
	 * + the user changes their payment method for a subscription
	 * + processing the upgrade from Subscriptions 1.5.x to 2.0.x
	 *
	 * @since 1.7.1
	 * @param array $meta_keys
	 * @return array
	 */
	public function subscriptions_get_excluded_order_meta_keys( $meta_keys ) {

		$meta_keys[] = $this->get_order_meta_prefix() . 'merchant_account_number';
		$meta_keys[] = $this->get_order_meta_prefix() . 'recon_batch_id';
		$meta_keys[] = $this->get_order_meta_prefix() . 'payment_grouping_code';
		$meta_keys[] = $this->get_order_meta_prefix() . 'txn_authorization_stamp';
		$meta_keys[] = $this->get_order_meta_prefix() . 'client_trans_id';

		return $meta_keys;
	}


	/**
	 * Returns true if this gateway with its current configuration supports
	 * subscriptions.  Requiring CSC for all transactions removes support for
	 * subscriptions
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway::supports_subscriptions()
	 * @return boolean true if the gateway supports subscriptions
	 */
	public function supports_subscriptions() {

		return parent::supports_subscriptions() && ! $this->csc_required();
	}


	/**
	 * Returns true if this gateway with its current configuration supports
	 * pre-orders.  Requiring CSC for all transactions removes support for
	 * pre-orders
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway::supports_pre_orders()
	 * @return boolean true if the gateway supports pre-orders
	 */
	public function supports_pre_orders() {

		return parent::supports_pre_orders() && ! $this->csc_required();
	}


	/**
	 * Returns true if the CSC is required for all transactions, including
	 * tokenized
	 *
	 * @since 1.0
	 * @return boolean true if the CSC is required for all transactions, even tokenized
	 */
	public function csc_required() {
		return $this->csc_enabled() && 'yes' == $this->require_csc;
	}


}

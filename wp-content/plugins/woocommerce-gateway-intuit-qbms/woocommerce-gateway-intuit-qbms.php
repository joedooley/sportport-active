<?php
/**
 * Plugin Name: WooCommerce Intuit QBMS Gateway
 * Plugin URI: http://www.woothemes.com/products/intuit-qbms/
 * Description: Accept credit cards in WooCommerce with the Intuit QBMS gateway
 * Author: WooThemes / SkyVerge
 * Author URI: http://www.woothemes.com/
 * Version: 1.8.0
 * Text Domain: woocommerce-gateway-intuit-qbms
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2013-2016 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Intuit-QBMS
 * @author    SkyVerge
 * @category  Payment-Gateways
 * @copyright Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Required functions
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'woo-includes/woo-functions.php' );
}

// Plugin updates
woothemes_queue_update( plugin_basename( __FILE__ ), '56ee7c24d725409e3244401ed625b4f3', '272221' );

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

// Required library classss
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'lib/skyverge/woocommerce/class-sv-wc-framework-bootstrap.php' );
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.2.0', __( 'WooCommerce Intuit QBMS Gateway', 'woocommerce-gateway-intuit-qbms' ), __FILE__, 'init_woocommerce_gateway_intuit_qbms', array( 'is_payment_gateway' => true, 'minimum_wc_version' => '2.3.6', 'backwards_compatible' => '4.2.0' ) );

function init_woocommerce_gateway_intuit_qbms() {

/**
 * # WooCommerce Intuit QBMS Gateway Main Plugin Class
 *
 * ## Plugin Overview
 *
 * This plugin adds Intuit QBMS as a payment gateway.  This class handles all the
 * non-gateway tasks such as verifying dependencies are met, loading the text
 * domain, etc.  It also loads the Intuit QBMS Gateway when needed now that the
 * gateway is only created on the checkout & settings pages / api hook.  The gateway is
 * also loaded in the following instances:
 *
 * + On the My Account page to display / change saved payment methods
 *
 * ## Features
 *
 * + Credit Card Authorization
 * + Credit Card Charge
 * + Tokenization
 * + WooCommerce Pre-orders Support
 * + WooCommerce Subscriptions Support
 *
 * A note on echeck support: although the code to support echeck transactions
 * is largely completed, Intuit claims that ACH transaction type is not
 * supported by QBMS.  Which doesn't make a whole lot of sense given all the
 * echeck API calls.  For now the code remains, but is not loaded or used.
 *
 * ## Admin Considerations
 *
 * + A user view/edit field is added for the Intuit QBMS customer id so it can easily be viewed/changed by the admin.
 * + A link from the order edit page to view the transaction in the Intuit Merchant Center
 *
 * ## Frontend Considerations
 *
 * Both the payment fields on checkout (and checkout->pay) and the My cards section on the My Account page are template
 * files for easy customization.
 *
 * ## Database
 *
 * ### Global Settings
 *
 * + `woocommerce_intuit_qbms_settings` - the serialized gateway settings array
 *
 * ### Options table
 *
 * + `wc_intuit_qbms_version` - the current plugin version, set on install/upgrade
 *
 * ### Order Meta
 *
 * + `_wc_intuit_qbms_environment` - the environment the transaction was created in, one of 'test' or 'production'
 * + `_wc_intuit_qbms_card_type` - the card type used for the transaction, if known
 * + `_wc_intuit_qbms_account_four` - the last four digits of the card used for the order
 * + `_wc_intuit_qbms_card_expiry_date` - the expiration date of the card used for the order
 * + `_wc_intuit_qbms_customer_id` - the Intuit QBMS customer ID for the order, set only if the customer is logged in/creating an account
 * + `_wc_intuit_qbms_payment_token` - the token for the credit card used for this transaction, set only if the customer is logged in and using a tokenized payment method
 * + `_wc_intuit_qbms_trans_id` - the credit card transaction ID returned by QBMS
 * + `_wc_intuit_qbms_authorization_code` - the authorization code returned by QBMS
 * + `_wc_intuit_qbms_client_trans_id` - the client transaction id returned by QBMS
 * + `_wc_intuit_qbms_merchant_account_number` - the QBMS account number of the merchant who is running the transaction using the customer's credit card, returned by QBMS for charge transactions
 * + `_wc_intuit_qbms_recon_batch_id` - This value is returned by QBMS from the transaction request, and is used internally by the QuickBooks/QBMS Recon (reconcile) feature, returned by QBMS for charge transactions
 * + `_wc_intuit_qbms_payment_grouping_code` - Internal code needed for the QuickBooks reconciliation feature, if integrating with QuickBooks, returned by QBMS for charge transactions
 * + `_wc_intuit_qbms_txn_authorization_stamp` - This value is used to support the credit card transaction Reconcile feature within QuickBooks, returned by QBMS for charge transactions
 * + `_wc_intuit_qbms_retry_count` - A count of the number of transaction attempts (ie failures) so that a unique transaction number can be generated for each request
 *
 * ### User Meta
 * + `_wc_intuit_qbms_customer_id` - production environment Intuit QBMS customer ID for the user
 * + `_wc_intuit_qbms_customer_id_test` - test environment Intuit QBMS customer ID for the user
 * + `_wc_intuit_qbms_payment_tokens` - production environment payment tokens
 * + `_wc_intuit_qbms_payment_tokens_test` - test environment payment tokens
 *
 * @since 1.1
 */
class WC_Intuit_QBMS extends SV_WC_Payment_Gateway_Plugin {


	/** string version number */
	const VERSION = '1.8.0';

	/** @var WC_Intuit_QBMS single instance of this plugin */
	protected static $instance;

	/** string the plugin id */
	const PLUGIN_ID = 'intuit_qbms';

	/** string plugin text domain, DEPRECATED as of 1.8.0 */
	const TEXT_DOMAIN = 'woocommerce-gateway-intuit-qbms';

	/** string the gateway class name */
	const CREDIT_CARD_GATEWAY_CLASS_NAME = 'WC_Gateway_Intuit_QBMS_Credit_Card';

	/** string the gateway id */
	const CREDIT_CARD_GATEWAY_ID = 'intuit_qbms';

	/** string the gateway class name */
	const ECHECK_GATEWAY_CLASS_NAME = 'WC_Gateway_Intuit_QBMS_eCheck';

	/** string the gateway id */
	const ECHECK_GATEWAY_ID = 'intuit_qbms_echeck';


	/**
	 * Setup main plugin class
	 *
	 * @since 1.0
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'gateways' => array(
					self::CREDIT_CARD_GATEWAY_ID => self::CREDIT_CARD_GATEWAY_CLASS_NAME,
					// self::ECHECK_GATEWAY_ID      => self::ECHECK_GATEWAY_CLASS_NAME,  // commented out until/if QBMS really supports echecks
				),
				'dependencies'       => array( 'SimpleXML', 'xmlwriter', 'dom', 'iconv' ),
				'require_ssl'        => true,
				'supports'           => array(
					self::FEATURE_CUSTOMER_ID,
					self::FEATURE_CAPTURE_CHARGE,
					self::FEATURE_MY_PAYMENT_METHODS,
				),
			)
		);

		// Load gateway files after woocommerce is loaded
		add_action( 'sv_wc_framework_plugins_loaded', array( $this, 'includes' ), 11 );

		add_action( 'init', array( $this, 'include_template_functions' ), 25 );

		if ( is_admin() && ! is_ajax() ) {

			// render the "View Transaction" link in the order edit page
			add_action( 'admin_footer-post.php', array( $this, 'render_view_transaction_form' ) );
			add_action( 'admin_init',            array( $this, 'enqueue_order_admin_js' ) );
		}
	}


	/**
	 * Load plugin text domain.
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway_Plugin::load_translation()
	 */
	public function load_translation() {

		load_plugin_textdomain( 'woocommerce-gateway-intuit-qbms', false, dirname( plugin_basename( $this->get_file() ) ) . '/i18n/languages' );
	}


	/**
	 * Loads any required files
	 *
	 * @since 1.0
	 */
	public function includes() {

		$plugin_path = $this->get_plugin_path();

		// gateway classes
		require_once( $plugin_path . '/includes/class-wc-gateway-intuit-qbms.php' );
		require_once( $plugin_path . '/includes/class-wc-gateway-intuit-qbms-credit-card.php' );
		// require_once( 'includes/class-wc-gateway-intuit-qbms-echeck.php' );  // commented out until/if QBMS really supports echecks

		// token class
		require_once( $plugin_path . '/includes/class-wc-intuit-qbms-payment-token.php' );
	}


	/**
	 * Function used to init any gateway template functions,
	 * making them pluggable by plugins and themes.
	 *
	 * @since 1.0
	 */
	public function include_template_functions() {

		require_once( $this->get_plugin_path() . '/includes/wc-gateway-intuit-qbms-template.php' );
	}


	/** Admin methods ******************************************************/


	/**
	 * Render the javascript snippet to submit the QBMS search form for the
	 * "View Transaction" button/order number link
	 *
	 * @since 1.4.0
	 */
	public function enqueue_order_admin_js() {

		if ( ! isset( $_GET['post'] ) ) {
			return;
		}

		// $typenow/$post globals aren't yet available by this action
		$post = get_post( $_GET['post'] );
		$typenow = $post->post_type;

		// bail if not on the shop_order admin page
		if ( 'shop_order' !== $typenow ) {
			return;
		}

		$order = wc_get_order( $post->ID );

		// bail if a different payment method was used
		if ( WC_Intuit_QBMS::CREDIT_CARD_GATEWAY_ID !== $order->payment_method ) {
			return;
		}

		wc_enqueue_js( '$( ".order_number a" ).click( function() { $( "#js-wc-' . $this->get_id_dasherized() . '-transaction-search" ).submit(); return false; } );' );
	}


	/**
	 * Renders the hidden "View Transaction" form on the edit shop order admin page,
	 * to post to the Intuit QBMS Merchant Center to view the transaction.
	 * There's no URL query string that will display a transaction for you, but we
	 * can search for the transaction and find it for the user.
	 *
	 * @since 1.0
	 * @see WC_Gateway_Intuit_QBMS::get_transaction_url()
	 */
	public function render_view_transaction_form() {

		global $typenow, $post;

		// bail if not on the shop_order admin page
		if ( 'shop_order' !== $typenow ) {
			return;
		}

		$order = wc_get_order( $post->ID );

		// bail if a different payment method was used
		if ( WC_Intuit_QBMS::CREDIT_CARD_GATEWAY_ID !== $order->payment_method ) {
			return;
		}

		// search form parameters
		$transaction_id = get_post_meta( $order->id, '_wc_' . WC_Intuit_QBMS::CREDIT_CARD_GATEWAY_ID . '_credit_card_trans_id', true );
		$date           = date( 'm/d/Y', strtotime( get_post_meta( $order->id, '_paid_date', true ) ) );

		// render a search form to post to the Intuit merchant center to view the transaction with the most open search parameters possible
		?>
		<form id="js-wc-<?php echo $this->get_id_dasherized(); ?>-transaction-search" action="<?php echo esc_url( $this->get_gateway( WC_Intuit_QBMS::CREDIT_CARD_GATEWAY_ID )->get_transaction_url( $order ) ); ?>" method="POST">
			<input type="hidden" name="transactionId"           value="<?php echo $transaction_id; ?>" />
			<input type="hidden" name="Search"                  value="Search" />
			<input type="hidden" name="reportPage"              value="TransactionFlexReporting" />
			<input type="hidden" name="ims.mp.isAdvancedSearch" value="true" />
			<input type="hidden" name="fromDate"                value="<?php echo $date; ?>" />
			<input type="hidden" name="toDate"                  value="<?php echo $date; ?>" />
			<input type="hidden" name="ccTypeVisa"              value="VISA" />
			<input type="hidden" name="ccTypeMC"                value="MC" />
			<input type="hidden" name="ccTypeDisc"              value="DISC" />
			<input type="hidden" name="ccTypeAmex"              value="AMEX" />
			<input type="hidden" name="ccTypeDiners"            value="DINERS" />
			<input type="hidden" name="ccTypeJcb"               value="JCB" />
			<input type="hidden" name="pinDebit"                value="all" />
			<input type="hidden" name="txnTypeA"                value="A" />
			<input type="hidden" name="txnTypeS"                value="S" />
			<input type="hidden" name="txnTypeD"                value="D" />
			<input type="hidden" name="txnTypeC"                value="C" />
			<input type="hidden" name="txnTypeV"                value="V" />
			<input type="hidden" name="response"                value="all" />
			<input type="hidden" name="auto_reject"             value="all" />
			<input type="hidden" name="swiped"                  value="all" />
			<input type="hidden" name="mobilePayment"           value="all" />
			<input type="hidden" name="paypagePayment"          value="all" />
			<input type="hidden" name="recurring"               value="all" />
			<input type="hidden" name="comCard"                 value="all" />
		</form>
		<?php
	}


	/** Helper methods ******************************************************/


	/**
	 * Main Intuit QBMS Instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.6.0
	 * @see wc_intuit_qmbs()
	 * @return WC_Intuit_QBMS
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Gets the plugin documentation URL
	 *
	 * @since 1.7.0
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string
	 */
	public function get_documentation_url() {
		return 'http://docs.woothemes.com/document/woocommerce-intuit-qbms/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 1.7.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {
		return 'http://support.woothemes.com/';
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.0
	 * @see SV_WC_Payment_Gateway::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce Intuit QBMS Gateway', 'woocommerce-gateway-intuit-qbms' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 1.0
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {
		return __FILE__;
	}


} // end WC_Intuit_QBMS


/**
 * Returns the One True Instance of Intuit QBMS
 *
 * @since 1.6.0
 * @return WC_Intuit_QBMS
 */
function wc_intuit_qbms() {
	return WC_Intuit_QBMS::instance();
}

// fire it up!
wc_intuit_qbms();

} // init_woocommerce_gateway_intuit_qbms

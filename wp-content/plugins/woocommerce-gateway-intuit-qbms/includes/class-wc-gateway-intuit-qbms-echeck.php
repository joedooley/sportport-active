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
 * Intuit QBMS eCheck Payment Gateway
 *
 * Handles all purchases, displaying saved accounts, etc
 *
 * This is a direct check gateway that supports tokenization, subscriptions and pre-orders.
 *
 * @since 1.0
 */
class WC_Gateway_Intuit_QBMS_eCheck extends WC_Gateway_Intuit_QBMS {


	/**
	 * Initialize the gateway
	 *
	 * @since 1.0
	 */
	public function __construct() {

		parent::__construct(
			WC_Intuit_QBMS::ECHECK_GATEWAY_ID,
			wc_intuit_qbms(),
			array(
				'method_title'       => __( 'Intuit QBMS eCheck', 'woocommerce-gateway-intuit-qbms' ),
				'method_description' => __( 'Allow customers to securely pay using their checking accounts with Intuit QBMS.', 'woocommerce-gateway-intuit-qbms' ),
				'supports'           => array(
					self::FEATURE_PRODUCTS,
					self::FEATURE_TOKENIZATION,
				 ),
				'payment_type'       => 'echeck',
				'echeck_fields'      => array( 'check_number', 'account_type' ),
				'environments'       => array( 'production' => __( 'Production', 'woocommerce-gateway-intuit-qbms' ), 'test' => __( 'Test', 'woocommerce-gateway-intuit-qbms' ) ),
				'shared_settings'    => $this->shared_settings_names,
			)
		);
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

		// transaction results
		$this->update_order_meta( $order->id, 'authorization_code', $response->get_check_authorization_code() );
		$this->update_order_meta( $order->id, 'client_trans_id',    $response->get_client_trans_id() );

	}


}

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
 * @package   WC-Intuit-QBMS/Templates
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

/**
 * Template Function Overrides
 *
 * @since 1.0
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( ! function_exists( 'woocommerce_intuit_qbms_payment_fields' ) ) {

	/**
	 * Pluggable function to render the checkout page payment fields form
	 *
	 * @since 1.0
	 * @param WC_Gateway_Intuit_QBMS_Credit_Card $gateway gateway object
	 */
	function woocommerce_intuit_qbms_payment_fields( $gateway ) {

		// safely display the description, if there is one
		if ( $gateway->get_description() )
			echo '<p>' . wp_kses_post( $gateway->get_description() ) . '</p>';

		$payment_method_defaults = array(
			'account-number' => '',
			'exp-month'      => '',
			'exp-year'       => '',
			'csc'            => '',
		);

		// for the demo environment, display a notice and supply a default test payment method
		if ( $gateway->is_environment( 'test' ) ) {
			echo '<p>' . __( 'TEST MODE ENABLED', 'woocommerce-gateway-intuit-qbms' ) . '</p>';

			$payment_method_defaults = array(
				'account-number' => '4111111111111111',
				'exp-month'      => '1',
				'exp-year'       => date( 'Y' ) + 1,
				'csc'            => '123',
			);

			// convenience for testing error conditions
			$test_conditions = array(
				'10200_comm'        => __( 'CC Processing Gateway comm error', 'woocommerce-gateway-intuit-qbms' ),
				'10201_login'       => __( 'Processing Gateway login error', 'woocommerce-gateway-intuit-qbms' ),
				'10301_ccinvalid'   => __( 'Invalid CC account number', 'woocommerce-gateway-intuit-qbms' ),
				'10400_insufffunds' => __( 'Insufficient funds', 'woocommerce-gateway-intuit-qbms' ),
				'10401_decline'     => __( 'Transaction declined', 'woocommerce-gateway-intuit-qbms' ),
				'10403_acctinvalid' => __( 'Invalid merchant account', 'woocommerce-gateway-intuit-qbms' ),
				'10404_referral'    => __( 'Declined pending voice auth', 'woocommerce-gateway-intuit-qbms' ),
				'10406_capture'     => __( 'Capture error', 'woocommerce-gateway-intuit-qbms' ),
				'10500_general'     => __( 'General error', 'woocommerce-gateway-intuit-qbms' ),
				'10000_avscvdfail'  => __( 'AVS Failure', 'woocommerce-gateway-intuit-qbms' ),
			);

			echo '<select name="wc-intuit-qbms-test-condition">';
			echo '<option value="">' . __( 'Test an Error Condition:', 'woocommerce-gateway-intuit-qbms' ) . '</option>';
			foreach ( $test_conditions as $key => $value )
				echo '<option value="' . $key . '">' . $value . '</option>';
			echo '</select>';
		}

		// tokenization is allowed if tokenization is enabled on the gateway
		$tokenization_allowed = $gateway->tokenization_enabled();

		// on the pay page there is no way of creating an account, so disallow tokenization for guest customers
		if ( $tokenization_allowed && is_checkout_pay_page() && ! is_user_logged_in() ) {
			$tokenization_allowed = false;
		}

		$tokens = array();
		$default_new_card = true;
		if ( $tokenization_allowed && is_user_logged_in() ) {
			$tokens = $gateway->get_payment_tokens( get_current_user_id() );

			foreach ( $tokens as $token ) {
				if ( $token->is_default() ) {
					$default_new_card = false;
					break;
				}
			}
		}

		// load the payment fields template file
		woocommerce_get_template(
			'checkout/intuit-qbms-payment-fields.php',
			array(
				'payment_method_defaults' => $payment_method_defaults,
				'enable_csc'              => $gateway->csc_enabled(),
				'tokens'                  => $tokens,
				'tokenization_allowed'    => $tokenization_allowed,
				'tokenization_forced'     => $gateway->tokenization_forced(),
				'default_new_card'        => $default_new_card,
			),
			'',
			$gateway->get_plugin()->get_plugin_path() . '/templates/'
		);

	}
}

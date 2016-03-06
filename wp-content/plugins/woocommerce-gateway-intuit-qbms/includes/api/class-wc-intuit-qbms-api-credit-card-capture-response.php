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
 * Intuit QBMS Credit Card Authorization Capture Response
 *
 * Represents a credit card auth capture response
 *
 * @since 1.1
 */
class WC_Intuit_QBMS_API_Credit_Card_Capture_Response extends WC_Intuit_QBMS_API_Credit_Card_Charge_Response {


	/**
	 * Not available for capture response
	 *
	 * @since 1.1
	 * @see SV_WC_Payment_Gateway_API_Authorization_Response::get_avs_result()
	 * @return null
	 */
	public function get_avs_result() {
		return null;
	}


	/**
	 * Not available for capture response
	 *
	 * @since 1.1
	 * @see SV_WC_Payment_Gateway_API_Authorization_Response::get_csc_result()
	 * @return null
	 */
	public function get_csc_result() {
		return null;
	}


	/**
	 * Not available for capture response
	 *
	 * @since 1.1
	 * @see SV_WC_Payment_Gateway_API_Authorization_Response::csc_match()
	 * @return null
	 */
	public function csc_match() {
		return null;
	}


}

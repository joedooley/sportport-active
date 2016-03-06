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
 * @package   WC-Intuit-QBMS/Classes
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WooCommerce Intuit QBMS Payment Token
 *
 * Represents a credit card or check payment token
 *
 * @since 1.0
 */
class WC_Intuit_QBMS_Payment_Token extends SV_WC_Payment_Gateway_Payment_Token {


	/**
	 * Initialize a payment token where $token is a 24 digit globally unique id
	 * provided by QBMS with a predetermined structure used to infer
	 * certain information about the payment method. The WalletEntryID is
	 * structured as follows:
	 *
	 * + 1 digit wallet entry type (1 - Credit Card, 2 - Check)
	 * + 2 digit brand type (01 - Visa, 02 - MasterCard, 03 - Amex, 04 - Discover, 05 - DinersClub, 06 - JCB, 00 - Check)
	 * + 17 digit random number
	 * + Last 4 digits of the credit card or check account number.
	 *
	 * @since 1.0
	 * @param string $token the QBMS Wallet entry ID value
	 * @param array $data associated data
	 */
	public function __construct( $token, $data ) {

		$data['type']      = $this->get_type_from_token( $token );
		$data['last_four'] = $this->get_last_four_from_token( $token );

		if ( 'credit_card' == $data['type'] )
			$data['card_type'] = $this->get_card_type_from_token( $token );


		parent::__construct( $token, $data );
	}


	/**
	 * Returns 'credit_card' or 'check' depending on the wallet type
	 *
	 * @since 1.0
	 * @param string $token payment token
	 * @return string one of 'credit_card' or 'check' depending on the payment type
	 */
	private function get_type_from_token( $token ) {

		return '1' == substr( $token, 0, 1 ) ? 'credit_card' : 'check';

	}


	/**
	 * Returns the payment type (visa, mc, amex, disc, diners, jcb, echeck, etc)
	 *
	 * @since 1.0
	 * @param string $token payment token
	 * @return string the payment type
	 */
	private function get_card_type_from_token( $token ) {

		$type_id = substr( $token, 1, 2 );

		switch ( $type_id ) {

			case '01': return 'visa';
			case '02': return 'mc';
			case '03': return 'amex';
			case '04': return 'disc';
			case '05': return 'diners';
			case '06': return 'jcb';
			case '00': return 'echeck';
			default:   return 'unknown';

		}

	}


	/**
	 * Returns the last four digits of the credit card or check account number
	 *
	 * @since 1.0
	 * @param string $token payment token
	 * @return string last four of account
	 */
	private function get_last_four_from_token( $token ) {
		return substr( $token, -4 );
	}


}

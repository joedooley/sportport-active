<?php
/**
 * WooCommerce Customer/Order CSV Export
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Customer/Order CSV Export to newer
 * versions in the future. If you wish to customize WooCommerce Customer/Order CSV Export for your
 * needs please refer to http://docs.woothemes.com/document/ordercustomer-csv-exporter/
 *
 * @package     WC-Customer-Order-CSV-Export/Export-Methods/HTTP-POST
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2015, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customer/Order CSV Export HTTP POST Class
 *
 * Simple wrapper for wp_remote_post() to POST exported CSV to remote URLs
 *
 * @since 3.0
 */
class WC_Customer_Order_CSV_Export_Method_HTTP_POST implements WC_Customer_Order_CSV_Export_Method {


	/**
	 * Performs an HTTP POST to the specified URL with the CSV
	 *
	 * @since 3.0
	 * @param string $filename unused
	 * @param string $csv the CSV to include the HTTP POST body
	 * @throws Exception WP HTTP error handling
	 */
	public function perform_action( $filename, $csv ) {

		$args = apply_filters( 'wc_customer_order_csv_export_http_post_args', array(
			'timeout'     => 60,
			'redirection' => 0,
			'httpversion' => '1.0',
			'sslverify'   => true,
			'blocking'    => true,
			'headers'     => array(
				'accept'       => 'text/csv',
				'content-type' => 'text/csv'
			),
			'body'        => $csv,
			'cookies'     => array(),
			'user-agent'  => "WordPress " . $GLOBALS['wp_version'],
		) );

		$response = wp_safe_remote_post( get_option( 'wc_customer_order_csv_export_http_post_url' ), $args );

		// check for errors
		if ( is_wp_error( $response ) ) {

			throw new Exception( $response->get_error_message() );
		}

		// log responses
		wc_customer_order_csv_export()->log( print_r( $response, true ) );
	}


} // end \WC_Customer_Order_CSV_Export_Method_HTTP_POST class

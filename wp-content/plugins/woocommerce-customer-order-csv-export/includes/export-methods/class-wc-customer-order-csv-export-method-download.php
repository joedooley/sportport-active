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
 * @package     WC-Customer-Order-CSV-Export/Export-Methods/Download
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2015, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customer/Order CSV Export Method Download
 *
 * Helper class for downloading a CSV via the browser
 *
 * @since 3.0
 */
class WC_Customer_Order_CSV_Export_Method_Download implements WC_Customer_Order_CSV_Export_Method {


	/**
	 * Downloads the CSV via the browser
	 *
	 * @since 3.0
	 * @param string $filename
	 * @param string $csv the CSV to download
	 */
	public function perform_action( $filename, $csv ) {

		// set headers for download
		header( apply_filters( 'wc_customer_order_csv_export_download_content_type', 'Content-Type: text/csv; charset=' . get_option( 'blog_charset' ) ) );
		header( sprintf( 'Content-Disposition: attachment; filename="%s"', $filename ) );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// allow plugins to add additional headers
		do_action( 'wc_customer_order_csv_export_download_after_headers' );

		// clear the output buffer
		@ini_set( 'zlib.output_compression', 'Off' );
		@ini_set( 'output_buffering', 'Off' );
		@ini_set( 'output_handler', '' );

		// open the output buffer for writing
		$fp = fopen( 'php://output', 'w' );

		// write the generated CSV to the output buffer
		fwrite( $fp, $csv );

		// close the output buffer
		fclose( $fp );

		exit;
	}


} // end \WC_Customer_Order_CSV_Export_Method_Download class

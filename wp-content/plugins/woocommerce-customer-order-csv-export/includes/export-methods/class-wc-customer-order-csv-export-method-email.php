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
 * @package     WC-Customer-Order-CSV-Export/Export-Methods/Email
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2015, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customer/Order CSV Export Email Class
 *
 * Helper class for emailing exported CSV
 *
 * @since 3.1
 */
class WC_Customer_Order_CSV_Export_Method_Email implements WC_Customer_Order_CSV_Export_Method {


	/** @var string temporary filename to be deleted */
	private $temp_filename;


	/**
	 * Emails the admin with the exported CSV as an attachment
	 *
	 * @since 3.1
	 * @param string $filename the attachment filename
	 * @param string $csv the CSV to attach to the email
	 */
	public function perform_action( $filename, $csv ) {

		$filename = $this->create_temp_file( $filename, $csv );

		if ( ! empty( $filename ) ) {
			$this->email_export( $filename );
		}

	}


	/**
	 * Create temp file
	 *
	 * @since 3.1
	 * @param string $filename the attachment filename
	 * @param string $file the file to write
	 * @return string $filename
	 */
	private function create_temp_file( $filename, $file ) {

		// prepend the temp directory
		$filename = get_temp_dir() . $filename;

		// create the file
		touch( $filename );

		// open the file, write file, and close it
		$handle = @fopen( $filename, 'w+');
		@fwrite( $handle, $file );
		@fclose( $handle );

		// make sure the temp file is removed after the email is sent
		$this->temp_filename = $filename;
		register_shutdown_function( array( $this, 'unlink_temp_file' ) );

		return $filename;
	}


	/**
	 * Unlink temp file
	 *
	 * @since 3.1
	 */
	public function unlink_temp_file() {

		if ( $this->temp_filename ) {
			@unlink( $this->temp_filename );
		}
	}


	/**
	 * Email the export
	 *
	 * @since 3.1
	 * @param string $filename the attachment filename
	 */
	private function email_export( $filename ) {

		// init email args
		$mailer  = WC()->mailer();
		$to      = ( $email = get_option('wc_customer_order_csv_export_email_recipients') ) ? $email : get_option( 'admin_email' );

		/**
		 * Allow actors to change the email subject used for automated exports.
		 *
		 * @since 3.1.0
		 * @param string the subject as set in the plugin settings
		 */
		$subject = apply_filters( 'wc_customer_order_csv_export_email_subject', get_option( 'wc_customer_order_csv_export_email_subject' ) );

		$message = sprintf( __( 'Order Export for %s', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ), date_i18n( wc_date_format(), current_time( 'timestamp' ) ) );

		/**
		 * Allow actors to change the email headers.
		 *
		 * @since 3.1.0
		 * @param string the message headers
		 * @param string the email ID
		 */
		$headers = apply_filters( 'woocommerce_email_headers', "Content-Type: text/plain\r\n", 'wc_customer_order_csv_export' );

		$attachments = array( $filename );
		$content_type = 'text/plain';

		// send email
		$mailer->send( $to, $subject, $message, $headers, $attachments, $content_type );
	}

} // end \WC_Customer_Order_CSV_Export_Method_Email class

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
 * @package     WC-Customer-Order-CSV-Export/Export-Methods/FTP-Implicit-SSL
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2015, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customer/Order CSV Export FTP over Implicit SSL Class
 *
 * Wrapper for cURL functions to transfer a file over FTP with implicit SSL
 *
 * @since 3.0
 */
class WC_Customer_Order_CSV_Export_Method_FTP_Implicit_SSL extends WC_Customer_Order_CSV_Export_Method_File_Transfer {


	/** @var resource cURL resource handle */
	private $curl_handle;

	/** @var string cURL URL for upload */
	private $url;


	/**
	 * Connect to FTP server over Implicit SSL/TLS
	 *
	 * @since 3.0
	 * @throws Exception
	 * @return \WC_Customer_Order_CSV_Export_Method_FTP_Implicit_SSL
	 */
	public function __construct() {

		parent::__construct();

		// set host/initial path
		$this->url = "ftps://{$this->server}/{$this->path}";

		// setup connection
		$this->curl_handle = curl_init();

		// check for successful connection
		if ( ! $this->curl_handle ) {

			throw new Exception( __( 'Could not initialize cURL.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}

		// connection options
		$options = array(
			CURLOPT_USERPWD        => $this->username . ':' . $this->password,
			CURLOPT_SSL_VERIFYPEER => false, // don't verify SSL
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_FTP_SSL        => CURLFTPSSL_ALL, // require SSL For both control and data connections
			CURLOPT_FTPSSLAUTH     => CURLFTPAUTH_DEFAULT, // let cURL choose the FTP authentication method (either SSL or TLS)
			CURLOPT_UPLOAD         => true,
			CURLOPT_PORT           => $this->port,
			CURLOPT_TIMEOUT        => $this->timeout,
		);

		// cURL FTP enables passive mode by default, so disable it by enabling the PORT command
		if ( ! $this->passive_mode ) {

			$options[ CURLOPT_FTPPORT ] = '-';
		}

		// allow modification of cURL options
		$options = apply_filters( 'wc_customer_order_csv_export_ftp_over_implicit_curl_options', $options, $this );

		// set connection options, use foreach so useful errors can be caught instead of a generic "cannot set options" error with curl_setopt_array()
		foreach ( $options as $option_name => $option_value ) {

			if ( ! curl_setopt( $this->curl_handle, $option_name, $option_value ) ) {

				throw new Exception( sprintf( __( 'Could not set cURL option: %s', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ), $option_name ) );
			}
		}
	}


	/**
	 * Upload the CSV by writing into temporary memory and upload the stream to remote file
	 *
	 * @since 3.0
	 * @param string $filename remote file name to create
	 * @param string $csv CSV content to upload
	 * @throws Exception Open remote file failure or write data failure
	 */
	public function perform_action( $filename, $csv ) {

		// set file name
		if ( ! curl_setopt( $this->curl_handle, CURLOPT_URL, $this->url . $filename ) ) {

			throw new Exception ( "Could not set cURL file name: {$filename}", WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
		}

		// open memory stream for writing
		$stream = fopen( 'php://temp', 'w+' );

		// check for valid stream handle
		if ( ! $stream ) {

			throw new Exception( __( 'Could not open php://temp for writing.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}

		// write CSV into the temporary stream
		fwrite( $stream, $csv );

		// rewind the stream pointer
		rewind( $stream );

		// set the file to be uploaded
		if ( ! curl_setopt( $this->curl_handle, CURLOPT_INFILE, $stream ) ) {

			throw new Exception( __( "Could not load file {$filename}", WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}

		// upload file
		if ( ! curl_exec( $this->curl_handle ) ) {

			throw new Exception( sprintf( __( 'Could not upload file. cURL Error: [%s] - %s', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ), curl_errno( $this->curl_handle ), curl_error( $this->curl_handle ) ) );
		}

		// close the stream handle
		fclose( $stream );
	}


	/**
	 * Attempt to close cURL handle
	 *
	 * @since 3.0
	 */
	public function __destruct() {

		// errors suppressed here as they are not useful
		@curl_close( $this->curl_handle );
	}


} // end \WC_Customer_Order_CSV_Export_FTP_Implicit_SSL class

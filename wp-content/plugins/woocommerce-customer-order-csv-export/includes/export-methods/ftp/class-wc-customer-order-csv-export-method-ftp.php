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
 * @package     WC-Customer-Order-CSV-Export/Export-Methods/FTP
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2015, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customer/Order CSV Export FTP Class
 *
 * Simple wrapper for ftp_* functions to upload a CSV file to a remote server via FTP/FTPS (explicit)
 *
 * @since 3.0
 */
class WC_Customer_Order_CSV_Export_Method_FTP extends WC_Customer_Order_CSV_Export_Method_File_Transfer {


	/** @var resource FTP connection resource */
	private $link;


	/**
	 * Connect to FTP server and authenticate via password
	 *
	 * @since 3.0
	 * @throws Exception
	 * @return \WC_Customer_Order_CSV_Export_Method_FTP
	 */
	public function __construct() {

		parent::__construct();

		// Handle errors from ftp_* functions that throw warnings for things like invalid username / password, failed directory changes, and failed data connections
		set_error_handler( array( $this, 'handle_errors' ) );

		// setup connection
		$this->link = null;

		if ( 'ftps' == $this->security && function_exists( 'ftp_ssl_connect' ) ) {

			$this->link = ftp_ssl_connect( $this->server, $this->port, $this->timeout );

		} elseif ( 'ftps' !== $this->security ) {

			$this->link = ftp_connect( $this->server, $this->port, $this->timeout );
		}

		// check for successful connection
		if ( ! $this->link ) {

			throw new Exception( __( "Could not connect via FTP to {$this->server} on port {$this->port}, check server address and port.", WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}

		// attempt to login, note that incorrect credentials throws an E_WARNING PHP error
		if ( ! ftp_login( $this->link, $this->username, $this->password ) ) {

			throw new Exception( __( "Could not authenticate via FTP with username {$this->username} and password <hidden>. Check username and password.", WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}

		// set passive mode if enabled
		if ( $this->passive_mode ) {

			// check for success
			if ( ! ftp_pasv( $this->link, true ) ) {

				throw new Exception( __( 'Could not set passive mode', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
			}
		}

		// change directories if initial path is populated, note that failing to change directory throws an E_WARNING PHP error
		if ( $this->path ) {

			// check for success
			if ( ! ftp_chdir( $this->link, '/' . $this->path ) ) {

				throw new Exception( __( "Could not change directory to {$this->path} - check path exists.", WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
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

		// upload the stream
		if ( ! ftp_fput( $this->link, $filename, $stream, FTP_ASCII ) ) {

			throw new Exception( __( "Could not upload file: {$filename} - check permissions.", WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}

		// close the stream handle
		fclose( $stream );
	}


	/**
	 * Handle PHP errors during the upload process -- some ftp_* functions throw E_WARNINGS in addition to returning false
	 * when encountering incorrect passwords, etc. Using a custom error handler serves to return helpful messages instead
	 * of "cannot connect" or similar.
	 *
	 * @since 3.0
	 * @param int $error_no unused
	 * @param string $error_string PHP error string
	 * @param string $error_file PHP file where error occurred
	 * @param int $error_line line number of error
	 * @return boolean false
	 * @throws Exception
	 */
	public function handle_errors( $error_no, $error_string, $error_file, $error_line ) {

		// only handle errors for our own files
		if ( false === strpos( $error_file, __FILE__ ) ) {

			return false;
		}

		throw new Exception( sprintf( __( 'FTP error: %s', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ), $error_string ) );
	}


	/**
	 * Attempt to close FTP link
	 *
	 * @since 3.0
	 */
	public function __destruct() {

		if ( $this->link ) {

			// errors suppressed here as they are not useful
			@ftp_close( $this->link );
		}

		// give error handling back to PHP
		restore_error_handler();
	}


} // end \WC_Customer_Order_CSV_Export_FTP class

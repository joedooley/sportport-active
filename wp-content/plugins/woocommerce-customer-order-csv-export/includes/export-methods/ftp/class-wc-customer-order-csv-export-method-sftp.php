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
 * @package     WC-Customer-Order-CSV-Export/Export-Methods/SFTP
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2015, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customer/Order CSV Export SFTP Class
 *
 * Simple wrapper for ssh2_* functions to upload a CSV file to a remote server via FTP over SSH
 *
 * @since 3.0
 */
class WC_Customer_Order_CSV_Export_Method_SFTP extends WC_Customer_Order_CSV_Export_Method_File_Transfer {


	/** @var resource sftp connection resource */
	private $sftp_link;


	/**
	 * Connect to SSH server, authenticate via password, and set up SFTP link
	 *
	 * @since 3.0
	 * @throws Exception - ssh2 extension not installed, failed SSH / SFTP connection, failed authentication
	 * @return \WC_Customer_Order_CSV_Export_Method_SFTP
	 */
	public function __construct() {

		parent::__construct();

		// check if ssh2 extension is installed
		if ( ! function_exists( 'ssh2_connect' ) ) {

			throw new Exception( __( 'SSH2 Extension is not installed, cannot connect via SFTP.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}

		// setup connection
		$this->ssh_link = ssh2_connect( $this->server, $this->port );

		// check for successful connection
		if ( ! $this->ssh_link ) {

			throw new Exception( __( "Could not connect via SSH to {$this->server} on port {$this->port}, check server address and port.", WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}

		// authenticate via password and check for successful authentication
		if ( ! ssh2_auth_password( $this->ssh_link, $this->username, $this->password ) ) {

			throw new Exception( __( "Could not authenticate via SSH with username {$this->username} and password. Check username and password.", WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}

		// setup SFTP link
		$this->sftp_link = ssh2_sftp( $this->ssh_link );

		// check for successful SFTP link
		if ( ! $this->sftp_link ) {

			throw new Exception( __( 'Could not setup SFTP link', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}
	}


	/**
	 * Open remote file and write CSV into it
	 *
	 * @since 3.0
	 * @param string $filename remote filename to create
	 * @param string $csv CSV content to upload
	 * @throws Exception Open remote file failure or write data failure
	 */
	public function perform_action( $filename, $csv ) {

		// open a file on the remote system for writing
		$stream = fopen( "ssh2.sftp://{$this->sftp_link}/{$this->path}{$filename}", 'w+' );

		// check for fopen failure
		if ( ! $stream ) {

			throw new Exception( __( "Could not open remote file: {$filename}.", WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}

		// write CSV to opened remote file
		if ( false === fwrite( $stream, $csv ) ) {

			throw new Exception( __( 'Could not write data from CSV.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}

		// close file
		fclose( $stream );
	}


} // end \WC_Customer_Order_CSV_Export_SFTP class

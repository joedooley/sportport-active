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
 * @package     WC-Customer-Order-CSV-Export/Export-Methods/File-Transfer
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2015, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customer/Order CSV Export File Transfer Class
 *
 * Simple abstract class that handles getting FTP credentials and connection information for
 * all of the FTP methods (FTP, FTPS, FTP over implicit SSL, SFTP)
 *
 * @since 3.0
 */
abstract class WC_Customer_Order_CSV_Export_Method_File_Transfer implements WC_Customer_Order_CSV_Export_Method {


	/** @var string the FTP server address */
	protected $server;

	/** @var string the FTP username */
	protected $username;

	/** @var string the FTP user password*/
	protected $password;

	/** @var string the FTP server port */
	protected $port;

	/** @var string the path to change to after connecting */
	protected $path;

	/** @var string the FTP security type, either `none`, `ftps`, `ftp-ssl`, `sftp` */
	protected $security;

	/** @var bool true to enable passive mode for the FTP connection, false otherwise */
	protected $passive_mode;

	/** @var int the timeout for the FTP connection in seconds */
	protected $timeout;


	/**
	 * Setup FTP information and check for any invalid/missing info
	 *
	 * @since 3.0
	 */
	public function __construct() {

		// set connection info
		$this->server       = get_option( 'wc_customer_order_csv_export_ftp_server' );
		$this->username     = get_option( 'wc_customer_order_csv_export_ftp_username' );
		$this->password     = get_option( 'wc_customer_order_csv_export_ftp_password', '' );
		$this->port         = get_option( 'wc_customer_order_csv_export_ftp_port' );
		$this->path         = get_option( 'wc_customer_order_csv_export_ftp_path', '' );
		$this->security     = get_option( 'wc_customer_order_csv_export_ftp_security' );
		$this->passive_mode = 'yes' === get_option( 'wc_customer_order_csv_export_ftp_passive_mode' );
		$this->timeout      = apply_filters( 'wc_customer_order_csv_export_ftp_timeout', 30 );

		// check for blank username
		if ( ! $this->username ) {

			throw new Exception( __( 'FTP Username is blank.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}

		/* allow blank passwords */

		// check for blank server
		if ( ! $this->server ) {

			throw new Exception( __( 'FTP Server is blank.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}

		// check for blank port
		if ( ! $this->port ) {

			throw new Exception ( __( 'FTP Port is blank.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}
	}


} // end \WC_Customer_Order_CSV_Export_File_Transfer class

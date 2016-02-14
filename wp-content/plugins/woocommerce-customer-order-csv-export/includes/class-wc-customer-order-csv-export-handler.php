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
 * @package     WC-Customer-Order-CSV-Export/Handler
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2015, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customer/Order CSV Export Handler
 *
 * Handles export actions/methods
 *
 * @since 3.0
 */
class WC_Customer_Order_CSV_Export_Handler {


	/** @var array order IDs or customer IDs to export */
	public $ids;

	/** @var string file name for export or download */
	public $filename;


	/**
	 * Initializes the export object from an array of valid order/customer IDs and sets the filename
	 *
	 * @since 3.0
	 * @param int|array $ids orders/customer IDs to export / download
	 * @param string $export_type what is being exported, `orders` or `customers`
	 * @return \WC_Customer_Order_CSV_Export_Handler
	 */
	public function __construct( $ids, $export_type = 'orders' ) {

		// handle single order/customer exports
		if ( ! is_array( $ids ) ) {

			$ids = array( $ids );
		}

		$this->export_type = $export_type;

		$this->ids = $ids;

		// set file name
		$this->filename = $this->replace_filename_variables();

		// instantiate writer here & get CSV
		$this->generator = new WC_Customer_Order_CSV_Export_Generator( $this->ids );
	}


	/**
	 * Exports orders/customers to CSV and downloads via browser
	 *
	 * @since 3.0
	 */
	public function download() {

		$this->export_via( 'download' );
	}


	/**
	 * Exports test CSV and downloads via browser
	 *
	 * @since 3.0
	 */
	public function test_download() {

		$this->test_export_via( 'download' );
	}


	/**
	 * Exports orders/customers to CSV and uploads to remote server
	 *
	 * @since 3.0
	 */
	public function upload() {

		$this->export_via( 'ftp' );
	}


	/**
	 * Exports test CSV and uploads to remote server
	 *
	 * @since 3.0
	 */
	public function test_upload() {

		$this->test_export_via( 'ftp' );
	}


	/**
	 * Exports orders/customers to CSV and HTTP POSTs to remote server
	 *
	 * @since 3.0
	 */
	public function http_post() {

		$this->export_via( 'http_post' );
	}


	/**
	 * Exports test CSV and HTTP POSTs to remote server
	 *
	 * @since 3.0
	 */
	public function test_http_post() {

		$this->test_export_via( 'http_post' );
	}


	/**
	 * Exports orders/customers to CSV and emails admin with CSV as attachment
	 *
	 * @since 3.0
	 */
	public function email() {

		$this->export_via( 'email' );
	}


	/**
	 * Exports test CSV and emails admin with CSV as attachment
	 *
	 * @since 3.0
	 */
	public function test_email() {

		$this->test_export_via( 'email' );
	}


	/**
	 * Exports CSV via the given method
	 *
	 * @since 3.0
	 * @param string $method the export method, `download`, `ftp`, `http_post`, `email`
	 */
	public function export_via( $method ) {

		// try to set unlimited script timeout
		@set_time_limit( 0 );

		try {

			// get method (download, FTP, etc)
			$export = $this->get_export_method( $method );

			if ( ! is_object( $export ) ) {

				throw new Exception( sprintf( __( 'Invalid Export Method: %s', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ), $method ) );
			}

			if ( 'orders' == $this->export_type ) {

				// mark each order as exported
				// this must be done before download, as the download function exits() to prevent additional output from contaminating the CSV file
				$this->mark_orders_as_exported( $method );
			}

			$export->perform_action( $this->filename, 'orders' == $this->export_type ? $this->generator->get_orders_csv() : $this->generator->get_customers_csv() );

		} catch ( Exception $e ) {

			// log errors
			wc_customer_order_csv_export()->log( $e->getMessage() );
		}
	}


	/**
	 * Exports a test CSV via the given method
	 *
	 * @since 3.0
	 * @param string $method the export method
	 * @return string 'Success' or error message
	 */
	public function test_export_via( $method ) {

		// try to set unlimited script timeout
		@set_time_limit( 0 );

		try {

			// get method (download, FTP, etc)
			$export = $this->get_export_method( $method );

			if ( ! is_object( $export ) ) {

				throw new Exception( sprintf( __( 'Invalid Export Method: %s', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ), $method ) );
			}

			// simple test CSV
			$export->perform_action( 'test.csv',"column_1,column_2,column_3\ntest_1,test_2,test_3" );

			return __( 'Test was successful!', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );

		} catch ( Exception $e ) {

			// log errors
			wc_customer_order_csv_export()->log( $e->getMessage() );

			return sprintf( __( 'Test failed: %s', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ), $e->getMessage() );
		}
	}


	/**
	 * Returns the export method object
	 *
	 * @since 3.0
	 * @param string $method the export method, `download`, `ftp`, `http_post`, or `email`
	 * @return object the export method
	 */
	private function get_export_method( $method ) {

		// get the export method specified
		switch ( $method ) {

			case 'download':
				require_once( wc_customer_order_csv_export()->get_plugin_path() . '/includes/export-methods/class-wc-customer-order-csv-export-method-download.php' );
				return new WC_Customer_Order_CSV_Export_Method_Download();

			case 'ftp':
				// abstract FTP class
				require_once( wc_customer_order_csv_export()->get_plugin_path() . '/includes/export-methods/ftp/abstract-wc-customer-order-csv-export-method-file-transfer.php' );

				$security = get_option( 'wc_customer_order_csv_export_ftp_security' );

				switch ( $security ) {

					// FTP over SSH
					case 'sftp' :
						require_once( wc_customer_order_csv_export()->get_plugin_path() . '/includes/export-methods/ftp/class-wc-customer-order-csv-export-method-sftp.php' );
						return new WC_Customer_Order_CSV_Export_Method_SFTP();

					// FTP with Implicit SSL
					case 'ftp_ssl' :
						require_once( wc_customer_order_csv_export()->get_plugin_path() . '/includes/export-methods/ftp/class-wc-customer-order-csv-export-method-ftp-implicit-ssl.php' );
						return new WC_Customer_Order_CSV_Export_Method_FTP_Implicit_SSL();

					// FTP with explicit SSL/TLS *or* regular FTP
					case 'ftps' :
					case 'none' :
						require_once( wc_customer_order_csv_export()->get_plugin_path() . '/includes/export-methods/ftp/class-wc-customer-order-csv-export-method-ftp.php' );
						return new WC_Customer_Order_CSV_Export_Method_FTP();
				}
				break;

			case 'http_post':
				require_once( wc_customer_order_csv_export()->get_plugin_path() . '/includes/export-methods/class-wc-customer-order-csv-export-method-http-post.php' );
				return new WC_Customer_Order_CSV_Export_Method_HTTP_POST();

			case 'email':
				require_once( wc_customer_order_csv_export()->get_plugin_path() . '/includes/export-methods/class-wc-customer-order-csv-export-method-email.php' );
				return new WC_Customer_Order_CSV_Export_Method_Email();

			default:

				/**
				 * CSV Export Get Export Method
				 *
				 * Triggered when getting the export method. This is designed for
				 * custom methods to hook in and load their class so it can be
				 * returned and used.
				 *
				 * @since 3.4.0
				 * @param \WC_Customer_Order_CSV_Export_Handler $this, handler instance
				 */
				do_action( 'wc_customer_order_csv_export_get_export_method', $this );

				$class_name = sprintf( 'WC_Customer_Order_CSV_Export_Custom_Method_%s', ucwords( strtolower( $method ) ) );

				return class_exists( $class_name ) ? new $class_name : null;
		}
	}


	/**
	 * Marks orders as exported by setting the `_wc_customer_order_csv_export_is_exported` order meta flag
	 *
	 * @since 3.0
	 * @param string $method the export method, `download`, `ftp`, `http_post`, or `email`
	 */
	public function mark_orders_as_exported( $method = 'download' ) {

		foreach ( $this->ids as $order_id ) {

			// add exported flag
			update_post_meta( $order_id, '_wc_customer_order_csv_export_is_exported', 1 );

			$order = wc_get_order( $order_id );

			switch ( $method ) {

				// note that order downloads using the AJAX order action are not marked or noted, only bulk order downloads
				case 'download':
					$message = __( 'downloaded.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
					break;

				case 'ftp':
					$message = __( 'uploaded to remote server.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
					break;

				case 'http_post':
					$message = __( 'POSTed to remote server.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
					break;

				case 'email':
					$message = __( 'emailed.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
					break;
			}

			/**
			 * Filter if an order note should be added when an order is successfully exported
			 *
			 * @since 3.9.1
			 * @param bool $add_order_note true if the order note should be added, false otherwise
			 */
			if ( apply_filters( 'wc_customer_order_csv_export_add_order_note', true ) ) {
				$order->add_order_note( sprintf( __( 'Order exported to CSV and successfully %s', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ), $message ) );
			}

			/**
			 * CSV Order Exported Action.
			 *
			 * Fired when an order is automatically exported. Note this includes
			 * orders exported via the Orders bulk action.
			 *
			 * @since 3.1
			 * @param WC_Order $order order being exported
			 * @param string $method how the order is exported (ftp, download, etc)
			 * @param string $message order note message
			 * @param \WC_Customer_Order_CSV_Export_Handler $this, handler instance
			 */
			do_action( 'wc_customer_order_csv_export_order_exported', $order, $method, $message, $this );
		}
	}


	/**
	 * Replaces variables in file name setting (e.g. %%timestamp%% becomes 2013_03_20_16_22_14 )
	 *
	 * @since 3.0
	 * @return string filename with variables replaced
	 */
	private function replace_filename_variables() {

		$pre_replace_filename = get_option( 'orders' == $this->export_type ? 'wc_customer_order_csv_export_order_filename' : 'wc_customer_order_csv_export_customer_filename' );

		$variables   = array( '%%timestamp%%', '%%order_ids%%' );
		$replacement = array( date( 'Y_m_d_H_i_s', current_time( 'timestamp' ) ), implode( '-', $this->ids ) );

		$post_replace_filename = str_replace( $variables, $replacement, $pre_replace_filename );

		return apply_filters( 'wc_customer_order_csv_export_filename', $post_replace_filename, $pre_replace_filename, $this->ids );
	}


} //end \WC_Customer_Order_CSV_Export_Handler class

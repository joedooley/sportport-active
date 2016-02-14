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
 * @package     WC-Customer-Order-CSV-Export/Admin
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2015, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Customer/Order CSV Export Admin Class
 *
 * Loads admin settings page and adds related hooks / filters
 *
 * @since 3.0
 */
class WC_Customer_Order_CSV_Export_Admin {


	/** @var string sub-menu page hook suffix */
	public $page;

	/** @var array tab IDs / titles */
	public $tabs;

	/** @var \SV_WP_Admin_Message_Handler instance */
	public $message_handler;

	/** @var string export start date for bulk customer export */
	public $customer_export_start_date;

	/** @var string export end date for bulk customer export */
	public $customer_export_end_date;


	/**
	 * Setup admin class
	 *
	 * @since 3.0
	 */
	public function __construct() {

		/** General Admin Hooks */

		// Load custom admin styles / scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'load_styles_scripts' ) );

		// Load WC styles / scripts
		add_filter( 'woocommerce_screen_ids', array( $this, 'load_wc_styles_scripts' ) );

		add_action( 'admin_init', array( $this, 'process_export' ) );

		// Add 'CSV Export' link under WooCommerce menu
		add_action( 'admin_menu', array( $this, 'add_menu_link' ) );

		// Render a custom test button when using woocommerce_admin_fields()
		add_action( 'woocommerce_admin_field_csv_test_button', array( $this, 'render_test_button' ) );

		/** Order Hooks */

		// Add 'Export Status' orders page column header
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_order_status_column_header' ), 20 );

		// Add 'Export Status' orders page column content
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'add_order_status_column_content' ) );

		// Add 'Export to CSV' action on orders page
		add_action( 'woocommerce_admin_order_actions_end', array( $this, 'add_order_action' ), 10, 2 );

		// Add 'Export to CSV' order meta box order action
		add_action( 'woocommerce_order_actions',       array( $this, 'add_order_meta_box_actions' ) );

		// Process 'Export to CSV' order meta box order action
		add_action( 'woocommerce_order_action_wc_customer_order_csv_export_download', array( $this, 'process_order_meta_box_actions' ) );

		// Add bulk order filter for exported / non-exported orders
		add_action( 'restrict_manage_posts', array( $this, 'filter_orders_by_export_status') , 20 );
		add_filter( 'request',               array( $this, 'filter_orders_by_export_status_query' ) );

		// Add bulk action to download multiple orders to CSV and mark them as exported / not-exported
		add_action( 'admin_footer-edit.php', array( $this, 'add_order_bulk_actions' ) );
		add_action( 'load-edit.php',         array( $this, 'process_order_bulk_actions' ) );
	}


	/**
	 * Load admin styles & scripts only on needed pages
	 *
	 * @since 3.0
	 * @param $hook_suffix
	 */
	public function load_styles_scripts( $hook_suffix ) {
		global $wp_scripts;

		// only load on settings / view orders pages
		if ( $this->page == $hook_suffix || 'edit.php' == $hook_suffix ) {

			// Admin CSS
			wp_enqueue_style( 'wc-customer-order-csv-export_admin', wc_customer_order_csv_export()->get_plugin_url() . '/assets/css/admin/wc-customer-order-csv-export-admin.min.css', array( 'dashicons' ), WC_Customer_Order_CSV_Export::VERSION );

			// settings/export page
			if ( $this->page == $hook_suffix ) {

				// jQuery Timepicker JS
				wp_enqueue_script( 'wc-customer-order-csv-export-jquery-timepicker', wc_customer_order_csv_export()->get_plugin_url() . '/assets/js/jquery-timepicker/jquery.timepicker.min.js', array(), WC_Customer_Order_CSV_Export::VERSION, true );

				// admin JS
				wp_enqueue_script( 'wc-customer-order-csv-export-admin', wc_customer_order_csv_export()->get_plugin_url() . '/assets/js/admin/wc-customer-order-csv-export-admin.min.js', array(), WC_Customer_Order_CSV_Export::VERSION, true );

				// calendar icon
				wp_localize_script( 'wc-customer-order-csv-export-admin', 'wc_customer_order_csv_export_admin_params', array( 'calendar_icon_url' => WC()->plugin_url() . '/assets/images/calendar.png' ) );

				// datepicker
				wp_enqueue_script( 'jquery-ui-datepicker' );

				// get jQuery UI version
				$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

				// enqueue UI CSS
				wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css' );
			}
		}
	}


	/**
	 * Add settings/export screen ID to the list of pages for WC to load its CSS/JS on
	 *
	 * @since 3.0
	 * @param array $screen_ids
	 * @return array
	 */
	public function load_wc_styles_scripts( $screen_ids ) {

		$screen_ids[] = 'woocommerce_page_wc_customer_order_csv_export';

		return $screen_ids;

	}


	/**
	 * Add 'CSV Export' sub-menu link under 'WooCommerce' top level menu
	 *
	 * @since 3.0
	 */
	public function add_menu_link() {

		$this->page = add_submenu_page(
			'woocommerce',
			__( 'CSV Export', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
			__( 'CSV Export', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
			'manage_woocommerce',
			'wc_customer_order_csv_export',
			array( $this, 'render_submenu_pages' )
		);
	}


	/**
	 * Render the sub-menu page for 'CSV Export'
	 *
	 * @since 3.0
	 */
	public function render_submenu_pages() {

		// permissions check
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$this->tabs = array(
			'export'   => __( 'Export', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
			'settings' => __( 'Settings', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
		);

		$current_tab = ( empty( $_GET[ 'tab' ] ) ) ? 'export' : urldecode( $_GET[ 'tab' ] );

		// settings
		if ( ! empty( $_POST ) && 'settings' == $current_tab ) {

			// security check
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], __FILE__ ) ) {

				wp_die( __( 'Action failed. Please refresh the page and retry.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
			}

			if ( isset( $_POST['wc_customer_order_csv_export_test_method'] ) ) {

				// process test
				$export = new WC_Customer_Order_CSV_Export_Handler( 0 );

				$result = $export->test_export_via( $_POST['wc_customer_order_csv_export_test_method'] );

				$this->message_handler->add_message( $result );

			} else {

				$orig_start_inverval = get_option( 'wc_customer_order_csv_export_auto_export_start_time' ) . get_option( 'wc_customer_order_csv_export_auto_export_interval' );

				// save settings
				woocommerce_update_options( $this->get_settings( 'settings' ) );

				// clear scheduled export event if export interval and/or start time were changed
				if ( $orig_start_inverval !== get_option( 'wc_customer_order_csv_export_auto_export_start_time' ) . get_option( 'wc_customer_order_csv_export_auto_export_interval' ) ) {

					// note this resets the next scheduled execution time to the time options were saved + the interval
					wp_clear_scheduled_hook( 'wc_customer_order_csv_export_auto_export_orders' );
				}

				$this->message_handler->add_message( __( 'Your settings have been saved.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
			}
		}

		?>
		<div class="wrap woocommerce">
		<form method="post" id="mainform" action="" enctype="multipart/form-data">
			<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
				<?php
				foreach ( $this->tabs as $tab_id => $tab_title ) :

					$class = ( $tab_id == $current_tab ) ? array( 'nav-tab', 'nav-tab-active' ) : array( 'nav-tab' );
					$url   = add_query_arg( 'tab', $tab_id, admin_url( 'admin.php?page=wc_customer_order_csv_export' ) );

					printf( '<a href="%s" class="%s">%s</a>', esc_url( $url ), implode( ' ', array_map( 'sanitize_html_class', $class ) ), esc_html( $tab_title ) );

				endforeach;
			?> </h2> <?php

		$this->message_handler->show_messages();

		if ( 'settings' == $current_tab ) {

			$this->render_settings_page();

		} else {

			$this->render_export_page();
		}

		?> </form>
		</div> <?php
	}


	/**
	 * Show Export page
	 *
	 * @since 3.0
	 */
	private function render_export_page() {

		// permissions check
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// show export form
		woocommerce_admin_fields( $this->get_settings( 'export' ) );

		// helper input
		?><input type="hidden" name="wc_customer_order_csv_export_bulk_export" value="1" /><?php

		wp_nonce_field( __FILE__ );
		submit_button( __( 'Export', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
	}


	/**
	 * Process bulk export
	 *
	 * Note this is hooked into `admin_init` as WC 2.1+ interferes with sending headers() from a sub-menu page
	 *
	 * @since 3.0
	 */
	public function process_export() {

		if ( ! isset( $_POST['wc_customer_order_csv_export_bulk_export'] ) || ! isset( $_POST['wc_customer_order_csv_export_type'] ) ) {
			return;
		}

		// security check
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], __FILE__ ) ) {

			wp_die( __( 'Action failed. Please refresh the page and retry.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}

		$export_type = $_POST['wc_customer_order_csv_export_type'];

		$query_args = array(
			'fields'         => 'ids',
			'post_type'      => 'shop_order',
			'post_status'    => ( ! empty( $_POST['wc_customer_order_csv_export_statuses'] ) && 'orders' == $export_type ) ? $_POST['wc_customer_order_csv_export_statuses'] : 'any',
			'posts_per_page' => empty( $_POST['wc_customer_order_csv_export_limit'] )  ? -1 : absint( $_POST['wc_customer_order_csv_export_limit'] ),
			'offset'         => empty( $_POST['wc_customer_order_csv_export_offset'] ) ? 0  : absint( $_POST['wc_customer_order_csv_export_offset'] ),
			'date_query'  => array(
				array(
					'before'    => empty( $_POST['wc_customer_order_csv_export_end_date'] )   ? date( 'Y-m-d 23:59', current_time( 'timestamp' ) )    : $_POST['wc_customer_order_csv_export_end_date'] . ' 23:59:59.99',
					'after'     => empty( $_POST['wc_customer_order_csv_export_start_date'] ) ? date( 'Y-m-d 00:00', 0 ) : $_POST['wc_customer_order_csv_export_start_date'],
					'inclusive' => true,
				),
			),
		);

		// allow offset to be used with "no" posts limit
		if ( $query_args['offset'] > 0 && -1 === $query_args['posts_per_page'] ) {
			$query_args['posts_per_page'] = 999999999999; // a really large number {@link http://dev.mysql.com/doc/refman/5.7/en/select.html#idm140195560794688}
		}

		// set start/end date for customer exports as class variables which are used when performing get_users() in the CSV generator
		if ( 'customers' == $export_type ) {

			$this->customer_export_start_date = str_replace( ' 00:00', '', $query_args['date_query'][0]['after'] );
			$this->customer_export_end_date   = str_replace( ' 23:59', '', $query_args['date_query'][0]['before'] );
		}

		/**
		 * Allow actors to change the WP_Query args used for selecting orders to export in the admin.
		 *
		 * @since 3.0.6
		 * @param array $query_args - WP_Query arguments
		 * @param string $export_type - either `customers` or `orders`
		 * @param \WC_Customer_Order_CSV_Export_Admin $this class instance
		 */
		$query_args = apply_filters( 'wc_customer_order_csv_export_admin_query_args', $query_args, $export_type, $this );

		// get order IDs
		$query = new WP_Query( $query_args );

		$order_ids = $query->posts;

		// handle subscription & renewal order filtering
		if ( wc_customer_order_csv_export()->is_plugin_active( 'woocommerce-subscriptions.php' ) ) {
			$subscriptions = $renewals = array();

			if ( isset( $_POST['wc_customer_order_csv_export_subscription_orders'] ) ) {
				$subscriptions = array_filter( $order_ids, array( 'WC_Subscriptions_Order', 'order_contains_subscription' ) );
			}

			if ( isset( $_POST['wc_customer_order_csv_export_subscription_renewals'] ) ) {
				$renewals = array_filter( $order_ids, array( 'WC_Subscriptions_Renewal_Order', 'is_renewal' ) );
			}

			if ( ! empty( $subscriptions ) || ! empty( $renewals ) ) {
				$order_ids = array_merge( $subscriptions, $renewals );
			}
		}

		if ( count( $order_ids ) ) {

			$export = new WC_Customer_Order_CSV_Export_Handler( $order_ids, $export_type );

			$export->download();

		} else {

			$this->message_handler->add_message( sprintf( __( 'No %s found to export', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ), $export_type ) );
		}
	}


	/**
	 * Show Settings page
	 *
	 * @since 3.0
	 */
	private function render_settings_page() {

		// render settings fields
		woocommerce_admin_fields( $this->get_settings( 'settings' ) );

		wp_nonce_field( __FILE__ );
		submit_button( __( 'Save settings', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
	}


	/**
	 * Adds 'Export Status' column header to 'Orders' page immediately after 'Order Status' column
	 *
	 * @since 3.0
	 * @param array $columns
	 * @return array $new_columns
	 */
	public function add_order_status_column_header( $columns ) {

		$new_columns = array();

		foreach ( $columns as $column_name => $column_info ) {

			$new_columns[ $column_name ] = $column_info;

			if ( 'order_status' == $column_name ) {

				$new_columns['csv_export_status'] = __( 'Export Status', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
			}
		}

		return $new_columns;
	}


	/**
	 * Adds 'Export Status' column content to 'Orders' page immediately after 'Order Status' column
	 *
	 * 'Not Exported' - if 'is_exported' order meta doesn't exist or is equal to 0
	 * 'Exported' - if 'is_exported' order meta exists and is equal to 1
	 *
	 * @since 3.0
	 * @param array $column name of column being displayed
	 */
	public function add_order_status_column_content( $column ) {
		global $post;

		if ( 'csv_export_status' == $column ) {

			$order = wc_get_order( $post->ID );

			$is_exported = false;

			if ( $order->wc_customer_order_csv_export_is_exported ) {

				$is_exported = true;
			}

			printf( '<mark class="%s">%s</mark>', $is_exported ? 'csv_exported' : 'csv_not_exported', $is_exported ? __( 'Exported', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) : __( 'Not Exported', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) );
		}
	}


	/**
	 * Adds 'Download to CSV' order action to 'Order Actions' column
	 *
	 * Processed via AJAX
	 *
	 * @since 3.0
	 * @param WC_Order $order
	 */
	public function add_order_action( $order ) {

		if ( ! $order->wc_customer_order_csv_export_is_exported ) {

			$action = 'export_to_csv';
			$url = wp_nonce_url( admin_url( 'admin-ajax.php?action=wc_customer_order_csv_export_export_order&order_id=' . $order->id ), 'wc_customer_order_csv_export_export_order' );
			$name = __( 'Download to CSV', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );

			printf( '<a class="button tips %s" href="%s" data-tip="%s">%s</a>', $action, esc_url( $url ), $name, $name );
		}

	}


	/**
	 * Add 'Download to CSV' link to order actions select box on edit order page
	 *
	 * @since 3.0
	 * @param array $actions order actions array to display
	 * @return array
	 */
	public function add_order_meta_box_actions( $actions ) {

		// add download to CSV action
		$actions['wc_customer_order_csv_export_download'] = __( 'Download to CSV', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );

		return $actions;
	}


	/**
	 * Process the 'Download to CSV' link in order actions select box on edit order page
	 *
	 * @since 3.0
	 * @param WC_Order $order
	 */
	public function process_order_meta_box_actions( $order ) {

		$export = new WC_Customer_Order_CSV_Export_Handler( $order->id );

		$export->download();
	}


	/**
	 * Add bulk filter for Exported / Un-Exported orders
	 *
	 * @since 3.0
	 */
	public function filter_orders_by_export_status() {
		global $typenow;

		if ( 'shop_order' == $typenow ) :

			$count = $this->get_order_count();

			$terms = array(
				0 => (object) array( 'count' => $count['not_exported'], 'term' => __( 'Not Exported', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) ),
				1 => (object) array( 'count' => $count['exported'], 'term' => __( 'Exported', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ) )
			);

			?>
		<select name="_shop_order_export_status" id="dropdown_shop_order_export_status">
			<option value=""><?php _e( 'Show all orders', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ); ?></option>
			<?php foreach ( $terms as $value => $term ) : ?>
			<option value="<?php echo $value; ?>" <?php echo esc_attr( isset( $_GET['_shop_order_export_status'] ) ? selected( $value, $_GET['_shop_order_export_status'], false ) : '' ); ?>>
				<?php printf( '%s (%s)', $term->term, $term->count ); ?>
			</option>
			<?php endforeach; ?>
		</select>
		<?php

		endif;
	}


	/**
	 * Process bulk filter action for Export / Un-Exported orders
	 *
	 * @since 3.0
	 * @param array $vars query vars without filtering
	 * @return array $vars query vars with (maybe) filtering
	 */
	public function filter_orders_by_export_status_query( $vars ) {
		global $typenow;

		if ( 'shop_order' == $typenow && isset( $_GET['_shop_order_export_status'] ) && is_numeric( $_GET['_shop_order_export_status'] ) ) {

			$vars['meta_key']   = '_wc_customer_order_csv_export_is_exported';
			$vars['meta_value'] = (int) $_GET['_shop_order_export_status'];
		}

		return $vars;
	}


	/**
	 * Add 'Download to CSV' custom bulk action to the 'Orders' page bulk action drop-down
	 *
	 * @since 3.0
	 */
	public function add_order_bulk_actions() {
		global $post_type, $post_status;

		if ( $post_type == 'shop_order' && $post_status != 'trash' ) {

			?>
			<script type="text/javascript">
				jQuery( document ).ready( function ( $ ) {
						  var $exported = $('<option>').val('mark_exported').text('<?php _e( 'Mark exported', WC_Customer_Order_CSV_Export::TEXT_DOMAIN )?>'),
							  $not_exported = $('<option>').val('mark_not_exported').text('<?php _e( 'Mark not exported', WC_Customer_Order_CSV_Export::TEXT_DOMAIN )?>'),
							  $download_to_csv = $('<option>').val('download_to_csv').text('<?php _e( 'Download to CSV', WC_Customer_Order_CSV_Export::TEXT_DOMAIN )?>');

						  $( 'select[name^="action"]' ).append( $exported, $not_exported, $download_to_csv );
					  });
			</script>
			<?php
		}
	}


	/**
	 * Processes the 'Download to CSV' custom bulk action on the 'Orders' page bulk action drop-down
	 *
	 * @since 3.0
	 */
	public function process_order_bulk_actions() {
		global $typenow;

		if ( 'shop_order' == $typenow ) {

			// get the action
			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
			$action        = $wp_list_table->current_action();

			// return if not processing our actions
			if ( ! in_array( $action, array( 'download_to_csv', 'mark_exported', 'mark_not_exported' ) ) ) {

				return;
			}

			// security check
			check_admin_referer( 'bulk-posts' );

			// make sure order IDs are submitted
			if ( isset( $_REQUEST['post'] ) ) {

				$order_ids = array_map( 'absint', $_REQUEST['post'] );
			}

			// return if there are no orders to export
			if ( empty( $order_ids ) ) {

				return;
			}

			// give ourselves an unlimited timeout if possible
			@set_time_limit( 0 );

			if ( 'download_to_csv' == $action ) {

				// setup export class
				$export = new WC_Customer_Order_CSV_Export_Handler( $order_ids );

				$export->download();

			} else {

				// mark each order as exported / not exported
				foreach( $order_ids as $order_id ) {
					update_post_meta( $order_id, '_wc_customer_order_csv_export_is_exported', ( 'mark_exported' == $action ) ? 1 : 0 );
				}
			}
		}
	}


	/**
	 * Get the order count for exported/not exported orders
	 *
	 * Orders placed prior to the installation / activation of the plugin will be counted as exported
	 *
	 * @since 3.9.2
	 * @return array { 'not_exported' => count, 'exported' => count }
	 */
	private function get_order_count() {

		$query_args = array(
			'fields'      => 'ids',
			'post_type'   => 'shop_order',
			'post_status' => isset( $_GET['post_status'] ) ? $_GET['post_status'] : 'any',
			'meta_query'  => array(
				array(
					'key'   => '_wc_customer_order_csv_export_is_exported',
					'value' => 0
				)
			),
			'nopaging'    => true,
		);

		$not_exported_query = new WP_Query( $query_args );

		$query_args['meta_query'][0]['value'] = 1;

		$exported_query = new WP_Query( $query_args );

		return array( 'not_exported' => $not_exported_query->found_posts, 'exported' => $exported_query->found_posts );
	}


	/**
	 * Returns settings array for use by output/save functions
	 *
	 * @since 3.0
	 * @param string $tab_id
	 * @return array
	 */
	public static function get_settings( $tab_id ) {

		$order_statuses = wc_get_order_statuses();

		// get the scheduled export time to display to user
		if ( $scheduled_timestamp = wp_next_scheduled( 'wc_customer_order_csv_export_auto_export_orders' ) ) {
			$scheduled_desc = sprintf( __( 'The next export is scheduled on <code>%s</code>', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ), get_date_from_gmt( date( 'Y-m-d H:i:s', $scheduled_timestamp ), wc_date_format() . ' ' . wc_time_format() ) );
		} else {
			$scheduled_desc = __( 'The export is not scheduled.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
		}

		$settings = array(

			'export' => array(

				array(
					'name' => __( 'Export', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type' => 'title',
				),

				array(
					'id'      => 'wc_customer_order_csv_export_type',
					'name'    => __( 'Export Orders or Customers', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type'    => 'radio',
					'options' => array(
						'orders' => __( 'Orders', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
						'customers' => __( 'Customers', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					),
					'default'  => 'orders',
				),

				array( 'type' => 'sectionend' ),

				array(
					'name' => __( 'Export Options', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type' => 'title',
				),

				array(
					'id'       => 'wc_customer_order_csv_export_statuses',
					'name'     => __( 'Order Statuses', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'Orders with these statuses will be included in the export.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type'     => 'multiselect',
					'options'  => $order_statuses,
					'default'  => '',
					'class'    => 'wc-enhanced-select chosen_select show_if_orders',
					'css'      => 'min-width: 250px',
				),

				array(
					'id'   => 'wc_customer_order_csv_export_start_date',
					'name' => __( 'Start Date', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc' => __( 'Start date of customers or orders to include in the exported file, in the format <code>YYYY-MM-DD.</code>', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type' => 'text',
				),

				array(
					'id'   => 'wc_customer_order_csv_export_end_date',
					'name' => __( 'End Date', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc' => __( 'End date of customers or orders to include in the exported file, in the format <code>YYYY-MM-DD.</code>', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type' => 'text',
				),

				array(
					'id'                => 'wc_customer_order_csv_export_limit',
					'name'              => __( 'Limit Records', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc'              => __( 'Limit the number of rows to be exported. Use this option when exporting very large files that are unable to complete in a single attempt.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type'              => 'number',
					'custom_attributes' => array(
						'min' => 0,
					),
				),

				array(
					'id'                => 'wc_customer_order_csv_export_offset',
					'name'              => __( 'Offset Records', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc'              => __( 'Set the number of records to be skipped in this export. Use this option when exporting very large files that are unable to complete in a single attempt.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type'              => 'number',
					'custom_attributes' => array(
						'min' => 0,
					),
				),

				array( 'type' => 'sectionend' ),
			),

			'settings' => array(

				array(
					'name' => __( 'Export Format', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type' => 'title'
				),

				array(
					'id'       => 'wc_customer_order_csv_export_order_format',
					'name'     => __( 'Order Export Format', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'Default is a new format for v3.0, Import matches the Customer/Order CSV Import plugin format, and legacy is prior to version 3', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type'     => 'select',
					'options'  => array(
						'default'                  => __( 'Default', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
						'default_one_row_per_item' => __( 'Default - One Row per Item', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
						'import'                   => __( 'CSV Import', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
						'legacy_one_row_per_item'  => __( 'Legacy - One Row per Item', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
						'legacy_single_column'     => __( 'Legacy - Single Column for all Items', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					),
					'default'  => 'default',
				),

				array(
					'id'       => 'wc_customer_order_csv_export_customer_format',
					'name'     => __( 'Customer Export Format', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'Default is a new format for v3.0, Import matches the Customer/Order CSV Import plugin format, Legacy is prior to version 3', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type'     => 'select',
					'options'  => array(
						'default' => __( 'Default', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
						'import'  => __( 'CSV Import', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
						'legacy'  => __( 'Legacy', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					),
					'default'  => 'default',
				),

				array(
					'id'       => 'wc_customer_order_csv_export_order_filename',
					'name'     => __( 'Order Export Filename', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'The filename for exported orders. Merge variables: %%timestamp%%, %%order_ids%%', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'default'  => 'orders-export-%%timestamp%%.csv',
					'css'      => 'min-width: 300px;',
					'type'     => 'text',
				),

				array(
					'id'       => 'wc_customer_order_csv_export_customer_filename',
					'name'     => __( 'Customer Export Filename', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'The filename for exported customers. Merge variables: %%timestamp%%', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'default'  => 'customers-export-%%timestamp%%.csv',
					'css'      => 'min-width: 300px;',
					'type'     => 'text',
				),

				array( 'type' => 'sectionend' ),

				array(
					'name' => __( 'Automated Export Settings', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type' => 'title'
				),

				array(
					'id'       => 'wc_customer_order_csv_export_auto_export_method',
					'name'     => __( 'Automatically Export Orders', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'Enable this to automatically export orders via the method & schedule selected.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type'     => 'select',
					'options'  => array(
						'disabled'  => __( 'Disabled', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
						'ftp'       => __( 'via FTP', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
						'http_post' => __( 'via HTTP POST', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
						'email'     => __( 'via Email', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					),
					'default' => 'disabled',
				),

				array(
					'id'       => 'wc_customer_order_csv_export_auto_export_start_time',
					'name'     => __( 'Export Start Time', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'Any new orders will start exporting at this time.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc'     => sprintf( 	__( 'Local time is <code>%s</code>.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ), date_i18n( wc_time_format() ) ) . ' ' . $scheduled_desc,
					'default'  => '',
					'type'     => 'text',
					'css'      => 'max-width: 100px;',
					'class'    => 'js-wc-customer-order-csv-export-auto-export-timepicker'
				),

				array(
					'id'       => 'wc_customer_order_csv_export_auto_export_interval',
					'name'     => __( 'Export Interval (in minutes)*', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'Any new orders will be exported on this schedule.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc'     => __( 'Required in order to schedule the automatic export.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'default'  => '30',
					'type'     => 'text',
					'css'      => 'max-width: 50px;'
				),

				array(
					'id'       => 'wc_customer_order_csv_export_auto_export_statuses',
					'name'     => __( 'Order Statuses', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'Orders with these statuses will be included in the export.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type'     => 'multiselect',
					'options'  => $order_statuses,
					'default'  => '',
					'class'    => 'wc-enhanced-select chosen_select',
					'css'      => 'min-width: 250px',
				),

				array( 'type' => 'sectionend' ),

				array(
					'id'   => 'wc_customer_order_csv_export_ftp_settings',
					'name' => __( 'FTP Settings', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type' => 'title'
				),

				array(
					'id'       => 'wc_customer_order_csv_export_ftp_server',
					'name'     => __( 'Server Address', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'The address of the remote FTP server to upload to.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'default'  => '',
					'type'     => 'text',
				),

				array(
					'id'       => 'wc_customer_order_csv_export_ftp_username',
					'name'     => __( 'Username', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'The username for the remote FTP server.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'default'  => '',
					'type'     => 'text',
				),

				array(
					'id'       => 'wc_customer_order_csv_export_ftp_password',
					'name'     => __( 'Password', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'The password for the remote FTP server.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'default'  => '',
					'type'     => 'password',
				),

				array(
					'id'       => 'wc_customer_order_csv_export_ftp_port',
					'name'     => __( 'Port', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'The password for the remote FTP server.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'default'  => '21',
					'type'     => 'text',
					'style'    => 'max-width: 50px;'
				),

				array(
					'id'       => 'wc_customer_order_csv_export_ftp_path',
					'name'     => __( 'Initial Path', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'The initial path for the remote FTP server with trailing slash, but excluding leading slash.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'default'  => '',
					'type'     => 'text',
				),

				array(
					'id'       => 'wc_customer_order_csv_export_ftp_security',
					'name'     => __( 'Security', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'Select the security type for the remote FTP server.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'default'  => 'none',
					'options' => array(
						'none'    => __( 'None', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
						'ftp_ssl' => __( 'FTP with Implicit SSL', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
						'ftps'    => __( 'FTP with Explicit TLS/SSL', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
						'sftp'    => __( 'SFTP (FTP over SSH)', WC_Customer_Order_CSV_Export::TEXT_DOMAIN )
					),
					'type'     => 'select',
				),

				array(
					'id'      => 'wc_customer_order_csv_export_ftp_passive_mode',
					'name'    => __( 'Passive Mode', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc'    => __( 'Enable passive mode if you are having issues connecting to FTP, especially if you see "PORT command successful" in the error log.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'default' => 'no',
					'type'    => 'checkbox',
				),

				array(
					'id'     => 'wc_customer_order_csv_export_test_button',
					'name'   => __( 'Test FTP', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'method' => 'ftp',
					'type'   => 'csv_test_button',
				),

				array( 'type' => 'sectionend' ),

				array(
					'id'   => 'wc_customer_order_csv_export_post_settings',
					'name' => __( 'HTTP POST Settings', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type' => 'title'
				),

				array(
					'id'       => 'wc_customer_order_csv_export_http_post_url',
					'name'     => __( 'HTTP POST URL', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'Enter the URL to POST the exported CSV to.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'default'  => '',
					'type'     => 'text',
				),

				array(
					'id'     => 'wc_customer_order_csv_export_test_button',
					'name'   => __( 'Test HTTP POST', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'method' => 'http_post',
					'type'   => 'csv_test_button',
				),

				array( 'type' => 'sectionend' ),

				array(
					'id'   => 'wc_customer_order_csv_export_email_settings',
					'name' => __( 'Email Settings', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'type' => 'title'
				),

				array(
					'id'       => 'wc_customer_order_csv_export_email_recipients',
					'name'     => __( 'Recipient(s)', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => sprintf( __( 'Enter recipients (comma separated) the exported CSV should be emailed to. Defaults to <em>%s</em>.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ), esc_attr( get_option('admin_email') ) ),
					'default'  => '',
					'type'     => 'text',
				),

				array(
					'id'       => 'wc_customer_order_csv_export_email_subject',
					'name'     => __( 'Email Subject', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'desc_tip' => __( 'Enter the email subject.', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'default'  => sprintf( __( '[%s] Order CSV Export', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ), get_option( 'blogname' ) ),
					'type'     => 'text',
				),

				array(
					'id'     => 'wc_customer_order_csv_export_test_button',
					'name'   => __( 'Test Email', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
					'method' => 'email',
					'type'   => 'csv_test_button',
				),

				array( 'type' => 'sectionend' ),
			),

		);

		if ( wc_customer_order_csv_export()->is_plugin_active( 'woocommerce-subscriptions.php' ) ) {

			$settings['export'][] = array(
				'name' => __( 'Subscriptions Options', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'type' => 'title',
			);

			$settings['export'][] = array(
				'id'            => 'wc_customer_order_csv_export_subscription_orders',
				'title'         => __( 'Export Subscriptions Orders Only', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'desc'          => __( 'Export subscription orders', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
			);

			$settings['export'][] = array(
				'id'            => 'wc_customer_order_csv_export_subscription_renewals',
				'desc'          => __( 'Export renewal orders', WC_Customer_Order_CSV_Export::TEXT_DOMAIN ),
				'type'          => 'checkbox',
				'checkboxgroup' => 'end',
			);

			$settings['export'][] = array( 'type' => 'sectionend' );

		}

		/**
		 * Allow actors to add or remove settings from the CSV export pages.
		 *
		 * @since 3.0.6
		 * @param array $settings an array of settings for the given tab
		 * @param string $tab_id current tab ID
		 */
		return apply_filters( 'wc_customer_order_csv_export_settings', $settings[ $tab_id ], $tab_id );
	}


	/**
	 * Render a test button
	 *
	 * @since 3.0
	 * @param array $field
	 */
	public function render_test_button( $field ) {

		$settings_exist = $this->method_settings_exist( $field['method'] );
		$name           = $field['name'];
		$atts           = array( 'data-method' => $field['method'] );

		// disable text button and change name if required
		if ( ! $settings_exist ) {
			$name = __( 'Please save your settings before testing', WC_Customer_Order_CSV_Export::TEXT_DOMAIN );
			$atts['disabled'] = 'disabled';
		}

		?>
			<tr valign="top">
				<th scope="row" class="titledesc">Test</th>
				<td class="forminp">
					<?php submit_button( $name, 'secondary', $field['id'], true, $atts ); ?>
				</td>
			</tr>
		<?php
	}


	/**
	 * Check if settings for chosen auto-export method are saved
	 *
	 * @since 3.1
	 * @param string $method export method, either `ftp` or `http_post`
	 * @return bool
	 */
	public function method_settings_exist( $method ) {

		// assume true
		$exist = true;

		if ( $method == 'ftp' ) {
			$exist = get_option( 'wc_customer_order_csv_export_ftp_server' ) && get_option( 'wc_customer_order_csv_export_ftp_username' ) && get_option( 'wc_customer_order_csv_export_ftp_password' );
		} elseif ( $method == 'http_post' ) {
			$exist = get_option( 'wc_customer_order_csv_export_http_post_url' );
		}

		return $exist;
	}


} // end \WC_Customer_Order_CSV_Export_Admin class

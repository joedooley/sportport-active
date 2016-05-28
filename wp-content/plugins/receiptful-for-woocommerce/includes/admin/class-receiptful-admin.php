<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Receiptful_Admin.
 *
 * Admin class.
 *
 * @class		Receiptful_Admin
 * @version		1.0.0
 * @author		Receiptful
 */
class Receiptful_Admin {


	/**
	 * URL for the store owner's Profile page in the Receiptful app.
	 * @var string
	 */
	public $receiptful_profile_url = 'https://app.receiptful.com/profile';


	/**
	 * URL for the store owner's Template in the Receiptful app.
	 * @var string
	 */
	public $receiptful_template_url = 'https://app.receiptful.com/template';


	/**
	 * URL for the store owner's Dashboard in the Receiptful app.
	 * @var string
	 */
	public $receiptful_stats_url = 'https://app.receiptful.com/dashboard';


	/**
	 * URL for the store owner's Dashboard in the Receiptful app.
	 * @var string
	 */
	public $receiptful_recommendations_url = 'https://app.receiptful.com/recommendations';


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Initialize class
		add_action( 'admin_init', array( $this, 'init' ), 1 );

		// Remove public key when API key gets changed (will be gotten automatically)
		add_action( 'update_option_receiptful_api_key', array( $this, 'delete_public_key' ), 10, 2 );

	}


	/**
	 * Class hooks.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Add WC settings tab
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'settings_tab' ), 60 );

		// Settings page contents
		add_action( 'woocommerce_settings_receiptful', array( $this, 'settings_page' ) );

		// Save settings page
		add_action( 'woocommerce_update_options_receiptful', array( $this, 'update_options' ) );


		// Add debug tool
		add_filter( 'woocommerce_debug_tools', array( $this, 'receiptful_status_tools' ) );
		add_action( 'admin_init', array( $this, 'process_receiptful_status_tools' ) );

		// Plugin activation message
		add_action( 'admin_notices', array( $this, 'plugin_activation' ) ) ;

		// Add the plugin page Settings and Docs links
		add_filter( 'plugin_action_links_' . plugin_basename( Receiptful()->file ), array( $this, 'receiptful_plugin_links' ));

	}


	/**
	 * Settings tab.
	 *
	 * Add a WooCommerce settings tab for the Receiptful settings page.
	 *
	 * @since 1.0.0
	 *
	 * @param	array	$tabs	Array of default tabs used in WC.
	 * @return	array			All WC settings tabs including newly added.
	 */
	public function settings_tab( $tabs ) {

		$tabs['receiptful'] = 'Receiptful';

		return $tabs;

	}


	/**
	 * Settings page array.
	 *
	 * Get settings page fields array.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of settings for the settings page.
	 */
	public function get_settings() {

		$settings = apply_filters( 'woocommerce_receiptful_settings', array(

			array(
				'title'		=> 'Receiptful',
				'type'		=> 'title',
				'desc'		=> sprintf( __( "To get started with Receiptful, please add your API key (<a href='%s' target='_blank'>which you can find here</a>) and save the settings.", 'receiptful-for-woocommerce' ), $this->receiptful_profile_url ),
			),
			array(
				'title'		=> __( 'API Key', 'receiptful-for-woocommerce' ),
				'desc'		=> '',
				'id'		=> 'receiptful_api_key',
				'default'	=> '',
				'type'		=> 'text',
				'autoload'	=> false
			),
			array(
				'type'		=> 'sectionend',
			),
			array(
				'title'		=> '',
				'type'		=> 'title',
				'desc'		=> sprintf( __( "<a href='%s'>Edit My Template</a> | <a href='%s'>View Statistics</a>", 'receiptful-for-woocommerce' ),	$this->receiptful_template_url, $this->receiptful_stats_url ),
				'id'		=> 'receiptful_links',
			),
			array(
				'title'   	=> __( 'Enable recommendations', 'receiptful-for-woocommerce' ),
				'desc' 	  	=> sprintf( __( "Enable product recommendations. Requires to have set this up in the <a href='%s'>Recommendations section</a>.", 'receiptful-for-woocommerce' ), $this->receiptful_recommendations_url ),
				'id' 	  	=> 'receiptful_enable_recommendations',
				'default' 	=> 'no',
				'type' 	  	=> 'checkbox',
				'autoload'	=> false
			),
			array(
				'title'   	=> __( 'Enable abandoned cart', 'receiptful-for-woocommerce' ),
				'desc' 	  	=> __( "Enable the abandoned cart functionality.", 'receiptful-for-woocommerce' ),
				'id' 	  	=> 'receiptful_enable_abandoned_cart',
				'default' 	=> 'no',
				'type' 	  	=> 'checkbox',
				'autoload'	=> false
			),
			array(
				'title'   	=> __( 'Enable Receiptful search', 'receiptful-for-woocommerce' ),
				'desc' 	  	=> __( "Enable the Receiptful search functionality.", 'receiptful-for-woocommerce' ),
				'id' 	  	=> 'receiptful_enable_search',
				'default' 	=> 'no',
				'type' 	  	=> 'checkbox',
				'autoload'	=> false
			),
			array(
				'type'		=> 'sectionend',
			),

		) );

		return $settings;

	}


	/**
	 * Settings page content.
	 *
	 * Output settings page content via WooCommerce output_fields() method.
	 *
	 * @since 1.0.0
	 */
	public function settings_page() {

		WC_Admin_Settings::output_fields( $this->get_settings() );

	}


	/**
	 * Save settings.
	 *
	 * Save settings based on WooCommerce save_fields() method.
	 *
	 * @since 1.0.0
	 */
	public function update_options() {

		WC_Admin_Settings::save_fields( $this->get_settings() );

	}


	/**
	 * Delete public key.
	 *
	 * Delete the public key when the API key gets updated.
	 *
	 * @since 1.1.4
	 */
	public function delete_public_key( $old_value, $value ) {

		delete_option( 'receiptful_public_user_key' );

	}


	/**
	 * Add product re-sync tool.
	 *
	 * Add a product re-sync tool to the System -> tools page to
	 * re-sync all products with Receiptful.
	 *
	 * @since 1.1.12
	 * @since 1.1.13 - Add Receipt (order) sync.
	 *
	 * @param	array	$tools	List of existing tools.
	 * @return	array			List of modified tools.
	 */
	public function receiptful_status_tools( $tools ) {

		$sync_queue = get_option( '_receiptful_queue', array( 'products' => array(), 'orders' => array() ) );
		$product_count_message = '';
		$order_count_message = '';
		if ( ! empty( $sync_queue['products'] ) ) {
			$product_count_message = '<strong>' . sprintf(  __( '%d products to be synced.', 'receiptful-for-woocommerce' ), count( $sync_queue['products']) ) . '</strong>&nbsp;';
		}

		if ( ! empty( $sync_queue['orders'] ) ) {
			$order_count_message = '<strong>' . sprintf(  __( '%d orders to be synced.', 'receiptful-for-woocommerce' ), count( $sync_queue['orders']) ) . '</strong>&nbsp;';
		}

		$tools['receiptful_product_sync'] = array(
			'name'		=> __( 'Synchronize products with Receiptful', 'receiptful-for-woocommerce' ),
			'button'	=> __( 'Synchronize', 'receiptful-for-woocommerce' ),
			'desc'		=> $product_count_message . __( 'This will update all products in Receiptful with all its latest data', 'receiptful-for-woocommerce' ),
		);

		$tools['receiptful_receipt_sync'] = array(
			'name'		=> __( 'Synchronize receipts with Receiptful', 'receiptful-for-woocommerce' ),
			'button'	=> __( 'Synchronize', 'receiptful-for-woocommerce' ),
			'desc'		=> $order_count_message . __( 'This will update all orders in Receiptful with the latest data', 'receiptful-for-woocommerce' ),
		);

		$tools['receiptful_clear_coupons'] = array(
			'name'		=> __( 'Clear unused, expired coupons created by Receiptful', 'receiptful' ),
			'button'	=> __( 'Trash expired coupons', 'receiptful' ),
			'desc'		=> __( 'Only coupons that have been expired for more than 7 days will be trashed.', 'receiptful' ),
		);

		$tools['receiptful_clear_coupons'] = array(
			'name'		=> __( 'Clear unused, expired coupons created by Receiptful', 'receiptful' ),
			'button'	=> __( 'Trash expired coupons', 'receiptful' ),
			'desc'		=> __( 'Only coupons that have been expired for more than 7 days will be trashed.', 'receiptful' ),
		);
		$tools['receiptful_clear_resend_queue'] = array(
			'name'		=> __( 'Clear resend queue', 'receiptful' ),
			'button'	=> __( 'Clear queue', 'receiptful' ),
			'desc'		=> __( 'Clear all the receipts on the Receiptful resend queue.', 'receiptful' ),
		);

		return $tools;

	}


	/**
	 * Process re-sync action.
	 *
	 * Make sure that the status tool 'Receiptful re-sync' is working.
	 *
	 * @since 1.1.12
	 * @since 1.1.13 - Add receipt sync handler.
	 */
	public function process_receiptful_status_tools() {

		// Bail if action is not set
		if ( ! isset( $_GET['action'] ) ) {
			return;
		}

		// Bail if nonce is incorrect
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'debug_action' ) ) {
			return;
		}

		// Product sync
		if ( 'receiptful_product_sync' == $_GET['action'] ) {

			// Get all product IDs
			$product_ids = get_posts( array(
				'fields'			=> 'ids',
				'posts_per_page'	=> -1,
				'post_type'			=> 'product',
				'post_status'		=> 'any',
			) );
			$product_ids = array_map( 'absint', $product_ids );

			$queue = get_option( '_receiptful_queue', array() );
			foreach ( $product_ids as $product_id ) {
				$queue['products'][ $product_id ] = array( 'id' => $product_id, 'action' => 'update' );
			}
			update_option( '_receiptful_queue', $queue );

		}

		// Order sync
		if ( 'receiptful_receipt_sync' == $_GET['action'] ) {

			// Get all receipt IDs
			$order_ids = get_posts( array(
				'fields'			=> 'ids',
				'posts_per_page'	=> -1,
				'post_type'			=> 'shop_order',
				'post_status'		=> array_keys( wc_get_order_statuses() ),
			) );
			$order_ids = array_map( 'absint', $order_ids );

			$queue = get_option( '_receiptful_queue', array() );
			foreach ( $order_ids as $order_id ) {
				$queue['orders'][ $order_id ] = array( 'id' => $order_id, 'action' => 'upload' );
			}
			update_option( '_receiptful_queue', $queue );

		}

		// Clear coupons
		if ( 'receiptful_clear_coupons' == $_GET['action'] ) {
			receiptful_clear_unused_coupons();
		}

		// Clear resend queue
		if ( 'receiptful_clear_resend_queue' == $_GET['action'] ) {
			update_option( '_receiptful_resend_queue', '' );
		}

		wp_redirect( esc_url_raw( admin_url( 'admin.php?page=wc-status&tab=tools' ) ) );
		die;

	}


	/**
	 * Plugin activation.
	 *
	 * Saves the version of the plugin to the database and displays an
	 * activation notice on where users can access the new options.
	 *
	 * @since 1.0.0
	 * @since 1.2.2 - Moved to admin class
	 */
	public function plugin_activation() {

		$api_key = get_option( 'receiptful_api_key' );
		if ( empty( $api_key ) ) {

			add_option( 'receiptful_woocommerce_version', Receiptful()->version );

			// admin.php?page=wc-settings&tab=receiptful
			$admin_url = add_query_arg( 'page', 'wc-settings', add_query_arg( 'tab', 'receiptful', admin_url( 'admin.php' ) ) );

			?><div class="updated">
			<p><?php
				printf( __( '%1$sReceiptful has been activated.%2$s Please %3$sclick here%4$s to add your API key & supercharge your receipts.', 'receiptful' ), '<strong>', '</strong>', '<a href="' . esc_url( $admin_url ) . '">', '</a>' );
				?></p>
			</div><?php

		}

	}


	/**
	 * Plugin page link.
	 *
	 * Add a 'settings' link to the plugin on the plugins page.
	 *
	 * @since 1.0.0
	 * @since 1.2.2 - Moved to admin class
	 *
	 * @param 	array $links	List of existing plugin links.
	 * @return 	array			List of modified plugin links.
	 */
	function receiptful_plugin_links( $links ) {

		$links['settings'] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=receiptful' ) . '">' . __( 'Settings', 'receiptful' ) . '</a>';

		return $links;

	}


}

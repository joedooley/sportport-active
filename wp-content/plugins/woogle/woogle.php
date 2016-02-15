<?php
/*
Plugin Name: Woogle
Plugin URI: http://patternsinthecloud.com
Description: Upload WooCommerce products to the Google Merchant Center
Version: 1.0.5
Author: Patterns in the Cloud
Author URI: http://patternsinthecloud.com
License: Single-site
*/

define( 'Woogle_Version', '1.0.5' );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	
	/**
	 * Activate hook
	 */
	function woogle_activate() {
		global $wpdb;
		/* 
		 * Migrate data from beta version
		 */
		// Migrate options
		$option_names = array(
			'merchant_id',
			'application_name',
			'client_id',
			'client_secret',
			'auto_stock_updates',
			'update_on_checkout',
			'debug',
			'license_key'
		);
		foreach ( $option_names as $name ) {
			$value = get_option( 'wc_google_shopping_api_' . $name );
			if ( ! empty( $value ) ) {
				add_option( 'woogle_' . $name, $value );
			}
		}
		// Migrate product data
		$product_meta = $wpdb->get_results(
			"SELECT post_id, meta_key, meta_value
			FROM {$wpdb->base_prefix}postmeta
			WHERE meta_key LIKE '\_wc\_google\_shopping\_api\_%'"
		);
		foreach ( $product_meta as $meta ) {
			$key = str_replace( '_wc_google_shopping_api', '_woogle', $meta->meta_key );
			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->base_prefix}postmeta
				WHERE post_id = %d AND meta_key = %s",
				$meta->post_id, $key
			) );
			if ( $count < 1 ) {
				add_post_meta( $meta->post_id, $key, $meta->meta_value, true );
			}
		}
	}
	register_activation_hook( __FILE__, 'woogle_activate' );
	
	/**
	 * Deactivate hook
	 */
	function woogle_deactivate() {
		
	}
	register_deactivation_hook( __FILE__, 'woogle_deactivate' );
	
	/**
	 * Uninstall hook
	 */
	function woogle_uninstall() {
		
	}
	register_uninstall_hook( __FILE__, 'woogle_uninstall' );
	
	function woogle_get_plugin_file() {
		return __FILE__;
	}
	
	function woogle_init() {
		set_include_path( get_include_path() . PATH_SEPARATOR . dirname( __FILE__ ) . '/vendor/google-api-php-client/src' );
		require_once( 'vendor/google-api-php-client/src/Google/autoload.php' );
	}
	add_action( 'plugins_loaded', 'woogle_init' );
	
	require_once( 'inc/scripts.php' );
	require_once( 'inc/settings.php' );
	require_once( 'inc/products.php' );
	require_once( 'inc/license.php');
	require_once( 'inc/api.php' );
}

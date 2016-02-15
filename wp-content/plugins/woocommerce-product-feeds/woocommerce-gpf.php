<?php
/*
Plugin Name: WooCommerce Google Product Feed
Plugin URI: http://www.leewillis.co.uk/wordpress-plugins/?utm_source=wordpress&utm_medium=www&utm_campaign=woocommerce-gpf
Description: Woocommerce extension that allows you to more easily populate advanced attributes into the Google Merchant Centre feed
Author: Lee Willis
Version: 4.0.2
Author URI: http://www.leewillis.co.uk/
License: GPLv3
*/

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), 'd55b4f852872025741312839f142447e', '18619' );


if ( is_admin() ) {
	require_once ( 'woocommerce-gpf-common.php' );
	require_once ( 'woocommerce-gpf-admin.php' );
}


/**
 * Bodge ffor WPEngine.com users - provide the feed at a URL that doesn't
 * rely on query arguments as WPEngine don't support URLs with query args
 * if the requestor is a googlebot. #broken
 */
function woocommerce_gpf_endpoints() {

	add_rewrite_tag( '%woocommerce_gpf%', '([^/]+)' );
	add_rewrite_tag( '%gpf_start%', '([0-9]{1,})' );
	add_rewrite_tag( '%gpf_limit%', '([0-9]{1,})' );
	add_rewrite_rule( 'woocommerce_gpf/([^/]+)/gpf_start/([0-9]{1,})/gpf_limit/([0-9]{1,})', 'index.php?woocommerce_gpf=$matches[1]&gpf_start=$matches[2]&gpf_limit=$matches[3]', 'top' );
	add_rewrite_rule( 'woocommerce_gpf/([^/]+)', 'index.php?woocommerce_gpf=$matches[1]', 'top' );

}
add_action( 'init', 'woocommerce_gpf_endpoints' );



/**
 * Include the relevant files dependant on the page request type
 */
function woocommerce_gpf_includes() {

	global $wp_query;

	// Parsing for legacy URLs
	if ( isset ( $_REQUEST['action'] ) && 'woocommerce_gpf' == $_REQUEST['action'] ) {
		if ( isset ( $_REQUEST['feed_format'] ) ) {
			$wp_query->query_vars['woocommerce_gpf'] = $_REQUEST['feed_format'];
		} else {
			$wp_query->query_vars['woocommerce_gpf'] = 'google';
		}
	}

	if ( isset( $wp_query->query_vars['woocommerce_gpf'] ) ) {
		require_once ( 'woocommerce-gpf-common.php' );
		require_once ( 'woocommerce-gpf-feed.class.php' );
		if ( 'google' == $wp_query->query_vars['woocommerce_gpf'] ) {
			require_once 'woocommerce-gpf-feed-google.php';
		} else if ( 'googleinventory' == $wp_query->query_vars['woocommerce_gpf'] ) {
			require_once 'woocommerce-gpf-feed-google-inventory.php';
		} else if ( 'bing' == $wp_query->query_vars['woocommerce_gpf'] ) {
			require_once 'woocommerce-gpf-feed-bing.php';
		}
		require_once ( 'woocommerce-gpf-frontend.php' );
	}

}
add_action( 'template_redirect', 'woocommerce_gpf_includes' );



/**
 * Create database tabe to cache the Google product taxonomy.
 */
function woocommerce_gpf_install() {

	global $wpdb;

	$table_name = $wpdb->prefix . 'woocommerce_gpf_google_taxonomy';

	$sql = "CREATE TABLE $table_name (
						 taxonomy_term text,
						 search_term text
							 )";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta( $sql );
	flush_rewrite_rules();

}

register_activation_hook( __FILE__, 'woocommerce_gpf_install' );


function woocommerce_gpf_block_wordpress_gzip_compression() {
	if ( isset( $_GET['woocommerce_gpf'] ) ) {
		remove_action( 'init', 'ezgz_buffer' );
	}
}
add_action( 'plugins_loaded', 'woocommerce_gpf_block_wordpress_gzip_compression' );

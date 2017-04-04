<?php
/**
 * Plugin Name:       WooCommerce PDF Invoices
 * Plugin URI:        https://wordpress.org/plugins/woocommerce-pdf-invoices
 * Description:       Automatically generate and attach customizable PDF Invoices to WooCommerce emails and connect with Dropbox, Google Drive, OneDrive or Egnyte.
 * Version:           2.6.4
 * Author:            Bas Elbers
 * Author URI:        http://wcpdfinvoices.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-pdf-invoices
 * Domain Path:       /lang
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BEWPI_VERSION', '2.6.4' );

/**
 * Load WooCommerce PDF Invoices plugin.
 */
function _bewpi_load_plugin() {

	define( 'BEWPI_FILE', __FILE__ );
	define( 'BEWPI_DIR', plugin_dir_path( __FILE__ ) );
	define( 'BEWPI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

	require_once BEWPI_DIR . 'includes/be-woocommerce-pdf-invoices.php';

	_bewpi_on_plugin_update();
}
add_action( 'plugins_loaded', '_bewpi_load_plugin', 10 );

/**
 * On plugin update.
 *
 * @since 2.5.0
 */
function _bewpi_on_plugin_update() {
	$current_version = get_site_option( 'bewpi_version' );
	if ( BEWPI_VERSION !== $current_version ) {

		// version 2.6.1- need to be updated with new email options and postmeta.
		if ( version_compare( $current_version, '2.6.1' ) <= 0 ) {
			// temporary change max execution time to higher value to prevent internal server errors.
			$max_execution_time = (int) ini_get( 'max_execution_time' );
			if ( 0 !== $max_execution_time ) {
				set_time_limit( 360 );
			}

			update_email_type_options();
			update_postmeta();

			set_time_limit( $max_execution_time );
		}

		update_site_option( 'bewpi_version', BEWPI_VERSION );
	}
}

/**
 * "Attach to Email" and "Attach to new order email" options changed to multi-checkbox, so update settings accordingly.
 *
 * @since 2.5.0
 */
function update_email_type_options() {
	$general_options = get_option( 'bewpi_general_settings' );
	// check if we need to add and/or remove options.
	if ( isset( $general_options['bewpi_email_type'] ) ) {
		$email_type = $general_options['bewpi_email_type'];
		if ( ! empty( $email_type ) ) {
			// set new email type option.
			$general_options[ $email_type ] = 1;
		}
		// delete old option.
		unset( $general_options['bewpi_email_type'] );

		update_option( 'bewpi_general_settings', $general_options );
	}

	if ( isset( $general_options['bewpi_new_order'] ) ) {
		$email_type = $general_options['bewpi_new_order'];
		if ( $email_type ) {
			// set invoice attach to new order email option.
			$general_options['new_order'] = 1;
		}
		// delete old option.
		unset( $general_options['bewpi_new_order'] );

		update_option( 'bewpi_general_settings', $general_options );
	}
}

/**
 *  Update postmeta in database.
 */
function update_postmeta() {
	$posts = get_posts( array(
		'numberposts' => -1,
		'post_type'   => 'shop_order',
		'post_status' => array_keys( wc_get_order_statuses() ),
		'fields'      => 'ids',
	) );

	$template_options = get_option( 'bewpi_template_settings' );

	$date_format = $template_options['bewpi_date_format'];
	if ( empty( $date_format ) ) {
		$date_format = (string) get_option( 'date_format' );
	}

	foreach ( $posts as $post_id ) {
		// create pdf path postmeta for all shop orders.
		create_pdf_path_postmeta( $post_id, $template_options );

		// format date postmeta to mysql date.
		update_date_format_postmeta( $post_id, $date_format );
	}
}

/**
 * Create full path postmeta for all orders that have a pdf invoice generated.
 *
 * @param int   $post_id Post ID or WC Order ID.
 * @param array $template_options User template options.
 *
 * @since 2.6.0
 */
function create_pdf_path_postmeta( $post_id, $template_options ) {
	$pdf_path = get_post_meta( $post_id, '_bewpi_invoice_pdf_path', true );
	if ( $pdf_path ) {
		return;
	}

	$formatted_invoice_number = get_post_meta( $post_id, '_bewpi_formatted_invoice_number', true );
	if ( ! $formatted_invoice_number ) {
		return;
	}

	// one folder for all invoices.
	$new_pdf_path = $formatted_invoice_number . '.pdf';
	if ( (bool) $template_options['bewpi_reset_counter_yearly'] ) {
		// yearly sub-folders.
		$invoice_year = get_post_meta( $post_id, '_bewpi_invoice_year', true );
		if ( $invoice_year ) {
			$new_pdf_path = $invoice_year . '/' . $formatted_invoice_number . '.pdf';
		}
	}

	if ( file_exists( BEWPI_INVOICES_DIR . $new_pdf_path ) ) {
		update_post_meta( $post_id, '_bewpi_invoice_pdf_path', $new_pdf_path );
	}
}

/**
 * Format date postmeta to mysql date.
 *
 * @param int    $post_id Post ID or WC Order ID.
 * @param string $date_format User option date format.
 *
 * @since 2.6.0
 */
function update_date_format_postmeta( $post_id, $date_format ) {
	$invoice_date = get_post_meta( $post_id, '_bewpi_invoice_date', true );
	if ( ! $invoice_date ) {
		return;
	}

	$date = DateTime::createFromFormat( $date_format, $invoice_date );
	if ( ! $date ) {
		return;
	}

	update_post_meta( $post_id, '_bewpi_invoice_date', $date->format( 'Y-m-d H:i:s' ) );
}

/**
 * Save install date, plugin version to db and set transient to show activation notice.
 *
 * @since 2.5.0
 */
function _bewpi_on_plugin_activation() {
	// save install datetime for plugin activation admin notice.
	update_site_option( 'bewpi_install_date', current_time( 'mysql' ) );

	// use transient to display activation admin notice.
	set_transient( 'bewpi-admin-notice-activation', true, 30 );

	// save plugin version for update function.
	update_site_option( 'bewpi_version', BEWPI_VERSION );
}

register_activation_hook( __FILE__, '_bewpi_on_plugin_activation' );

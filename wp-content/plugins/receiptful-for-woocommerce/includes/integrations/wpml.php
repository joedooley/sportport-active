<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WooCommerce WPML compatibility.
 *
 * @author		Receiptful
 * @version		1.0.0
 * @since		1.1.5
 */


/**
 * Remove WPML email.
 *
 * @since 1.1.5
 */
function receiptful_wpml_compat_completed_notification() {

	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return;
	}

	global $woocommerce_wpml;
	remove_action( 'woocommerce_order_status_completed_notification', array( $woocommerce_wpml->emails, 'email_heading_completed' ), 9 );

}
add_action( 'woocommerce_order_status_completed_notification', 'receiptful_wpml_compat_completed_notification', 5 );


/**
 * Remove WPML email.
 *
 * @since 1.1.5
 */
function receiptful_wpml_compat_completed_refresh() {

	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return;
	}

	global $woocommerce_wpml;
	remove_action( 'woocommerce_order_status_completed', array( $woocommerce_wpml->emails, 'refresh_email_lang_complete' ), 9 );

}
add_action( 'woocommerce_order_status_completed', 'receiptful_wpml_compat_completed_refresh', 5 );


/**
 * Remove WPML email.
 *
 * @since 1.1.5
 */
function receiptful_wpml_compat_pending_processing() {

	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return;
	}

	global $woocommerce_wpml;
	remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $woocommerce_wpml->emails, 'email_heading_processing' ) );
	remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $woocommerce_wpml->emails, 'refresh_email_lang' ), 9 );

}
add_action( 'woocommerce_order_status_pending_to_processing_notification', 'receiptful_wpml_compat_pending_processing', 5 );


/**
 * Remove WPML email.
 *
 * @since 1.1.5
 */
function receiptful_wpml_compat_pending_hold() {

	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return;
	}

	global $woocommerce_wpml;
	remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $woocommerce_wpml->emails, 'email_heading_processing' ) );
	remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $woocommerce_wpml->emails, 'refresh_email_lang' ), 9 );

}
add_action( 'woocommerce_order_status_pending_to_on-hold_notification', 'receiptful_wpml_compat_pending_hold', 5 );

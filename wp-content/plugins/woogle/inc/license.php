<?php

define( 'Woogle_License_Url', 'https://woogleshopping.com' );
define( 'Woogle_License_Item_Name', 'Woogle' );

function woogle_get_license_key() {
	$license_key = get_option( 'woogle_license_key' );
	if ( empty( $license_key ) ) {
		return '';
	}
	$license_key = trim( $license_key );
	return $license_key;
}

function woogle_license_key_is_expired() {
	$license_key_expires = get_option( 'woogle_license_key_expires' );
	if ( strtotime( $license_key_expires ) < time() ) {
		return true;
	}
	return false;
}

function woogle_license_key_is_valid() {
	$license_key_validated = get_option( 'woogle_license_key_validated' );
	if ( $license_key_validated ) {
		return true;
	}
	return false;
}

function woogle_plugin_updater() {
	$license_key = woogle_get_license_key();
	if ( empty( $license_key ) ) {
		return;
	}
	
	require_once( 'edd/woogle-plugin-updater.php' );
	$edd_updater = new Woogle_Plugin_Updater( Woogle_License_Url, woogle_get_plugin_file(), array(
		'version' => Woogle_Version,
		'license' => $license_key,
		'item_name' => Woogle_License_Item_Name,
		'author' => 'Patterns In the Cloud'
	) );
}
add_action( 'admin_init', 'woogle_plugin_updater', 0 );

function woogle_activate_license_key() {
	$license_key = woogle_get_license_key();
	if ( empty( $license_key ) ) {
		return NULL;
	}
	
	$api_params = array(
		'edd_action' => 'activate_license',
		'item_name' => urlencode( Woogle_License_Item_Name ),
		'license' => $license_key,
		'url' => home_url(),
		'wc_version' => WOOCOMMERCE_VERSION,
		'woogle_version' => Woogle_Version
	);

	$response = wp_remote_get( add_query_arg( $api_params, Woogle_License_Url ), array(
		'timeout' => 15, 'sslverify' => false
	) );

	if ( is_wp_error( $response ) ) {
		return NULL;
	}

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	return $license_data;
}

function woogle_update_license_key_status() {
	$result = woogle_activate_license_key();
	if ( $result == NULL ) {
		update_option( 'woogle_license_key_validated', '0' );
		update_option( 'woogle_license_key_expires', '0' );
		WC_Admin_Settings::add_error( __( 'Your Woogle License Key is invalid!', 'woogle' ) );
	} elseif ( $result->license == 'valid' || $result->license == 'expired' ) {
		update_option( 'woogle_license_key_validated', '1' );
		update_option( 'woogle_license_key_expires', $result->expires );
		WC_Admin_Settings::add_message( __( 'Your Woogle License Key is valid!', 'woogle' ) );
	} else {
		update_option( 'woogle_license_key_validated', '0' );
		update_option( 'woogle_license_key_expires', '0' );
		WC_Admin_Settings::add_error( __( 'Your Woogle License Key is invalid!', 'woogle' ) );
	}
}

function woogle_license_key_status_notices() {
	if ( isset( $_REQUEST['section'] ) && $_REQUEST['section'] == 'woogle' ) {
		return;
	}
	
	$license_key = woogle_get_license_key();
	$settings_page_url = esc_attr( admin_url( '/admin.php?page=wc-settings&tab=products&section=woogle') );
	$license_url = esc_attr( Woogle_License_Url );
	if ( empty( $license_key ) ) {
		$message = <<<MESSAGE
Your <a href="{$settings_page_url}">Woogle License Key</a> is missing. Please go to
<a href="{$license_url}">{$license_url}</a> to purchase a license and receive core updates.
MESSAGE;
		woogle_add_message( __( $message, 'woogle' ), 'error' );
	} else {
		$license_key_is_valid = woogle_license_key_is_valid();
		if ( $license_key_is_valid ) {
			$license_key_is_expired = woogle_license_key_is_expired();
			if ( $license_key_is_expired ) {
				$message = <<<MESSAGE
Your <a href="{$settings_page_url}">Woogle License Key</a> has expired. Please go to
<a href="{$license_url}">{$license_url}</a> to purchase a license and receive core updates.
MESSAGE;
				woogle_add_message( __( $message, 'woogle' ), 'update-nag' );
			}
		} else {
			$message = <<<MESSAGE
Your <a href="{$settings_page_url}">Woogle License Key</a> is invalid. Please go to
<a href="{$license_url}">{$license_url}</a> to purchase a license and receive core updates.
MESSAGE;
			woogle_add_message( __( $message, 'woogle' ), 'error' );
		}
	}
}
add_action( 'admin_init', 'woogle_license_key_status_notices' );
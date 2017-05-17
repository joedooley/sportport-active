<?php

/*
  Plugin Name: WooCommerce Variation Swatches and Photos
  Plugin URI: https://woocommerce.com/products/variation-swatches-and-photos/
  Description: WooCommerce Swatches and Photos allows you to configure colors and photos for shoppers on your site to use when picking variations. Requires WooCommerce 1.5.7+
  Version: 3.0.3
  Author: Lucas Stark
  Author URI: https://www.elementstark.com/about
  Requires at least: 3.5
  Tested up to: 4.7.3

  Copyright: © 2009-2017 Lucas Stark.
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
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
woothemes_queue_update( plugin_basename( __FILE__ ), '37bea8d549df279c8278878d081b062f', '18697' );


if ( is_woocommerce_active() ) {

	require 'classes/class-wc-swatches-compatibility.php';

	add_action( 'init', 'wc_swatches_and_photos_load_textdomain', 0 );

	function wc_swatches_and_photos_load_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wc_swatches_and_photos' );
		load_textdomain( 'wc_swatches_and_photos', WP_LANG_DIR . '/woocommerce/wc_swatches_and_photos-' . $locale . '.mo' );
		load_plugin_textdomain( 'wc_swatches_and_photos', false, plugin_basename( dirname( __FILE__ ) ) . '/i18n/languages' );
	}

	add_action( 'plugins_loaded', 'wc_swatches_on_plugin_loaded' );

	function wc_swatches_on_plugin_loaded() {
		if ( WC_Swatches_Compatibility::is_wc_version_gte_2_7() ) {
			require 'woocommerce-swatches-main.php';
			$GLOBALS['woocommerce_swatches'] = new WC_SwatchesPlugin();
		} elseif ( WC_Swatches_Compatibility::is_wc_version_gte_2_4() && !WC_Swatches_Compatibility::is_wc_version_gte_2_7() ) {
			require 'back_compat_less_27/woocommerce-swatches.php';
		} else {
			require 'back_compat_less_24/woocommerce-swatches.php';
		}
	}
}
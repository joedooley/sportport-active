<?php
/*
Plugin Name: Local SEO for Yoast SEO
Version: 3.0
Plugin URI: https://yoast.com/wordpress/local-seo/
Description: This Local SEO module adds all the needed functionality to get your site ready for Local Search Optimization
Author: Team Yoast and Arjan Snaterse
Author URI: https://yoast.com

Copyright 2012-2014 Joost de Valk & Arjan Snaterse

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * All functionality for fetching location data and creating an KML file with it.
 *
 * @package    Yoast SEO
 * @subpackage Yoast SEO Local
 */

define( 'WPSEO_LOCAL_VERSION', '3.0' );

if ( ! defined( 'WPSEO_LOCAL_PATH' ) ) {
	define( 'WPSEO_LOCAL_PATH', plugin_dir_path( __FILE__ ) );
}

if( ! defined( 'WPSEO_LOCAL_FILE') ) {
	define( 'WPSEO_LOCAL_FILE', __FILE__ );
}

// Load text domain
add_action( 'init', 'wpseo_local_load_textdomain' );
function wpseo_local_load_textdomain() {
	load_plugin_textdomain( 'yoast-local-seo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/**
 * Initialize the Local SEO module on plugins loaded, so WP SEO should have set its constants and loaded its main classes.
 *
 * @since 0.2
 */
function wpseo_local_seo_init() {
	global $wpseo_local_core;

	if ( defined( 'WPSEO_VERSION' ) ) {

		if ( version_compare( WPSEO_VERSION, '1.4.99', '>=' ) ) {

			require_once 'classes/class-core.php';
			require_once 'includes/wpseo-local-functions.php';
			require_once 'includes/ajax-functions.php';
			require_once 'classes/class-core.php';
			require_once 'classes/class-admin.php';
			require_once 'classes/class-admin-wrappers.php';
			require_once 'classes/class-metaboxes.php';
			require_once 'classes/class-frontend.php';
			require_once 'classes/class-storelocator.php';
			require_once 'classes/class-taxonomy.php';
			require_once 'widgets/widget-show-address.php';
			require_once 'widgets/widget-show-map.php';
			require_once 'widgets/widget-show-openinghours.php';
			require_once 'widgets/widget-storelocator-form.php';

			$wpseo_local_core = new WPSEO_Local_Core();
			$wpseo_local_admin = new WPSEO_Local_Admin();
			$wpseo_local_metaboxes = new WPSEO_Local_Metaboxes();
			$wpseo_local_frontend = new WPSEO_Local_Frontend();
			$wpseo_local_storelocator = new WPSEO_Local_Storelocator();
			$wpseo_local_taxonomy = new WPSEO_Local_Taxonomy();

		}else {
			add_action( 'all_admin_notices', 'yoast_wpseo_local_upgrade_error' );
		}

	} else {
		add_action( 'all_admin_notices', 'wpseo_local_missing_error' );
	}
}

add_action( 'plugins_loaded', 'wpseo_local_seo_init' );


/**
 * Throw an error if Yoast SEO is not installed.
 *
 * @since 0.2
 */
function wpseo_local_missing_error() {
	echo '<div class="error"><p>Please <a href="' . admin_url( 'plugin-install.php?tab=search&type=term&s=wordpress+seo&plugin-search-input=Search+Plugins' ) . '">install &amp; activate Yoast SEO by Yoast</a> and then go to the Local SEO section to enable the Local SEO module to work.</p></div>';
}

/**
 * Throw an error if Yoast SEO is out of date.
 *
 * @since 1.5.4
 */
function yoast_wpseo_local_upgrade_error() {
	echo '<div class="error"><p>' . __( 'Please upgrade the Yoast SEO plugin to the latest version to allow the Local SEO module to work.', 'yoast-local-seo' ) . '</p></div>';
}

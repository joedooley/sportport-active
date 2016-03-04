<?php
/*
Plugin Name: Local SEO for Yoast SEO
Version: 3.1
Plugin URI: https://yoast.com/wordpress/local-seo/
Description: This Local SEO module adds all the needed functionality to get your site ready for Local Search Optimization
Author: Team Yoast and Arjan Snaterse
Author URI: https://yoast.com

Copyright 2012-2016 Joost de Valk & Arjan Snaterse

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

define( 'WPSEO_LOCAL_VERSION', '3.1' );

if ( ! defined( 'WPSEO_LOCAL_PATH' ) ) {
	define( 'WPSEO_LOCAL_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WPSEO_LOCAL_FILE' ) ) {
	define( 'WPSEO_LOCAL_FILE', __FILE__ );
}

// Load text domain
load_plugin_textdomain( 'yoast-local-seo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

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

/**
 * Initialize the Local SEO module on plugins loaded, so WP SEO should have set its constants and loaded its main classes.
 *
 * @since 0.2
 */
function wpseo_local_seo_init() {
	global $wpseo_local_core;

	if ( defined( 'WPSEO_VERSION' ) ) {

		if ( version_compare( WPSEO_VERSION, '1.4.99', '>=' ) ) {
			$wpseo_local_core         = new WPSEO_Local_Core();
			$wpseo_local_admin        = new WPSEO_Local_Admin();
			$wpseo_local_metaboxes    = new WPSEO_Local_Metaboxes();
			$wpseo_local_frontend     = new WPSEO_Local_Frontend();
			$wpseo_local_storelocator = new WPSEO_Local_Storelocator();
			$wpseo_local_taxonomy     = new WPSEO_Local_Taxonomy();

		}
		else {
			add_action( 'all_admin_notices', 'yoast_wpseo_local_upgrade_error' );
		}

	}
	else {
		add_action( 'all_admin_notices', 'wpseo_local_missing_error' );
	}
}

add_action( 'init', 'wpseo_local_seo_init' );

function wpseo_local_seo_init_widgets() {
	$widgets = array(
		"WPSEO_Show_Address",
		"WPSEO_Show_Map",
		"WPSEO_Show_OpeningHours",
	);

	if ( wpseo_has_multiple_locations() ) {
		$widgets[] = "WPSEO_Storelocator_Form";
	}

	foreach( $widgets as $widget ) {
		register_widget( $widget );
	}
}

add_action( 'widgets_init', 'wpseo_local_seo_init_widgets' );


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

/**
 * Instantiate the plugin license manager for the current plugin and activate it's license.
 */
function yoast_wpseo_local_activate_license( ) {
	if ( class_exists( 'Yoast_Plugin_License_Manager' ) ) {
		if ( ! class_exists( 'Yoast_Product_WPSEO_Local' ) ) {
			require_once dirname( WPSEO_LOCAL_FILE ) . '/classes/class-product.php';
		}

		$license_manager = new Yoast_Plugin_License_Manager( new Yoast_Product_WPSEO_Local() );
		$license_manager->activate_license();
	}
}

/*
 * When the plugin is deactivated and activated again, the license have to be activated. This is mostly the case
 * during a update of the plugin. To solve this, we hook into the activation process by calling a method that will
 * activate the license.
 */
register_activation_hook( WPSEO_LOCAL_FILE, 'yoast_wpseo_local_activate_license' );

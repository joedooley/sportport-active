<?php

/**
 * @wordpress-plugin
 * Plugin Name:       NC Size Chart for Woocommerce
 * Description:       This plugin allow you to use size charts to products on woocommerce.
 * Version:           1.0.6
 * Author:            Nabaraj Chapagain
 * Author URI:        http://ncplugins.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       productsize-chart-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function productsize_chart_activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-productsize-chart-activator.php';
	productsize_chart_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function productsize_chart_deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-productsize-chart-deactivator.php';
	productsize_chart_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'productsize_chart_activate_plugin_name' );
register_deactivation_hook( __FILE__, 'productsize_chart_deactivate_plugin_name' );

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )  ) { 

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-productsize-chart.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_productsize_chart_for_woocommerce() {

	$plugin = new productsize_chart();
	$plugin->run();

}
run_productsize_chart_for_woocommerce();
}
<?php
/**
 * SuperSide Me is a super app-style mobile navigation plugin for WordPress.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2017 Robin Cornett
 * @license   GPL-2.0+
 * @link      https://robincornett.com
 *
 * @wordpress-plugin
 * Plugin Name:       SuperSide Me
 * Plugin URI:        https://robincornett.com/downloads/superside-me
 * Description:       SuperSide Me is a super app-style mobile navigation plugin for WordPress.
 * Version:           2.2.2
 * Author:            Robin Cornett
 * Author URI:        https://robincornett.com
 * Text Domain:       superside-me
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define the current plugin version
if ( ! defined( 'SUPERSIDEME_VERSION' ) ) {
	define( 'SUPERSIDEME_VERSION', '2.2.2' );
}

// Define the plugin basename
if ( ! defined( 'SUPERSIDEME_BASENAME' ) ) {
	define( 'SUPERSIDEME_BASENAME', plugin_basename( __FILE__ ) );
}

// Define the plugin file
if ( ! defined( 'SUPERSIDEME_PLUGIN_FILE' ) ) {
	define( 'SUPERSIDEME_PLUGIN_FILE', __FILE__ );
}

// Load required files;
function superside_me_require() {
	$files = array(
		'class-supersideme',
		'class-supersideme-definesettings',
		'class-supersideme-getsetting',
		'class-supersideme-builder',
		'class-supersideme-cron',
		'class-supersideme-css',
		'class-supersideme-customizer',
		'class-supersideme-enqueue',
		'class-supersideme-helper',
		'class-supersideme-helptabs',
		'class-supersideme-licensing',
		'class-supersideme-settings',
		'helper-functions',
	);

	foreach ( $files as $file ) {
		require plugin_dir_path( __FILE__ ) . 'includes/' . $file . '.php';
	}

}
superside_me_require();

// Instantiate dependent classes
$supersideme_builder    = new SuperSide_Me_Builder;
$supersideme_cron       = new SuperSide_Me_Cron;
$supersideme_customizer = new SuperSide_Me_Customizer;
$supersideme_enqueue    = new SuperSideMeEnqueue;
$supersideme_help       = new SuperSide_Me_HelpTabs;
$supersideme_licensing  = new SuperSide_Me_Licensing;
$supersideme_settings   = new SuperSide_Me_Settings;

$supersideme = new Superside_Me(
	$supersideme_builder,
	$supersideme_cron,
	$supersideme_customizer,
	$supersideme_enqueue,
	$supersideme_help,
	$supersideme_licensing,
	$supersideme_settings
);

$supersideme->run();

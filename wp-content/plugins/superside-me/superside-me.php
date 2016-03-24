<?php
/**
 * SuperSide Me is a super app-style mobile navigation plugin for WordPress.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2016 Robin Cornett
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 *
 * @wordpress-plugin
 * Plugin Name:       SuperSide Me
 * Plugin URI:        https://robincornett.com/downloads/superside-me
 * Description:       SuperSide Me is a super app-style mobile navigation plugin for WordPress.
 * Version:           1.9.0
 * Author:            Robin Cornett
 * Author URI:        http://robincornett.com
 * Text Domain:       superside-me
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'SUPERSIDEME_BASENAME' ) ) {
	define( 'SUPERSIDEME_BASENAME', plugin_basename( __FILE__ ) );
}

// Load required files;
function superside_me_require() {
	$files = array(
		'class-supersideme',
		'class-supersideme-builder',
		'class-supersideme-css',
		'class-supersideme-customizer',
		'class-supersideme-helper',
		'class-supersideme-licensing',
		'class-supersideme-settings',
		'deprecated-filters',
		'helper-functions',
	);

	foreach ( $files as $file ) {
		require plugin_dir_path( __FILE__ ) . 'includes/' . $file . '.php';
	}

}
superside_me_require();

// Instantiate dependent classes
$supersideme_builder    = new SuperSide_Me_Builder();
$supersideme_css        = new Superside_Me_CSS();
$supersideme_customizer = new Superside_Me_Customizer();
$supersideme_helper     = new SuperSide_Me_Helper();
$supersideme_licensing  = new SuperSide_Me_Licensing();
$supersideme_settings   = new Superside_Me_Settings();

$supersideme = new Superside_Me(
	$supersideme_builder,
	$supersideme_css,
	$supersideme_customizer,
	$supersideme_licensing,
	$supersideme_settings
);

$supersideme->run();

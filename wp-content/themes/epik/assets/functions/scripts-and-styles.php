<?php
/**
 * This file enqueues scripts and styles
 *
 * @author     Joe Dooley
 * @package    SportPort Active Theme
 * @subpackage Customizations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Enqueue global theme scripts and styles. Includes
 * localization script for accessibility mobile navigation.
 *
 */
function spa_scripts_styles() {

	wp_enqueue_style(
		'fonts',
		get_stylesheet_directory_uri() . '/assets/fonts/fonts.css',
		array(),
		CHILD_THEME_VERSION
	);

	wp_enqueue_style(
		'ionicons',
		'//code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css',
		array(),
		'2.0.1'
	);

	wp_enqueue_script(
		'spa-custom-scripts',
		get_stylesheet_directory_uri() . '/assets/js/custom.min.js',
		array( 'jquery' ),
		CHILD_THEME_VERSION,
		true
	);

	wp_enqueue_script(
		'spa-vendor-scripts',
		get_stylesheet_directory_uri() . '/assets/js/vendors.min.js',
		array(),
		CHILD_THEME_VERSION,
		true
	);

	$output = array(
		'mainMenu' => __( 'Menu', 'epik' ),
		//'subMenu'  => __( 'Menu', 'epik' ),
	);


	wp_localize_script(
		'spa-custom-scripts',
		'DigitalL10n',
		$output
	);


}



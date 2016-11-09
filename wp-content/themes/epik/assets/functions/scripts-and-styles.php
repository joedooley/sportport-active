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
 */
function spa_scripts_styles() {

	wp_enqueue_style(
		'dashicons'
	);

	wp_enqueue_style(
		'fonts',
		CHILD_URL . '/dist/fonts/fonts.css',
		CHILD_THEME_VERSION
	);

	wp_enqueue_script(
		'spa-all-js',
		CHILD_URL . '/dist/js/all.js',
		[ 'jquery' ],
		CHILD_THEME_VERSION,
		true
	);

	$output = [
		'mainMenu' => __( 'Menu', 'EPIK' ),
		//'subMenu'  => __( 'Menu', 'EPIK'' ),
	];

	wp_localize_script(
		'spa-all-js',
		'DigitalL10n',
		$output
	);

}



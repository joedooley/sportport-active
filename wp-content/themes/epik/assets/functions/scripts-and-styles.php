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

	if ( is_product() || is_cart() ) {
		wp_enqueue_script(
			'increment-decrement',
			CHILD_JS_DIR . '/single/input-increment-decrement.js',
			[ 'jquery' ],
			CHILD_THEME_VERSION,
			true
		);
	}

	wp_enqueue_script(
		'spa-site-js',
		CHILD_JS_DIR . '/site.js',
		[ 'jquery' ],
		CHILD_THEME_VERSION,
		true
	);

	$output = [
		'mainMenu' => __( 'Menu', 'EPIK' ),
		//'subMenu'  => __( 'Menu', 'EPIK'' ),
	];

	wp_localize_script(
		'spa-site-js',
		'DigitalL10n',
		$output
	);

}



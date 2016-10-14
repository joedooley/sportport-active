<?php
/**
 * This file contains markup for the homepage
 *
 * @package    SportPort Active
 * @author     Developing Designs - Joe Dooley
 * @link       https://www.developingdesigns.com
 * @copyright  Joe Dooley, Developing Designs
 * @license    GPL-2.0+
 */

if ( ! function_exists( 'spa_fc_check' ) ) {

	add_action( 'get_header', 'spa_fc_check' );
	/**
	 * Outputs ACF flexible content fields. See
	 * '/assets/functions/theme-functions.php'
	 * for details.
	 */
	function spa_fc_check() {

		if ( have_rows( 'flexible_content' ) ) {

			add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
			remove_action( 'genesis_loop', 'genesis_do_loop' );
			add_action( 'genesis_loop', 'spa_acf_flexible_content' );

			add_theme_support( 'genesis-structural-wraps', [
				'header',
				'nav',
				'subnav',
				// 'site-inner',
				'footer-widgets',
				'footer',
			] );

		}
	}
}


add_action( 'wp_enqueue_scripts', function() {

	wp_enqueue_script(
		'backstretch',
		get_stylesheet_directory_uri() . '/assets/js/vendors/single/jquery.backstretch.min.js',
		array( 'jquery' ),
		'2.0.4',
		true
	);

	wp_enqueue_script(
		'backstretch-set',
		get_stylesheet_directory_uri() . '/assets/js/custom/single/backstretch-set.js',
		array( 'jquery', 'backstretch' ),
		CHILD_THEME_VERSION,
		true
	);

});


genesis();

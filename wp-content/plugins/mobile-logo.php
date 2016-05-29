<?php
/*
  Plugin Name:  Flex Alternate Mobile Logo
  Plugin URI:   https://github.com/SimpleProThemes/flex-alternate-mobile-logo
  Description:  Adding alternate mobile logo in Flex Pro Theme
  Author:       Simpleprothemes (@simpleprothemes)
  Author URI:   http://www.simpleprothemes.com/
  Version:      0.1
  Text Domain:  faml
  Domain Path:  /languages/
*/

/**
 * Copyright (c) 2015 www.simpleprothemes.com. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 */

/* Prevent direct access to the plugin */
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( __( "Sorry, you are not allowed to access this page directly.", 'faml' ) );
}

define( 'FAML_PLUGIN_DIR', dirname( __FILE__ ) );
$version = '0.1';

class FAML_Customizer {

	public function register( $wp_customize ) {
		$this->flex_alternate_mobile_logo( $wp_customize );
	}

	function flex_alternate_mobile_logo( $wp_customize ) {
		$wp_customize->add_setting( 'flex_alt_mobile_logo', array(
				'default'           => '',
				'type'              => 'option',
				'sanitize_callback' => 'esc_url_raw',
			) );

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'flex_alt_mobile_logo', array(
				'label'       => sprintf( __( 'Upload Alternate Mobile Logo:', 'faml' ), $image ),
				'section'     => 'header_image',
				'settings'    => 'flex_alt_mobile_logo',
				'description' => __( 'Upload your alternate mobile logo', 'faml' ),
				'priority'    => 45,
			) ) );

		$wp_customize->add_setting( 'breakpoint', array(
				'default'           => '960',
				'type'              => 'option',
				'sanitize_callback' => 'absint',
			) );

		$wp_customize->add_control( 'breakpoint', array(
				'label'       => __( 'Enter a breakpoint', 'faml' ),
				'section'     => 'header_image',
				'settings'    => 'breakpoint',
				'description' => __( 'Enter the integer value. It will genarate @media queries.', 'faml' ),
				'type'        => 'text',
				'priority'    => 50,
			) );

	}
}

add_action( 'init', 'flex_alternate_mobile_logo_init' );
function flex_alternate_mobile_logo_init() {
	global $faml;
	$faml = new FAML_Customizer();
	add_action( 'customize_register', array( $faml, 'register' ) );
}

add_filter( 'genesis_seo_title', 'flex_alternate_mobile_logo', 12, 3 );
function flex_alternate_mobile_logo( $title, $inside, $wrap ) {
	$mobile_logo_url = get_option( 'flex_alt_mobile_logo' );
	if ( ! empty( $mobile_logo_url ) ) {
		$mobile_logo = '<img src="' . $mobile_logo_url . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" title="' . esc_attr( get_bloginfo( 'name' ) )
		               . '" width="125" height="125" id="mobile-logo" data-pin-no-hover="true" />';

		preg_match_all( "#<a[^>]+>(.+?)</a>#ims", $title, $m );
		$logo = $m[1][0] . $mobile_logo;

		$title = str_replace( $m[1][0], $logo, $title );
	}

	return $title;
}

add_action( 'wp_head', 'faml_media_queries' );
function faml_media_queries() {
	$breakpoint      = get_option( 'breakpoint', 960 );
	$mobile_logo_url = get_option( 'flex_alt_mobile_logo' );

	if ( ! empty( $mobile_logo_url ) && ( ! empty( $breakpoint ) ) ) {

		$css = '
	#mobile-logo {
		display: none;
	}
	@media only screen and (max-width: ' . $breakpoint . 'px) {
		#logo,
		#rlogo {
			display: none;
		}

		#mobile-logo {
			display: block;
			margin: 0 auto;
		}
	}
	';
		/** Minify a bit */
		$css = str_replace( "\t", '', $css );
		$css = str_replace( array( "\n", "\r" ), ' ', $css );

		/** Echo the CSS */
		echo '<style type="text/css" media="screen">' . $css . '</style>';
	}
}

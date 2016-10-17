<?php
/**
 * Customize Background Image Control Class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 3.4.0
 */

/**
 * Get default link color for Customizer.
 *
 * Abstracted here since at least two functions use it.
 *
 * @since 1.0.0
 *
 * @return string Hex color code for link color.
 */
function atmosphere_customizer_get_default_link_color() {
	return '#55acee';
}

/**
 * Get default accent color for Customizer.
 *
 * Abstracted here since at least two functions use it.
 *
 * @since 1.0.0
 *
 * @return string Hex color code for accent color.
 */

function atmosphere_customizer_get_default_accent_color() {
	return '#34313b';
}

add_action( 'customize_register', 'atmosphere_customizer_register' );
/**
 * Register settings and controls with the Customizer.
 *
 * @since 1.0.0
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 */
function atmosphere_customizer_register() {

	global $wp_customize;

	$wp_customize->add_section( 'atmosphere-image', array(
		'title'       => __( 'Front Page Image', 'atmosphere' ),
		'description' => __( '<p>Use the default image or personalize your site by uploading your own image for the front page 1 widget background.</p><p>The default image is <strong>1600 x 1050 pixels</strong>.</p>', 'atmosphere' ),
		'priority'    => 75,
	) );

	$wp_customize->add_setting( 'atmosphere-front-image', array(
		'default'           => sprintf( '%s/images/front-page-1.jpg', get_stylesheet_directory_uri() ),
		'sanitize_callback' => 'esc_url_raw',
		'type'              => 'option',
	) );

	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'front-background-image', array(
				'label'    => __( 'Front Image Upload', 'atmosphere' ),
				'section'  => 'atmosphere-image',
				'settings' => 'atmosphere-front-image',
			) ) );

	$wp_customize->add_setting( 'atmosphere_link_color', array(
			'default'           => atmosphere_customizer_get_default_link_color(),
			'sanitize_callback' => 'sanitize_hex_color',
		) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'atmosphere_link_color', array(
				'description' => __( 'Change the default color for linked titles, menu links, post info links and more.', 'atmosphere' ),
				'label'       => __( 'Link Color', 'atmosphere' ),
				'section'     => 'colors',
				'settings'    => 'atmosphere_link_color',
			) ) );

	$wp_customize->add_setting( 'atmosphere_accent_color', array(
			'default'           => atmosphere_customizer_get_default_accent_color(),
			'sanitize_callback' => 'sanitize_hex_color',
		) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'atmosphere_accent_color', array(
				'description' => __( 'Change the default color for button hover and the footer widget background.', 'atmosphere' ),
				'label'       => __( 'Accent Color', 'atmosphere' ),
				'section'     => 'colors',
				'settings'    => 'atmosphere_accent_color',
			) ) );

}


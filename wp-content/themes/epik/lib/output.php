<?php
/* 
 * Adds the required CSS to the front end.
 */

add_action( 'wp_enqueue_scripts', 'epik_css' );
/**
* Checks the settings for the images and background colors for each image
* If any of these value are set the appropriate CSS is output
*
* @since 1.0
*/
function epik_css() {

	$handle  = defined( 'CHILD_THEME_NAME' ) && CHILD_THEME_NAME ? sanitize_title_with_dashes( CHILD_THEME_NAME ) : 'child-theme';

	$opts = apply_filters( 'epik_images', array( '1', '3', '4', '5', '6', '7', '8', '9', '14' ) );

	$settings = array();

	foreach( $opts as $opt ){
		$settings[$opt]['image'] = get_option( $opt .'-image', sprintf( '%s/images/bg-%s.jpg', get_stylesheet_directory_uri(), $opt ) );
	}

$css = '';

foreach ( $settings as $section => $value ) { 

	$background = $value['image'] ? sprintf( 'background-image: url(%s);', $value['image'] ) : '';

	$css .= ( ! empty( $section ) && ! empty( $background ) ) ? sprintf( '.hf%s { %s }', $section, $background ) : '';

}

if( $css ){
	wp_add_inline_style( $handle, $css );
}

}

<?php
/**
 * This file adds the theme defaults to SportPort Active Theme
 * @author     Joe Dooley
 * @package    SportPort Active Theme
 * @subpackage Customizations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly



add_filter( 'genesis_seo_title', 'spa_header_inline_logo', 10, 3 );
/**
 * Replace Header Site Title with Inline Logo.
 * Use a Mobile Logo if wp_is_mobile().
 *
 * @param $title
 * @param $inside
 * @param $wrap
 *
 * @return string
 */
function spa_header_inline_logo( $title, $inside, $wrap ) {

	$logo_mobile = '<img id="mobile-logo" src="' . get_stylesheet_directory_uri() . '/images/mobile-logo@2x.png" alt="' . esc_attr( get_bloginfo( 'name' ) ) .
	               '" title="' . esc_attr( get_bloginfo( 'name' ) ) . '" width="262" height="23" />';

	$logo = '<img src="' . get_stylesheet_directory_uri() . '/images/logo@2x.png" alt="' . esc_attr( get_bloginfo( 'name' ) ) .
	        '" title="' . esc_attr( get_bloginfo( 'name' ) ) . '" width="317" height="91" />';

	$inside = wp_is_mobile() ? sprintf( '<a href="%s" title="%s">%s</a>', trailingslashit( home_url() ), esc_attr( get_bloginfo( 'name' ) ), $logo_mobile ) : sprintf( '<a href="%s" title="%s">%s</a>', trailingslashit( home_url() ), esc_attr( get_bloginfo( 'name' ) ), $logo );

	// Determine which wrapping tags to use - changed is_home to is_front_page to fix Genesis bug
	$wrap = is_front_page() && 'title' === genesis_get_seo_option( 'home_h1_on' ) ? 'h1' : 'p';

	// A little fallback, in case an SEO plugin is active - changed is_home to is_front_page to fix Genesis bug
	$wrap = is_front_page() && ! genesis_get_seo_option( 'home_h1_on' ) ? 'h1' : $wrap;

	// And finally, $wrap in h1 if HTML5 & semantic headings enabled
	$wrap = genesis_html5() && genesis_get_seo_option( 'semantic_headings' ) ? 'h1' : $wrap;

	return sprintf( '<%1$s %2$s>%3$s</%1$s>', $wrap, genesis_attr( 'site-title' ), $inside );

}


/**
 * Set it and Forget it! Fix Gravity Form Tabindex Conflicts.
 * Assign the $starting_index variable to a high number.
 *
 * @link     http://gravitywiz.com/fix-gravity-form-tabindex-conflicts/
 *
 * @param        $tab_index
 * @param   bool $form
 *
 * @return  int
 */
add_filter( 'gform_tabindex', 'spa_forget_about_tabindex', 10, 2 );
function spa_forget_about_tabindex( $tab_index, $form = false ) {

	$starting_index = 1000;

	if ( $form ) {
		add_filter( 'gform_tabindex_' . $form['id'], 'spa_forget_about_tabindex' );
	}

	return GFCommon::$tab_index >= $starting_index ? GFCommon::$tab_index : $starting_index;
}


// Enable shortcodes in widgets
add_filter( 'widget_text', 'do_shortcode' );


// Enable PHP in widgets
add_filter( 'widget_text', 'spa_execute_php', 100 );
function spa_execute_php( $html ) {
	if ( strpos( $html, "<" . "?php" ) !== false ) {
		ob_start();
		eval( "?" . ">" . $html );
		$html = ob_get_contents();
		ob_end_clean();
	}

	return $html;
}


add_filter( 'upload_mimes', 'spa_svg_mime_types' );
/**
 * Allow SVG's in the WordPress uploader.
 * @author Joe Dooley
 */
function spa_svg_mime_types( $mimetypes ) {
	$mimetypes['svg'] = 'image/svg+xml';

	return $mimetypes;
}


add_action( 'admin_head', 'spa_svg_size' );
/**
 * Hack to make SVG's look normal in the WordPress media library.
 * @author Joe Dooley
 */
function spa_svg_size() {
	echo '<style>
    svg, img[src*=".svg"] {
      max-width: 150px !important;
      max-height: 150px !important;
    }
  </style>';
}



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
 *
 * Replace Header Site Title with Inline Logo
 *
 * Fixes Genesis bug - when using static front page and blog page (admin reading settings) Home page is <p> tag and Blog page is <h1> tag
 *
 * Replaces "is_home" with "is_front_page" to correctly display Home page wit <h1> tag and Blog page with <p> tag
 *
 * @author AlphaBlossom / Tony Eppright
 * @link   http://www.alphablossom.com/a-better-wordpress-genesis-responsive-logo-header/
 *
 * @edited by Sridhar Katakam
 * @link   http://www.sridharkatakam.com/use-inline-logo-instead-background-image-genesis/
 *
 **/
function spa_header_inline_logo( $title, $inside, $wrap ) {

	$logo = '<img src="' . get_stylesheet_directory_uri() . '/images/logo.png" alt="' . esc_attr(
			get_bloginfo(
				'name' ) ) . '" title="' . esc_attr( get_bloginfo( 'name' ) ) . '" width="320" height="31" />';

	$inside = sprintf( '<a href="%s" title="%s">%s</a>', trailingslashit( home_url() ), esc_attr( get_bloginfo( 'name' ) ), $logo );

	// Determine which wrapping tags to use - changed is_home to is_front_page to fix Genesis bug
	$wrap = is_front_page() && 'title' === genesis_get_seo_option( 'home_h1_on' ) ? 'h1' : 'p';

	// A little fallback, in case an SEO plugin is active - changed is_home to is_front_page to fix Genesis bug
	$wrap = is_front_page() && ! genesis_get_seo_option( 'home_h1_on' ) ? 'h1' : $wrap;

	// And finally, $wrap in h1 if HTML5 & semantic headings enabled
	$wrap = genesis_html5() && genesis_get_seo_option( 'semantic_headings' ) ? 'h1' : $wrap;

	return sprintf( '<%1$s %2$s>%3$s</%1$s>', $wrap, genesis_attr( 'site-title' ), $inside );

}

//add_filter( 'genesis_footer_creds_text', 'spa_personalize_footer_creds' );
///**
// * Personalize the copyright output in the footer
// *
// * @param $output
// *
// */
//function spa_personalize_footer_creds( $output ) {
//
//	echo '<div class="creds">
//		    <p><span>Copyright &copy;' . date( 'Y' ) . '</span>
//		        <a href="/" title="spa Places Mystical Tours of Ireland">- spa Places Tour</a>
//		         - All Rights Reserved<br>Designed and Developed by
//		        <a href="https://www.developingdesigns.com/" title="Developing Designs is a marketing
//		        agency specializing in custom WordPress Development, Digital Advertising and SEO"
//		        target="blank">Developing Designs</a>
//		    </p>
//		 </div>';
//
//}


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



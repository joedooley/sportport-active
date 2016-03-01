<?php
//* Start the engine
require_once( get_template_directory() . '/lib/init.php' );

//* Setup Theme
include_once( get_stylesheet_directory() . '/assets/functions/theme-functions.php' );

//* Include Customizer files
include_once( get_stylesheet_directory() . '/assets/functions/admin/output.php' );

//* Include widgets.php
require_once( get_stylesheet_directory() . '/assets/functions/widgets.php' );

//* Include genesis.php
require_once( get_stylesheet_directory() . '/assets/functions/genesis.php' );

//* Include scripts-and-styles.php
require_once( get_stylesheet_directory() . '/assets/functions/scripts-and-styles.php' );

//* Include woocommerce.php
require_once( get_stylesheet_directory() . '/assets/functions/woocommerce.php' );


//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', 'Epik Theme', 'epik' );
define( 'CHILD_THEME_URL', 'http://appfinite.com/themes/epik' );
define( 'CHILD_THEME_VERSION', '1.0' );



//* Add Image upload to WordPress Theme Customizer
add_action( 'customize_register', 'epik_customizer' );
function epik_customizer() {
	require_once( get_stylesheet_directory() . '/assets/functions/admin/customize.php' );
}





//* GRITEYE CUSTOM CODE BELOW ========================================================================

//* Remove the entry meta in the entry header (requires HTML5 theme support)
remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );

/**
 * see https://journalxtra.com/wordpress/genesis-theme/integrate-woocommerce-genesis-themes/
 * on how to more fully integrate WooCommerce into Genesis
*/
/* WooCommerce Integration */

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('woocommerce_before_main_content', 'vr_theme_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'vr_theme_wrapper_end', 10);

function vr_theme_wrapper_start() {

 // Remove .entry class from WooCommerce div content container
 function vr_post_class( $wp_classes, $extra_classes )
 {
     // List of the only WP generated classes that are not allowed
     $blacklist = array( 'entry' );

     // Blacklist result:
     $wp_classes = array_diff( $wp_classes, $blacklist );

     // Add the extra classes back untouched
     return array_merge( $wp_classes, (array) $extra_classes );
 }
 add_filter( 'post_class', 'vr_post_class', 10, 2 );

 // Add 'page' class to <article>
 function vr_extra_article_class( $attributes ) {
   $attributes['class'] = $attributes['class']. ' page';
     return $attributes;
 }
 add_filter( 'genesis_attr_entry', 'vr_extra_article_class' );

 // Add containers around content
 do_action( 'genesis_before_entry' );

 printf( '<article %s>', genesis_attr( 'entry' ) );

 // uncomment to add post/page title
 /* do_action( 'genesis_entry_header' ); */

 // uncomment to add by-lines
 /* do_action( 'genesis_before_entry_content' ); */

 printf( '<div %s>', genesis_attr( 'entry-content' ) );
}

function vr_theme_wrapper_end() {
 echo '</div>';

 do_action( 'genesis_after_entry_content' );

 // Uncomment to display categories & tags
 /* do_action( 'genesis_entry_footer' ); */

 echo '</article>';

 do_action( 'genesis_after_entry' );
}



/**
 * see http://www.carriedils.com/woocommerce-genesis-important-style/ about
 * when using Woo and Genesis, we want Genesis CSS to load later
 * Remove Genesis child theme style sheet
 * @uses  genesis_meta  <genesis/lib/css/load-styles.php>
*/
remove_action( 'genesis_meta', 'genesis_load_stylesheet' );

/**
 * Enqueue Genesis child theme style sheet at higher priority
 * @uses wp_enqueue_scripts <http://codex.wordpress.org/Function_Reference/wp_enqueue_style>
 */
add_action( 'wp_enqueue_scripts', 'genesis_enqueue_main_stylesheet', 15 );


/* Disable WordPress Admin Bar for all users but admins. */
  show_admin_bar(false);


//* Modify breadcrumb arguments.
add_filter( 'genesis_breadcrumb_args', 'sp_breadcrumb_args' );
function sp_breadcrumb_args( $args ) {
	$args['home'] = 'Home';
	$args['sep'] = ' ::: ';
	$args['list_sep'] = ', '; // Genesis 1.5 and later
	$args['prefix'] = '<div class="breadcrumb">';
	$args['suffix'] = '</div>';
	$args['heirarchial_attachments'] = true; // Genesis 1.5 and later
	$args['heirarchial_categories'] = true; // Genesis 1.5 and later
	$args['display'] = true;
	$args['labels']['prefix'] = '';
	$args['labels']['author'] = 'SportPort ';
	$args['labels']['category'] = 'SportPort '; // Genesis 1.6 and later
	$args['labels']['tag'] = 'Archives for ';
	$args['labels']['date'] = 'Archives for ';
	$args['labels']['search'] = 'Search for ';
	$args['labels']['tax'] = 'Archives for ';
	$args['labels']['post_type'] = 'Archives for ';
	$args['labels']['404'] = 'Not found: '; // Genesis 1.5 and later
return $args;
}

/* Add body class to every page with a category */

function pn_body_class_add_categories( $classes ) {

	// Only proceed if we're on a single post page
	if ( !is_single() )
		return $classes;

	// Get the categories that are assigned to this post
	$post_categories = get_the_category();

	// Loop over each category in the $categories array
	foreach( $post_categories as $current_category ) {

		// Add the current category's slug to the $body_classes array
		$classes[] = 'category-' . $current_category->slug;

	}

	// Finally, return the $body_classes array
	return $classes;
}
add_filter( 'body_class', 'pn_body_class_add_categories' );


//* END GRITEYE CUSTOM ================================================================




add_action( 'genesis_before', 'spa_google_tag_manager' );
/**
 * Add required Google Tag Manager script
 * after the opening <body> tag. Needed for
 * Google Tag Manager for WordPress plugin.
 *
 * @return 		void
 * @author 		Joe Dooley
 *
 */
function spa_google_tag_manager() {
	if ( function_exists( 'gtm4wp_the_gtm_tag' ) ) {
		gtm4wp_the_gtm_tag();
	}
}



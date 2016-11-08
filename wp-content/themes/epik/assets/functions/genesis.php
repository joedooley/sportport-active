<?php
/**
 * This file contains all Genesis specific functions
 *
 * @author     Joe Dooley
 * @package    SportPort Active Theme
 * @subpackage Customizations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'spa_add_theme_support' );
/**
 * Add theme support features on after-theme-setup hook
 *
 * @author Joe Dooley
 *
 */
function spa_add_theme_support() {

	add_theme_support( 'html5' );
	add_theme_support( 'genesis-responsive-viewport' );
	add_theme_support( 'custom-background' );

	add_theme_support( 'genesis-structural-wraps', array(
		'header',
		'nav',
		'subnav',
		'inner',
		'footer-widgets',
		'footer',
	) );



	add_action( 'wp_enqueue_scripts', 'spa_scripts_styles' );

	//* Remove the site description
	remove_action( 'genesis_site_description', 'genesis_seo_site_description' );

	//* Remove header right widget area
	unregister_sidebar( 'header-right' );

	//* Reposition secondary navigation menu
	remove_action( 'genesis_after_header', 'genesis_do_nav' );
	add_action( 'genesis_header', 'genesis_do_nav', 12 );

	// Remove Page/Post Edit Links
	add_filter( 'genesis_edit_post_link', '__return_false' );

	/**
	 * Load Internationalization File
	 */
	load_child_theme_textdomain( 'epik', apply_filters( 'child_theme_textdomain', get_stylesheet_directory() . '/languages', 'epik' ) );


}


//* Remove the entry meta in the entry header (requires HTML5 theme support)
remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );



/**
 * Customize search form input box text
 *
 * @return string|void
 */
add_filter( 'genesis_search_text', function () {
	return esc_attr( 'Search...' );
} );


/**
 * see http://www.carriedils.com/woocommerce-genesis-important-style/ about
 * when using Woo and Genesis, we want Genesis CSS to load later
 * Remove Genesis child theme style sheet
 * @uses  genesis_meta  <genesis/lib/css/load-styles.php>
 */
remove_action( 'genesis_meta', 'genesis_load_stylesheet' );
add_action( 'wp_enqueue_scripts', 'genesis_enqueue_main_stylesheet', 15 );


/**
 * Register custom image sizes
 */
add_action( 'init', function() {

	add_image_size( 'featured-img', 730, 420, true );
	add_image_size( 'featured-page', 341, 173, true );
	add_image_size( 'portfolio-thumbnail', 264, 200, true );
	add_image_size( 'featured-posts', 450, 250, true );

});



add_filter( 'genesis_breadcrumb_args', 'sp_breadcrumb_args' );
/**
 * Modify breadcrumb arguments.
 *
 * @param $args
 *
 * @return mixed
 */
function sp_breadcrumb_args( $args ) {
	$args['home']                    = 'Home';
	$args['sep']                     = ' ::: ';
	$args['list_sep']                = ', '; // Genesis 1.5 and later
	$args['prefix']                  = '<div class="breadcrumb">';
	$args['suffix']                  = '</div>';
	$args['heirarchial_attachments'] = true; // Genesis 1.5 and later
	$args['heirarchial_categories']  = true; // Genesis 1.5 and later
	$args['display']                 = true;
	$args['labels']['prefix']        = '';
	$args['labels']['author']        = 'SportPort ';
	$args['labels']['category']      = 'SportPort '; // Genesis 1.6 and later
	$args['labels']['tag']           = 'Archives for ';
	$args['labels']['date']          = 'Archives for ';
	$args['labels']['search']        = 'Search for ';
	$args['labels']['tax']           = 'Archives for ';
	$args['labels']['post_type']     = 'Archives for ';
	$args['labels']['404']           = 'Not found: '; // Genesis 1.5 and later
	return $args;
}


add_filter( 'body_class', 'pn_body_class_add_categories' );
/**
 * Add body class to every page with a category
 *
 * @param $classes
 *
 * @return array
 *
 */
function pn_body_class_add_categories( $classes ) {

	// Only proceed if we're on a single post page
	if ( ! is_single() ) {
		return $classes;
	}

	// Get the categories that are assigned to this post
	$post_categories = get_the_category();

	// Loop over each category in the $categories array
	foreach ( $post_categories as $current_category ) {

		// Add the current category's slug to the $body_classes array
		$classes[] = 'category-' . $current_category->slug;

	}

	return $classes;
}



add_filter( 'genesis_author_box_title', 'spa_author_box_title' );
/**
 * Customize author box title
 *
 * @return string
 */
function spa_author_box_title() {

	return '<span itemprop="name">' . get_the_author() . '</span>';

}



/**
 * Modify size of the Gravatar in the author box
 *
 * @param $size
 *
 * @return int
 */
add_filter( 'genesis_author_box_gravatar_size', function() {

	return 160;

});



/**
 * Modify Comment Author Avatar Size
 * @param $args
 *
 * @return mixed
 */
add_filter( 'genesis_comment_list_args', function( $args ) {

	$args['avatar_size'] = 120;

	return $args;

});


add_action( 'genesis_before_entry', 'be_remove_post_title_in_grid' );
/**
 *  Remove Post Title from Grid Posts
 */
function be_remove_post_title_in_grid() {

	if ( ! apply_filters( 'is_genesis_grid_loop', false ) ) {
		return;
	}

	$grid_items = [
		'teaser' => 'teaser',
	    'feature' => 'feature',
	];

	if ( is_array( $grid_items ) ) {

		remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
		remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
		remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );

		remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_open', 5 );
		remove_action( 'genesis_entry_footer', 'genesis_post_meta' );
		remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_close', 15 );

		add_action( 'genesis_entry_content', 'genesis_entry_footer_markup_open', 9 );
		add_action( 'genesis_entry_content', 'genesis_post_meta', 9 );
		add_action( 'genesis_entry_content', 'genesis_entry_footer_markup_close', 9 );

		add_action( 'genesis_entry_content', 'genesis_entry_header_markup_open', 9 );
		add_action( 'genesis_entry_content', 'genesis_do_post_title', 9 );
		add_action( 'genesis_entry_content', 'genesis_entry_header_markup_close', 9 );


	}

}


add_filter( 'genesis_post_meta', 'spa_post_meta_filter' );
/**
 * Remove post tags and limit post categories to one using
 * limited_post_categories custom shortcode
 *
 * @uses functions/shortcodes.php
 *
 * @param $post_meta
 *
 * @return string
 *
 */
function spa_post_meta_filter( $post_meta ) {

	if ( ! apply_filters( 'is_genesis_grid_loop', false ) ) {
		return $post_meta;
	}

	return '[limited_post_categories before=""]';
}



add_filter( 'get_the_content_more_link', 'spa_read_more_link' );
/**
 * Modify the Genesis content limit read more link
 *
 * @return string
 */
function spa_read_more_link() {
	return '... <div class="read-more-button"><a href="' . get_permalink() . '">Read More</a></div>';
}

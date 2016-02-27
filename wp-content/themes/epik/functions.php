<?php
//* Start the engine
require_once( get_template_directory() . '/lib/init.php' );

//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', 'Epik Theme', 'epik' );
define( 'CHILD_THEME_URL', 'http://appfinite.com/themes/epik' );

//* Enqueue Scripts/Styles
add_action( 'wp_enqueue_scripts', 'epik_enqueue_scripts_styles' );
function epik_enqueue_scripts_styles() {

	wp_enqueue_script( 'epik-responsive-menu', get_bloginfo( 'stylesheet_directory' ) . '/assets/js/responsive-menu.js', array( 'jquery' ), '1.0.0' );
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style( 'google-font', '//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700', array(), PARENT_THEME_VERSION );
	wp_enqueue_style( 'prefix-font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css', array(), '4.0.3' );

}

//* Add Image upload to WordPress Theme Customizer
add_action( 'customize_register', 'epik_customizer' );
function epik_customizer(){

	require_once( get_stylesheet_directory() . '/lib/customize.php' );

}

//* Include Section Image CSS
include_once( get_stylesheet_directory() . '/lib/output.php' );

//* Add HTML5 markup structure
add_theme_support( 'html5' );

//* Add viewport meta tag for mobile browsers
add_theme_support( 'genesis-responsive-viewport' );

//* Add support for custom background
add_theme_support( 'custom-background' );

// Create additional color style options
add_theme_support( 'genesis-style-selector', array(
	'epik-black' 		=>	__( 'Black', 'epik' ),
	'epik-blue' 		=>	__( 'Blue', 'epik' ),
	'epik-darkblue'		=>	__( 'Dark Blue', 'epik' ),
	'epik-gray' 		=> 	__( 'Gray', 'epik' ),
	'epik-green' 		=> 	__( 'Green', 'epik' ),
	'epik-orange' 		=> 	__( 'Orange', 'epik' ),
	'epik-pink' 		=> 	__( 'Pink', 'epik' ),
	'epik-purple' 		=> 	__( 'Purple', 'epik' ),
	'epik-red' 			=> 	__( 'Red', 'epik' ),
) );

//* Add support for custom header
add_theme_support( 'custom-header', array(
	'width'           => 360,
	'height'          => 164,
	'header-selector' => '.site-title a',
	'header-text'     => false,
) );

// Add new image sizes
add_image_size( 'featured-img', 730, 420, TRUE );
add_image_size( 'featured-page', 341, 173, TRUE );
add_image_size( 'portfolio-thumbnail', 264, 200, TRUE );

//* Add support for structural wraps
add_theme_support( 'genesis-structural-wraps', array(
	'header',
	'nav',
	'subnav',
	'inner',
	'footer-widgets',
	'footer',
) );

// Reposition the Secondary Navigation
remove_action( 'genesis_after_header', 'genesis_do_subnav' ) ;
add_action( 'genesis_before_header', 'genesis_do_subnav' );

// Before Header Wrap
add_action( 'genesis_before_header', 'before_header_wrap' );
function before_header_wrap() {
	echo '<div class="head-wrap">';
}

// Reposition the Primary Navigation
remove_action( 'genesis_after_header', 'genesis_do_nav' ) ;
add_action( 'genesis_after_header', 'genesis_do_nav' );

// After Header Wrap
add_action( 'genesis_after_header', 'after_header_wrap' );
function after_header_wrap() {
	echo '</div>';
}

//* Hooks after-entry widget area to single posts
add_action( 'genesis_after_entry', 'after_entry_widget', 5  );
function after_entry_widget() {

    if ( ! is_singular( 'post' ) )
    	return;

    genesis_widget_area( 'after-entry', array(
		'before' => '<div class="after-entry widget-area"><div class="wrap">',
		'after'  => '</div></div>',
    ) );

}

// Customize search form input box text
add_filter( 'genesis_search_text', 'custom_search_text' );
function custom_search_text($text) {
    return esc_attr( 'Search...' );
}

add_action( 'admin_menu', 'epik_theme_settings_init', 15 );
/**
 * This is a necessary go-between to get our scripts and boxes loaded
 * on the theme settings page only, and not the rest of the admin
 */
function epik_theme_settings_init() {
    global $_genesis_admin_settings;

    add_action( 'load-' . $_genesis_admin_settings->pagehook, 'epik_add_portfolio_settings_box', 20 );
}

// Add Portfolio Settings box to Genesis Theme Settings
function epik_add_portfolio_settings_box() {
    global $_genesis_admin_settings;

    add_meta_box( 'genesis-theme-settings-epik-portfolio', __( 'Portfolio Page Settings', 'epik' ), 'epik_theme_settings_portfolio',     $_genesis_admin_settings->pagehook, 'main' );
}

/**
 * Adds Portfolio Options to Genesis Theme Settings Page
 */
function epik_theme_settings_portfolio() { ?>

	<p><?php _e("Display which category:", 'genesis'); ?>
	<?php wp_dropdown_categories(array('selected' => genesis_get_option('epik_portfolio_cat'), 'name' => GENESIS_SETTINGS_FIELD.'[epik_portfolio_cat]', 'orderby' => 'Name' , 'hierarchical' => 1, 'show_option_all' => __("All Categories", 'genesis'), 'hide_empty' => '0' )); ?></p>

	<p><?php _e("Exclude the following Category IDs:", 'genesis'); ?><br />
	<input type="text" name="<?php echo GENESIS_SETTINGS_FIELD; ?>[epik_portfolio_cat_exclude]" value="<?php echo esc_attr( genesis_get_option('epik_portfolio_cat_exclude') ); ?>" size="40" /><br />
	<small><strong><?php _e("Comma separated - 1,2,3 for example", 'genesis'); ?></strong></small></p>

	<p><?php _e('Number of Posts to Show', 'genesis'); ?>:
	<input type="text" name="<?php echo GENESIS_SETTINGS_FIELD; ?>[epik_portfolio_cat_num]" value="<?php echo esc_attr( genesis_option('epik_portfolio_cat_num') ); ?>" size="2" /></p>

	<p><span class="description"><?php _e('<b>NOTE:</b> The Portfolio Page displays the "Portfolio Page" image size plus the excerpt or full content as selected below.', 'epik'); ?></span></p>

	<p><?php _e("Select one of the following:", 'genesis'); ?>
	<select name="<?php echo GENESIS_SETTINGS_FIELD; ?>[epik_portfolio_content]">
		<option style="padding-right:10px;" value="full" <?php selected('full', genesis_get_option('epik_portfolio_content')); ?>><?php _e("Display post content", 'genesis'); ?></option>
		<option style="padding-right:10px;" value="excerpts" <?php selected('excerpts', genesis_get_option('epik_portfolio_content')); ?>><?php _e("Display post excerpts", 'genesis'); ?></option>
	</select></p>

	<p><label for="<?php echo GENESIS_SETTINGS_FIELD; ?>[epik_portfolio_content_archive_limit]"><?php _e('Limit content to', 'genesis'); ?></label> <input type="text" name="<?php echo GENESIS_SETTINGS_FIELD; ?>[epik_portfolio_content_archive_limit]" id="<?php echo GENESIS_SETTINGS_FIELD; ?>[epik_portfolio_content_archive_limit]" value="<?php echo esc_attr( genesis_option('epik_portfolio_content_archive_limit') ); ?>" size="3" /> <label for="<?php echo GENESIS_SETTINGS_FIELD; ?>[epik_portfolio_content_archive_limit]"><?php _e('characters', 'genesis'); ?></label></p>

	<p><span class="description"><?php _e('<b>NOTE:</b> Using this option will limit the text and strip all formatting from the text displayed. To use this option, choose "Display post content" in the select box above.', 'genesis'); ?></span></p>
<?php
}

//* Add support for 3-column footer widgets
add_theme_support( 'genesis-footer-widgets', 3 );

// Register widget areas
genesis_register_sidebar( array(
	'id'			=> 'slider-wide',
	'name'			=> __( 'Slider Wide', 'epik' ),
	'description'	=> __( 'This is the wide slider section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'slider',
	'name'			=> __( 'Slider', 'epik' ),
	'description'	=> __( 'This is the slider section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'welcome-wide',
	'name'			=> __( 'Welcome Wide', 'epik' ),
	'description'	=> __( 'This is the Wide (full width) section of the Welcome area.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'welcome-feature-1',
	'name'			=> __( 'Welcome Feature #1', 'epik' ),
	'description'	=> __( 'This is the first column of the Welcome feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'welcome-feature-2',
	'name'			=> __( 'Welcome Feature #2', 'epik' ),
	'description'	=> __( 'This is the second column of the Welcome feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'welcome-feature-3',
	'name'			=> __( 'Welcome Feature #3', 'epik' ),
	'description'	=> __( 'This is the third column of the Welcome feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-1',
	'name'			=> __( 'Home Feature #1 (Left)', 'epik' ),
	'description'	=> __( 'This is the first column of the feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-2',
	'name'			=> __( 'Home Feature #2 (Right)', 'epik' ),
	'description'	=> __( 'This is the second column of the feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-3',
	'name'			=> __( 'Home Feature #3 (Gray)', 'epik' ),
	'description'	=> __( 'This is the 3rd column of the feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-4',
	'name'			=> __( 'Home Feature #4 (White)', 'epik' ),
	'description'	=> __( 'This is the 4th column of the feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-5',
	'name'			=> __( 'Home Feature #5 (Dark Gray)', 'epik' ),
	'description'	=> __( 'This is the 5th column of the feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-6',
	'name'			=> __( 'Home Feature #6 (White)', 'epik' ),
	'description'	=> __( 'This is the 6th column of the feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-7',
	'name'			=> __( 'Home Feature #7 (Gray)', 'epik' ),
	'description'	=> __( 'This is the 7th column of the feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-8',
	'name'			=> __( 'Home Feature #8 (White)', 'epik' ),
	'description'	=> __( 'This is the 8th column of the feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-9',
	'name'			=> __( 'Home Feature #9 (Gray)', 'epik' ),
	'description'	=> __( 'This is the 9th column of the feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-10',
	'name'			=> __( 'Home Feature #10', 'epik' ),
	'description'	=> __( 'This is the 10th column of the feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-11',
	'name'			=> __( 'Home Feature #11', 'epik' ),
	'description'	=> __( 'This is the 11th column of the feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-12',
	'name'			=> __( 'Home Feature #12', 'epik' ),
	'description'	=> __( 'This is the 12th column of the feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-13',
	'name'			=> __( 'Home Feature #13', 'epik' ),
	'description'	=> __( 'This is the 13th column of the feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-14',
	'name'			=> __( 'Home Feature #14 (White)', 'epik' ),
	'description'	=> __( 'This is the 14th column of the feature section of the homepage.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'after-entry',
	'name'			=> __( 'After Entry', 'epik' ),
	'description'	=> __( 'This widget will show up at the very end of each post.', 'epik' ),
) );

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

add_theme_support( 'genesis-connect-woocommerce' );
add_theme_support('woocommerce');


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

/**
 * Add floating logo
*/

add_action( 'genesis_before', 'add_logo_div' );

function add_logo_div() {

  echo '
<div class="sp-logo-div"></div>

';

} // End add_logo_div()

/* Disable WordPress Admin Bar for all users but admins. */
 // show_admin_bar(false);


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

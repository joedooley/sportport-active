<?php
/**
 * This file adds the giveaway template to SportPort Active Theme
 * @author     Joe Dooley
 * @package    SportPort Active Theme
 * @subpackage Customizations
 */

// This file adds the Giveaway template to the Epik Theme.

// Template Name: Giveaway

// Add custom body class to the head
add_filter( 'body_class', 'epik_add_body_class' );
function epik_add_body_class( $classes ) {
	$classes[] = 'giveaway-template';

	return $classes;
}

//* Remove Skip Links
remove_action( 'genesis_before_header', 'genesis_skip_links', 5 );

//* Dequeue Skip Links Script
add_action( 'wp_enqueue_scripts', 'genesis_sample_dequeue_skip_links' );
function genesis_sample_dequeue_skip_links() {
	wp_dequeue_script( 'skip-links' );
}

//* Force full width content layout
add_filter( 'genesis_site_layout', '__genesis_return_full_width_content' );

//* Remove Genesis_Before hook
remove_all_actions( 'genesis_before' );

//* Remove site header elements
remove_action( 'genesis_header', 'genesis_header_markup_open', 5 );
remove_action( 'genesis_header', 'genesis_do_header' );
remove_action( 'genesis_header', 'genesis_header_markup_close', 15 );

//* Remove navigation
remove_theme_support( 'genesis-menus' );

//* Remove breadcrumbs
remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );

//* Remove Entry Header
remove_all_actions( 'genesis_entry_header' );

//* Remove footer widgets
remove_action( 'genesis_before_footer', 'genesis_footer_widget_areas' );

//* Remove site footer elements
remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
remove_action( 'genesis_footer', 'genesis_do_footer' );
remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );

//* Remove Genesis_Before_Footer
remove_all_actions( 'genesis_before_footer' );

// Register a custom image size for Page Top images
add_image_size( 'giveaway-image', 1200, 500, true );

// Display Page Top Image (if present) above the content on static Pages
add_action( 'genesis_after_header', 'sk_display_featured_image' );
function sk_display_featured_image() {

	$image = get_field( 'background_image' );


	?>

	<div class = "page-top-image">
		<img src = "<?php echo $image['url']; ?>" />
	</div>
<?php }

genesis();

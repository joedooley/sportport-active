<?php
/**
* Plugin Name: Envira Gallery - Lightbox for Custom Images
* Plugin URI: http://enviragallery.com
* Version: 2.0
* Author: Erica Franz
* Author URI: https://fatpony.me/
* Description: Use the Envira Gallery lightbox for non-gallery images throughout your site.
*/

defined( 'ABSPATH' ) or die( 'No jellyfish!' );

add_action('wp_head', 'ekf_frontend_header', 99);
/**
 * Always enqueue Envira CSS and JS
 */
function ekf_frontend_header() {
	wp_enqueue_style( 'envira-gallery-style' );
	wp_enqueue_script( 'envira-gallery-script' );
}


add_action('wp_footer', 'ekf_frontend_footer', 99);
/**
 * Register Envirabox for .envirabox class images
 */
function ekf_frontend_footer() {
	?>
	<script type="text/javascript">jQuery('.envirabox').envirabox();</script>
	<?php
}


add_filter('the_content', 'ekf_add_classes_to_linked_images', 100, 1);
/** 
 * Add the .envirabox class to linked images
 */
function ekf_add_classes_to_linked_images($html) {

    $classes = 'envirabox'; // can do multiple classes, separate with space

    $patterns = array();
    $replacements = array();

    $patterns[0] = '/<a(?![^>]*class)([^>]*)>\s*<img([^>]*)>\s*<\/a>/'; // matches img tag wrapped in anchor tag where anchor has no existing classes
    $replacements[0] = '<a\1 class="' . $classes . '"><img\2></a>';

    $patterns[1] = '/<a([^>]*)class="([^"]*)"([^>]*)>\s*<img([^>]*)>\s*<\/a>/'; // matches img tag wrapped in anchor tag where anchor has existing classes contained in double quotes
    $replacements[1] = '<a\1class="' . $classes . ' \2"\3><img\4></a>';

    $patterns[2] = '/<a([^>]*)class=\'([^\']*)\'([^>]*)>\s*<img([^>]*)>\s*<\/a>/'; // matches img tag wrapped in anchor tag where anchor has existing classes contained in single quotes
    $replacements[2] = '<a\1class="' . $classes . ' \2"\3><img\4></a>';

    $html = preg_replace($patterns, $replacements, $html);

    return $html;
}
<?php
/**
 * Functions
 *
 * @package    SportPort Active
 * @author     Developing Designs - Joe Dooley
 * @link       https://www.developingdesigns.com
 * @copyright  Joe Dooley, Developing Designs
 * @license    GPL-2.0+
 */

//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', 'Epik Theme' );
define( 'CHILD_THEME_URL', 'https://www.sportportactive.com/' );
define( 'CHILD_THEME_VERSION', '1.0.0' );
define( 'CHILD_THEME_TEXTDOMAIN', 'epik' );

// Our text domain name and languages dir (don't change this).
load_child_theme_textdomain(
	CHILD_THEME_TEXTDOMAIN,
	get_stylesheet_directory() . '/languages'
);


require_once( get_template_directory() . '/lib/init.php' );
include_once( get_stylesheet_directory() . '/assets/functions/theme-functions.php' );
include_once( get_stylesheet_directory() . '/assets/functions/output.php' );
require_once( get_stylesheet_directory() . '/assets/functions/widgets.php' );
require_once( get_stylesheet_directory() . '/assets/functions/genesis.php' );
require_once( get_stylesheet_directory() . '/assets/functions/scripts-and-styles.php' );
require_once( get_stylesheet_directory() . '/assets/functions/woocommerce.php' );
require_once( get_stylesheet_directory() . '/assets/functions/layout.php' );
require_once( get_stylesheet_directory() . '/assets/functions/theme-options-page.php' );


/**
 * Add Image upload to WordPress Theme Customizer
 */
add_action( 'customize_register', function() {
	require_once( get_stylesheet_directory() . '/assets/functions/customize.php' );
});












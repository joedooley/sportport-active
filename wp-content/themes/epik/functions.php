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

/**
 * Bootstrap Genesis and include theme files.
 */
//include_once get_template_directory() . '/lib/init.php';
include_once __DIR__ . '/assets/functions/theme-functions.php';
require_once __DIR__ . '/assets/functions/widgets.php';
require_once __DIR__ . '/assets/functions/genesis.php';
require_once __DIR__ . '/assets/functions/scripts-and-styles.php';
require_once __DIR__ . '/assets/functions/woocommerce.php';
require_once __DIR__ . '/assets/functions/layout.php';
require_once __DIR__ . '/assets/functions/theme-options-page.php';
require_once __DIR__ . '/assets/functions/shortcodes.php';


/**
 * Define Child Theme Constants
 */
define( 'CHILD_THEME_NAME', 'Epik Theme' );
define( 'CHILD_THEME_URL', 'https://www.sportportactive.com/' );
define( 'CHILD_THEME_VERSION', '1.0.0' );
define( 'CHILD_THEME_TEXTDOMAIN', 'epik' );
define( 'CHILD_JS_DIR', get_stylesheet_directory_uri() . '/dist/js/custom' );
define( 'CHILD_VENDOR_JS_DIR', get_stylesheet_directory_uri() . '/dist/js/vendors' );


/**
 * Enable WooCommerce support.
 */
add_theme_support( 'genesis-connect-woocommerce' );


/**
 * Enable WooCommerce FacetWP variations
 */
add_filter( 'facetwp_enable_product_variations', '__return_true' );


/**
 * Show admin bar
 */
add_filter( 'show_admin_bar', '__return_true' );

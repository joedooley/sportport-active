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
 * Bootstrap Genesis
 */
require_once( get_template_directory() . '/lib/init.php' );


/**
 * Define Child Theme Constants
 */
define( 'CHILD_THEME_NAME', 'Epik Theme' );
define( 'CHILD_THEME_URL', 'https://www.sportportactive.com/' );
define( 'CHILD_THEME_VERSION', '1.0.0' );
define( 'CHILD_THEME_TEXTDOMAIN', 'epik' );
define( 'CHILD_FUNCTIONS_DIR', CHILD_DIR . '/assets/functions' );


/**
 * Load Internationalization File
 */
load_child_theme_textdomain( CHILD_THEME_TEXTDOMAIN, CHILD_DIR . '/languages' );

//require_once( CHILD_FUNCTIONS_DIR . '/customize.php' );
//include_once( CHILD_FUNCTIONS_DIR . '/output.php' );

include_once( CHILD_FUNCTIONS_DIR . '/theme-functions.php' );
require_once( CHILD_FUNCTIONS_DIR . '/widgets.php' );
require_once( CHILD_FUNCTIONS_DIR . '/genesis.php' );
require_once( CHILD_FUNCTIONS_DIR . '/scripts-and-styles.php' );
require_once( CHILD_FUNCTIONS_DIR . '/woocommerce.php' );
require_once( CHILD_FUNCTIONS_DIR . '/layout.php' );
require_once( CHILD_FUNCTIONS_DIR . '/theme-options-page.php' );






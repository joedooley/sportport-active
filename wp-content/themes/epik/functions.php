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

//* Include theme-options-page.php
require_once( get_stylesheet_directory() . '/assets/functions/admin/theme-options-page.php' );


//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', 'Epik Theme', 'epik' );
define( 'CHILD_THEME_URL', 'http://appfinite.com/themes/epik' );
define( 'CHILD_THEME_VERSION', '1.0' );



//* Add Image upload to WordPress Theme Customizer
add_action( 'customize_register', 'epik_customizer' );
function epik_customizer() {
	require_once( get_stylesheet_directory() . '/assets/functions/admin/customize.php' );
}





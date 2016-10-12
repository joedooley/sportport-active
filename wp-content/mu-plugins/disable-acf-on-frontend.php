<?php
/**
 * Plugin Name: Disable ACF on Frontend
 * Description: Provides a performance boost when ACF frontend functions are not used.
 * Version:     1.0
 * Author:      Joe Dooley
 * Author URI:  https://www.developingdesigns.com/
 * License:     MIT
 * License URI: http://www.opensource.org/licenses/mit-license.php
 */


/**
 * Disable ACF on Frontend of site.
 *
 * @param $plugins array
 * @return array
 */
add_filter( 'option_active_plugins', function ( $plugins ) {
	if ( is_admin() ) {
		return $plugins;
	}
	foreach ( $plugins as $i => $plugin ) {
		if ( 'advanced-custom-fields-pro/acf.php' === $plugin ) {
			unset( $plugins[ $i ] );
		}
	}

	return $plugins;
} );




<?php
/*
Plugin Name: Better WP ShowHide Elements
Plugin URI: http://akenn.org
Description: Adds some Javascript for you to hide or show what ever ID you want with a mouse click.
Version: 1.0
Author: Andrew Kennedy
Author URI: http://akenn.org
*/

function wp_showhide_scripts() {
  wp_enqueue_script(
    'wp-showhide',
    plugins_url("showhide.js", __FILE__),
    array( 'jquery' )
  );
}

add_action( 'wp_enqueue_scripts', 'wp_showhide_scripts' );

?>

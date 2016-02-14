<?php
/*
Plugin Name: Genesis Easy Columns
Plugin URI: http://www.code96wd.com/wordpress-plugins/
Description: Easily add Genesis column shortcodes to your WordPress editor.
Version: 1.4
Author: Matthew Smith
Author URI: http://www.code96wd.com
License: GPL2
*/

/*********************************
* Global Variables
*********************************/
	$gc_options = get_option('gc_settings');

/*********************************
* Includes
*********************************/
include('includes/functions.php');
include('includes/shortcodes.php');
include('includes/gc-settings.php');
<?php
/*
Plugin Name: FacetWP - Facet Stack
Description: A widget for creating a stack of facets
Version: 1.0
Author: David Cramer
Author URI: http://cramer.co.za
GitHub URI: Desertsnowman/facet-stack
License: GPLv2 or later
*/

defined( 'ABSPATH' ) or exit;

// setup constants
define( 'FACET_STACK_PATH', plugin_dir_path( __FILE__ ) );
define( 'FACET_STACK_URL', plugin_dir_url( __FILE__ ) );
define( 'FACET_STACK_VER', '1.0' );
define( 'FACET_STACK_BASENAME', plugin_basename( __FILE__ ) );

// load widget and initilize Facet Stack.
include_once FACET_STACK_PATH . 'includes/widget.php';
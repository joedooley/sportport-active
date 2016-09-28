<?php
/*
Plugin Name: CloudFlare
Plugin URI: http://www.cloudflare.com/wiki/CloudFlareWordPressPlugin
Description: CloudFlare integrates your blog with the CloudFlare platform.
Version: 3.0.5
Author: John Wineman, Furkan Yilmaz, Junade Ali (CloudFlare Team)
License: BSD-3-Clause
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

define('CLOUDFLARE_MIN_PHP_VERSION', '5.3.10');
define('CLOUDFLARE_MIN_WP_VERSION', '3.4');
define('CLOUDFLARE_PLUGIN_DIR', plugin_dir_path(__FILE__));

// PHP version check has to go here because the below code uses namespaces
if (version_compare(PHP_VERSION, CLOUDFLARE_MIN_PHP_VERSION, '<')) {
    // We need to load "plugin.php" manually to call "deactivate_plugins"
    require_once ABSPATH.'wp-admin/includes/plugin.php';

    deactivate_plugins(plugin_basename(__FILE__), true);
    wp_die('<p>The CloudFlare plugin requires a php version of at least '.CLOUDFLARE_MIN_PHP_VERSION.' you have '.PHP_VERSION.'.</p>', 'Plugin Activation Error', array('response' => 200, 'back_link' => true));
}

// Plugin uses namespaces. To support old PHP version which doesn't support
// namespaces we load everything in "cloudflare.loader.php"
require_once CLOUDFLARE_PLUGIN_DIR.'cloudflare.loader.php';

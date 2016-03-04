<?php
/*
Plugin Name: WordPress SEO News
Version: 3.1
Plugin URI: https://yoast.com/wordpress/plugins/news-seo/#utm_source=wpadmin&utm_medium=plugin&utm_campaign=wpseonewsplugin
Description: Google News plugin for the WordPress SEO plugin
Author: Team Yoast
Author URI: http://yoast.com/
Text Domain: wpseo_news
License: GPL v3

WordPress SEO Plugin
Copyright (C) 2008-2014, Team Yoast

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'WPSEO_NEWS_FILE' ) ) {
	define( 'WPSEO_NEWS_FILE', __FILE__ );
}

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload_52.php' ) ) {
	require dirname( __FILE__ ) . '/vendor/autoload_52.php';
}


// Load text domain
add_action( 'init', 'wpseo_news_load_textdomain' );
function wpseo_news_load_textdomain() {
	load_plugin_textdomain( 'wordpress-seo-news', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/**
 * WPSEO News __main method
 */
function __wpseo_news_main() {
	new WPSEO_News();
}

// Load WPSEO News
add_action( 'plugins_loaded', '__wpseo_news_main' );

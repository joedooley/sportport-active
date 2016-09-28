<?php

$currentDir = dirname(__FILE__);

define('_SQ_NAME_', 'squirrly');
define('_SQ_PLUGIN_NAME_', 'squirrly-seo'); //THIS LINE WILL BE CHANGED WITH THE USER SETTINGS
define('_THEME_NAME_', 'default'); //THIS LINE WILL BE CHANGED WITH THE USER SETTINGS

defined('_SQ_DASH_URL_') ||  define('_SQ_DASH_URL_', 'https://my.squirrly.co/');
$scheme = (((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") || (defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN)) ? 'https:' : 'http:'); //CHECK IF SCURE

defined('SQ_URI') || define('SQ_URI', (WP_VERSION_ID >= 3000) ? 'wp350' : 'wp2');
defined('_SQ_API_URL_') || define('_SQ_API_URL_', $scheme . '//api.squirrly.co/');

defined('_SQ_STATIC_API_URL_') || define('_SQ_STATIC_API_URL_', $scheme . '//storage.googleapis.com/squirrly/');
defined('_SQ_SUPPORT_URL_') || define('_SQ_SUPPORT_URL_', 'https://plus.google.com/u/0/communities/104196720668136264985');

/* Directories */
define('_SQ_ROOT_DIR_', realpath(dirname($currentDir)));
define('_SQ_CLASSES_DIR_', _SQ_ROOT_DIR_ . '/classes/');
define('_SQ_CONTROLLER_DIR_', _SQ_ROOT_DIR_ . '/controllers/');
define('_SQ_MODEL_DIR_', _SQ_ROOT_DIR_ . '/models/');
define('_SQ_TRANSLATIONS_DIR_', _SQ_ROOT_DIR_ . '/translations/');
define('_SQ_CORE_DIR_', _SQ_ROOT_DIR_ . '/core/');
define('_SQ_ALL_THEMES_DIR_', _SQ_ROOT_DIR_ . '/themes/');
define('_SQ_THEME_DIR_', _SQ_ROOT_DIR_ . '/themes/' . _THEME_NAME_ . '/');

/* URLS */
define('_SQ_URL_', plugins_url('', $currentDir));
define('_SQ_ALL_THEMES_URL_', _SQ_URL_ . '/themes/');
define('_SQ_THEME_URL_', _SQ_URL_ . '/themes/' . _THEME_NAME_ . '/');


$upload_dir = wp_upload_dir();
if (is_dir($upload_dir['basedir'])) {
    $upload_path = $upload_dir['basedir'] . '/' . _SQ_NAME_ . '/';

    //create directory if doesn't exists
    if (!is_dir($upload_path)) {
        wp_mkdir_p($upload_path);
    }

    if (is_dir($upload_path) && (function_exists('wp_is_writable') && wp_is_writable($upload_path))) {
        define('_SQ_CACHE_DIR_', realpath($upload_path) . '/');
        define('_SQ_CACHE_URL_', $upload_dir['baseurl'] . '/' . _SQ_NAME_ . '/');
    }
}

defined('_SQ_CACHE_DIR_') || define('_SQ_CACHE_DIR_', _SQ_ROOT_DIR_ . '/cache/');
defined('_SQ_CACHE_URL_') || define('_SQ_CACHE_URL_', _SQ_URL_ . '/cache/');



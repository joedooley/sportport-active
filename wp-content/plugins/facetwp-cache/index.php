<?php
/*
Plugin Name: FacetWP - Cache
Description: Caching support for FacetWP
Version: 1.3.2
Author: FacetWP, LLC
Author URI: https://facetwp.com/
GitHub URI: facetwp/facetwp-cache
*/

defined( 'ABSPATH' ) or exit;

class FacetWP_Cache
{

    private static $instance;


    function __construct() {

        // setup variables
        define( 'FACETWP_CACHE_VERSION', '1.3.2' );
        define( 'FACETWP_CACHE_DIR', dirname( __FILE__ ) );

        add_action( 'init' , array( $this, 'init' ) );
        add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 999 );
    }


    /**
     * Singleton
     */
    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self;
        }
        return self::$instance;
    }


    /**
     * Intialize
     */
    function init() {

        // upgrade
        include( FACETWP_CACHE_DIR . '/includes/class-upgrade.php' );
        $upgrade = new FacetWP_Cache_Upgrade();

        add_filter( 'facetwp_ajax_response', array( $this, 'save_cache' ), 10, 2 );
        add_action( 'facetwp_inject_template', array( $this, 'inject_template' ) );
        add_action( 'facetwp_cache_cleanup', array( $this, 'cleanup' ) );

        // Schedule daily cleanup
        if ( ! wp_next_scheduled( 'facetwp_cache_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'facetwp_cache_cleanup' );
        }

        // Manually purge cache
        if ( isset( $_GET['fwpcache'] ) && current_user_can( 'manage_options' ) ) {
            $this->cleanup( $_GET['fwpcache'] );
        }
    }


    /**
     * Cache the AJAX response
     */
    function save_cache( $output, $params ) {
        global $wpdb;

        // Caching support
        if ( defined( 'FACETWP_CACHE' ) && FACETWP_CACHE ) {
            $data = $params['data'];

            // Generate the cache token
            $cache_name = md5( json_encode( $data ) );
            $cache_uri = $data['http_params']['uri'];

            // Set the cache expiration
            $cache_lifetime = apply_filters( 'facetwp_cache_lifetime', 3600, array(
                'uri' => $cache_uri
            ) );

            $nocache = isset( $data['http_params']['get']['nocache'] );

            if ( false === $nocache ) {
                $wpdb->insert( $wpdb->prefix . 'facetwp_cache', array(
                    'name' => $cache_name,
                    'uri' => $cache_uri,
                    'value' => $output,
                    'expire' => date( 'Y-m-d H:i:s', time() + $cache_lifetime )
                ) );
            }
        }

        return $output;
    }


    /**
     * Support CSS-based templates
     * Save the cached output right before PHP shutdown
     */
    function inject_template( $output ) {
        $data = stripslashes_deep( $_POST['data'] );
        $this->save_cache( json_encode( $output ), array( 'data' => $data ) );
    }


    /**
     * Delete expired cache
     */
    function cleanup( $uri = false ) {
        global $wpdb;

        if ( false === $uri ) {
            $now = date( 'Y-m-d H:i:s' );
            $wpdb->query( "DELETE FROM {$wpdb->prefix}facetwp_cache WHERE expire < '$now'" );
        }
        elseif ( 'all' == $uri ) {
            $wpdb->query( "TRUNCATE {$wpdb->prefix}facetwp_cache" );
        }
        else {
            $uri = ( 'this' == $uri ) ? $this->get_uri() : $uri;
            $wpdb->query(
                $wpdb->prepare( "DELETE FROM {$wpdb->prefix}facetwp_cache WHERE uri = %s", $uri )
            );
        }
    }


    /**
     * 
     */
    function admin_bar_menu( $wp_admin_bar ) {

        // Only show the menu on the front-end
        if ( is_admin() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $args = array(
            array(
                'id' => 'fwp-cache',
                'title' => 'FWP',
            ),
            array(
                'id' => 'fwp-cache-clear-page',
                'title' => 'Clear cache (this page)',
                'parent' => 'fwp-cache',
                'href' => '?fwpcache=this',
            ),
            array(
                'id' => 'fwp-cache-clear-all',
                'title' => 'Clear cache (all)',
                'parent' => 'fwp-cache',
                'href' => '?fwpcache=all',
            )
        );

        foreach ( $args as $arg ) {
            $wp_admin_bar->add_node( $arg );
        }
    }


    /**
     * Get the current page URI
     */
    function get_uri() {
        $uri = $_SERVER['REQUEST_URI'];
        if ( false !== ( $pos = strpos( $uri, '?' ) ) ) {
            $uri = substr( $uri, 0, $pos );
        }
        return trim( $uri, '/' );
    }
}


function FWP_Cache() {
    return FacetWP_Cache::instance();
}


$fwp_cache = FWP_Cache();

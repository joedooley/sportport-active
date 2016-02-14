<?php
/**
 * Plugin Name: Envira Gallery - Dynamic Addon
 * Plugin URI:  http://enviragallery.com
 * Description: Enables dynamic gallery generation for Envira galleries and albums.
 * Author:      Thomas Griffin
 * Author URI:  http://thomasgriffinmedia.com
 * Version:     1.1.1
 * Text Domain: envira-dynamic
 * Domain Path: languages
 *
 * Envira Gallery is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Envira Gallery is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Envira Gallery. If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main plugin class.
 *
 * @since 1.0.0
 *
 * @package Envira_Dynamic
 * @author  Tim Carr
 */
class Envira_Dynamic {

	/**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $version = '1.1.1';

    /**
     * The name of the plugin.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_name = 'Envira Dynamic';

    /**
     * Unique plugin slug identifier.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_slug = 'envira-dynamic';

    /**
     * Plugin file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the plugin textdomain.
        add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

        // Load the plugin.
        add_action( 'envira_gallery_init', array( $this, 'init' ), 99 );
        
    }

	/**
     * Loads the plugin textdomain for translation.
     *
     * @since 1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    }
    
    /**
	 * Fired when the plugin is activated.
	 *
	 * @since 1.0.0
	 *
	 * @global object $wpdb         The WordPress database object.
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false otherwise.
	 */
    public function activate( $network_wide = false ) {
		
		// Bail if the main class does not exist.
	    if ( ! class_exists( 'Envira_Gallery' ) ) {
	        return;
	    }
	    
		// Check if we are on a multisite install, activating network wide, or a single install
	    if ( is_multisite() && $network_wide ) {
		    // Multisite network wide activation
            // Iterate through each blog in multisite, creating a default gallery and album if needed
            $sites = wp_get_sites( array(
                'limit' => 0,
            ) );
            if ( is_array( $sites ) && count( $sites ) > 0 ) {
                foreach ( $sites as $site ) {
                    switch_to_blog( $site['blog_id'] );
                    $this->generate_dynamic_gallery();
                    $this->generate_dynamic_album();
                    restore_current_blog();
                }
            }
	    } else {
		    // Single Site - create dynamic gallery if needed
		    $this->generate_dynamic_gallery();
		    $this->generate_dynamic_album();
	    }
		    
    }
    
    /**
	* Checks if a Dynamic Gallery already exists. If not, a dynamic gallery is created.
	*
	* @since 1.0.0.
	*/
    public function generate_dynamic_gallery() {
	    
	    global $wpdb;
	    
	    // Get Envira Gallery Instance
	    $instance = Envira_Gallery_Common::get_instance();
	    
	    // Generate the custom gallery options holder for default dynamic galleries if it does not exist.
        $query = $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = '%s' AND post_type = '%s' LIMIT 1", 
        						 'envira-dynamic-gallery', 
        						 'envira' );
        $exists = $wpdb->get_var( $query );
        if ( !is_null( $exists ) ) {
	        return;
        }
        
        // Dynamic gallery does not exist - create it
		$args = array(
            'post_type'   => 'envira',
            'post_name'   => 'envira-dynamic-gallery',
            'post_title'  => __( 'Envira Dynamic Gallery', 'envira-dynamic' ),
            'post_status' => 'publish'
        );
        $dynamic_id = wp_insert_post( $args );
        
        // If successful, update our option so that we can know which gallery is dynamic.
        if ( $dynamic_id ) {
            update_option( 'envira_dynamic_gallery', $dynamic_id );

            // Loop through the defaults and prepare them to be stored.
            $defaults = $instance->get_config_defaults( $dynamic_id );
            foreach ( $defaults as $key => $default ) {
                $fields['config'][$key] = $default;
            }

            // Update some default post meta fields.
            $fields = array(
                'id'     => $dynamic_id,
                'config' => array(
                    'title'   => __( 'Envira Dynamic Gallery', 'envira-dynamic' ),
                    'slug'    => 'envira-dynamic-gallery',
                    'classes' => array( 'envira-dynamic-gallery' ),
                    'type'    => 'dynamic'
                ),
                'gallery' => array(),
            );

            // Update the meta field.
            update_post_meta( $dynamic_id, '_eg_gallery_data', $fields );
        }
        
    }
    
    /**
	* Checks if a Dynamic Album already exists. If not, a dynamic album is created.
	*
	* @since 1.0.0.
	*/
    public function generate_dynamic_album() {
	    
	    global $wpdb;

        // Check if the Albums Addon is activated
        // If not, don't attempt to generate a dynamic album
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        if ( ! is_plugin_active( 'envira-albums/envira-albums.php' ) ) {
            return;
        }
        if ( ! class_exists( 'Envira_Albums_Common' ) ) {
            return;
        }
	    
	    // Get Envira Album Instance
	    $instance = Envira_Albums_Common::get_instance();
	    
	    // Generate the custom album options holder for default dynamic albums if it does not exist.
        $query = $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = '%s' AND post_type = '%s' LIMIT 1", 
        						 'envira-dynamic-album', 
        						 'envira_album' );
        $exists = $wpdb->get_var( $query );
        if ( !is_null( $exists ) ) {
	        return;
        }
        
        // Dynamic album does not exist - create it
		$args = array(
            'post_type'   => 'envira_album',
            'post_name'   => 'envira-dynamic-album',
            'post_title'  => __( 'Envira Dynamic Album', 'envira-dynamic' ),
            'post_status' => 'publish'
        );
        $dynamic_id = wp_insert_post( $args );
        
        // If successful, update our option so that we can know which album is dynamic.
        if ( $dynamic_id ) {
            update_option( 'envira_dynamic_album', $dynamic_id );

            // Loop through the defaults and prepare them to be stored.
            $defaults = $instance->get_config_defaults( $dynamic_id );
            foreach ( $defaults as $key => $default ) {
                $fields['config'][$key] = $default;
            }

            // Update some default post meta fields.
            $fields = array(
                'id'     => $dynamic_id,
                'config' => array(
                    'title'   => __( 'Envira Dynamic Album', 'envira-dynamic' ),
                    'slug'    => 'envira-dynamic-album',
                    'classes' => array( 'envira-dynamic-album' ),
                    'type'    => 'dynamic'
                ),
                'gallery' => array(),
            );

            // Update the meta field.
            update_post_meta( $dynamic_id, '_eg_album_data', $fields );
        }
        
    }
    
    /**
	 * Fired when the plugin is uninstalled.
	 *
	 * @since 1.0.0
	 *
	 * @global object $wpdb The WordPress database object.
	 */
	function deactivate() {
	
	    // Bail if the main class does not exist.
	    if ( ! class_exists( 'Envira_Gallery' ) ) {
	        return;
	    }
	    
	    // Check if we are on a multisite install, activating network wide, or a single install
	    if ( is_multisite() ) {
		    // Multisite network wide activation
		    // Iterate through each blog in multisite, removing dynamic gallery and album if needed
		    global $wpdb;
	        $site_list = $wpdb->get_results( "SELECT * FROM $wpdb->blogs ORDER BY blog_id" );
	        foreach ( (array) $site_list as $site ) {
	            switch_to_blog( $site->blog_id );
	            $this->remove_dynamic_gallery();
	            $this->remove_dynamic_album();
	            restore_current_blog();
	        }
	    } else {
		    // Single Site - remove dynamic gallery and album if needed
		    $this->remove_dynamic_gallery();
	        $this->remove_dynamic_album();
	    }
	
	}
	
	/**
	* Removes the dynamic gallery
	*
    * @since 1.0.0
	*/
	public function remove_dynamic_gallery() {
		
		// Grab the dynamic gallery ID and use that to delete the gallery.
        $dynamic_id = get_option( 'envira_dynamic_gallery' );
        if ( $dynamic_id ) {
            wp_delete_post( $dynamic_id, true );
        }

        // Delete the option.
        delete_option( 'envira_dynamic_gallery' );
	    
	}
	
	/**
	* Removes the dynamic album
	*
    * @since 1.0.0
	*/
	public function remove_dynamic_album() {
		
		// Grab the dynamic album ID and use that to delete the album.
        $dynamic_id = get_option( 'envira_dynamic_album' );
        if ( $dynamic_id ) {
            wp_delete_post( $dynamic_id, true );
        }

        // Delete the option.
        delete_option( 'envira_dynamic_album' );
        
	}

    /**
     * Loads the plugin into WordPress.
     *
     * @since 1.0.0
     */
    public function init() {

        // Load admin only components.
        if ( is_admin() ) {
            $this->require_admin();
        }

        // Load global components.
        $this->require_global();

        // Load the updater
        add_action( 'envira_gallery_updater', array( $this, 'updater' ) );

    }

    /**
     * Loads all admin related files into scope.
     *
     * @since 1.0.0
     */
    public function require_admin() {

		require plugin_dir_path( __FILE__ ) . 'includes/admin/metaboxes.php';
        require plugin_dir_path( __FILE__ ) . 'includes/admin/table.php';

    }

    /**
	 * Initializes the addon updater.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The user license key.
	 */
	function updater( $key ) {

	    $args = array(
	        'plugin_name' => $this->plugin_name,
	        'plugin_slug' => $this->plugin_slug,
	        'plugin_path' => plugin_basename( __FILE__ ),
	        'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . $this->plugin_slug,
	        'remote_url'  => 'http://enviragallery.com/',
	        'version'     => $this->version,
	        'key'         => $key
	    );
        
	    $updater = new Envira_Gallery_Updater( $args );

	}

    /**
     * Loads all global files into scope.
     *
     * @since 1.0.0
     */
    public function require_global() {

		require plugin_dir_path( __FILE__ ) . 'includes/global/common.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/shortcode-album.php';
		require plugin_dir_path( __FILE__ ) . 'includes/global/shortcode-gallery.php';
		
    }

     /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Dynamic object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Dynamic ) ) {
            self::$instance = new Envira_Dynamic();
        }

        return self::$instance;

    }

}

// Load the main plugin class.
$envira_dynamic = Envira_Dynamic::get_instance();

// Register activation and deactivation hooks
register_activation_hook( __FILE__, array( &$envira_dynamic, 'activate' ) );
register_deactivation_hook( __FILE__, array( &$envira_dynamic, 'deactivate' ) );
add_action( 'activate_wpmu_site', array( &$envira_dynamic, 'activate' ) );
<?php
/**
 * Plugin Name: Envira Gallery - Albums Addon
 * Plugin URI:  http://enviragallery.com
 * Description: Enables album capabilities for Envira galleries.
 * Author:      Thomas Griffin
 * Author URI:  http://thomasgriffinmedia.com
 * Version:     1.2.4.7
 * Text Domain: envira-albums
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
 * @package Envira_Albums
 * @author  Tim Carr
 */
class Envira_Albums {

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
    public $version = '1.2.4.7';

    /**
     * The name of the plugin.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_name = 'Envira Albums';

    /**
     * Unique plugin slug identifier.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_slug = 'envira-albums';

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

   		// Fire a hook before the class is setup.
        do_action( 'envira_albums_pre_init' );

        // Load the plugin textdomain.
        add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

        // Load the plugin widget.
        add_action( 'widgets_init', array( $this, 'widget' ) );

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
     * Registers the Envira Albums widget.
     *
     * @since 1.0.0
     */
    public function widget() {

		require plugin_dir_path( __FILE__ ) . 'includes/global/widget.php';
        register_widget( 'Envira_Albums_Widget' );

    }

    /**
     * Loads the plugin into WordPress.
     *
     * @since 1.0.0
     */
    public function init() {

    	// Display a notice if Envira Standalone isn't enabled
    	// Don't load anything else until Standalone is enabled
    	if ( !defined( 'ENVIRA_STANDALONE_PLUGIN_NAME' ) ) {
    		add_action( 'admin_notices', array( $this, 'standalone_notice' ) );
	        return;
    	}

        // Run hook once Envira has been initialized.
        do_action( 'envira_albums_init' );

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
	 * Outputs 'standalone addon required' notice for the addon to work.
	 *
	 * @since 1.0.0
	 */
	function standalone_notice() {

	    ?>
	    <div class="error">
	        <p><?php printf( __( 'The <strong>%s Addon</strong> requires the Envira Standalone addon. Please install and activate the Standalone Addon.', 'envira-albums' ), $this->plugin_name ); ?></p>
	    </div>
	    <?php

	}

    /**
     * Loads all admin related files into scope.
     *
     * @since 1.0.0
     */
    public function require_admin() {

        require plugin_dir_path( __FILE__ ) . 'includes/admin/ajax.php';
        require plugin_dir_path( __FILE__ ) . 'includes/admin/capabilities.php';
        require plugin_dir_path( __FILE__ ) . 'includes/admin/common.php';
        require plugin_dir_path( __FILE__ ) . 'includes/admin/editor.php';
        require plugin_dir_path( __FILE__ ) . 'includes/admin/export.php';
        require plugin_dir_path( __FILE__ ) . 'includes/admin/import.php';
        require plugin_dir_path( __FILE__ ) . 'includes/admin/metaboxes.php';
        require plugin_dir_path( __FILE__ ) . 'includes/admin/posttype.php';

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
        require plugin_dir_path( __FILE__ ) . 'includes/global/posttype.php';
        require plugin_dir_path( __FILE__ ) . 'includes/global/shortcode.php';

    }

    /**
     * Returns an album based on ID.
     *
     * @since 1.0.0
     *
     * @param int $id     The album ID used to retrieve an album.
     * @return array|bool Array of album data or false if none found.
     */
    public function get_album( $id ) {

        // Attempt to return the transient first, otherwise generate the new query to retrieve the data.
        if ( false === ( $album = get_transient( '_eg_cache_' . $id ) ) ) {
            $album = $this->_get_album( $id );
            if ( $album ) {
                $expiration = Envira_Gallery_Common::get_instance()->get_transient_expiration_time( 'envira-albums' );
                set_transient( '_eg_cache_' . $id, $album, $expiration );
            }
        }

        // Return the album data.
        return $album;

    }

    /**
     * Internal method that returns an album based on ID.
     *
     * @since 1.0.0
     *
     * @param int $id     The album ID used to retrieve an album.
     * @return array|bool Array of album data or false if none found.
     */
    public function _get_album( $id ) {

        return get_post_meta( $id, '_eg_album_data', true );

    }

    /**
     * Returns an album based on slug.
     *
     * @since 1.0.0
     *
     * @param string $slug The album slug used to retrieve an album.
     * @return array|bool  Array of album data or false if none found.
     */
    public function get_album_by_slug( $slug ) {

        // Attempt to return the transient first, otherwise generate the new query to retrieve the data.
        if ( false === ( $album = get_transient( '_eg_cache_' . $slug ) ) ) {
            $album = $this->_get_album_by_slug( $slug );
            if ( $album ) {
                $expiration = Envira_Gallery_Common::get_instance()->get_transient_expiration_time( 'envira-albums' );
                set_transient( '_eg_cache_' . $slug, $album, $expiration );
            }
        }

        // Return the album data.
        return $album;

    }

    /**
     * Internal method that returns an album based on slug.
     *
     * @since 1.0.0
     *
     * @param string $slug The album slug used to retrieve an album.
     * @return array|bool  Array of album data or false if none found.
     */
    public function _get_album_by_slug( $slug ) {

		// Get Envira Album CPT by slug
		$albums = get_posts(
			array(
				'post_type' 	=> 'envira_album',
				'name' 			=> $slug,
				'fields'        => 'ids',
				'posts_per_page'=> 1,
			)
		);
		if ( $albums ) {
			return get_post_meta( $albums[0], '_eg_album_data', true );
		}

    }

    /**
     * Returns all albums created on the site.
     *
     * @since 1.0.0
     *
     * @return array|bool Array of album data or false if none found.
     */
    public function get_albums() {

        // Attempt to return the transient first, otherwise generate the new query to retrieve the data.
        if ( false === ( $albums = get_transient( '_ea_cache_all' ) ) ) {
            $albums = $this->_get_albums();
            if ( $albums ) {
                $expiration = Envira_Gallery_Common::get_instance()->get_transient_expiration_time( 'envira-albums' );
                set_transient( '_ea_cache_all', $albums, $expiration );
            }
        }

        // Return the album data.
        return $albums;

    }

    /**
     * Internal method that returns all albums created on the site.
     *
     * @since 1.0.0
     *
     * @return array|bool Array of album data or false if none found.
     */
    public function _get_albums() {

        $albums = get_posts(
            array(
                'post_type'     => 'envira_album',
                'no_found_rows' => true,
                'cache_results' => false,
                'nopaging'      => true,
                'fields'        => 'ids',
                'meta_query'    => array(
                    array(
                        'key' => '_eg_album_data'
                    )
                )
            )
        );
        if ( empty( $albums ) ) {
            return false;
        }

        // Now loop through all the albums found and only use albums that have galleries in them. 
        $ret = array();
        foreach ( $albums as $id ) {
            $data = get_post_meta( $id, '_eg_album_data', true );

            // Skip albums with no galleries in them
            if ( empty( $data['galleryIDs'] ) ) {
                continue;
            }

            // Skip certain album types
            if ( 'defaults' == Envira_Albums_Shortcode::get_instance()->get_config( 'type', $data ) || 'dynamic' == Envira_Albums_Shortcode::get_instance()->get_config( 'type', $data ) ) {
                continue;
            }

            $ret[] = $data;
        }

        // Return the album data.
        return $ret;

    }

     /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Albums object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Albums ) ) {
            self::$instance = new Envira_Albums();
        }

        return self::$instance;

    }

}

// Load the main plugin class.
$envira_albums = Envira_Albums::get_instance();

// Conditionally load the template tag.
if ( ! function_exists( 'envira_album' ) ) {
    /**
     * Primary template tag for outputting Envira albums in templates.
     *
     * @since 1.0.0
     *
     * @param int $id 		  The ID of the album to load.
     * @param string $type    The type of field to query.
     * @param array $args     Associative array of args to be passed.
     * @param bool $return    Flag to echo or return the gallery HTML.
     */
    function envira_album( $id, $type = 'id', $args = array(), $return = false ) {

        // If we have args, build them into a shortcode format.
        $args_string = '';
        if ( ! empty( $args ) ) {
            foreach ( (array) $args as $key => $value ) {
                $args_string .= ' ' . $key . '="' . $value . '"';
            }
        }

        // Build the shortcode.
        $shortcode = ! empty( $args_string ) ? '[envira-album ' . $type . '="' . $id . '"' . $args_string . ']' : '[envira-album ' . $type . '="' . $id . '"]';

        // Return or echo the shortcode output.
        if ( $return ) {
            return do_shortcode( $shortcode );
        } else {
            echo do_shortcode( $shortcode );
        }

    }
}
<?php
/**
 * Common admin class.
 *
 * @since 1.0.0
 *
 * @package Envira_Albums
 * @author  Tim Carr
 */
class Envira_Albums_Common_Admin {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Envira_Albums::get_instance();
        
        // Load admin assets.
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );

        // Flush Album Caches on Post/Page/CPT deletion / restore
        add_action( 'wp_trash_post', array( $this, 'trash_untrash_albums' ) );
        add_action( 'untrash_post', array( $this, 'trash_untrash_albums' ) );

        // Remove Gallery from Album(s) when Gallery Deleted
        add_action( 'envira_gallery_trash', array( $this, 'delete_gallery_from_albums' ), 10, 2 );
    
    }
    
    /**
     * Loads styles for our admin tables.
     *
     * @since 1.0.0
     *
     * @return null Return early if not on the proper screen.
     */
    public function admin_styles() {

        if ( 'envira_album' !== get_current_screen()->post_type ) {
            return;
        }

        // Load necessary admin styles.
        wp_register_style( $this->base->plugin_slug . '-admin-style', plugins_url( 'assets/css/admin.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( $this->base->plugin_slug . '-admin-style' );

        // Fire a hook to load in custom admin styles.
        do_action( 'envira_albums_admin_styles' );

    }

    /**
     * Flush album cache when an album is deleted
     *
     * @since 1.0.0
     *
     * @param $id   The post ID being trashed.
     * @return null Return early if no album is found.
     */
    public function trash_untrash_albums( $id ) {

        $album = get_post( $id );

        // Flush necessary gallery caches to ensure trashed albums are not showing.
        Envira_Albums_Common::get_instance()->flush_album_caches( $id );

        // Return early if not an Envira album.
        if ( 'envira_album' !== $album->post_type ) {
            return;
        }

    }

    /**
     * Delete gallery from albums when a gallery is deleted
     *
     * @since 1.1.0.1
     *
     * @param int $id       Envira Gallery ID being trashed.
     * @param array $data   Envira Gallery Data
     */
    public function delete_gallery_from_albums( $id, $data ) {

        // Iterate through Albums, removing Gallery
         // Output all other galleries not assigned to this album
        // Build arguments
        $arguments = array(
            'post_type'         => 'envira',
            'post_status'       => 'publish',
            'posts_per_page'    => -1,
            'orderby'           => 'title',
            'order'             => 'ASC',
        );

        // Get Albums
        $albums = new WP_Query( array(
            'post_type'         => 'envira_album',
            'post_status'       => 'publish',
            'posts_per_page'    => -1,
        ) );
        if ( ! $albums->posts || count( $albums->posts ) == 0 ) {
            return;
        }

        // Iterate through Albums
        foreach ( $albums->posts as $album ) {
            // Check metadata to see if the gallery exists
            $album_data = $this->base->get_album( $album->ID );
            if ( ! is_array( $album_data ) ) {
                continue;
            }

            // Check gallery exists in Album
            if ( ! isset( $album_data['galleryIDs'] ) ) {
                continue;
            }
            if ( ! is_array( $album_data['galleryIDs'] ) ) {
                continue;
            }
            if ( ( $key = array_search( $id, $album_data['galleryIDs'] ) ) !== false ) {
                // Delete Gallery ID + Gallery Details in Album
                unset( $album_data['galleryIDs'][ $key ] );
                unset( $album_data['gallery'][ $album->ID ] );

                // Update Album Meta
                update_post_meta( $album->ID, '_eg_album_data', $album_data );
                break; // No need to search any more items in the array
            }
        }

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Albums_Common_Admin object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Albums_Common_Admin ) ) {
            self::$instance = new Envira_Albums_Common_Admin();
        }

        return self::$instance;

    }

}

// Load the common admin class.
$envira_albums_common_admin = Envira_Albums_Common_Admin::get_instance();
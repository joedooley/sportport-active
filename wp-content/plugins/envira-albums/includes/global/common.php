<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Albums
 * @author  Tim Carr
 */
class Envira_Albums_Common {

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

    }

    /**
     * Helper method to flush album caches once an album is updated.
     *
     * @since 1.0.0
     *
     * @param int $post_id The current post ID.
     * @param string $slug The unique album slug.
     */
    public function flush_album_caches( $post_id, $slug = '' ) {

        // Delete known album caches.
        delete_transient( '_eg_cache_' . $post_id );
        delete_transient( '_ea_cache_all' );

        // Possibly delete slug gallery cache if available.
        if ( ! empty( $slug ) ) {
            delete_transient( '_eg_cache_' . $slug );
        }

        // Run a hook for Addons to access.
        do_action( 'envira_albums_flush_caches', $post_id, $slug );

    }
    
    /**
     * Helper method for setting default config values.
     *
     * @since 1.0.0
     *
     * @global int $id      The current post ID.
     * @global object $post The current post object.
     * @param string $key   The default config key to retrieve.
     * @return string       Key value on success, false on failure.
     */
    public function get_config_default( $key ) {

        global $id, $post;

        // Get the current post ID. If ajax, grab it from the $_POST variable.
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            $post_id = absint( $_POST['post_id'] );
        } else {
            $post_id = isset( $post->ID ) ? $post->ID : (int) $id;
        }
        
        // Prepare default values.
        $defaults = $this->get_config_defaults( $post_id );

        // Return the key specified.
        return isset( $defaults[$key] ) ? $defaults[$key] : false;

    }
    
    /**
     * Retrieves the slider config defaults.
     *
     * @since 1.0.9
     *
     * @param int $post_id The current post ID.
     * @return array       Array of slider config defaults.
     */
    public function get_config_defaults( $post_id ) {

		// Prepare default values.
        $defaults = array(
	        // Galleries Tab
            'type'                => 'default',
            
            // Config Tab
            'columns'             => '3',
            'gallery_theme'       => 'base',
            'back'                => 0,
            'description_position'=> 0,
            'description'		  => '',
            'display_titles'	  => 0,
            'display_captions'    => 0,
            'display_image_count' => 0,
            'gutter'              => 10,
            'margin'              => 10,
            'sorting'			  => 0,
            'crop_width'          => 960,
            'crop_height'         => 300,
            'crop'                => 0,
            'dimensions'		  => 0,
            'isotope'			  => 1,
            'css_animations'	  => 1,
            
            // Lightbox
            'lightbox'            => false,
            'lightbox_theme'      => 'base',
            'title_display'       => 'float',
            'arrows'              => 1,
            'arrows_position'     => 'inside',
            'keyboard'            => 1,
            'mousewheel'          => 1,
            'toolbar'             => 1,
            'toolbar_title'		  => 0,
            'toolbar_position'    => 'top',
            'aspect'              => 1,
            'loop'                => 1,
            'effect'              => 'fade',
            'html5'               => false,
            
            // Thumbnails
            'thumbnails'          => 1,
            'thumbnails_width'    => 75,
            'thumbnails_height'   => 50,
            'thumbnails_position' => 'bottom',

            // Mobile
            'mobile'              => 1,
            'mobile_width'        => 320,
            'mobile_height'       => 240,
            'mobile_lightbox'     => 1,
            'mobile_touchwipe'    => 1,
            'mobile_touchwipe_close' => 0,
            'mobile_arrows'       => 1,
            'mobile_toolbar'      => 1,
            'mobile_thumbnails'   => 1,
            
            // Misc
            'title'               => '',
            'slug'                => '',
            'classes'             => array(),
            'rtl'                 => 0,
        );

        // Allow devs to filter the defaults.
        $defaults = apply_filters( 'envira_albums_defaults', $defaults, $post_id );
                
        return $defaults;

    }

    /**
     * Helper method for retrieving display sorting options.
     *
     * @since 1.2.4.4
     *
     * @return array Array of sorting options
     */
    public function get_sorting_options() {

        $options = array(
            array(
                'name'  => __( 'No Sorting', 'envira-albums' ),
                'value' => 0,
            ),
            array(
                'name'  => __( 'Random', 'envira-albums' ),
                'value' => 'random',
            ),
            array(
                'name'  => __( 'Title', 'envira-albums' ),
                'value' => 'title',
            ),
            array(
                'name'  => __( 'Caption', 'envira-albums' ),
                'value' => 'caption',
            ),
            array(
                'name'  => __( 'Alt Text', 'envira-albums' ),
                'value' => 'alt',
            ),
        );

        return apply_filters( 'envira_albums_sorting_options', $options );

    }

    /**
     * Helper method for retrieving sorting directions
     *
     * @since 1.2.4.4
     *
     * @return array Array of sorting directions
     */
    public function get_sorting_directions() {

        $directions = array(
            array(
                'name'  => __( 'Ascending (A-Z)', 'envira-albums' ),
                'value' => 'ASC',
            ),
            array(
                'name'  => __( 'Descending (Z-A)', 'envira-albums' ),
                'value' => 'DESC',
            ),
        );

        return apply_filters( 'envira_albums_common_get_sorting_directions', $directions );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Gallery_Common object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Albums_Common ) ) {
            self::$instance = new Envira_Albums_Common();
        }

        return self::$instance;

    }

}

// Load the common class.
$envira_albums_common = Envira_Albums_Common::get_instance();
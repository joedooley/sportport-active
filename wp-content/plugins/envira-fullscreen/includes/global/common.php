<?php
/**
 * Common class.
 *
 * @since 1.0.4
 *
 * @package Envira_Fullscreen
 * @author  Tim Carr
 */
class Envira_Fullscreen_Common {

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
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        add_filter( 'envira_gallery_defaults', array( $this, 'get_config_defaults' ), 10, 2 );
        add_filter( 'envira_albums_defaults', array( $this, 'get_config_defaults' ), 10, 2 );
    }
    
    /**
     * Applies a default to the addon setting.
     *
     * @since 1.0.0
     *
     * @param array $defaults  Array of default config values.
     * @param int $post_id     The current post ID.
     * @return array $defaults Amended array of default config values.
     */
    function get_config_defaults( $defaults, $post_id ) {

        // Disabled by default.
        $defaults['fullscreen'] = 0;
        return $defaults;

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Gallery_Common object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Fullscreen_Common ) ) {
            self::$instance = new Envira_Fullscreen_Common();
        }

        return self::$instance;

    }

}

// Load the common class.
$envira_fullscreen_common = Envira_Fullscreen_Common::get_instance();
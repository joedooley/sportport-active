<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Social
 * @author  Tim Carr
 */
class Envira_Social_Common {

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

        add_filter( 'envira_gallery_defaults', array( $this, 'defaults' ), 10, 2 );
		
    }

    /**
     * Adds the default settings for this addon.
     *
     * @since 1.0.0
     *
     * @param array $defaults  Array of default config values.
     * @param int $post_id     The current post ID.
     * @return array $defaults Amended array of default config values.
     */
    function defaults( $defaults, $post_id ) {
    
        // Add default settings to main defaults array
        $defaults['social']                     = 0;
        $defaults['social_facebook']            = 0;
        $defaults['social_twitter']             = 0;
        $defaults['social_google']              = 0;
        $defaults['social_pinterest']           = 0;
        $defaults['social_email']               = 0;
        $defaults['social_position']            = 'top-left';
        $defaults['social_orientation']         = 'vertical';

        // Lightbox defaults
        $defaults['social_lightbox']            = 0;
        $defaults['social_lightbox_facebook']   = 0;
        $defaults['social_lightbox_twitter']    = 0;
        $defaults['social_lightbox_google']     = 0;
        $defaults['social_lightbox_pinterest']  = 0;
        $defaults['social_lightbox_email']      = 0;
        $defaults['social_lightbox_position']   = 'top-left';
        $defaults['social_lightbox_orientation']= 'vertical';
        $defaults['social_lightbox_outside']    = 0;

        // Return
        return $defaults;
    
    }

    /**
     * Helper method for retrieving social networks.
     *
     * @since 1.0.0
     *
     * @return array Array of social networks.
     */
    public function get_networks() {

        $networks = array(
            'facebook'  => __( 'Facebook', 'envira-social' ),
            'twitter'   => __( 'Twitter', 'envira-social' ),
            'google'    => __( 'Google', 'envira-social' ),
            'pinterest' => __( 'Pinterest', 'envira-social' ),
            'email'     => __( 'Email', 'envira-social' ),
        );

        return apply_filters( 'envira_social_networks', $networks );

    }

    /**
     * Helper method for retrieving positions.
     *
     * @since 1.0.0
     *
     * @return array Array of positions.
     */
    public function get_positions() {

        $positions = array(
            'top-left'      => __( 'Top Left', 'envira-social' ),
            'top-right'     => __( 'Top Right', 'envira-social' ),
            'bottom-left'   => __( 'Bottom Left', 'envira-social' ),
            'bottom-right'  => __( 'Bottom Right', 'envira-social' ),
        );

        return apply_filters( 'envira_social_positions', $positions );

    }

    /**
     * Helper method for retrieving orientations.
     *
     * @since 1.0.0
     *
     * @return array Array of positions.
     */
    public function get_orientations() {

        $orientations = array(
            'horizontal'    => __( 'Horizontal', 'envira-social' ),
            'vertical'      => __( 'Vertical', 'envira-social' ),
        );

        return apply_filters( 'envira_social_orientations', $orientations );

    }

    /**
     * Helper function to retrieve a Setting
     *
     * @since 1.0.0
     *
     * @param string $key Setting
     * @return array Settings
     */
    public function get_setting( $key ) {
        
        // Get settings
        $settings = Envira_Social_Common::get_instance()->get_settings();

        // Check setting exists
        if ( ! is_array( $settings ) ) {
            return false;
        }
        if ( ! array_key_exists( $key, $settings ) ) {
            return false;
        }

        $setting = apply_filters( 'envira_social_setting', $settings[ $key ] );
        return $setting;

    }

    /**
     * Helper function to retrieve Settings
     *
     * @since 1.0.0
     *
     * @return array Settings
     */
    public function get_settings() {
        
        $settings = get_option( 'envira-social' );
        $settings = apply_filters( 'envira_social_settings', $settings );
        return $settings;

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Social_Common object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Social_Common ) ) {
            self::$instance = new Envira_Social_Common();
        }

        return self::$instance;

    }

}

// Load the common class.
$envira_social_common = Envira_Social_Common::get_instance();
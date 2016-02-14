<?php
/**
 * Common class.
 *
 * @since 1.3.0
 *
 * @package Envira_Tags
 * @author  Tim Carr
 */
class Envira_Tags_Common {

    /**
     * Holds the class object.
     *
     * @since 1.3.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.3.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Primary class constructor.
     *
     * @since 1.3.0
     */
    public function __construct() {

    	add_filter( 'envira_gallery_defaults', array( $this, 'defaults' ), 10, 2 );

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
	public function defaults( $defaults, $post_id ) {

	    // Disable filtering by default.
	    $defaults['tags'] = 0;
	    $defaults['tags_filter'] = '';
	    $defaults['tags_limit'] = 0;
	    $defaults['tags_all'] = __( 'All', 'envira-tags' );
	    return $defaults;

	}

    /**
     * Returns an array of settings
     *
     * @since 1.3.1
     */
    public function get_settings() {

        // Get settings
        $settings = get_option( 'envira-tags' );

        // If no settings exist, create a blank array for them
        if ( ! is_array( $settings ) ) {
            $settings = array(
                'imagga_enabled'            => false,
                'imagga_authorization_code' => '',
                'imagga_confidence'         => 40,
            );
        }

        return $settings;

    }

    /**
     * Updates settings with the given key/value pairs
     *
     * @since 1.3.1
     *
     * @param   array   $settings   Settings
     */
    public function save_settings( $settings ) {

        // If the auth code starts with 'Basic ', which it does if copied from Imagga using the copy button,
        // strip this part
        if ( isset( $settings['imagga_authorization_code'] ) ) {
            $settings['imagga_authorization_code'] = str_replace( 'Basic ', '', $settings['imagga_authorization_code'] );
        }

        // Cast some values
        $settings['imagga_confidence'] = absint( $settings['imagga_confidence'] );

        // Save
        update_option( 'envira-tags', $settings );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.3.0
     *
     * @return object The Envira_Tags_Common object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Common ) ) {
            self::$instance = new Envira_Tags_Common();
        }

        return self::$instance;

    }

}

// Load the common class.
$envira_tags_common = Envira_Tags_Common::get_instance();
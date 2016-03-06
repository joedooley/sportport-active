<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */
class OMAPI_Shortcode {

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

	    // Set our object.
	    $this->set();

	    // Load actions and filters.
	    add_shortcode( 'optin-monster', array( $this, 'shortcode' ) );
        add_shortcode( 'optin-monster-shortcode', array( $this, 'shortcode_v1' ) );
        add_filter( 'widget_text', 'shortcode_unautop' );
        add_filter( 'widget_text', 'do_shortcode' );

    }

    /**
     * Sets our object instance and base class instance.
     *
     * @since 1.0.0
     */
    public function set() {

        self::$instance = $this;
        $this->base 	= OMAPI::get_instance();

    }

    /**
     * Creates the shortcode for the plugin.
     *
     * @since 1.0.0
     *
     * @global object $post The current post object.
     *
     * @param array $atts Array of shortcode attributes.
     * @return string     The optin output.
     */
    public function shortcode( $atts ) {

        global $post;

        $optin_id = false;
        if ( isset( $atts['id'] ) ) {
            $optin_id = (int) $atts['id'];
        } else if ( isset( $atts['slug'] ) ) {
            $optin = get_page_by_path( $atts['slug'], OBJECT, 'omapi' );
            if ( $optin ) {
                $optin_id = $optin->ID;
            }
        } else {
            // A custom attribute must have been passed. Allow it to be filtered to grab the optin ID from a custom source.
            $optin_id = apply_filters( 'optin_monster_api_custom_optin_id', false, $atts, $post );
        }

        // Allow the optin ID to be filtered before it is stored and used to create the optin output.
        $optin_id = apply_filters( 'optin_monster_api_pre_optin_id', $optin_id, $atts, $post );

        // If there is no optin, do nothing.
        if ( ! $optin_id ) {
            return false;
        }

		// Try to grab the stored HTML.
		$optin = $this->base->get_optin( $optin_id );
        $html  = trim( html_entity_decode( stripslashes( $optin->post_content ), ENT_QUOTES ), '\'' );
        if ( ! $html ) {
            return false;
        }

        // Make sure to apply shortcode filtering.
		OMAPI::get_instance()->output->set_slug( $optin );

		// Possibly add support for Mailpoet.
		$mailpoet = get_post_meta( $optin->ID, '_omapi_mailpoet', true );
		if ( $mailpoet ) {
	        OMAPI::get_instance()->output->wp_helper();
		}

        // Return the HTML.
        return $html;

    }

    /**
     * Backwards compat shortcode for v1.
     *
     * @since 1.0.0
     *
     * @global object $post The current post object.
     *
     * @param array $atts Array of shortcode attributes.
     * @return string     The optin output.
     */
    public function shortcode_v1( $atts ) {

        // Run the v2 implementation.
        $atts['slug'] = $atts['id'];
        unset( $atts['id'] );
        return $this->shortcode( $atts );

    }

}
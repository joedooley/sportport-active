<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Deeplinking_Shortcode
 * @author  Tim Carr
 */
class Envira_Deeplinking_Shortcode {

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
        $this->base = Envira_Deeplinking::get_instance();

        // Register script.
        wp_register_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/min/envira-deeplinking-min.js', $this->base->file ), array( 'jquery' ), $this->base->version, false );
                
        // Actions
        add_action( 'envira_gallery_before_output', array( $this, 'enqueue_script' ) );
        add_action( 'envira_gallery_api_end_global', array( $this, 'init' ) );
        add_action( 'envira_gallery_api_before_show', array( $this, 'change_hash' ) );
        add_action( 'envira_gallery_api_after_close', array( $this, 'remove_location_hash' ) );

    }

    /**
    * Enqueue scripts if Deeplinking is enabled on a gallery
    *
    * @since 1.0.5
    */
    function enqueue_script( $data ) {

        // Get instance
        $instance = Envira_Gallery_Shortcode::get_instance();

        // Bail if deeplinking not enabled
        if ( ! $instance->get_config( 'deeplinking', $data ) ) {
            return;
        }

        // Enqueue script
        wp_enqueue_script( $this->base->plugin_slug . '-script' );
        
    }

    /**
     * Checks if any of the galleries have Deeplinking enabled
     *
     * If so, initialises deeplinking once.
     *
     * @since 1.0.5
     */
    function init( $galleries ) {

        // Get instance
        $instance = Envira_Gallery_Shortcode::get_instance();

        // Iterate through galleries
        foreach ( $galleries as $data ) {
            // Bail if deeplinking not enabled
            if ( $instance->get_config( 'deeplinking', $data ) ) {
                // Init once and quit
                ?>
                envira_deeplinking();
                <?php
                break;
            }
        }

    }

    /**
     * Changes a window hash.
     *
     * @since 1.0.0
     *
     * @param array $data Data for the Envira gallery.
     * @return null       Return early if deeplinking is not enabled.
     */
    function change_hash( $data ) {

        // Get instance
        $instance = Envira_Gallery_Shortcode::get_instance();

        // Bail if deeplinking not enabled
        if ( ! $instance->get_config( 'deeplinking', $data ) ) {
            return;
        }

        // Get hash from rel or data attribute, depending on gallery config
        if ( $instance->get_config( 'html5', $data ) ) {    
            ?>
            window.location.hash = "!" + $(this.element).attr("data-envirabox-group") + "-" + $('img', this.element ).data('envira-item-id');
            <?php
        } else {
            ?>
            window.location.hash = "!" + $(this.element).attr("rel") + "-" + $('img', this.element ).data('envira-item-id');
            <?php
        }

    }

    /**
     * Removes a hash from the location bar.
     *
     * @since 1.0.0
     *
     * @param array $data Data for the Envira gallery.
     * @return null       Return early if deeplinking is not enabled.
     */
    function remove_location_hash( $data ) {

        if ( ! Envira_Gallery_Shortcode::get_instance()->get_config( 'deeplinking', $data ) ) {
            return;
        }
        ?>
        if ('pushState' in history) {
            history.pushState( '', document.title, window.location.pathname );
        } else {
            window.location.hash = '';
        }
        <?php

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Deeplinking_Shortcode object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Deeplinking_Shortcode ) ) {
            self::$instance = new Envira_Deeplinking_Shortcode();
        }

        return self::$instance;

    }

}

// Load the common class.
$envira_deeplinking_shortcode = Envira_Deeplinking_Shortcode::get_instance();
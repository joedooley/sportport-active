<?php
/**
 * Ajax class.
 *
 * @since 1.3.0
 *
 * @package Envira_Tags
 * @author  Tim Carr
 */
class Envira_Tags_AJAX {

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
     * Holds the Imagga class object.
     *
     * @since 1.3.1
     *
     * @var object
     */
    public $imagga;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        add_action( 'wp_ajax_envira_tags_tag_multiple_images', array( $this, 'tag_multiple_images' ) );
        add_action( 'envira_gallery_ajax_load_image', array( $this, 'load_image' ), 10, 2 ); // Upload
        add_action( 'envira_gallery_ajax_insert_images', array( $this, 'insert_images' ), 10, 2 ); // Select from Media Library
        add_filter( 'envira_gallery_ajax_item_data', array( $this, 'item_data' ), 10, 3 );
        add_filter( 'envira_gallery_ajax_save_meta', array( $this, 'save' ), 10, 4 );

    }

    /**
    * Tags multiple images with the given tags
    *
    * @since 1.1.9
    */
    function tag_multiple_images() {

        // Run a security check first.
        check_ajax_referer( 'envira-tags-nonce', 'nonce' );

        // Prepare variables
        $post_id = absint( $_POST['post_id'] );
        $tags = explode( ',', sanitize_text_field( $_POST['tags'] ) );
        $image_ids = $_POST['attachment_ids'];

        // Check we have required data
        if ( empty( $post_id ) ) {
            wp_die( 0 );
        }
        if ( count( $tags ) == 0 ) {
            wp_die( 0 );
        }
        if ( count( $image_ids ) == 0 ) {
            wp_die( 0 );
        } 

        // Iterate through images, adding tags to each
        foreach ( $image_ids as $image_id ) {
            wp_set_object_terms( $image_id, $tags, 'envira-tag', true ); // true = append
        }

        // Done
        wp_die( 1 );

    }


    /**
     * Runs a Tag-specific WordPress Action when an image is uploaded through Envira. 
     *
     * @since 1.1.4
     *
     * @param int   $attachment_id  Attachment ID
     * @param int   $post_id        Envira Gallery ID
     * @return null
     */
    public function load_image( $attachment_id, $post_id ) {

        // If Imagga Tagging is enabled, run it on the attachment ID now
        $settings = Envira_Tags_Common::get_instance()->get_settings();
        if ( $settings['imagga_enabled'] && ! empty( $settings['imagga_authorization_code'] ) ) {
            // Setup Imagga
            $this->imagga = new Envira_Tags_Imagga( $settings['imagga_authorization_code'] );

            // Adds Tags from Imagga
            $this->add_imagga_tags_to_image( $attachment_id, $settings['imagga_confidence'] );
        }

        do_action( 'envira_tags_ajax_load_image', $attachment_id, $post_id );

    }

    /**
     * Runs a Tag-specific WordPress Action when an image is selected from the
     * Media Library for inclusion in an Envira Gallery. 
     *
     * @since 1.1.4
     *
     * @param array $images         Gallery Images that have just been inserted
     * @param int   $post_id        Envira Gallery ID
     * @return null
     */
    public function insert_images( $images, $post_id ) {

        // If Imagga Tagging is enabled, run it on the attachment IDs now
        $settings = Envira_Tags_Common::get_instance()->get_settings();
        if ( $settings['imagga_enabled'] && ! empty( $settings['imagga_authorization_code'] ) ) {
            // Setup Imagga
            $this->imagga = new Envira_Tags_Imagga( $settings['imagga_authorization_code'] );

            // Iterate through images
            foreach ( $images as $image ) {
                // Add Tags to Image
                $this->add_imagga_tags_to_image( $image['id'], $settings['imagga_confidence'] );
            }
        }

        do_action( 'envira_tags_ajax_insert_images', $images, $post_id );

    }

    /**
     * Adds Imagga Tags to an Image
     *
     * @since 1.3.1
     *
     * @param int   $attachment_id      Attachment ID
     * @param int   $minimum_confidence The minimum confidence required to include the tag
     */
    private function add_imagga_tags_to_image( $attachment_id, $minimum_confidence = 40 ) {

        // Get Image URL
        $image_url = wp_get_attachment_url( $attachment_id );

        // Get tags for this image
        $tags = $this->imagga->get_image_tags( $image_url );

        // If no tags or an error occured, return
        if ( ! $tags || is_wp_error( $tags ) ) {
            return false;
        }

        // Iterate through tags
        $image_tags = array();
        foreach ( $tags as $tag ) {
            // If a tag's confidence is lower than our confidence settings, skip this tag
            if ( $tag->confidence < $minimum_confidence ) {
                continue;
            }

            // Add tag to array
            $image_tags[] = $tag->tag;
        }

        // If image tags were found, add them to the image
        if ( count( $image_tags ) > 0 ) {
            wp_set_object_terms( $attachment_id, $image_tags, 'envira-tag' );
        }

        return true;

    }

    /**
     * Applies a default to the addon ajax setting.
     *
     * @since 1.0.0
     *
     * @param array $gallery_data  Array of gallery data.
     * @param object $attachment   The attachment object.
     * @param int $id              The attachment ID.
     * @return array $gallery_data Amended array of gallery data.
     */
    public function item_data( $gallery_data, $attachment, $id ) {

        // Set to an empty array by default.
        $gallery_data['gallery'][ $id ]['tags'] = array();
        return $gallery_data;

    }

    /**
     * Saves the addon ajax setting.
     *
     * @since 1.0.0
     *
     * @param array $gallery_data  Array of gallery data to be saved.
     * @param array $meta          Array of meta entered by the user.
     * @param int $attach_id       The attachment ID.
     * @param int $post_id         The current post ID.
     * @return array $gallery_data Amended array of gallery data to be saved.
     */
    function save( $gallery_data, $meta, $attach_id, $post_id ) {

        // Explode the tag list and save.
        if ( isset( $meta['tags'] ) ) {
            $tags = explode( ',', $meta['tags'] );

            // Store tags in taxonomy
            wp_set_object_terms( $attach_id, $tags, 'envira-tag' );

            // If this is being converted from the old style tags in meta to the new style tags in a taxonomy, blank the old style meta, as we no longer use it
            unset( $gallery_data['gallery'][ $attach_id ]['tags'] );
        }

        return $gallery_data;

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.3.0
     *
     * @return object The Envira_Tags_AJAX object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_AJAX ) ) {
            self::$instance = new Envira_Tags_AJAX();
        }

        return self::$instance;

    }

}

// Load the AJAX class.
$envira_tags_ajax = Envira_Tags_AJAX::get_instance();
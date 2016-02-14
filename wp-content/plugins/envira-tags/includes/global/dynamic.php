<?php
/**
 * Dynamic class.
 *
 * @since 1.3.0
 *
 * @package Envira_Tags_Dynamic
 * @author  Tim Carr
 */
class Envira_Tags_Dynamic {

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

    	add_filter( 'envira_dynamic_get_dynamic_gallery_types', array( $this, 'get_dynamic_gallery_types' ) );
        add_filter( 'envira_dynamic_get_images_by_tag', array( $this, 'get_images_by_tag' ), 10, 3 );

    }

    /**
     * Adds the Tag Addon Dynamic methods for retrieving images to the 
     * array of available Gallery Types
     *
     * @since 1.1.0
     *
     * @param array $types  Dynamic Gallery Types
     * @return array        New Dynamic Gallery Types
     */
    function get_dynamic_gallery_types( $types ) {

        $types['envira_dynamic_get_images_by_tag'] = '#^tags-#';

        return $types;

    }

    /**
    * Retrieves the image data by tag across all Envira Galleries
    *
    * @since 1.1.0
    *
    * @param array $dynamic_data    Existing Dynamic Data Array
    * @param string $id             ID (tag-term)
    * @param array $data            Gallery Configuration
    * @return bool|array            Array of data on success, false on failure
    */
    function get_images_by_tag( $dynamic_data, $id, $data ) {

        // Get term
        $term_parts = explode( '-', $id );
        $term = '';
        foreach ( $term_parts as $i => $term_part ) {
            // Skip first string (= tags)
            if ( $i == 0 ) {
                continue;
            }

            // Add to term string
            $term .= '-' . $term_part;
        }

        // Get limit
        $limit = ( ( isset( $data['config']['tags_limit'] ) && $data['config']['tags_limit'] > 0 ) ? $data['config']['tags_limit'] : -1 );
        
        // Prepare query args.
        $args = array(
            'post_type'         => 'attachment',
            'post_status'       => 'publish',
            'posts_per_page'    => $limit,
            'tax_query'         => array(
                array(
                    'taxonomy'  => 'envira-tag',
                    'field'     => 'slug',
                    'terms'     => trim( $term, '-' ),
                ),
            ),
        );

        // Run query
        $attachments = new WP_Query( $args );

        // Check for results
        if ( ! isset( $attachments->posts ) || count ( $attachments->posts ) == 0 ) {
            return $dynamic_data;
        }
        
        // Iterate through attachments
        foreach ( (array) $attachments->posts as $i => $attachment ) {
            // Get image details
            $src = wp_get_attachment_image_src( $attachment->ID, 'full' );
            
            // Build image attributes to match Envira Gallery
            $dynamic_data[ $attachment->ID ] = array(
                'status'            => 'published',
                'src'               => ( isset( $src[0] ) ? esc_url( $src[0] ) : '' ),
                'title'             => $attachment->post_title,
                'link'              => ( isset( $src[0] ) ? esc_url( $src[0] ) : '' ),
                'alt'               => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
                'caption'           => $attachment->post_excerpt,
                'thumb'             => '',
                'link_new_window'   => 0,
            );
        } 
        
        return $dynamic_data;

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.3.0
     *
     * @return object The Envira_Tags_Dynamic object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Dynamic ) ) {
            self::$instance = new Envira_Tags_Dynamic();
        }

        return self::$instance;

    }

}

// Load the dynamic class.
$envira_tags_dynamic = Envira_Tags_Dynamic::get_instance();
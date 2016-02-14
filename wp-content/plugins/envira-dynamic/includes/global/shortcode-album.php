<?php
/**
 * Dynamic Album Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Dynamic
 * @author  Tim Carr
 */
class Envira_Dynamic_Album_Shortcode {

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
	    
	    // Register Album Dynamic Shortcode
        add_shortcode( 'envira-album-dynamic', array( $this, 'shortcode' ) );
        add_filter( 'envira_albums_custom_gallery_data', array( $this, 'parse_shortcode_attributes' ), 10, 3 );
        add_filter( 'envira_albums_pre_data', array( $this, 'inject_galleries' ), 10, 3 );

        // Register Filters for Dynamic Album Types
        add_filter( 'envira_dynamic_get_galleries', array( $this, 'get_galleries' ), 10, 3 );
    }

    /**
     * Returns an array of Dynamic Album Types
     * Defaults: Envira Gallery IDs
     *
     * Other Addons can add to this list of types and then define their own actions for grabbing the galleries for
     * insertion into the Dynamic Album.
     *
     * @since 1.0.0
     *
     * @return array Types
     */
    public function get_dynamic_album_types() {

    	// Build array of default types
    	// key = WordPress Filter, value = preg_match statement
    	$types = array(
    		'envira_dynamic_get_galleries' 	=> '#^custom-#',
    	);

    	// Filter types
    	$types = apply_filters( 'envira_dynamic_get_dynamic_album_types', $types );

    	return $types;

    }
    
    /**
	 * Parses the Dynamic attributes and filters them into the data.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $bool   Boolean (false) since no data is found yet.
	 * @param array $atts  Array of shortcode attributes to parse.
	 * @param object $post The current post object.
	 * @return array $data Array of dynamic gallery data.
	 */
    public function parse_shortcode_attributes( $bool, $atts, $post ) {
	    
	    // If the dynamic attribute is not set to true, do nothing.
	    if ( empty( $atts['dynamic'] ) ) {
	        return $bool;
	    }
	
	    // Now that we have a dynamic album, prepare atts to be parsed with defaults.
	    $dynamic_id = Envira_Dynamic_Common::get_instance()->get_album_dynamic_id();
	    $defaults   = get_post_meta( $dynamic_id, '_eg_album_data', true );
	    $data       = array();
	    foreach ( (array) $atts as $key => $value ) {
	        // Cast any 'true' or 'false' atts to a boolean value.
	        if ( 'true' == $value ) {
	            $atts[$key] = 1;
	            $value      = 1;
	        }
	
	        if ( 'false' == $value ) {
	            $atts[$key] = 0;
	            $value      = 0;
	        }
	
	        // Store data
	        $data[ $key ] = $value;
	    }
	    
	    // If the data is empty, return false.
	    if ( empty( $data ) || empty( $defaults ) ) {
	        return false;
	    }
	    
	    // Merge in the defaults into the data.
	    $config           = $defaults;
	    $config['id']     = str_replace( '-', '_', $atts['dynamic'] ); // Replace dashes with underscores.
	    $config_array     = $defaults['config'];
	    $parsed_array     = wp_parse_args( $data, $defaults['config'] );
	    $config['config'] = $parsed_array;

	    // Store the dynamic ID within the config
	    // This allows Addons which support both Galleries and Albums grab the Dynamic ID to figure out
	    // whether the Addon is looking at a Gallery or Album
	    $config['config']['id'] = $dynamic_id;
	    
	    // Parse the args and return the data.
	    return apply_filters( 'envira_dynamic_album_parsed_data', $config, $data, $defaults, $atts, $post );
	    
    }
    
    /**
	 * Injects galleries into the given $data array, using the $data settings (i.e. the dynamic album settings)
	 *
	 * @since 1.0.0
	 *
	 * @param array $data  Album Config.
	 * @param int $id      The album ID.
	 * @return array $data Amended array of gallery config, with galleries.
	 */
	function inject_galleries( $data, $id ) {

	    // Return early if not an Dynamic slider.
	    $instance = Envira_Albums_Shortcode::get_instance();
	    if ( 'dynamic' !== $instance->get_config( 'type', $data ) ) {
	        return $data;
	    }
	
	    // $id should be false, so we need to set it now.
	    if ( ! $id ) {
	        $id = $instance->get_config( 'dynamic', $data );
	    }

	    /**
		* Get images based on supplied Dynamic settings
	    * Checks for:
	    * - Envira Gallery IDs: [envira-album-dynamic id="custom-xxx" galleries="id,id,id"]
	    */
	    $dynamic_data = array();
	    $types = $this->get_dynamic_album_types();
	    foreach ( $types as $filter_to_execute => $preg_match ) {
	    	if ( preg_match( $preg_match, $id ) ) {
	    		// Run action for this preg_match
	    		$rule_matched = true;
	    		$dynamic_data = apply_filters( $filter_to_execute, $dynamic_data, $id, $data );
	    		break;
	    	}
	    }

		// Filter 
		$dynamic_data = apply_filters( 'envira_albums_dynamic_queried_data', $dynamic_data, $id, $data );

		// Check galleries were found
		if ( count( $dynamic_data ) == 0 ) {
			// No galleries found, nothing to inject - just return data
			return $data;
		}
		
		// Galleries found - insert into data
		$data['galleryIDs'] = $dynamic_data['galleryIDs'];
		$data['galleries'] = $dynamic_data['galleries'];
	
	    // Return the modified data.
	    return apply_filters( 'envira_albums_dynamic_data', $data, $id );

	}
	
	/**
	* Retrieves the album data for custom gallery sets
	*
	* @param array $dynamic_data 	Dynamic Data
	* @param int $id				custom-id
	* @param array $data			Album Configuration
	* @param bool|array $galleries	Array of gallery IDs to use
	* @return bool|array			Array of gallery data on success, false on failure
	*/
	public function get_galleries( $dynamic_data, $id, $data, $galleries = false ) {

		// Gallery IDs will be set in either:
		// - 1. $data['config']['galleries'] (by parse_shortcode_attributes())
		// - 2. $galleries array (passed to this function)
		$instance    = Envira_Albums_Shortcode::get_instance();
	    $data_galleries = $instance->get_config( 'galleries', $data );
	    if ( ! $data_galleries ) {
	        if ( ! $galleries ) {
		        // No galleries specified matching (1) or (2) above - bail
		        return false;
	        }
	    } else {
	    	// If = 'all', get all gallery IDs
	    	if ( $data_galleries == 'all' ) {
	    		$all_galleries = Envira_Gallery::get_instance()->get_galleries();
	    		foreach ( $all_galleries as $gallery ) {
	    			$galleries .= $gallery['id'] . ',';
	    		}
	    	} else {
		        $galleries = $data_galleries;
		    }
	    }

	    // $galleries now reflects the exact galleries we want to include in the Album
	    // Get Envira Gallery instance and populate album data with gallery info
	    $gallery_instance = Envira_Gallery::get_instance();
	    $gallery_config_instance = Envira_Gallery_Shortcode::get_instance();

	    // Set some child arrays, if not already defined
	    if ( ! array_key_exists ( 'galleryIDs', $dynamic_data ) ) {
	    	$dynamic_data['galleryIDs'] = array();
	    }
		if ( ! array_key_exists ( 'galleries', $dynamic_data ) ) {
	    	$dynamic_data['galleries'] = array();
	    }

	    $galleries     = explode( ',', rtrim( (string) $galleries, ',' ) );
	    foreach ( (array) $galleries as $i => $gallery_id ) {
		    
		    // Get gallery and check it exists
		    $gallery = $gallery_instance->get_gallery( $gallery_id );
		    
		    // Skip blank galleries
		    if ( ! isset( $gallery['gallery'] ) ) {
		    	continue;
		    }

		    // Get first image from gallery
		    $gallery_images = $gallery['gallery'];
		    reset( $gallery_images );
			
		    // Add to album data
		    $gallery_data['galleryIDs'][] = $gallery_id;
		    $gallery_data['galleries'][ $gallery_id ] = array(
			    'title' 		=> $gallery_config_instance->get_config( 'title', $gallery ),
			    'alt'			=> $gallery_config_instance->get_config( 'title', $gallery ),
			    'cover_image_id'=> key( $gallery_images ),
		    );
	    }

	    // Check we found at least one gallery
	    if ( ! isset( $gallery_data ) ) {
	    	return false;
	    }
	    
	    return apply_filters( 'envira_gallery_dynamic_custom_image_data', $gallery_data, $id, $data );
	    
	}
	
	/**
	 * Create the album shortcode
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Array of shortcode attributes.
	 */
	function shortcode( $atts ) {
	
	    // If no ID, return false.
	    if ( empty( $atts['id'] ) ) {
	        return false;
	    }
	
	    // Pull out the ID and remove from atts.
	    $id = $atts['id'];
	    unset( $atts['id'] );
	
	    // Prepare the args to be output into query string shortcode format for the shortcode.
	    $output_args = '';
	    foreach ( $atts as $k => $v ) {
	        $output_args .= $k . '=' . $v . ' ';
	    }
	
	    // Map to the Envira Album shortcode with the proper data structure.
	    return do_shortcode( '[envira-album dynamic="' . $id . '" ' . trim( $output_args ) . ']' );
	
	}
	    
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Dynamic_Album_Shortcode object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Dynamic_Album_Shortcode ) ) {
            self::$instance = new Envira_Dynamic_Album_Shortcode();
        }

        return self::$instance;

    }

}

// Load the shortcode class.
$envira_dynamic_album_shortcode = Envira_Dynamic_Album_Shortcode::get_instance();

// Conditionally load the template tag.
if ( ! function_exists( 'envira_dynamic_album' ) ) {
    /**
     * Template tag function for outputting dynamic albums with Envira.
     *
     * @since 1.0.0
     *
     * @param array $args  Args used for the gallery init script.
     * @param bool $return Flag for returning or echoing the gallery content.
     */
    function envira_dynamic_album( $args = array(), $return = false ) {

        // If no ID, return false.
        if ( empty( $args['id'] ) ) {
            return false;
        }

        // Pull out the ID and remove from args.
        $id = $args['id'];
        unset( $args['id'] );

        // Call main Envira Gallery template tag function
        envira_album( $id, 'dynamic', $args, $return );

    }
}
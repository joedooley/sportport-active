<?php
/**
 * Dynamic Gallery Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Dynamic
 * @author  Tim Carr
 */
class Envira_Dynamic_Gallery_Shortcode {

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
	    
	    // Register Gallery Dynamic Shortcode
        add_shortcode( 'envira-gallery-dynamic', array( $this, 'shortcode' ) );
        add_shortcode( 'envira-gallery_dynamic', array( $this, 'shortcode' ) );
        add_filter( 'post_gallery', array( $this, 'override_gallery' ) , 9999, 2 );
        add_filter( 'envira_gallery_custom_gallery_data', array( $this, 'parse_shortcode_attributes' ), 10, 3 );
        //add_filter( 'envira_gallery_pre_data', array( $this, 'inject_images' ), 10, 2 );

        // Register Filters for Dynamic Gallery Types
        add_filter( 'envira_dynamic_get_custom_images', array( $this, 'get_custom_images' ), 10, 3 );
        add_filter( 'envira_dynamic_get_nextgen_images', array( $this, 'get_nextgen_images' ), 10, 3 );
        add_filter( 'envira_dynamic_get_folder_images', array( $this, 'get_folder_images' ), 10, 3 );
    }

    /**
     * Returns an array of Dynamic Gallery Types
     * Defaults: Media Library Images, NextGen Gallery Images and Folder Images
     *
     * Other Addons can add to this list of types and then define their own actions for grabbing the images for
     * insertion into the Dynamic Gallery.
     *
     * @since 1.0.0
     *
     * @return array Types
     */
    public function get_dynamic_gallery_types() {

    	// Build array of default types
    	// key = WordPress Filter, value = preg_match statement
    	$types = array(
    		'envira_dynamic_get_custom_images' 	=> '#^custom-#',
    		'envira_dynamic_get_nextgen_images' => '#^nextgen-#',
    		'envira_dynamic_get_folder_images'	=> '#^folder-#',
    	);

    	// Filter types
    	$types = apply_filters( 'envira_dynamic_get_dynamic_gallery_types', $types );

    	return $types;

    }
    		
    /**
	 * Parses the Dynamic Gallery attributes and filters them into the data.
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
	
	    // Now that we have a dynamic gallery, prepare atts to be parsed with defaults.
	    $dynamic_id = Envira_Dynamic_Common::get_instance()->get_gallery_dynamic_id();
	    $defaults   = get_post_meta( $dynamic_id, '_eg_gallery_data', true );

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

	    // Inject images
	    $data = $this->inject_images( $config, false );

	    // Parse the args and return the data.
	    return apply_filters( 'envira_dynamic_gallery_parsed_data', $data, $defaults, $atts, $post );
	    
    }

    /**
	 * Injects gallery images into the given $data array, using the $data settings (i.e. the dynamic gallery settings)
	 *
	 * @since 1.0.0
	 *
	 * @param array $data  Gallery Config.
	 * @param int $id      The gallery ID.
	 * @return array $data Amended array of gallery config, with images.
	 */
	function inject_images( $data, $id ) {

	    // Return early if not an Dynamic gallery.
	    $instance = Envira_Gallery_Shortcode::get_instance();
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
	    * - Media Library Image IDs: [envira-gallery-dynamic id="custom-xxx" images="id,id,id"]
	    * - NextGen Gallery ID: [envira-gallery-dynamic id="nextgen-id"]
	    * - Folder: [envira-gallery-dynamic id="folder-foldername"]
	    */
	    $dynamic_data = array();
	    $rule_matched = false;
	    $types = $this->get_dynamic_gallery_types();
	    foreach ( $types as $filter_to_execute => $preg_match ) {
	    	if ( preg_match( $preg_match, $id ) ) {
	    		// Run action for this preg_match
	    		$rule_matched = true;
	    		$dynamic_data = apply_filters( $filter_to_execute, $dynamic_data, $id, $data );
	    		break;
	    	}
	    }

	    /**
		* Get images based on supplied Dynamic settings
	    * Checks for:
	    * - Post/Page ID: [envira-gallery-dynamic id="id" exclude="id,id,id"]
	    */
		if ( ! $rule_matched ) {
			$exclude      = ! empty( $data['config']['exclude'] ) ? $data['config']['exclude'] : false;
	        $images       = $this->get_attached_images( $id, $exclude );
	        $dynamic_data = $this->get_custom_images( $dynamic_data, $id, $data, implode( ',', (array) $images ) );
		}

		// Filter 
		$dynamic_data = apply_filters( 'envira_gallery_dynamic_queried_data', $dynamic_data, $id, $data );
		
		// Check image(s) were found
		if ( count( $dynamic_data ) == 0 ) {
			// No images found, nothing to inject - just return data
			return $data;
		}

		// Generate thumbnails
		$dynamic_data = $this->maybe_generate_thumbnails( $data, $dynamic_data );

		// Insert images into gallery data
		$data['gallery'] = $dynamic_data;

	    // Return the modified data.
	    return apply_filters( 'envira_gallery_dynamic_data', $data, $id );

	}

	/**
	 * Generates thumbnails for the Dynamic Gallery if they are enabled on the Dynamic Gallery Settings
	 *
	 * @since 1.0.2
	 *
	 * @param array $data Dynamic Gallery Data
	 * @param array $dynamic_data Gallery Images
	 * @return array Gallery Images w/ thumbnail attribute
	 */
	public function maybe_generate_thumbnails( $data, $dynamic_data ) {

		// If the thumbnails option is checked for the Dynamic Gallery, generate thumbnails accordingly.
        if ( ! isset( $data['config']['thumbnails'] ) || ! $data['config']['thumbnails'] ) {
        	return $dynamic_data;
        }

        // Get common and shortcode instances
        $common = Envira_Gallery_Common::get_instance();
        $shortcode = Envira_Gallery_Shortcode::get_instance();

        // Build args for image resizing
        $args = array(
            'position' => 'c',
            'width'    => $shortcode->get_config( 'thumbnails_width', $common->get_config_default( 'thumbnails_width' ) ),
            'height'   => $shortcode->get_config( 'thumbnails_height', $common->get_config_default( 'thumbnails_height' ) ),
            'quality'  => 100,
            'retina'   => false
        );
        $args = apply_filters( 'envira_gallery_crop_image_args', $args );

        // Iterate through dynamically obtained images, creating thumbnails
        foreach ( $dynamic_data as $id => $item ) {
        	// Generate the cropped image.
            $cropped_image = $common->resize_image( $item['src'], $args['width'], $args['height'], true, $args['position'], $args['quality'], $args['retina'] );
            
            // If there is an error, possibly output error message, otherwise woot!
            if ( is_wp_error( $cropped_image ) ) {
                // If debugging is defined, print out the error.
                if ( defined( 'ENVIRA_GALLERY_CROP_DEBUG' ) && ENVIRA_GALLERY_CROP_DEBUG ) {
                    echo '<pre>' . var_export( $cropped_image->get_error_message(), true ) . '</pre>';
                }
            } else {
                $dynamic_data[ $id ]['thumb'] = $cropped_image;
            }
        }
        
        // Return
        return $dynamic_data;

	}
	
	/**
	* Retrieves the image data for custom image sets
	*
	* @param array $dynamic_data 	Existing Dynamic Data Array
	* @param int $id				ID (either custom-ID or Page/Post ID)
	* @param array $data			Gallery Configuration
	* @param bool|array $images		Array of image IDs to use (optional)
	* @return bool|array			Array of data on success, false on failure
	*/
	public function get_custom_images( $dynamic_data, $id, $data, $images = false ) {

		// Image IDs will be set in either:
		// - 1. $data['config']['images'] (by parse_shortcode_attributes())
		// - 2. $images array (passed to this function)
		$instance    = Envira_Gallery_Shortcode::get_instance();
	    $data_images = $instance->get_config( 'images', $data );
	    if ( ! $data_images ) {
	        if ( ! $images ) {
		        // No images specified matching (1) or (2) above - bail
	            return false;
	        }
	    } else {
	        $images = $data_images;
	    }
	    
	    // $images now reflects the exact images we want to include in the Gallery
	    $images     = explode( ',', (string) $images );
	    foreach ( (array) $images as $i => $image_id ) {
		    // Get image attachment and check it exists
		    $attachment = get_post( $image_id );
		    if ( !$attachment ) {
			    continue;
		    }
		    
		    // Get image details
		    $src = wp_get_attachment_image_src( $image_id, 'full' );
		    
		    // Build image attributes to match Envira Gallery
		    $dynamic_data[ $image_id ] = array(
				'status' 			=> 'published',
				'src' 				=> ( isset( $src[0] ) ? esc_url( $src[0] ) : '' ),
				'title' 			=> $attachment->post_title,
				'link' 				=> ( isset( $src[0] ) ? esc_url( $src[0] ) : '' ),
				'alt' 				=> get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
				'caption' 			=> $attachment->post_excerpt,
				'thumb' 			=> '',
				'link_new_window' 	=> 0,
		    );
	    }

	    return apply_filters( 'envira_gallery_dynamic_custom_image_data', $dynamic_data, $id, $data );
	    
	}
	
	/**
	* Retrieves the image data for a given NextGen Gallery ID
	*
	* @param array $dynamic_data 	Existing Dynamic Data Array
	* @param int $id			NextGen Gallery ID
	* @param array $data		Gallery Configuration
	* @return bool|array		Array of data on success, false on failure
	*/
	public function get_nextgen_images( $dynamic_data, $id, $data ) {
		
		// Return false if the NextGen database class is not available.
	    if ( ! class_exists( 'nggdb' ) ) {
	        return false;
	    }

	    // Get NextGen Gallery ID
	    $nextgen_id   = explode( '-', $id );
	    $id = $nextgen_id[1];
	    
	    // Get NextGen Gallery Objects
	    $nggdb = new nggdb();
	    $objects = apply_filters( 'envira_gallery_dynamic_get_nextgen_image_data', $nggdb->get_gallery( $id ), $id );

	    // Return if no objects found
	    if ( !$objects ) {
		    return false;
	    }
		
		// Build gallery
		foreach ( (array) $objects as $key => $object ) {
			// Depending on the NextGEN version, the structure of the object will vary
			if ( ! isset( $object->_ngiw ) ) {
				// Get path for gallery
				if ( ! isset( $nextgen_gallery_path ) ) {
					global $wpdb;
					$nextgen_gallery_path = $wpdb->get_row( $wpdb->prepare( "SELECT path FROM $wpdb->nggallery WHERE gid = %d", $id) );
				}

				$image = $object->_orig_image;
				$image_url = get_bloginfo( 'url' ) . '/' . $nextgen_gallery_path->path . '/' . str_replace( ' ', '%20', $image->filename );
			} else {
				$image = $object->_ngiw->_orig_image;
				$image_url = get_bloginfo( 'url' ) . '/' . $image->path . '/' . str_replace( ' ', '%20', $image->filename );
			}
			
			// Build image attributes to match Envira Gallery
		    $dynamic_data[ $image->pid ] = array(
				'status' 			=> 'published',
				'src' 				=> $image_url,
				'title' 			=> ( isset( $image->alttext ) ? strip_tags( esc_attr( $image->alttext ) ) : '' ),
				'link' 				=> $image_url,
				'alt' 				=> ( isset( $image->alttext ) ? strip_tags( esc_attr( $image->alttext ) ) : '' ),
				'caption' 			=> ( isset( $image->description ) ? $image->description : '' ),
				'thumb' 			=> '',
				'link_new_window' 	=> 0,
		    ); 

		}
	    
	    return apply_filters( 'envira_gallery_dynamic_nextgen_images', $dynamic_data, $objects, $id, $data );
	    
	}
	
	/**
	* Retrieves the image data for a given folder inside the wp-content folder
	*
	* @param array $dynamic_data 	Existing Dynamic Data Array
	* @param string $folder			Directory Name
	* @param array $data			Gallery Configuration
	* @return bool|array			Array of data on success, false on failure
	*/
	public function get_folder_images( $dynamic_data, $folder, $data ) {

		// Get any instances we want to use
		$instance = Envira_Gallery_Shortcode::get_instance();

		// Get folder
		$folder_parts = explode( '-', $folder );
		$folder = '';
		foreach ( $folder_parts as $i => $folder_part ) {
			// Skip first string (= folder)
			if ( $i == 0 ) {
				continue;
			}

			// Add to folder string
			$folder .= '/' . $folder_part;
		}

		// Check directory exists
		$folder_path = WP_CONTENT_DIR . $folder;
		$folder_url = WP_CONTENT_URL . $folder;
		if ( ! file_exists( $folder_path ) ) {
			return false;
		}
		
		// Get all files from the folder
		$h = opendir( $folder_path );
		$files = array();
		while( $file = readdir( $h ) ) {
			$files[] = $file;		
		}
		
		// Get all images from $files
		$images = preg_grep( '/\.(jpg|jpeg|png|gif)(?:[\?\#].*)?$/i', $files );

		// Check we have at least one image
		if ( count( $images ) == 0 ) {
			return false;
		}
		
		// Build gallery
	    foreach ( (array) $images as $i => $image_filename ) {
		    
		    // Get file path and URL
		    $file_path = $folder_path . '/' . $image_filename;
		    $file_url = $folder_url . '/' . $image_filename;
		    
		    // Get file info
		    $info = pathinfo( $folder_path . '/' . $image_filename );
			$ext  = $info['extension'];
			$name = wp_basename( $file_path, ".$ext" );
			
		    // If the current file we are on is a resized file, don't include it in the results
			// Gallery
			$suffix = '-' . $instance->get_config( 'crop_width', $data ) . 'x' . $instance->get_config( 'crop_height', $data ) . ( $instance->get_config( 'crop', $data ) ? '_c ' : '' ) . '.' . $ext;
			if ( strpos( $image_filename, $suffix ) !== false ) {
				continue;
			}

			// Mobile
			$suffix = '-' . $instance->get_config( 'mobile_width', $data ) . 'x' . $instance->get_config( 'mobile_height', $data ) . ( $instance->get_config( 'crop', $data ) ? '_c ' : '' ) . '.' . $ext;
			if ( strpos( $image_filename, $suffix ) !== false ) {
				continue;
			}

			// Lightbox Thumbnails
			$suffix = '-' . $instance->get_config( 'thumbnails_width', $data ) . 'x' . $instance->get_config( 'thumbnails_height', $data ) . '_c.' . $ext;
			if ( strpos( $image_filename, $suffix ) !== false ) {
				continue;
			}

		    $dynamic_data[ $i ] = array(
				'status' 			=> 'published',
				'src' 				=> $file_url,
				'title' 			=> '',
				'link' 				=> $file_url,
				'alt' 				=> '',
				'caption' 			=> '',
				'thumb' 			=> '',
				'link_new_window' 	=> 0,
		    ); 
	    }
	    
	    return apply_filters( 'envira_gallery_dynamic_folder_images', $dynamic_data, $files, $folder, $data );
			
	}
	
	/**
	* Retrieves the image data for images attached to the given Post/Page/CPT ID
	*
	* @param int $id			Post/Page/CPT ID
	* @param array $data		Gallery Configuration
	* @param string $fields		Fields to return
	* @return bool|array		Array of data on success, false on failure
	*/
	private function get_attached_images( $id, $exclude, $fields = 'ids' ) {
		
		// Prepare query args.
	    $args = array(
	        'orderby'        => 'menu_order',
	        'order'          => 'ASC',
	        'post_type'      => 'attachment',
	        'post_parent'    => $id,
	        'post_mime_type' => 'image',
	        'post_status'    => null,
	        'posts_per_page' => -1,
	        'fields'         => $fields
	    );
	
	    // Add images to exclude if necessary.
	    if ( $exclude ) {
	        $args['post__not_in'] = (array) explode( ',', $exclude );
	    }
	
	    // Allow args to be filtered and then query the images.
	    $args   = apply_filters( 'envira_gallery_dynamic_attached_image_args', $args, $id, $fields, $exclude );
	    $images = get_posts( $args );
	
	    // If no images are found, return false.
	    if ( ! $images ) {
	        return false;
	    }
	    
	    return apply_filters( 'envira_gallery_dynamic_attached_images', $images, $id, $exclude, $fields );
		
	}
	
	/**
	* Overrides the default WordPress Gallery with an Envira Gallery
	*
	* @since 1.0.0
	*
	* @param string $html HTML
	* @param array $atts Attributes
	* @return string HTML
	*/
	function override_gallery( $html, $atts ) {
		
	    // If there is no Envira Gallery attribute or we want to stop the gallery output, return the default gallery output.
	    if ( empty( $atts['envira'] ) || apply_filters( 'envira_gallery_dynamic_pre_gallery', false ) ) {
	        return $html;
	    }
	    
	    // Declare a static incremental to ensure unique IDs when multiple galleries are called.
	    global $post;
	    static $dynamic_i = 0;
	
	    // Either grab custom images or images attached to the post.
	    $images = false;
	    if ( ! empty( $atts['ids'] ) ) {
	        $images = $atts['ids'];
	    } else {
	        if ( empty( $post->ID ) ) {
	            return $html;
	        }
	
	        $exclude = ! empty( $atts['exclude'] ) ? $atts['exclude'] : false;
	        $images  = $this->get_attached_images( $post->ID, $exclude );
	    }
	
	    // If no images have been found, return the default HTML.
	    if ( ! $images ) {
	        return $html;
	    }
	
	    // Set the shortcode atts to be passed into shortcode regardless.
	    $args           = array();
	    $args['images'] = implode( ',', (array) $images );
	    //$args['link']   = ! empty( $atts['link'] ) ? $atts['link'] : 'none';
	
	    // Check if the envira_args attribute is set and parse the query string provided.
	    if ( ! empty( $atts['envira_gallery_args'] ) ) {
	        wp_parse_str( html_entity_decode( $atts['envira_gallery_args'] ), $parsed_args );
	        $args = array_merge( $parsed_args, $args );
	        $args = apply_filters( 'envira_gallery_dynamic_gallery_args', $args, $atts, $dynamic_i );
	    }
	
	    // Prepare the args to be output into query string shortcode format for the shortcode.
	    $output_args = '';
	    foreach ( $args as $k => $v ) {
	        $output_args .= $k . '=' . $v . ' ';
	    }
	
	    // Increment the static counter.
	    $dynamic_i++;
	
	    // Map to the new Envira shortcode with the proper data structure.
	    return do_shortcode( '[envira-gallery-dynamic id="custom-gallery-' . $dynamic_i . '" ' . trim( $output_args ) . ']' );
	
	}
	
	/**
	 * Create the gallery shortcode
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
	
	    // Map to the Envira Gallery shortcode with the proper data structure.
	    return do_shortcode( '[envira-gallery dynamic="' . $id . '" ' . trim( $output_args ) . ']' );
	
	}
	    
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Dynamic_Gallery_Shortcode object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Dynamic_Gallery_Shortcode ) ) {
            self::$instance = new Envira_Dynamic_Gallery_Shortcode();
        }

        return self::$instance;

    }

}

// Load the shortcode class.
$envira_dynamic_gallery_shortcode = Envira_Dynamic_Gallery_Shortcode::get_instance();

// Conditionally load the template tag.
if ( ! function_exists( 'envira_dynamic' ) ) {
    /**
     * Template tag function for outputting dynamic galleries with Envira.
     *
     * @since 1.0.0
     *
     * @param array $args  Args used for the gallery init script.
     * @param bool $return Flag for returning or echoing the gallery content.
     */
    function envira_dynamic( $args = array(), $return = false ) {

        // If no ID, return false.
        if ( empty( $args['id'] ) ) {
            return false;
        }

        // Pull out the ID and remove from args.
        $id = $args['id'];
        unset( $args['id'] );

        // Call main Envira Gallery template tag function
        envira_gallery( $id, 'dynamic', $args, $return );

    }
}
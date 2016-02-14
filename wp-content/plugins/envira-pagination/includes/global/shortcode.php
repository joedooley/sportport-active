<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Pagination
 * @author  Tim Carr
 */
class Envira_Pagination_Shortcode {

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
     * Standalone: Holds the gallery/album data.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $data;

    /**
     * Standalone: Current Page for a Gallery/Album
     *
     * @since 1.0.0
     *
     * @var int
     */
    public $currentPage = 1;

	/**
     * Standalone: Total Pages for a Gallery/Album
     *
     * @since 1.0.0
     *
     * @var int
     */
    public $totalPages = 1;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

		// Standalone: Add rel prev/next links to Galleries and Albums
		add_action( 'envira_standalone_gallery_pre_get_posts', array( $this, 'maybe_rel_gallery' ) );
		add_action( 'envira_standalone_album_pre_get_posts', array( $this, 'maybe_rel_album' ) );
		
		// Output Pagination
        add_filter( 'envira_gallery_pre_data', array( $this, 'paginate_gallery' ), 10, 2 );
		add_filter( 'envira_albums_pre_data', array( $this, 'paginate_album' ), 10, 2 );
		
    }
    
    /**
	* Called if the Standalone Addon is going to load a Gallery
	*
	* @since 1.0.4
	*
	* @param object $query WP_Query
	*/
    public function maybe_rel_gallery( $query ) {

    	if ( ! isset( $query->query['name'] ) ) {
    		return;
    	}
	    
		// Check if Pagination is enabled on this Standalone Gallery
		// If so, add rel next/prev to the <head> of the site
		$instance = Envira_Gallery::get_instance();
		$data = $instance->get_gallery_by_slug( $query->query['name'] );
		
		// Check we found a valid Gallery
		if ( !$data || !is_array( $data ) ) {
			return;
		}
		
		// Get gallery config to see if pagination is enabled
	    $paginate = absint( $this->get_gallery_config( 'pagination', $data ) );
	    $position = $this->get_gallery_config( 'pagination_position', $data );
	    $imagesPerPage = absint( $this->get_gallery_config( 'pagination_images_per_page', $data ) );

	    // If images per page is less than 1, force the value so there's no division by zero error
		if ( $imagesPerPage < 1 ) {
			$imagesPerPage = 1;
		}

	    // Bail if pagination disabled
	    if ( !$paginate ) {
		    return;
	    }
	    
	    // Bail if the number of images are less than or equal to the number of images per page
	    if ( count( $data['gallery'] ) <= $imagesPerPage ) {
		    return;
	    }
	    
	    // Determine which page we are on and the total number of pages available in this Gallery
	    $this->data			= $data;
	    $this->currentPage 	= $this->get_pagination_page();
	    $this->totalPages 	= ceil( count( $data['gallery'] ) / $imagesPerPage ); 
	    
	    // Check we have at least one page
	    if ( $this->totalPages < 2 ) {
		    return;
	    }

	    // If Pagination and Tags are enabled for this Gallery, disable JS tag filtering
	    // and use non-JS tag filtering instead
	    if ( $this->get_gallery_config( 'tags', $data ) ) {
	    	remove_action( 'envira_gallery_api_enviratope', 'envira_tags_filter_enviratope' );
	    }
	    
	    // Add wp_head action to add rel links to the header of the site
	    add_action( 'wp_head', array( $this, 'add_rel_links' ) );
	    
    }
    
    /**
	* Called if the Standalone Addon is going to load an Album
	*
	* @since 1.0.4
	*
	* @param object $query WP_Query
	*/
    public function maybe_rel_album( $query ) {
	    
		// Check if Pagination is enabled on this Standalone Album
		// If so, add rel next/prev to the <head> of the site
		$instance = Envira_Albums::get_instance();
		$data = $instance->get_album_by_slug( $query->query['name'] );
		
		// Check we found a valid Gallery
		if ( !$data || !is_array( $data ) ) {
			return;
		}
		
		// Get gallery config to see if pagination is enabled
	    $paginate = absint( $this->get_album_config( 'pagination', $data ) );
	    $position = $this->get_album_config( 'pagination_position', $data );
	    $galleriesPerPage = absint( $this->get_album_config( 'pagination_images_per_page', $data ) );
	    
	    // Bail if pagination disabled
	    if ( !$paginate ) {
		    return;
	    }
	    
	    // Bail if the number of galleries are less than or equal to the number of galleries per page
	    if ( count( $data['galleryIDs'] ) <= $galleriesPerPage ) {
		    return $data;
	    }
	    
		// Determine which page we are on and the total number of pages available in this Album
	    $this->data			= $data;
	    $this->currentPage 	= $this->get_pagination_page();
	    $this->totalPages 	= ceil( count( $data['galleryIDs'] ) / $galleriesPerPage ); 
	    
	    // Check we have at least one page
	    if ( $this->totalPages < 2 ) {
		    return;
	    }
	    
	    // Add wp_head action to add rel links to the header of the site
	    add_action( 'wp_head', array( $this, 'add_rel_links' ) );
	    
    }
    
    /**
	* Add link rel prev/next to the header of the WordPress site
	*/
    public function add_rel_links() {
	    
	    // Previous
	    if ( $this->currentPage > 1 ) {
		    $url = add_query_arg( array(
			    'page' => ( $this->currentPage - 1 ),
		    ), get_permalink( $this->data['id']) );
		     
		    echo '<link rel="prev" href="' . $url . '" />';
	    }
	    
	    // Next
	    if ( $this->currentPage < $this->totalPages ) {
		    $url = add_query_arg( array(
			    'page' => ( $this->currentPage + 1 ),
		    ), get_permalink( $this->data['id']) );
		    
		    echo '<link rel="next" href="' . $url . '" />';
	    }
	    
    }
    
    
    /**
	* Paginate images, if pagination is enabled on this gallery
	*
	* @since 1.0.0
	*
	* @param array 	$data Gallery Data
	* @param int 	$gallery_id Gallery ID
	* @return array Modified Gallery Data
	*/
    public function paginate_gallery( $data, $gallery_id ) {
	    
	    // Get config
	    $paginate = absint( $this->get_gallery_config( 'pagination', $data ) );
	    $position = $this->get_gallery_config( 'pagination_position', $data );
	    $imagesPerPage = absint( $this->get_gallery_config( 'pagination_images_per_page', $data ) );
	    
	    // Don't modify gallery data if pagination is disabled
	    if ( ! $paginate ) {
		    return $data;
	    }
	    
	    // Don't modify gallery data if the number of images are less than or equal to the number of images per page
	    if ( count( $data['gallery'] ) <= $imagesPerPage ) {
		    return $data;
	    }
	    
	    // Determine which page we are on, and define the start index from a zero based index
	    $start = ( ( $this->get_pagination_page() - 1 ) * $imagesPerPage );

	    // If an envira_id is specified in the URL, don't set the start point on every gallery to be the same
	   	if ( ! is_singular( array( 'envira', 'envira_album' ) ) ) {
	    	if ( isset( $_GET['envira_id'] ) && $gallery_id != $_GET['envira_id'] ) {
	    		// This gallery isn't being paginated, but is being displayed
	    		// Set the start to zero
	    		$start = 0;
	    	}
	    }

	    // Store the original total number of pages available - this allows paginate_gallery_markup() to know
	    // how many links to output for the pagination
	    $data['config']['pagination_total_pages'] = ceil( count( $data['gallery'] ) / $imagesPerPage );

	    // Extract subset of images, and apply them back to the $data
	    // This means the gallery will only output the specified number of images
	    // based on the page index we are on
    	$images = array_slice( $data['gallery'], $start, $imagesPerPage, true );
    	$data['gallery'] = $images;
	    
		// Enable pagination display before images, after images or both
		if ( $position == 'above' || $position == 'both' ) {
			add_filter( 'envira_gallery_output_before_container', array( $this, 'paginate_markup' ), 1, 2 );
		}
		if ($position == 'below' || $position == 'both' ) {
			add_filter( 'envira_gallery_output_after_container', array( $this, 'paginate_markup' ), 1, 2 );
	    }
	    
	    // Return
	    return $data;
	    
    }
    
    /**
	* Paginate galleries, if pagination is enabled on this album
	*
	* @since 1.0.0
	*
	* @param array 	$data Album Data
	* @param int 	$album_id Album ID
	* @return array Modified Album Data
	*/
    public function paginate_album( $data, $album_id ) {
	    
	    // Get config
	    $paginate = absint( $this->get_album_config( 'pagination', $data ) );
	    $position = $this->get_album_config( 'pagination_position', $data );
	    $galleriesPerPage = absint( $this->get_album_config( 'pagination_images_per_page', $data ) );
	    
	    // Don't modify gallery data if pagination is disabled
	    if ( ! $paginate ) {
		    return $data;
	    }
	    
	    // Don't modify gallery data if the number of images are less than or equal to the number of images per page
	    if ( count( $data['galleryIDs'] ) <= $galleriesPerPage ) {
		    return $data;
	    }
	    
	    // Determine which page we are on, and define the start index from a zero based index
	    $start = ( ( $this->get_pagination_page() - 1 ) * $galleriesPerPage );
	    
	    // Store the original total number of pages available - this allows paginate_gallery_markup() to know
	    // how many links to output for the pagination
	    $data['config']['pagination_total_pages'] = ceil( count( $data['galleries'] ) / $galleriesPerPage );

	    // If an envira_id is specified in the URL, don't set the start point on every album to be the same
	   	if ( ! is_singular( array( 'envira', 'envira_album' ) ) ) {
	    	if ( isset( $_GET['envira_id'] ) && $album_id != $_GET['envira_id'] ) {
	    		// This album isn't being paginated, but is being displayed
	    		// Set the start to zero
	    		$start = 0;
	    	}
	    }
	    
	    // Extract subset of galleries, and apply them back to the $data
	    // This means the album will only output the specified number of galleries
	    // based on the page index we are on
	    $galleryIDs = array_slice( $data['galleryIDs'], $start, $galleriesPerPage, true );
	    $data['galleryIDs'] = $galleryIDs;
	    
	    $galleries = array_slice( $data['galleries'], $start, $galleriesPerPage, true );
	    $data['galleries'] = $galleries;
	    
		// Enable pagination display before galleries, after galleries or both
		if ( $position == 'above' || $position == 'both' ) {
			add_filter( 'envira_albums_output_before_container', array( $this, 'paginate_markup' ), 1, 2 );
		}
		if ($position == 'below' || $position == 'both' ) {
			add_filter( 'envira_albums_output_after_container', array( $this, 'paginate_markup' ), 1, 2 );
	    }
	    
	    // Return
	    return $data;
	    
    }
    
    /**
	* Append the pagination markup to the end of the gallery or album
	*
	* @since 1.0.0
	*
	* @param string $html HTML Markup
	* @param array $data Gallery Data
	* @return string Modified HTML Markup
	*/
    public function paginate_markup( $html, $data ) {

	    global $post;

	    /**
	    * If Pagination isn't used on a Standalone Gallery/Album, we need to adjust the format
	    * so that a visitor can paginate an individual Gallery/Album. This allows multiple Galleries/Albums
	    * to be embedded in a single Page
	    */
	    if ( ! is_singular( array( 'envira', 'envira_album' ) ) ) {
	    	$base_args = array(
	    		'envira_id' => $data['id'],
	    		'page' => '%#%',
	    	);
	    	$format = '?envira_id=' . $data['id'] . '&page=%#%';

	    	// We only set the current page to the paged argument if we're generating markup
	    	// for the gallery/album we're currently paginating
	    	$current = 1;
	    	if ( isset( $_GET['envira_id'] ) && $data['id'] == $_GET['envira_id'] ) {
	    		$current = $this->get_pagination_page();	
	    	}
	    } else {
	    	$base_args = array(
	    		'page' => '%#%',
	    	);
	    	$format = '?page=%#%';

	    	$current = $this->get_pagination_page();
	    }

	    // Generate base URL
	    $url = add_query_arg( $base_args, get_permalink( $post->ID ) );
	    
	    // Build pagination
	    $pagination_args = array(
			'base' 					=> $url . ( ( isset( $data['config']['pagination_scroll'] ) && $data['config']['pagination_scroll'] == '1' ) ? '#envira-gallery-wrap-' . $data['id'] : '' ),
		    'format' 				=> $format,
		    'total' 				=> $data['config']['pagination_total_pages'],
		    'current' 				=> $current,
		    'show_all' 				=> false,
		    'end_size' 				=> 1,
		    'mid_size' 				=> 2,
		    'prev_next' 			=> (bool) $this->get_gallery_config( 'pagination_prev_next', $data ),
		    'prev_text' 			=> $this->get_gallery_config( 'pagination_prev_text', $data ),
		    'next_text' 			=> $this->get_gallery_config( 'pagination_next_text', $data ),
		    'type' 					=> 'plain',
		    'add_args' 				=> false,
		    'add_fragment' 			=> '',
		    'before_page_number' 	=> '',
		    'after_page_number' 	=> '',
	    );

	    // Filter pagination args
	    $pagination_args = apply_filters( 'envira_pagination_link_args', $pagination_args, $html, $data );

	    // Output pagination
	    $pagination = '<div class="envira-pagination">' . paginate_links( $pagination_args ) . '</div>';
	    
	    // Modify the pagination HTML
	    $pagination = str_replace( '<a class="prev', '<a rel="prev" class="prev', $pagination );
	    $pagination = str_replace( '<a class="next', '<a rel="next" class="next', $pagination );
		
		// Return
		return $html . $pagination;
		    
    }
    
    /**
     * Helper method for retrieving config values for a Gallery
     *
     * @since 1.0.0
     *
     * @param string $key The config key to retrieve.
     * @param array $data The gallery data to use for retrieval.
     * @return string     Key value on success, default if not set.
     */
    public function get_gallery_config( $key, $data ) {

        $instance = Envira_Gallery_Common::get_instance();
        return isset( $data['config'][$key] ) ? $data['config'][$key] : $instance->get_config_default( $key );

    }
    
     /**
     * Helper method for retrieving config values for an Album
     *
     * @since 1.0.0
     *
     * @param string $key The config key to retrieve.
     * @param array $data The gallery data to use for retrieval.
     * @return string     Key value on success, default if not set.
     */
    public function get_album_config( $key, $data ) {

        $instance = Envira_Albums_Common::get_instance();
        return isset( $data['config'][$key] ) ? $data['config'][$key] : $instance->get_config_default( $key );

    }
        
    /**
     * Helper method for retrieving the current page number a visitor is viewing
     * within a paginated gallery or album.
     *
     * @since 1.0.0
     *
     * @return int Page Number
     */
    public function get_pagination_page() {
	    
	    $page = str_replace( '/', '', get_query_var( 'page' ) );
	    
	    if ( $page < 1 ) {
		    $page = 1;
	    }
	    
	    return $page;
	    
    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Pagination_Shortcode object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Pagination_Shortcode ) ) {
            self::$instance = new Envira_Pagination_Shortcode();
        }

        return self::$instance;

    }

}

// Load the shortcode class.
$envira_pagination_shortcode = Envira_Pagination_Shortcode::get_instance();
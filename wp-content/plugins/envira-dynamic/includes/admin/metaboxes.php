<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Dynamic
 * @author  Tim Carr
 */
class Envira_Dynamic_Metaboxes {

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
     * Holds the Envira Gallery Dynamic ID.
     *
     * @since 1.0.0
     *
     * @var int
     */
    public $gallery_dynamic_id;
    
    /**
     * Holds the Envira Album Dynamic ID.
     *
     * @since 1.0.0
     *
     * @var int
     */
    public $album_dynamic_id;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

		// Load the base class object.
        $this->base = Envira_Dynamic::get_instance();
        
        // Get Envira Gallery and Albums Dynamic ID
        $this->gallery_dynamic_id = get_option( 'envira_dynamic_gallery' );
        $this->album_dynamic_id = get_option( 'envira_dynamic_album' );

        // Hide Slug Box
        add_filter( 'envira_gallery_metabox_styles', array( $this, 'maybe_hide_slug_box' ) );
        add_filter( 'envira_album_metabox_styles', array( $this, 'maybe_hide_slug_box' ) );

        // Actions and Filters: Galleries
        add_filter( 'envira_gallery_types', array( $this, 'add_dynamic_type' ), 9999, 2 );
        add_action( 'envira_gallery_display_dynamic', array( $this, 'images_display' ) );
        
        // Actions and Filters: Albums
        add_filter( 'envira_albums_types', array( $this, 'add_dynamic_type' ), 9999, 2 );
        add_action( 'envira_albums_display_dynamic', array( $this, 'images_display' ) );
        
    }

    /**
     * Removes the slug metabox if we are on a Dynamic Gallery or Album
     *
     * @since 1.0.0
     */
    public function maybe_hide_slug_box( ) {

        if ( !isset( $_GET['post'] ) ) {
            return;
        }

        // Check if we are viewing a Dynamic Gallery or Album
        if ( $_GET['post'] != $this->gallery_dynamic_id && $_GET['post'] != $this->album_dynamic_id ) {
            return;
        }

        ?>
        <style type="text/css"> #edit-slug-box { display: none; } </style>
        <?php

    }
    
    /**
	 * Changes the available Gallery Type to Dynamic if the user is editing
	 * the Envira Dynamic Post
	 *
	 * @since 1.0.0
	 *
	 * @param array $types Gallery Types
	 * @param WP_Post $post WordPress Post
	 * @return array Gallery Types
	 */
    public function add_dynamic_type( $types, $post ) {
	    
	    // Check Post = Dynamic
	    switch ( get_post_type( $post ) ) {
		    case 'envira':
		    	if ( $post->ID != $this->gallery_dynamic_id) {
				    return $types;
			    }
		    	break;
		    case 'envira_album':
		    	if ( $post->ID != $this->album_dynamic_id) {
				    return $types;
			    }
		    	break;
		    default:
		    	// Not an Envira CPT
		    	return $types;
		    	break;
	    }
	    
	    // Change Types = Dynamic only
	    $types = array(
		    'dynamic' => __( 'Dynamic', 'envira-dynamic' ),
	    );
	    
	    return $types;
	    
    }
    
    /**
	 * Display output for the Images Tab
	 *
	 * @since 1.0.0
	 * @param WP_Post $post WordPress Post
	 */
    public function images_display( $post ) {
		
		?>
		<div id="envira-dynamic">
        	<p class="envira-intro">
	        	<?php
		        switch ( get_post_type ( $post ) ) {
			        case 'envira':
			        	printf( __( 'This gallery and its settings will be used as defaults for any dynamic gallery you create on this site. Any of these settings can be overwritten on an individual gallery basis via template tag arguments or shortcode parameters. <a href="%s" title="Click here for Dynamic Addon documentation." target="_blank">Click here for Dynamic Addon documentation.</a>', 'envira-dynamic' ), 'http://enviragallery.com/docs/dynamic-addon/' ); 
						break;
			        case 'envira_album':
			        	printf( __( 'This album and its settings will be used as defaults for any dynamic album you create on this site. Any of these settings can be overwritten on an individual album basis via template tag arguments or shortcode parameters. <a href="%s" title="Click here for Dynamic Addon documentation." target="_blank">Click here for Dynamic Addon documentation.</a>', 'envira-dynamic' ), 'http://enviragallery.com/docs/dynamic-addon/' ); 
	        	    	break;
		        }
	        	?>
	        </p>
    	</div>
    	<?php
		    
    }
    
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Dynamic_Metaboxes object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Dynamic_Metaboxes ) ) {
            self::$instance = new Envira_Dynamic_Metaboxes();
        }

        return self::$instance;

    }

}

// Load the metaboxes class.
$envira_dynamic_metaboxes = Envira_Dynamic_Metaboxes::get_instance();
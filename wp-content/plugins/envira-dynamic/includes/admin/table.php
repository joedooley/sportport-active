<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Dynamic
 * @author  Tim Carr
 */
class Envira_Dynamic_Table {

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
        
        // Get Envira Gallery and Album Dynamic IDs
        $this->gallery_dynamic_id = get_option( 'envira_dynamic_gallery' );
        $this->album_dynamic_id = get_option( 'envira_dynamic_album' );
        
        // Actions and Filters
        add_action( 'admin_head', array( $this, 'remove_checkbox' ) );
        add_filter( 'page_row_actions', array( $this, 'remove_row_actions' ), 10, 2 );
        add_filter( 'post_row_actions', array( $this, 'remove_row_actions' ), 10, 2 );

    }
    
    /**
	 * Removes the Checkbox from the Envira Dynamic Post
	 * This prevents accidental trashing of the Post
	 *
	 * @since 1.0.0
	 * 
	 */
	public function remove_checkbox() {
		
		// Gallery
		if ( isset( get_current_screen()->post_type ) && 'envira' == get_current_screen()->post_type ) {
	        ?>
	        <script type="text/javascript">
	            jQuery(document).ready(function($){
	                $('#post-<?php echo $this->gallery_dynamic_id; ?> .check-column, #post-<?php echo $this->gallery_dynamic_id; ?> .column-shortcode, #post-<?php echo $this->gallery_dynamic_id; ?> .column-template, #post-<?php echo $this->gallery_dynamic_id; ?> .column-images').empty();
	            });
	        </script>
	        <?php
	    }
	    
	    // Album
	    if ( isset( get_current_screen()->post_type ) && 'envira_album' == get_current_screen()->post_type ) {
	        ?>
	        <script type="text/javascript">
	            jQuery(document).ready(function($){
	                $('#post-<?php echo $this->album_dynamic_id; ?> .check-column, #post-<?php echo $this->album_dynamic_id; ?> .column-shortcode, #post-<?php echo $this->album_dynamic_id; ?> .column-template, #post-<?php echo $this->album_dynamic_id; ?> .column-images').empty();
	            });
	        </script>
	        <?php
	    }
	    
	}
   
	/**
	 * Removes Trash and View actions from the Envira Dynamic Gallery Post
	 *
	 * @since 1.0.0
	 *
	 * @param array $actions Post Row Actions
	 * @param WP_Post $post WordPress Post
	 * @return array Post Row Actions
	 */
	public function remove_row_actions( $actions, $post ) {
		
		switch ( get_post_type( $post ) ) {
			case 'envira':
				// Check Post = Envira Gallery Dynamic Post
				if ( $post->ID != $this->gallery_dynamic_id ) {
					return $actions;
				}
				break;
			case 'envira_album':
				// Check Post = Envira Album Dynamic Post
				if ( $post->ID != $this->album_dynamic_id ) {
					return $actions;
				}
				break;
			default:
				// Not an Envira CPT
				return $actions;
				break;
		}
		
		
		// If here, this is the Envira Dynamic Post
		// Remove View + Trash Actions
		unset( $actions['trash'], $actions['view'] );
		
		return $actions;
		
	}  
    
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Dynamic_Table object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Dynamic_Table ) ) {
            self::$instance = new Envira_Dynamic_Table();
        }

        return self::$instance;

    }

}

// Load the table class.
$envira_dynamic_table = Envira_Dynamic_Table::get_instance();
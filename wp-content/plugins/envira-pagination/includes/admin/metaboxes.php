<?php
/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Pagination
 * @author  Tim Carr
 */
class Envira_Pagination_Metaboxes {

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

		// Envira Gallery
        add_filter( 'envira_gallery_defaults', array( $this, 'defaults' ), 10, 2 );
		add_filter( 'envira_gallery_tab_nav', array( $this, 'tab_nav' ) );
		add_action( 'envira_gallery_tab_pagination', array( $this, 'settings_screen' ) );
		add_filter( 'envira_gallery_save_settings', array( $this, 'gallery_settings_save' ), 10, 2 );

		// Envira Albums
		add_filter( 'envira_albums_defaults', array( $this, 'defaults' ), 10, 2 );
		add_filter( 'envira_albums_tab_nav', array( $this, 'tab_nav' ) );
		add_action( 'envira_albums_tab_pagination', array( $this, 'settings_screen' ) );
		add_filter( 'envira_albums_save_settings', array( $this, 'albums_settings_save' ), 10, 2 );
    }
    
    /**
	 * Adds the default settings for this addon.
	 *
	 * @since 1.0.0
	 *
	 * @param array $defaults  Array of default config values.
	 * @param int $post_id     The current post ID.
	 * @return array $defaults Amended array of default config values.
	 */
	function defaults( $defaults, $post_id ) {
	
	    // Add Pagination default settings to main defaults array
	    $defaults['pagination']         		= 0;
	    $defaults['pagination_position']		= 'below';
	    $defaults['pagination_images_per_page'] = 9;
	    $defaults['pagination_prev_next'] 		= 0;
	    $defaults['pagination_prev_text'] 		= __( '« Previous', 'envira-pagination' );
	    $defaults['pagination_next_text'] 		= __( 'Next »', 'envira-pagination' );
	    $defaults['pagination_scroll']			= 0;
	    
	    // Return
	    return $defaults;
	
	}
	
	/**
     * Helper method for retrieving position values.
     *
     * @since 1.0.2
     *
     * @return array Array of position data.
     */
    public function get_positions() {

        $positions = array(
            array(
                'value' => 'above',
                'name'  => __( 'Above Images', 'envira-pagination' )
            ),
			array(
                'value' => 'below',
                'name'  => __( 'Below Images', 'envira-pagination' )
            ),
            array(
                'value' => 'both',
                'name'  => __( 'Above and Below Images', 'envira-pagination' )
            ),
        );

        return apply_filters( 'envira_pagination_positions', $positions );

    }
	
	/**
	 * Adds a new tab for this addon.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tabs  Array of default tab values.
	 * @return array $tabs Amended array of default tab values.
	 */
	function tab_nav( $tabs ) {
	
	    $tabs['pagination'] = __( 'Pagination', 'envira-pagination' );
	    return $tabs;
	
	}

    /**
	 * Adds addon settings ui to the new tab
	 *
	 * @since 1.0.0
	 *
	 * @param object $post The current post object.
	 */
	function settings_screen( $post ) {
		
		// Get post type so we load the correct metabox instance and define the input field names
		// Input field names vary depending on whether we are editing a Gallery or Album
		$postType = get_post_type( $post );
		switch ( $postType ) {
			/**
			* Gallery
			*/
			case 'envira':
				$instance = Envira_Gallery_Metaboxes::get_instance();
				$key = '_envira_gallery';
				break;
			
			/**
			* Album
			*/
			case 'envira_album':
				$instance = Envira_Albums_Metaboxes::get_instance();
				$key = '_eg_album_data[config]';
				break;
		}
		
	    ?>
	    <div id="envira-pagination">
	        <p class="envira-intro"><?php _e( 'The settings below adjust the Pagination settings.', 'envira-pagination' ); ?></p>
	        <table class="form-table">
	            <tbody>
	                <tr id="envira-config-pagination-box">
	                    <th scope="row">
	                        <label for="envira-config-pagination"><?php _e( 'Enable Pagination?', 'envira-pagination' ); ?></label>
	                    </th>
	                    <td>
	                        <input id="envira-config-pagination" type="checkbox" name="<?php echo $key; ?>[pagination]" value="1" <?php checked( $instance->get_config( 'pagination', $instance->get_config_default( 'pagination' ) ), 1 ); ?> data-envira-conditional="envira-config-pagination-position-box,envira-config-pagination-posts-per-page-box,envira-config-pagination-prev-next-box,envira-config-pagination-prev-text-box,envira-config-pagination-next-text-box,envira-config-pagination-scroll-box" />
	                        <span class="description"><?php _e( 'Enables or disables Pagination.', 'envira-pagination' ); ?></span>
	                    </td>
	                </tr>
	                
	                <tr id="envira-config-pagination-position-box">
                        <th scope="row">
                            <label for="envira-config-columns"><?php _e( 'Pagination Position', 'envira-gallery' ); ?></label>
                        </th>
                        <td>
                            <select id="envira-config-columns" name="<?php echo $key; ?>[pagination_position]">
                                <?php foreach ( (array) $this->get_positions() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $instance->get_config( 'pagination_position', $instance->get_config_default( 'pagination_position' ) ) ); ?>><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Choose where to display Pagination.', 'envira-gallery' ); ?></p>
                        </td>
                    </tr>
	                <tr id="envira-config-pagination-posts-per-page-box">
	                    <th scope="row">
	                        <label for="envira-config-pagination-posts-per-page"><?php _e( 'Images per Page', 'envira-pagination' ); ?></label>
	                    </th>
	                    <td>
	                        <input id="envira-config-pagination-posts-per-page" type="number" name="<?php echo $key; ?>[pagination_images_per_page]" min="0" max="999" step="1" value="<?php echo $instance->get_config( 'pagination_images_per_page', $instance->get_config_default( 'pagination_images_per_page' ) ); ?>" />
	                        <span class="description"><?php _e( 'The number of images to display on each page.', 'envira-pagination' ); ?></span>
	                    </td>
	                </tr>
	                <tr id="envira-config-pagination-prev-next-box">
	                    <th scope="row">
	                        <label for="envira-config-pagination-prev-next"><?php _e( 'Display Previous and Next Links?', 'envira-pagination' ); ?></label>
	                    </th>
	                    <td>
	                        <input id="envira-config-pagination-prev-next" type="checkbox" name="<?php echo $key; ?>[pagination_prev_next]" value="1" <?php checked( $instance->get_config( 'pagination_prev_next', $instance->get_config_default( 'pagination_prev_next' ) ), 1 ); ?> />
	                        <span class="description"><?php _e( 'Displays Previous and Next links either side of the numerical pagination.', 'envira-pagination' ); ?></span>
	                    </td>
	                </tr>
	                
	                <tr id="envira-config-pagination-prev-text-box">
	                    <th scope="row">
	                        <label for="envira-config-pagination-prev-text"><?php _e( 'Previous Link Label', 'envira-pagination' ); ?></label>
	                    </th>
	                    <td>
	                        <input id="envira-config-pagination-prev-text" type="text" name="<?php echo $key; ?>[pagination_prev_text]" value="<?php echo $instance->get_config( 'pagination_prev_text', $instance->get_config_default( 'pagination_prev_text' ) ); ?>" />
	                        <span class="description"><?php _e( 'The text to display when the Previous Link is displayed.', 'envira-pagination' ); ?></span>
	                    </td>
	                </tr>
	                
	                <tr id="envira-config-pagination-next-text-box">
	                    <th scope="row">
	                        <label for="envira-config-pagination-next-text"><?php _e( 'Next Link Label', 'envira-pagination' ); ?></label>
	                    </th>
	                    <td>
	                        <input id="envira-config-pagination-next-text" type="text" name="<?php echo $key; ?>[pagination_next_text]" value="<?php echo $instance->get_config( 'pagination_next_text', $instance->get_config_default( 'pagination_next_text' ) ); ?>" />
	                        <span class="description"><?php _e( 'The text to display when the Next Link is displayed.', 'envira-pagination' ); ?></span>
	                    </td>
	                </tr>

	                <tr id="envira-config-pagination-scroll-box">
	                    <th scope="row">
	                        <label for="envira-config-pagination-scroll"><?php _e( 'Scroll to Gallery?', 'envira-pagination' ); ?></label>
	                    </th>
	                    <td>
	                        <input id="envira-config-pagination-scroll" type="checkbox" name="<?php echo $key; ?>[pagination_scroll]" value="1" <?php checked( $instance->get_config( 'pagination_scroll', $instance->get_config_default( 'pagination_scroll' ) ), 1 ); ?> />
	                        <span class="description"><?php _e( 'If enabled, scrolls / jumps to the gallery when the pagination is used.', 'envira-pagination' ); ?></span>
	                    </td>
	                </tr>
	            </tbody>
	        </table>
	    </div>
	    <?php
	
	}
	
	/**
	 * Saves the addon's settings for Galleries.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int $pos_tid     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	function gallery_settings_save( $settings, $post_id ) {
		
	    $settings['config']['pagination']          			= ( isset( $_POST['_envira_gallery']['pagination'] ) ? 1 : 0 );
	    $settings['config']['pagination_position']          = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_envira_gallery']['pagination_position']);
	    $settings['config']['pagination_images_per_page'] 	= absint( $_POST['_envira_gallery']['pagination_images_per_page'] );
	    $settings['config']['pagination_prev_next'] 		= ( isset( $_POST['_envira_gallery']['pagination_prev_next'] ) ? 1 : 0 );
	    $settings['config']['pagination_prev_text'] 		= sanitize_text_field( $_POST['_envira_gallery']['pagination_prev_text'] );
	    $settings['config']['pagination_next_text'] 		= sanitize_text_field( $_POST['_envira_gallery']['pagination_next_text'] );
	    $settings['config']['pagination_scroll']          	= ( isset( $_POST['_envira_gallery']['pagination_scroll'] ) ? 1 : 0 );
	    
	    return $settings;
	
	}
		
	/**
	 * Saves the addon's settings for Albums.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int $pos_tid     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	function albums_settings_save( $settings, $post_id ) {
	
	    $settings['config']['pagination']          			= ( isset( $_POST['_eg_album_data']['config']['pagination'] ) ? 1 : 0 );
	    $settings['config']['pagination_position']          = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_eg_album_data']['config']['pagination_position']);
	    $settings['config']['pagination_images_per_page'] 	= absint( $_POST['_eg_album_data']['config']['pagination_images_per_page'] );
	    $settings['config']['pagination_prev_next'] 		= ( isset( $_POST['_eg_album_data']['config']['pagination_prev_next'] ) ? 1 : 0 );
	    $settings['config']['pagination_prev_text'] 		= sanitize_text_field( $_POST['_eg_album_data']['config']['pagination_prev_text'] );
	    $settings['config']['pagination_next_text'] 		= sanitize_text_field( $_POST['_eg_album_data']['config']['pagination_next_text'] );
	    $settings['config']['pagination_scroll']          	= ( isset( $_POST['_eg_album_data']['config']['pagination_scroll'] ) ? 1 : 0 );

	    return $settings;
	
	}
	
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Pagination_Metaboxes object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Pagination_Metaboxes ) ) {
            self::$instance = new Envira_Pagination_Metaboxes();
        }

        return self::$instance;

    }

}

// Load the metabox class.
$envira_pagination_metaboxes = Envira_Pagination_Metaboxes::get_instance();
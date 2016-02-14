<?php
/**
 * Metaboxes class.
 *
 * @since 1.0.5
 *
 * @package Envira_Deeplinking
 * @author  Tim Carr
 */
class Envira_Deeplinking_Metaboxes {

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
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Actions and Filters
        add_action( 'envira_gallery_config_box', array( $this, 'config_display' ) );
        add_filter( 'envira_gallery_save_settings', array( $this, 'save' ), 10, 2 );
        
    }
    
    /**
	 * Display output for the Config Tab
	 *
	 * @since 1.0.0
	 * @param WP_Post $post WordPress Post
	 */
    public function config_display( $post ) {
		
        $instance = Envira_Gallery_Metaboxes::get_instance();
        ?>
        <tr id="envira-config-deeplinking-box">
            <th scope="row">
                <label for="envira-config-deeplinking"><?php _e( 'Enable Deeplinking?', 'envira-deeplinking' ); ?></label>
            </th>
            <td>
                <input id="envira-config-deeplinking" type="checkbox" name="_envira_gallery[deeplinking]" value="<?php echo $instance->get_config( 'deeplinking', $instance->get_config_default( 'deeplinking' ) ); ?>" <?php checked( $instance->get_config( 'deeplinking', $instance->get_config_default( 'deeplinking' ) ), 1 ); ?> />
                <span class="description"><?php _e( 'Enables or disables deeplinking capabilities for gallery lightbox images.', 'envira-deeplinking' ); ?></span>
            </td>
        </tr>
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
    function save( $settings, $post_id ) {

        // Settings
        $settings['config']['deeplinking'] = isset( $_POST['_envira_gallery']['deeplinking'] ) ? 1 : 0;

        // Return
        return $settings;
    
    }
    
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Deeplinking_Metaboxes object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Deeplinking_Metaboxes ) ) {
            self::$instance = new Envira_Deeplinking_Metaboxes();
        }

        return self::$instance;

    }

}

// Load the metaboxes class.
$envira_deeplinking_metaboxes = Envira_Deeplinking_Metaboxes::get_instance();
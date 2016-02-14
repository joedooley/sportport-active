<?php
/**
 * Settings class.
 *
 * @since 1.3.1
 *
 * @package Envira_Tags_Settings
 * @author  Tim Carr
 */
class Envira_Tags_Settings {

    /**
     * Holds the class object.
     *
     * @since 1.3.1
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.3.1
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.3.1
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 1.3.1
     */
    public function __construct() {

        // Base and Common Classes
        $this->base = Envira_Tags::get_instance();

        // Tab in Settings
		add_filter( 'envira_gallery_settings_tab_nav', array( $this, 'settings_tabs' )  );
		add_action( 'envira_gallery_tab_settings_tags', array( $this, 'settings_screen' )  );
		add_action( 'init', array( $this, 'settings_save' )  );

    }


    /**
	 * Add a tab to the Envira Gallery Settings screen
	 *
	 * @since 1.3.1
	 *
	 * @param array $tabs Existing tabs
	 * @return array New tabs
	 */
	function settings_tabs( $tabs ) {

		$tabs['tags'] = __( 'Tags', 'envira-tags' );

		return $tabs;

	}

	/**
	 * Callback for displaying the UI for standalone settings tab.
	 *
	 * @since 1.3.1
	 */
	function settings_screen() {

		// Get settings
		$settings = Envira_Tags_Common::get_instance()->get_settings();
	    ?>
	    <div id="envira-settings-tags">
	        <table class="form-table">
	            <tbody>
	            	<form action="edit.php?post_type=envira&amp;page=envira-gallery-settings#!envira-tab-tags" method="post">
            			
            			<tr id="envira-settings-imagga-enabled-box">
		                    <th scope="row">
		                        <label for="envira-tags-imagga-enabled"><?php _e( 'Imagga: Enable Auto Tagging?', 'envira-tags' ); ?></label>
		                    </th>
		                    <td>
	                            <input type="checkbox" name="envira-tags-imagga-enabled" id="envira-tags-imagga-enabled" value="1"<?php checked( $settings['imagga_enabled'], 1 ); ?> />
	                            <p class="description"><?php echo sprintf( __( 'Imagga will read each image you upload to an Envira Gallery, and automatically tag it. <a href="%s" target="_blank">Find out more</a>', 'envira-tags' ) , 'https://imagga.com/' ); ?></p>
		                    </td>
		                </tr>

            			<tr id="envira-settings-imagga-authorization-box">
		                    <th scope="row">
		                        <label for="envira-tags-imagga-authorization-code"><?php _e( 'Imagga: Authorization Code', 'envira-tags' ); ?></label>
		                    </th>
		                    <td>
	                            <input type="text" name="envira-tags-imagga-authorization-code" id="envira-tags-imagga-authorization-code" value="<?php echo $settings['imagga_authorization_code']; ?>" />
	                            <p class="description"><?php echo sprintf( __( '<a href="%s" target="_blank">Sign up for the Imagga API</a>, and make a note of your Authorization code once completed. Enter the code here.', 'envira-tags' ), 'https://imagga.com/auth/signup/hacker' ); ?></p>
		                    </td>
		                </tr>

		                <tr id="envira-settings-imagga-confidence-box">
		                    <th scope="row">
		                        <label for="envira-tags-imagga-confidence"><?php _e( 'Imagga: Minimum Confidence', 'envira-tags' ); ?></label>
		                    </th>
		                    <td>
	                            <input type="number" name="envira-tags-imagga-confidence" id="envira-tags-imagga-confidence" value="<?php echo $settings['imagga_confidence']; ?>" />
	                            <span class="envira-unit">%</span>
	                            <p class="description"><?php _e( 'If specified, only adds tags to images where Imagga matches or exceeds the above confidence percentage rating. A lower confidence means it is more likely less accurate tags will be included in an image.', 'envira-tags' ); ?></p>
		                    </td>
		                </tr>

		                <tr>
		                	<th scope="row">
		                		<?php 
		                		wp_nonce_field( 'envira-tags-nonce', 'envira-tags-nonce' );
		                		submit_button( __( 'Save', 'envira-tag' ), 'primary', 'envira-gallery-verify-submit', false );
		                		?>
		                	</th>
		                	<td>&nbsp;</td>
		                </tr>
	                </form>
	            </tbody>
	        </table>
	    </div>
	    <?php

	}

	/**
	 * Callback for saving the settings
	 *
	 * @since 1.3.1
	 */
	function settings_save() {

		// Check we saved some settings
		if ( ! isset( $_REQUEST ) ) {
			return;
		}

		// Check nonce is valid
		if ( ! isset( $_REQUEST['envira-tags-nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_REQUEST['envira-tags-nonce'], 'envira-tags-nonce' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_nonce' ) );
			return;
		}

		// Get existing settings
		$instance = Envira_Tags_Common::get_instance();
		$settings = $instance->get_settings();

		// Build settings array
		$settings['imagga_enabled'] 			= ( isset( $_POST['envira-tags-imagga-enabled'] ) ? true : false );
		$settings['imagga_authorization_code']  = sanitize_text_field( $_POST['envira-tags-imagga-authorization-code'] );
		
		// Save settings
		$instance->save_settings( $settings );

		// Output success notice
		add_action( 'admin_notices', array( $this, 'notice_success' ) );

	}

	/**
	 * Outputs a message to tell the user that the nonce field is invalid
	 *
	 * @since 1.3.1
	 */
	function notice_nonce() {

		?>
	    <div class="error">
	        <p><?php echo ( __( 'The nonce field is invalid.', 'envira-tags' ) ); ?></p>
	    </div>
	    <?php

	}

	/**
	 * Outputs a message to tell the user that settings are saved
	 *
	 * @since 1.3.1
	 */
	function notice_success() {

		?>
	    <div class="updated">
	        <p><?php echo ( __( 'Tags settings updated successfully!', 'envira-tags' ) ); ?></p>
	    </div>
	    <?php

	}

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.3.1
     *
     * @return object The Envira_Tags_Settings object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Settings ) ) {
            self::$instance = new Envira_Tags_Settings();
        }

        return self::$instance;

    }

}

// Load the metabox class.
$envira_tags_settings = Envira_Tags_Settings::get_instance();
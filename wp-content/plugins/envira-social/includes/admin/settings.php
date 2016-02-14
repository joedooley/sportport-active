<?php
/**
 * Settings class.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @author  Thomas Griffin
 */
class Envira_Social_Settings {

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
     * Holds the common class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $common;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Envira_Gallery::get_instance();
        $this->common = Envira_Social_Common::get_instance();

        // Actions
        add_filter( 'envira_gallery_settings_tab_nav', array( $this, 'tabs' ) );
        add_action( 'envira_gallery_tab_settings_social', array( $this, 'settings' ) );
        add_action( 'init', array( $this, 'save' ) );

    }

    /**
     * Add a tab to the Envira Gallery Settings screen
     *
     * @since 1.0.0
     *
     * @param array $tabs Existing tabs
     * @return array New tabs
     */
    public function tabs( $tabs ) {

        $tabs['social'] = __( 'Social', 'envira-social' );

        return $tabs;

    }

    /**
     * Outputs settings screen for the Social Tab.
     *
     * @since 1.0.0
     */
    function settings() {

        // Get settings
        $facebook_app_id = $this->common->get_setting( 'facebook_app_id' );
        $twitter_username = $this->common->get_setting( 'twitter_username' );
        ?>
        <div id="envira-settings-social">
            <table class="form-table">
                <tbody>
                    <form action="edit.php?post_type=envira&amp;page=envira-gallery-settings#!envira-tab-social" method="post">
                        <tr id="envira-social-facebook-app-id-box">
                            <th scope="row">
                                <label for="envira-social-facebook-app-id"><?php _e( 'Facebook Application ID', 'envira-social' ); ?></label>
                            </th>
                            <td>
                                <input name="envira-social-facebook-app-id" id="envira-social-facebook-app-id" value="<?php echo ( ! $facebook_app_id ? '' : $facebook_app_id ); ?>" />
                                <p class="description">
                                    <strong><?php _e('Required: ', 'envira-social'); ?></strong>
                                    <?php _e( 'Visit https://developers.facebook.com/quickstarts/?platform=web, and register a new app.  The URL must = ' . get_bloginfo( 'url' ) . '.  Refer to our <a href="http://enviragallery.com/docs/social-addon">Documentation</a> for full instructions.', 'envira-social' ); ?>
                                </p>
                            </td>
                        </tr>

                        <tr id="envira-social-twitter-username-box">
                            <th scope="row">
                                <label for="envira-social-twitter-username"><?php _e( 'Twitter Username', 'envira-social' ); ?></label>
                            </th>
                            <td>
                                <input name="envira-social-twitter-username" id="envira-social-twitter-username" value="<?php echo ( ! $twitter_username ? '' : $twitter_username ); ?>" />
                                <p class="description">
                                    <strong><?php _e('Required: ', 'envira-social'); ?></strong>
                                    <?php _e( 'Visit https://cards-dev.twitter.com/validator, and enter ' . get_bloginfo( 'url' ) . ' into the Card validator.  Then click Request Approval. Refer to our <a href="http://enviragallery.com/docs/social-addon">Documentation</a> for full instructions.', 'envira-social' ); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php submit_button( __( 'Save', 'envira-social' ), 'primary', 'envira-gallery-verify-submit', false ); ?></th>
                            <td><?php wp_nonce_field( 'envira-social-nonce', 'envira-social-nonce' ); ?></td>
                        </tr>
                    </form>
                </tbody>
            </table>
        </div>
        <?php

    }

    /**
     * Saves settings if POSTed
     *
     * @since 1.0.0
     *
     */
    public function save() {

        // Check we saved some settings
        if ( ! isset( $_POST ) ) {
            return;
        }

        // Check nonce exists
        if ( ! isset( $_POST['envira-social-nonce'] ) ) {
            return;
        }

        // Check nonce is valid
        if ( ! wp_verify_nonce( $_POST['envira-social-nonce'], 'envira-social-nonce' ) ) {
            add_action( 'admin_notices', array( $this, 'notice_nonce' ) );
            return;
        }

        // Save
        $settings = array(
            'facebook_app_id'   => $_POST['envira-social-facebook-app-id'],
            'twitter_username'  => ( ( strpos ( $_POST['envira-social-twitter-username'], '@' ) === FALSE ) ? '@' . $_POST['envira-social-twitter-username'] : $_POST['envira-social-twitter-username'] ),  
        );
        update_option( 'envira-social', $settings );

        // Show confirmation that settings saved
        add_action( 'admin_notices', array( $this, 'notice_saved' ) );

    }

    /**
     * Outputs a WordPress style notification message to tell the user that the nonce field is invalid
     *
     * @since 1.0.0
     */
    public function notice_nonce() {

        ?>
        <div class="error">
            <p><?php echo ( __( 'The nonce field is invalid.', 'envira-social' ) ); ?></p>
        </div>
        <?php

    }

    /**
     * Outputs a WordPress style notification message to tell the user that the settings have been saved
     *
     * @since 1.0.0
     */
    public function notice_saved() {

        ?>
        <div class="updated">
            <p><?php echo ( __( 'Social settings saved!', 'envira-social' ) ); ?></p>
        </div>
        <?php

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Social_Settings object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Social_Settings ) ) {
            self::$instance = new Envira_Social_Settings();
        }

        return self::$instance;

    }

}

// Load the settings class.
$envira_social_settings = Envira_Social_Settings::get_instance();
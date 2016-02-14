<?php
/**
 * Plugin Name: Envira Gallery - Slideshow Addon
 * Plugin URI:  http://enviragallery.com
 * Description: Enables slideshows for Envira galleries.
 * Author:      Thomas Griffin
 * Author       URI: http://thomasgriffinmedia.com
 * Version:     1.0.6
 * Text Domain: envira-slideshow
 * Domain Path: languages
 *
 * Envira Gallery is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Envira Gallery is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Envira Gallery. If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define necessary addon constants.
define( 'ENVIRA_SLIDESHOW_PLUGIN_NAME', 'Envira Gallery - Slideshow Addon' );
define( 'ENVIRA_SLIDESHOW_PLUGIN_VERSION', '1.0.6' );
define( 'ENVIRA_SLIDESHOW_PLUGIN_SLUG', 'envira-slideshow' );

add_action( 'plugins_loaded', 'envira_slideshow_plugins_loaded' );
/**
 * Ensures the full Envira Gallery plugin is active before proceeding.
 *
 * @since 1.0.0
 *
 * @return null Return early if Envira Gallery is not active.
 */
function envira_slideshow_plugins_loaded() {

    // Bail if the main class does not exist.
    if ( ! class_exists( 'Envira_Gallery' ) ) {
        return;
    }

    // Fire up the addon.
    add_action( 'envira_gallery_init', 'envira_slideshow_plugin_init' );
    
    // Load the plugin textdomain.
    load_plugin_textdomain( ENVIRA_SLIDESHOW_PLUGIN_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

}

/**
 * Loads all of the addon hooks and filters.
 *
 * @since 1.0.0
 */
function envira_slideshow_plugin_init() {

    add_action( 'envira_gallery_updater', 'envira_slideshow_updater' );
    add_filter( 'envira_gallery_defaults', 'envira_slideshow_defaults', 10, 2 );
    
    // Gallery
    add_filter( 'envira_gallery_tab_nav', 'envira_slideshow_tab_nav' );
    add_action( 'envira_gallery_tab_slideshow', 'envira_slideshow_tab_slideshow' );
    add_filter( 'envira_gallery_save_settings', 'envira_slideshow_save', 10, 2 );
    
    // Albums
    add_filter( 'envira_albums_tab_nav', 'envira_slideshow_tab_nav' );
    add_action( 'envira_albums_tab_slideshow', 'envira_slideshow_tab_slideshow' );
    add_filter( 'envira_albums_save_settings', 'envira_album_slideshow_save', 10, 2 );
    
    // Output
    add_action( 'envira_gallery_api_config', 'envira_slideshow_output' );
    add_action( 'envira_albums_api_config', 'envira_album_slideshow_output' );
    
    add_filter( 'envira_gallery_toolbar_after_prev', 'envira_slideshow_toolbar_button', 10, 2 );
    add_filter( 'envira_albums_toolbar_after_prev', 'envira_album_slideshow_toolbar_button', 10, 2 );

}

/**
 * Initializes the addon updater.
 *
 * @since 1.0.0
 *
 * @param string $key The user license key.
 */
function envira_slideshow_updater( $key ) {

    $args = array(
        'plugin_name' => ENVIRA_SLIDESHOW_PLUGIN_NAME,
        'plugin_slug' => ENVIRA_SLIDESHOW_PLUGIN_SLUG,
        'plugin_path' => plugin_basename( __FILE__ ),
        'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . ENVIRA_SLIDESHOW_PLUGIN_SLUG,
        'remote_url'  => 'http://enviragallery.com/',
        'version'     => ENVIRA_SLIDESHOW_PLUGIN_VERSION,
        'key'         => $key
    );
    
    $updater = new Envira_Gallery_Updater( $args );

}

/**
 * Applies a default to the addon setting.
 *
 * @since 1.0.0
 *
 * @param array $defaults  Array of default config values.
 * @param int $post_id     The current post ID.
 * @return array $defaults Amended array of default config values.
 */
function envira_slideshow_defaults( $defaults, $post_id ) {

    $defaults['slideshow'] = 0;
    $defaults['autoplay']  = 0;
    $defaults['ss_speed']  = 5000;
    return $defaults;

}

/**
 * Filters in a new tab for the addon.
 *
 * @since 1.0.0
 *
 * @param array $tabs  Array of default tab values.
 * @return array $tabs Amended array of default tab values.
 */
function envira_slideshow_tab_nav( $tabs ) {

    $tabs['slideshow'] = __( 'Slideshow', 'envira-slideshow' );
    return $tabs;

}

/**
 * Callback for displaying the UI for setting gallery slideshow options.
 *
 * @since 1.0.0
 *
 * @param object $post The current post object.
 */
function envira_slideshow_tab_slideshow( $post ) {

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
    <div id="envira-slideshow">
        <p class="envira-intro"><?php _e( 'The settings below adjust the slideshow settings for the gallery.', 'envira-slideshow' ); ?></p>
        <table class="form-table">
            <tbody>
                <tr id="envira-config-slideshow-box">
                    <th scope="row">
                        <label for="envira-config-slideshow"><?php _e( 'Enable Gallery Slideshow?', 'envira-slideshow' ); ?></label>
                    </th>
                    <td>
                        <input id="envira-config-slideshow" type="checkbox" name="<?php echo $key; ?>[slideshow]" value="<?php echo $instance->get_config( 'slideshow', $instance->get_config_default( 'slideshow' ) ); ?>" <?php checked( $instance->get_config( 'slideshow', $instance->get_config_default( 'slideshow' ) ), 1 ); ?> />
                        <span class="description"><?php _e( 'Enables or disables the gallery lightbox slideshow.', 'envira-slideshow' ); ?></span>
                    </td>
                </tr>
                <tr id="envira-config-slideshow-autoplay-box">
                    <th scope="row">
                        <label for="envira-config-slideshow-autoplay"><?php _e( 'Autoplay the Slideshow?', 'envira-slideshow' ); ?></label>
                    </th>
                    <td>
                        <input id="envira-config-slideshow-autoplay" type="checkbox" name="<?php echo $key; ?>[autoplay]" value="<?php echo $instance->get_config( 'autoplay', $instance->get_config_default( 'autoplay' ) ); ?>" <?php checked( $instance->get_config( 'autoplay', $instance->get_config_default( 'autoplay' ) ), 1 ); ?> />
                        <span class="description"><?php _e( 'Enables or disables autoplaying the slideshow on lightbox open.', 'envira-slideshow' ); ?></span>
                    </td>
                </tr>
                <tr id="envira-config-slideshow-speed-box">
                    <th scope="row">
                        <label for="envira-config-slideshow-speed"><?php _e( 'Slideshow Speed', 'envira-slideshow' ); ?></label>
                    </th>
                    <td>
                        <input id="envira-config-slideshow-speed" type="number" name="<?php echo $key; ?>[ss_speed]" value="<?php echo $instance->get_config( 'ss_speed', $instance->get_config_default( 'ss_speed' ) ); ?>" />
                        <p class="description"><?php _e( 'Sets the speed of the gallery lightbox slideshow.', 'envira-slideshow' ); ?></p>
                    </td>
                </tr>
                <?php do_action( 'envira_gallery_slideshow_box', $post ); ?>
            </tbody>
        </table>
    </div>
    <?php

}

/**
 * Saves the addon setting.
 *
 * @since 1.0.0
 *
 * @param array $settings  Array of settings to be saved.
 * @param int $post_id     The current post ID.
 * @return array $settings Amended array of settings to be saved.
 */
function envira_slideshow_save( $settings, $post_id ) {

    $settings['config']['slideshow'] = isset( $_POST['_envira_gallery']['slideshow'] ) ? 1 : 0;
    $settings['config']['autoplay']  = isset( $_POST['_envira_gallery']['autoplay'] ) ? 1 : 0;
    $settings['config']['ss_speed']  = absint( $_POST['_envira_gallery']['ss_speed'] );
    return $settings;

}

/**
 * Saves the addon setting for Albms
 *
 * @since 1.0.0
 *
 * @param array $settings  Array of settings to be saved.
 * @param int $post_id     The current post ID.
 * @return array $settings Amended array of settings to be saved.
 */
function envira_album_slideshow_save( $settings, $post_id ) {

    $settings['config']['slideshow'] = isset( $_POST['_eg_album_data']['config']['slideshow'] ) ? 1 : 0;
    $settings['config']['autoplay']  = isset( $_POST['_eg_album_data']['config']['autoplay'] ) ? 1 : 0;
    $settings['config']['ss_speed']  = absint( $_POST['_eg_album_data']['config']['ss_speed'] );
    return $settings;

}

/**
 * Outputs the slideshow settings for a gallery.
 *
 * @since 1.0.0
 *
 * @param array $data Data for the Envira gallery.
 * @return null       Return early if the slideshow is not enabled.
 */
function envira_slideshow_output( $data ) {

    if ( ! Envira_Gallery_Shortcode::get_instance()->get_config( 'slideshow', $data ) ) {
        return;
    }

    // Output the slideshow init code.
    echo 'autoPlay:' . Envira_Gallery_Shortcode::get_instance()->get_config( 'autoplay', $data ) . ',';
    echo 'playSpeed:' . Envira_Gallery_Shortcode::get_instance()->get_config( 'ss_speed', $data ) . ',';

}

/**
 * Outputs the slideshow settings for an album.
 *
 * @since 1.0.0
 *
 * @param array $data Data for the Envira Album.
 * @return null       Return early if the slideshow is not enabled.
 */
function envira_album_slideshow_output( $data ) {

	$instance = Envira_Albums_Shortcode::get_instance();

    if ( ! $instance->get_config( 'slideshow', $data ) ) {
        return;
    }

    // Output the slideshow init code.
    echo 'autoPlay:' . $instance->get_config( 'autoplay', $data ) . ',';
    echo 'playSpeed:' . $instance->get_config( 'ss_speed', $data ) . ',';

}

/**
 * Outputs the slideshow button in the gallery toolbar.
 *
 * @since 1.0.0
 *
 * @param string $template  The template HTML for the gallery toolbar.
 * @param array $data       Data for the Envira gallery.
 * @return string $template Amended template HTML for the gallery toolbar.
 */
function envira_slideshow_toolbar_button( $template, $data ) {

    if ( ! Envira_Gallery_Shortcode::get_instance()->get_config( 'slideshow', $data ) ) {
        return $template;
    }

    // Create the slideshow button.
    $button = '<li><a class="btnPlay" title="' . __( 'Start Slideshow', 'envira-slideshow' ) . '" href="javascript:;"></a></li>';

    // Return with the button appended to the template.
    return $template . $button;

}

/**
 * Outputs the slideshow button in the album toolbar.
 *
 * @since 1.0.4
 *
 * @param string $template  The template HTML for the album toolbar.
 * @param array $data       Data for the Envira album.
 * @return string $template Amended template HTML for the album toolbar.
 */
function envira_album_slideshow_toolbar_button( $template, $data ) {

    if ( ! Envira_Albums_Shortcode::get_instance()->get_config( 'slideshow', $data ) ) {
        return $template;
    }

    // Create the slideshow button.
    $button = '<li><a class="btnPlay" title="' . __( 'Start Slideshow', 'envira-slideshow' ) . '" href="javascript:;"></a></li>';

    // Return with the button appended to the template.
    return $template . $button;

}
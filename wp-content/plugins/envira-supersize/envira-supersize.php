<?php
/**
 * Plugin Name: Envira Gallery - Supersize Addon
 * Plugin URI:  http://enviragallery.com
 * Description: Enables a custom "supersize" lightbox view for Envira galleries.
 * Author:      Thomas Griffin
 * Author URI:  http://thomasgriffinmedia.com
 * Version:     1.1.1
 * Text Domain: envira-supersize
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
define( 'ENVIRA_SUPERSIZE_PLUGIN_NAME', 'Envira Gallery - Supersize Addon' );
define( 'ENVIRA_SUPERSIZE_PLUGIN_VERSION', '1.1.1' );
define( 'ENVIRA_SUPERSIZE_PLUGIN_SLUG', 'envira-supersize' );

add_action( 'plugins_loaded', 'envira_supersize_plugins_loaded' );
/**
 * Ensures the full Envira Gallery plugin is active before proceeding.
 *
 * @since 1.0.0
 *
 * @return null Return early if Envira Gallery is not active.
 */
function envira_supersize_plugins_loaded() {

    // Bail if the main class does not exist.
    if ( ! class_exists( 'Envira_Gallery' ) ) {
        return;
    }

    // Display a notice if Envira does not meet the proper version to run the addon.
    if ( version_compare( Envira_Gallery::get_instance()->version, '1.0.4.2', '<' ) ) {
        add_action( 'admin_notices', 'envira_supersize_version_notice' );
        return;
    };

    // Fire up the addon.
    add_action( 'envira_gallery_init', 'envira_supersize_plugin_init' );
    
    // Load the plugin textdomain.
    load_plugin_textdomain( ENVIRA_SUPERSIZE_PLUGIN_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

}

/**
 * Outputs a required version notice for the addon to work with Envira.
 *
 * @since 1.0.0
 */
function envira_supersize_version_notice() {

    ?>
    <div class="error">
        <p><?php printf( __( 'The <strong>%s</strong> requires Envira Gallery 1.0.4.2 or later to work. Please update Envira Gallery to use this addon.', 'envira-supersize' ), ENVIRA_SUPERSIZE_PLUGIN_NAME ); ?></p>
    </div>
    <?php

}

/**
 * Loads all of the addon hooks and filters.
 *
 * @since 1.0.0
 */
function envira_supersize_plugin_init() {

    // Updater and Defaults
    add_action( 'envira_gallery_updater', 'envira_supersize_updater' );
    add_filter( 'envira_gallery_defaults', 'envira_supersize_defaults', 10, 2 );
    
    // Galleries
    add_action( 'envira_gallery_lightbox_box', 'envira_gallery_supersize_setting' );
    add_filter( 'envira_gallery_save_settings', 'envira_gallery_supersize_save', 10, 2 );
    add_filter( 'envira_gallery_output', 'envira_supersize_css', 10, 2 );
    add_action( 'envira_gallery_api_config_callback', 'envira_supersize_init' );
    add_action( 'envira_gallery_api_after_load', 'envira_supersize_load' );

	// Albums
	add_action( 'envira_albums_lightbox_box', 'envira_albums_supersize_setting' );
    add_filter( 'envira_albums_save_settings', 'envira_albums_supersize_save', 10, 2 );
    add_filter( 'envira_albums_output', 'envira_supersize_css', 10, 2 );
    add_action( 'envira_albums_api_config_callback', 'envira_supersize_init' );
    add_action( 'envira_albums_api_after_load', 'envira_supersize_load' );

}

/**
 * Initializes the addon updater.
 *
 * @since 1.0.0
 *
 * @param string $key The user license key.
 */
function envira_supersize_updater( $key ) {

    $args = array(
        'plugin_name' => ENVIRA_SUPERSIZE_PLUGIN_NAME,
        'plugin_slug' => ENVIRA_SUPERSIZE_PLUGIN_SLUG,
        'plugin_path' => plugin_basename( __FILE__ ),
        'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . ENVIRA_SUPERSIZE_PLUGIN_SLUG,
        'remote_url'  => 'http://enviragallery.com/',
        'version'     => ENVIRA_SUPERSIZE_PLUGIN_VERSION,
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
function envira_supersize_defaults( $defaults, $post_id ) {

    // Disabled by default.
    $defaults['supersize'] = 0;
    return $defaults;

}

/**
 * Adds addon setting to the Config tab for Galleries
 *
 * @since 1.0.0
 *
 * @param object $post The current post object.
 */
function envira_gallery_supersize_setting( $post ) {

    $instance = Envira_Gallery_Metaboxes::get_instance();
    ?>
    <tr id="envira-config-supersize-box">
        <th scope="row">
            <label for="envira-config-supersize"><?php _e( 'Enable Lightbox Supersize?', 'envira-supersize' ); ?></label>
        </th>
        <td>
            <input id="envira-config-supersize" type="checkbox" name="_envira_gallery[supersize]" value="<?php echo $instance->get_config( 'supersize', $instance->get_config_default( 'supersize' ) ); ?>" <?php checked( $instance->get_config( 'supersize', $instance->get_config_default( 'supersize' ) ), 1 ); ?> />
            <span class="description"><?php _e( 'Enables or disables supersize mode for gallery lightbox images.', 'envira-supersize' ); ?></span>
        </td>
    </tr>
    <?php

}

/**
 * Adds addon setting to the Config tab for Albums
 *
 * @since 1.0.0
 *
 * @param object $post The current post object.
 */
function envira_albums_supersize_setting( $post ) {

    $instance = Envira_Albums_Metaboxes::get_instance();
    ?>
    <tr id="envira-config-supersize-box">
        <th scope="row">
            <label for="envira-config-supersize"><?php _e( 'Enable Lightbox Supersize?', 'envira-supersize' ); ?></label>
        </th>
        <td>
            <input id="envira-config-supersize" type="checkbox" name="_eg_album_data[config][supersize]" value="<?php echo $instance->get_config( 'supersize', $instance->get_config_default( 'supersize' ) ); ?>" <?php checked( $instance->get_config( 'supersize', $instance->get_config_default( 'supersize' ) ), 1 ); ?> />
            <span class="description"><?php _e( 'Enables or disables supersize mode for gallery lightbox images.', 'envira-supersize' ); ?></span>
        </td>
    </tr>
    <?php

}

/**
 * Saves the addon setting for Galleries
 *
 * @since 1.0.0
 *
 * @param array $settings  Array of settings to be saved.
 * @param int $post_id     The current post ID.
 * @return array $settings Amended array of settings to be saved.
 */
function envira_gallery_supersize_save( $settings, $post_id ) {
	
	$settings['config']['supersize'] = isset( $_POST['_envira_gallery']['supersize'] ) ? 1 : 0;
    return $settings;

}

/**
 * Saves the addon setting for Albums
 *
 * @since 1.0.0
 *
 * @param array $settings  Array of settings to be saved.
 * @param int $post_id     The current post ID.
 * @return array $settings Amended array of settings to be saved.
 */
function envira_albums_supersize_save( $settings, $post_id ) {
	
	$settings['config']['supersize'] = isset( $_POST['_eg_album_data']['config']['supersize'] ) ? 1 : 0;
    return $settings;

}

/**
 * Adds some CSS styles to help with Supersize mode.
 *
 * @since 1.0.0
 *
 * @param string $html  HTML output of the gallery or album.
 * @param array $data   Array of gallery data.
 * @return string  		Amended HTML output of the gallery or album.
 */
function envira_supersize_css( $html, $data ) {

	// Check whether request is for a Gallery or Album
	$type = get_post_type( $data['id'] );
	
	// Check if Supersize is enabled
    switch ( $type ) {
	    case 'envira_album':
	    	// Album
	    	if ( ! Envira_Albums_Shortcode::get_instance()->get_config( 'supersize', $data ) ) {
		        return $html;
		    }
	    	break;
	    default:
	    	// Gallery
	    	if ( ! Envira_Gallery_Shortcode::get_instance()->get_config( 'supersize', $data ) ) {
		        return $html;
		    }
	    	break;
    }
    
	// If here, Supersize is enabled on the Gallery or Album
    // Enqueue CSS
    wp_register_style( ENVIRA_SUPERSIZE_PLUGIN_SLUG . '-style', plugins_url( 'assets/css/envira-supersize.css', plugin_basename( __FILE__ ) ), array(), ENVIRA_SUPERSIZE_PLUGIN_VERSION );
	wp_enqueue_style( ENVIRA_SUPERSIZE_PLUGIN_SLUG . '-style' );

    return $html;

}

/**
 * Initializes lightbox supersizing.
 *
 * @since 1.0.0
 *
 * @param array $data Data for the Envira gallery.
 * @return null       Return early if supersize is not enabled.
 */
function envira_supersize_init( $data ) {

    if ( ! Envira_Gallery_Shortcode::get_instance()->get_config( 'supersize', $data ) ) {
        return;
    }

    // Initialize supersize mode.
    ob_start();
    ?>
    margin: 0,
    padding: 0,
    autoCenter: true,
    tpl: {
        wrap: '<div class="envirabox-wrap envira-supersize" tabIndex="-1"><div class="envirabox-skin"><div class="envirabox-outer"><div class="envirabox-inner"></div></div></div></div>'
    },
    <?php
    do_action( 'envira_supersize_init', $data );
    echo ob_get_clean();

}

/**
 * Loads the supersize view in the lightbox.
 *
 * @since 1.0.0
 *
 * @param array $data Data for the Envira gallery.
 * @return null       Return early if supersize is not enabled.
 */
function envira_supersize_load( $data ) {

    if ( ! Envira_Gallery_Shortcode::get_instance()->get_config( 'supersize', $data ) ) {
        return;
    }

    ob_start();
    ?>
    $.extend(this, {
        width       : '100%',
        height      : '100%'
    });
    <?php
    echo ob_get_clean();

}
<?php
/**
 * Plugin Name: Envira Gallery - CSS Addon
 * Plugin URI:  http://enviragallery.com
 * Description: Enables custom CSS output for Envira galleries.
 * Author:      Thomas Griffin
 * Author URI:  http://thomasgriffinmedia.com
 * Version:     1.0.7
 * Text Domain: envira-css
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
define( 'ENVIRA_CUSTOM_CSS_PLUGIN_NAME', 'Envira Gallery - CSS Addon' );
define( 'ENVIRA_CUSTOM_CSS_PLUGIN_VERSION', '1.0.7' );
define( 'ENVIRA_CUSTOM_CSS_PLUGIN_SLUG', 'envira-css' );

add_action( 'plugins_loaded', 'envira_custom_css_plugins_loaded' );
/**
 * Ensures the full Envira Gallery plugin is active before proceeding.
 *
 * @since 1.0.0
 *
 * @return null Return early if Envira Gallery is not active.
 */
function envira_custom_css_plugins_loaded() {

    // Bail if the main class does not exist.
    if ( ! class_exists( 'Envira_Gallery' ) ) {
        return;
    }

    // Fire up the addon.
    add_action( 'envira_gallery_init', 'envira_custom_css_plugin_init' );
    
    // Load the plugin textdomain.
    load_plugin_textdomain( ENVIRA_CUSTOM_CSS_PLUGIN_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

}

/**
 * Loads all of the addon hooks and filters.
 *
 * @since 1.0.0
 */
function envira_custom_css_plugin_init() {

    add_action( 'envira_gallery_updater', 'envira_custom_css_updater' );
    add_filter( 'envira_gallery_defaults', 'envira_custom_css_defaults', 10, 2 );
    add_action( 'envira_gallery_misc_box', 'envira_custom_css_setting', 999 );
    add_filter( 'envira_gallery_save_settings', 'envira_custom_css_save', 10, 2 );
    add_filter( 'envira_gallery_output_start', 'envira_custom_css_output', 0, 2 );
    
    // Album Support
    add_action( 'envira_albums_misc_box', 'envira_custom_css_setting_album', 999 );
    add_filter( 'envira_albums_save_settings', 'envira_custom_css_save_album', 10, 2 );
	add_filter( 'envira_gallery_output_start', 'envira_custom_css_output_album', 0, 2 );
}

/**
 * Initializes the addon updater.
 *
 * @since 1.0.0
 *
 * @param string $key The user license key.
 */
function envira_custom_css_updater( $key ) {

    $args = array(
        'plugin_name' => ENVIRA_CUSTOM_CSS_PLUGIN_NAME,
        'plugin_slug' => ENVIRA_CUSTOM_CSS_PLUGIN_SLUG,
        'plugin_path' => plugin_basename( __FILE__ ),
        'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . ENVIRA_CUSTOM_CSS_PLUGIN_SLUG,
        'remote_url'  => 'http://enviragallery.com/',
        'version'     => ENVIRA_CUSTOM_CSS_PLUGIN_VERSION,
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
function envira_custom_css_defaults( $defaults, $post_id ) {

    // Disabled by default.
    $defaults['custom_css'] = '';
    return $defaults;

}

/**
 * Adds addon setting to the Misc tab for Galleries
 *
 * @since 1.0.0
 *
 * @param object $post The current post object.
 */
function envira_custom_css_setting( $post ) {

    $instance = Envira_Gallery_Metaboxes::get_instance();
    ?>
    <tr id="envira-config-custom-css-box">
        <th scope="row">
            <label for="envira-config-custom-css"><?php _e( 'Custom Gallery CSS', 'envira-css' ); ?></label>
        </th>
        <td>
            <textarea id="envira-config-custom-css" rows="10" cols="75" name="_envira_gallery[custom_css]" placeholder="<?php printf( __( 'e.g. %s', 'envira-css' ), '#envira-gallery-' . $post->ID . ' { margin-bottom: 20px; }' ); ?>"><?php echo $instance->get_config( 'custom_css', $instance->get_config_default( 'custom_css' ) ); ?></textarea>
            <p class="description"><?php printf( __( 'All custom CSS for this gallery should start with <code>%s</code>. <a href="%s" title="Need help?" target="_blank">Help?</a>', 'envira-css' ), '#envira-gallery-' . $post->ID, 'http://enviragallery.com/docs/css-addon/' ); ?></p>
        </td>
    </tr>
    <?php

}

/**
 * Adds addon setting to the Misc tab for Albums
 *
 * @since 1.0.0
 *
 * @param object $post The current post object.
 */
function envira_custom_css_setting_album( $post ) {

	$data = get_post_meta( $post->ID, '_eg_album_data', true );
	?>
    <tr id="envira-config-custom-css-box">
        <th scope="row">
            <label for="envira-config-custom-css"><?php _e( 'Custom Album CSS', 'envira-css' ); ?></label>
        </th>
        <td>
            <textarea id="envira-config-custom-css" rows="10" cols="75" name="_eg_album_data[config][custom_css]" placeholder="<?php printf( __( 'e.g. %s', 'envira-css' ), '#envira-gallery-' . $post->ID . ' { margin-bottom: 20px; }' ); ?>"><?php echo ( isset( $data['config']['custom_css'] ) ? $data['config']['custom_css'] : '' ); ?></textarea>
            <p class="description"><?php printf( __( 'All custom CSS for this gallery should start with <code>%s</code>. <a href="%s" title="Need help?" target="_blank">Help?</a>', 'envira-css' ), '#envira-gallery-' . $post->ID, 'http://enviragallery.com/docs/css-addon/' ); ?></p>
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
 * @param int $postid      The current post ID.
 * @return array $settings Amended array of settings to be saved.
 */
function envira_custom_css_save( $settings, $post_id ) {

    $settings['config']['custom_css'] = isset( $_POST['_envira_gallery']['custom_css'] ) ? trim( esc_html( $_POST['_envira_gallery']['custom_css'] ) ) : '';
    return $settings;

}

/**
 * Saves the addon setting for Albums
 *
 * @since 1.0.0
 *
 * @param array $settings  Array of settings to be saved.
 * @param int $postid      The current post ID.
 * @return array $settings Amended array of settings to be saved.
 */
function envira_custom_css_save_album( $settings, $post_id ) {
	
	$settings['config']['custom_css'] = isset( $_POST['_eg_album_data']['config']['custom_css'] ) ? trim( esc_html( $_POST['_eg_album_data']['config']['custom_css'] ) ) : '';
    return $settings;

}

/**
 * Outputs the custom CSS to the specific gallery.
 *
 * @since 1.0.0
 *
 * @param string $gallery  The HTML output for the gallery.
 * @param array $data      Data for the Envira gallery.
 * @return string $gallery Amended gallery HTML.
 */
function envira_custom_css_output( $gallery, $data ) {

    // If there is no style, return the default gallery HTML.
    $instance = Envira_Gallery_Shortcode::get_instance();
    if ( ! $instance->get_config( 'custom_css', $data ) ) {
        return $gallery;
    }

    // Minify the CSS.
    $minify = preg_replace( '/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', '', $data['config']['custom_css'] );
    $minify = str_replace( array( "\r\n","\r","\t","\n",'  ','    ','     ' ), '', $data['config']['custom_css'] );

    // Build out the custom CSS.
    $style = '<style type="text/css">' . html_entity_decode( $minify, ENT_QUOTES ) . '</style>';

    // Return the style prepended to the gallery.
    return $style . $gallery;

}

/**
 * Outputs the custom CSS to the specific album.
 *
 * @since 1.0.0
 *
 * @param string $gallery  The HTML output for the album.
 * @param array $data      Data for the Envira album.
 * @return string $gallery Amended gallery HTML.
 */
function envira_custom_css_output_album( $gallery, $data ) {
	
    // If there is no style, return the default gallery HTML.
    $instance = Envira_Gallery_Shortcode::get_instance();
    if ( ! $instance->get_config( 'custom_css', $data ) ) {
        return $gallery;
    }

    // Minify the CSS.
    $minify = preg_replace( '/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', '', $data['config']['custom_css'] );
    $minify = str_replace( array( "\r\n","\r","\t","\n",'  ','    ','     ' ), '', $data['config']['custom_css'] );

    // Build out the custom CSS.
    $style = '<style type="text/css">' . html_entity_decode( $minify, ENT_QUOTES ) . '</style>';

    // Return the style prepended to the gallery.
    return $style . $gallery;

}
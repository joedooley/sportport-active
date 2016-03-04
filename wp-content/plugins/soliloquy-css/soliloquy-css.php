<?php
/**
 * Plugin Name: Soliloquy - CSS Addon
 * Plugin URI:  http://soliloquywp.com
 * Description: Enables custom CSS output for Soliloquy sliders.
 * Author:      Thomas Griffin
 * Author URI:  http://thomasgriffinmedia.com
 * Version:     2.1.3
 * Text Domain: soliloquy-css
 * Domain Path: languages
 *
 * Soliloquy is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Soliloquy is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Soliloquy. If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define necessary addon constants.
define( 'SOLILOQUY_CUSTOM_CSS_PLUGIN_NAME', 'Soliloquy - CSS Addon' );
define( 'SOLILOQUY_CUSTOM_CSS_PLUGIN_VERSION', '2.1.3' );
define( 'SOLILOQUY_CUSTOM_CSS_PLUGIN_SLUG', 'soliloquy-css' );

add_action( 'plugins_loaded', 'soliloquy_custom_css_plugins_loaded' );
/**
 * Ensures the full Soliloquy plugin is active before proceeding.
 *
 * @since 1.0.0
 *
 * @return null Return early if Soliloquy is not active.
 */
function soliloquy_custom_css_plugins_loaded() {

    // Bail if the main class does not exist.
    if ( ! class_exists( 'Soliloquy' ) ) {
        return;
    }

    // Fire up the addon.
    add_action( 'soliloquy_init', 'soliloquy_custom_css_plugin_init' );
    
    // Loads the plugin textdomain for translation
    load_plugin_textdomain( SOLILOQUY_CUSTOM_CSS_PLUGIN_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

}

/**
 * Loads all of the addon hooks and filters.
 *
 * @since 1.0.0
 */
function soliloquy_custom_css_plugin_init() {

    add_action( 'soliloquy_updater', 'soliloquy_custom_css_updater' );
    add_filter( 'soliloquy_defaults', 'soliloquy_custom_css_defaults', 10, 2 );
    add_action( 'soliloquy_misc_box', 'soliloquy_custom_css_setting', 999 );
    add_filter( 'soliloquy_save_settings', 'soliloquy_custom_css_save', 10, 2 );

    // Metabox - Single Slide
    add_filter( 'soliloquy_meta_defaults', 'soliloquy_custom_css_meta_defaults', 10, 3 );
    add_action( 'soliloquy_after_image_meta_settings', 'soliloquy_custom_css_meta', 10, 3 );
    add_action( 'soliloquy_after_video_meta_settings', 'soliloquy_custom_css_meta', 10, 3 );
    add_filter( 'soliloquy_ajax_save_meta', 'soliloquy_custom_css_save_meta', 10, 4 );

    // Frontend
    add_filter( 'soliloquy_output_start', 'soliloquy_custom_css_output', 0, 2 );
    add_filter( 'soliloquy_output_item_classes', 'soliloquy_custom_css_classes_output', 10, 4 );


}

/**
 * Initializes the addon updater.
 *
 * @since 1.0.0
 *
 * @param string $key The user license key.
 */
function soliloquy_custom_css_updater( $key ) {

    $args = array(
        'plugin_name' => SOLILOQUY_CUSTOM_CSS_PLUGIN_NAME,
        'plugin_slug' => SOLILOQUY_CUSTOM_CSS_PLUGIN_SLUG,
        'plugin_path' => plugin_basename( __FILE__ ),
        'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . SOLILOQUY_CUSTOM_CSS_PLUGIN_SLUG,
        'remote_url'  => 'http://soliloquywp.com/',
        'version'     => SOLILOQUY_CUSTOM_CSS_PLUGIN_VERSION,
        'key'         => $key
    );
    $soliloquy_custom_css_updater = new Soliloquy_Updater( $args );

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
function soliloquy_custom_css_defaults( $defaults, $post_id ) {

    // Empty by default.
    $defaults['custom_css'] = '';
    return $defaults;

}

/**
 * Adds addon setting to the Misc tab.
 *
 * @since 1.0.0
 *
 * @param object $post The current post object.
 */
function soliloquy_custom_css_setting( $post ) {

    $instance = Soliloquy_Metaboxes::get_instance();
    ?>
    <tr id="soliloquy-config-custom-css-box">
        <th scope="row">
            <label for="soliloquy-config-custom-css"><?php _e( 'Custom Slider CSS', 'soliloquy-css' ); ?></label>
        </th>
        <td>
            <textarea id="soliloquy-config-custom-css" rows="10" cols="75" name="_soliloquy[custom_css]" placeholder="<?php printf( __( 'e.g. %s', 'soliloquy-css' ), '#soliloquy-container-' . $post->ID . ' { margin-bottom: 20px; }' ); ?>"><?php echo $instance->get_config( 'custom_css', $instance->get_config_default( 'custom_css' ) ); ?></textarea>
            <p class="description"><?php printf( __( 'All custom CSS for this slider should start with <code>%s</code>. <a href="%s" title="Need help?" target="_blank">Help?</a>', 'soliloquy-css' ), '#soliloquy-container-' . $post->ID, 'http://soliloquywp.com/docs/css-addon/' ); ?></p>
        </td>
    </tr>
    <?php

}

/**
 * Saves the addon setting.
 *
 * @since 1.0.0
 *
 * @param array $settings  Array of settings to be saved.
 * @param int $pos_tid     The current post ID.
 * @return array $settings Amended array of settings to be saved.
 */
function soliloquy_custom_css_save( $settings, $post_id ) {

    $settings['config']['custom_css'] = isset( $_POST['_soliloquy']['custom_css'] ) ? trim( esc_html( $_POST['_soliloquy']['custom_css'] ) ) : '';
    return $settings;

}

/**
 * Applies a default to the addon meta settings.
 *
 * @since 2.1.2
 *
 * @param array $defaults  Array of default config values.
 * @param int $post_id     The current post ID.
 * @param int $attach_id   The current attachment ID.
 * @return array $defaults Amended array of default config values.
 */
function soliloquy_custom_css_meta_defaults( $defaults, $post_id, $attach_id ) {

    $defaults['custom_css_classes']= '';
    return $defaults;

}

/**
 * Outputs the custom css classes meta fields.
 *
 * @since 2.1.2
 *
 * @param int $attach_id The current attachment ID.
 * @param array $data    Array of attachment data.
 * @param int $post_id   The current post ID.
 */
function soliloquy_custom_css_meta( $attach_id, $data, $post_id ) {

    $instance = Soliloquy_Metaboxes::get_instance();
    ?>
    <label class="setting">
        <span class="name"><?php _e( 'CSS Classes', 'soliloquy-schedule' ); ?></span>
        <input id="soliloquy-custom-css-classes-<?php echo $attach_id; ?>" class="soliloquy-custom-css-classes" type="text" name="_soliloquy[custom_css_classes]" data-soliloquy-meta="custom_css_classes" value="<?php echo $instance->get_meta( 'custom_css_classes', $attach_id, $instance->get_meta_default( 'custom_css_classes', $attach_id ) ); ?>" />    
    </label>
    <?php

}

/**
 * Saves the addon meta settings.
 *
 * @since 1.0.0
 *
 * @param array $settings  Array of settings to be saved.
 * @param array $meta      Array of slide meta to use for saving.
 * @param int $attach_id   The current attachment ID.
 * @param int $post_id     The current post ID.
 * @return array $settings Amended array of settings to be saved.
 */
function soliloquy_custom_css_save_meta( $settings, $meta, $attach_id, $post_id ) {

    $settings['slider'][ $attach_id ]['custom_css_classes']       = isset( $meta['custom_css_classes'] ) ? $meta['custom_css_classes'] : '';
    return $settings;

}

/**
 * Outputs the custom CSS to the specific slider.
 *
 * @since 1.0.0
 *
 * @param string $slider  The HTML output for the slider.
 * @param array $data     Data for the slider.
 * @return string $slider Amended slider HTML.
 */
function soliloquy_custom_css_output( $slider, $data ) {

    // If there is no style, return the default slider HTML.
    $instance = Soliloquy_Shortcode::get_instance();
    if ( ! $instance->get_config( 'custom_css', $data ) ) {
    	return $slider;
    }

    // Build out the custom CSS.
    $style = '<style type="text/css">' . $instance->minify( html_entity_decode( $data['config']['custom_css'] ) ) . '</style>';

    // Return the style prepended to the slider.
    return $style . $slider;

}

/**
 * Outputs the custom css classes.
 *
 * @since 2.1.2
 *
 * @param array $classes CSS Classes
 * @param array $item Slider Item (Image or Video)
 * @param int $i Slider Index
 * @param array $data Slider Data
 * @return array CSS Classes
 */
function soliloquy_custom_css_classes_output( $classes, $item, $i, $data ) {

    if ( isset( $item['custom_css_classes'] ) && ! empty( $item['custom_css_classes'] ) ) {
        // Explode into array
        $css_classes = explode( ' ', trim( $item['custom_css_classes'] ) );
        $classes = array_merge( $classes, $css_classes );
    }

    return $classes;

}
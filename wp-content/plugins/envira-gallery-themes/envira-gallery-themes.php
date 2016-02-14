<?php
/**
 * Plugin Name: Envira Gallery - Gallery Themes Addon
 * Plugin URI:  http://enviragallery.com
 * Description: Enables custom themes for the grid display of Envira galleries.
 * Author:      Thomas Griffin
 * Author URI:  http://thomasgriffinmedia.com
 * Version:     1.0.7
 * Text Domain: envira-gallery-themes
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
define( 'ENVIRA_GALLERY_THEMES_PLUGIN_NAME', 'Envira Gallery - Gallery Themes Addon' );
define( 'ENVIRA_GALLERY_THEMES_PLUGIN_VERSION', '1.0.7' );
define( 'ENVIRA_GALLERY_THEMES_PLUGIN_SLUG', 'envira-gallery-themes' );

add_action( 'plugins_loaded', 'envira_gallery_themes_plugins_loaded' );
/**
 * Ensures the full Envira Gallery plugin is active before proceeding.
 *
 * @since 1.0.0
 *
 * @return null Return early if Envira Gallery is not active.
 */
function envira_gallery_themes_plugins_loaded() {

    // Bail if the main class does not exist.
    if ( ! class_exists( 'Envira_Gallery' ) ) {
        return;
    }

    // Fire up the addon.
    add_action( 'envira_gallery_init', 'envira_gallery_themes_plugin_init' );

    // Load the plugin textdomain.
    load_plugin_textdomain( ENVIRA_GALLERY_THEMES_PLUGIN_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

}

/**
 * Loads all of the addon hooks and filters.
 *
 * @since 1.0.0
 */
function envira_gallery_themes_plugin_init() {

    add_action( 'envira_gallery_updater', 'envira_gallery_themes_updater' );
    add_filter( 'envira_gallery_gallery_themes', 'envira_gallery_themes_gallery_themes' );
    add_filter( 'envira_gallery_lightbox_themes', 'envira_gallery_themes_lightbox_themes' );
    add_filter( 'envira_gallery_output_after_link', 'envira_gallery_themes_output', 10, 5 );

}

/**
 * Initializes the addon updater.
 *
 * @since 1.0.0
 *
 * @param string $key The user license key.
 */
function envira_gallery_themes_updater( $key ) {

    $args = array(
        'plugin_name' => ENVIRA_GALLERY_THEMES_PLUGIN_NAME,
        'plugin_slug' => ENVIRA_GALLERY_THEMES_PLUGIN_SLUG,
        'plugin_path' => plugin_basename( __FILE__ ),
        'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . ENVIRA_GALLERY_THEMES_PLUGIN_SLUG,
        'remote_url'  => 'http://enviragallery.com/',
        'version'     => ENVIRA_GALLERY_THEMES_PLUGIN_VERSION,
        'key'         => $key
    );
    
    $updater = new Envira_Gallery_Updater( $args );

}

/**
 * Adds custom gallery themes to the available list of gallery themes.
 *
 * @since 1.0.0
 *
 * @param array $themes  Array of gallery themes.
 * @return array $themes Amended array of gallery themes.
 */
function envira_gallery_themes_gallery_themes( $themes ) {

    // Add custom themes here.
    $themes[] = array(
        'value' => 'subtle',
        'name'  => __( 'Subtle', 'envira-gallery-themes' ),
        'file'  => __FILE__
    );

    $themes[] = array(
        'value' => 'sleek',
        'name'  => __( 'Sleek', 'envira-gallery-themes' ),
        'file'  => __FILE__
    );

    $themes[] = array(
        'value' => 'captioned',
        'name'  => __( 'Captioned', 'envira-gallery-themes' ),
        'file'  => __FILE__
    );

    $themes[] = array(
        'value' => 'showcase',
        'name'  => __( 'Showcase', 'envira-gallery-themes' ),
        'file'  => __FILE__
    );

    return $themes;

}

/**
 * Adds custom lightbox themes to the available list of lightbox themes.
 *
 * @since 1.0.0
 *
 * @param array $themes  Array of lightbox themes.
 * @return array $themes Amended array of lightbox themes.
 */
function envira_gallery_themes_lightbox_themes( $themes ) {

    // Add custom themes here.
    $themes[] = array(
        'value' => 'subtle',
        'name'  => __( 'Subtle', 'envira-gallery-themes' ),
        'file'  => __FILE__
    );

    $themes[] = array(
        'value' => 'sleek',
        'name'  => __( 'Sleek', 'envira-gallery-themes' ),
        'file'  => __FILE__
    );

    $themes[] = array(
        'value' => 'captioned',
        'name'  => __( 'Captioned', 'envira-gallery-themes' ),
        'file'  => __FILE__
    );

    $themes[] = array(
        'value' => 'showcase',
        'name'  => __( 'Showcase', 'envira-gallery-themes' ),
        'file'  => __FILE__
    );

    return $themes;

}

/**
 * Adds custom HTML output for specific gallery themes.
 *
 * @since 1.0.0
 *
 * @param string $output  String of gallery output.
 * @param mixed $id       The ID of the gallery.
 * @param array $item     Array of data about the image.
 * @param array $data     Array of gallery data.
 * @param int $i          The current index in the gallery.
 * @return string $output Amended string of gallery output.
 */
function envira_gallery_themes_output( $output, $id, $item, $data, $i ) {

    $instance = Envira_Gallery_Shortcode::get_instance();
    if ( 'captioned' == $instance->get_config( 'gallery_theme', $data ) ) {
        if ( ! empty( $item['title'] ) ) {
            $caption  = '<div class="envira-gallery-captioned-data">';
                $caption .= '<p class="envira-gallery-captioned-text">';
                    $output_caption = ! empty( $item['caption'] ) ? $item['caption'] : $item['title'];
                    $caption .= $output_caption;
                $caption .= '</p>';
            $caption .= '</div>';
            $caption  = apply_filters( 'envira_gallery_themes_captioned_output', $caption, $id, $item, $data, $i );
            return $output . $caption;
        }
    }

    // Return the output.
    return $output;

}
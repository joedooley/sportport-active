<?php
/**
 * Plugin Name: Envira Gallery - Standalone Addon
 * Plugin URI:  http://enviragallery.com
 * Description: Enables unique URL access points for Envira galleries.
 * Author:      Thomas Griffin
 * Author URI:  http://thomasgriffinmedia.com
 * Version:     1.1.0
 * Text Domain: envira-standalone
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
define( 'ENVIRA_STANDALONE_PLUGIN_NAME', 'Envira Gallery - Standalone Addon' );
define( 'ENVIRA_STANDALONE_PLUGIN_VERSION', '1.1.0' );
define( 'ENVIRA_STANDALONE_PLUGIN_SLUG', 'envira-standalone' );

register_deactivation_hook( __FILE__, 'envira_standalone_deactivation' );
/**
 * Fired when the plugin is deactivated to clear flushed permalinks flag and flush the permalinks.
 *
 * @since 1.0.1
 *
 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false otherwise.
 */
function envira_standalone_deactivation( $network_wide ) {

	// Flush rewrite rules
	flush_rewrite_rules();

	// Set flag = false in options
	update_option( 'envira-standalone-flushed', false );

}

add_action( 'plugins_loaded', 'envira_standalone_plugins_loaded' );
/**
 * Ensures the full Envira Gallery plugin is active before proceeding.
 *
 * @since 1.0.0
 *
 * @return null Return early if Envira Gallery is not active.
 */
function envira_standalone_plugins_loaded() {

    // Bail if the main class does not exist.
    if ( ! class_exists( 'Envira_Gallery' ) ) {
        return;
    }

    // Display a notice if Envira does not meet the proper version to run the addon.
    if ( version_compare( Envira_Gallery::get_instance()->version, '1.0.9', '<' ) ) {
        add_action( 'admin_notices', 'envira_standalone_version_notice' );
        return;
    };

    // Maybe flush rewrite rules on init
	add_action( 'init', 'envira_standalone_maybe_flush_permalinks' );

    // Fire up the addon.
    add_action( 'envira_gallery_init', 'envira_standalone_plugin_init' );

    // Load the plugin textdomain.
    load_plugin_textdomain( ENVIRA_STANDALONE_PLUGIN_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

}

/**
 * Fired when the plugin is activated and deactivated to flush permalinks.
 *
 * @since 1.0.1
 */
function envira_standalone_maybe_flush_permalinks() {

	$flushed = get_option( 'envira-standalone-flushed' );
    if ( !$flushed ) {

		// Flush rewrite rules.
	    flush_rewrite_rules();

	    // Set flag = true in options
		update_option( 'envira-standalone-flushed', true );

	}

}

/**
 * Outputs a required version notice for the addon to work with Envira.
 *
 * @since 1.0.0
 */
function envira_standalone_version_notice() {

    ?>
    <div class="error">
        <p><?php printf( __( 'The <strong>%s</strong> requires Envira Gallery 1.0.9 or later to work. Please update Envira Gallery to use this addon.', 'envira-standalone' ), ENVIRA_STANDALONE_PLUGIN_NAME ); ?></p>
    </div>
    <?php

}

/**
 * Loads all of the addon hooks and filters.
 *
 * @since 1.0.0
 */
function envira_standalone_plugin_init() {
	
	// Admin CSS
	add_action( 'envira_albums_admin_styles', 'envira_standalone_admin_css' );
	add_action( 'envira_gallery_admin_styles', 'envira_standalone_admin_css' );
	
	// Tab in Settings
	add_filter( 'envira_gallery_settings_tab_nav', 'envira_standalone_settings_tabs');
	add_action( 'envira_gallery_tab_settings_standalone', 'envira_standalone_settings_standalone_tab' );
	add_action( 'init', 'envira_standalone_settings_save' );

    add_action( 'envira_gallery_updater', 'envira_standalone_updater' );
    add_filter( 'envira_gallery_post_type_args', 'envira_gallery_post_type' );
    add_filter( 'envira_albums_post_type_args', 'envira_albums_post_type' );
    add_filter( 'envira_gallery_metabox_ids', 'envira_standalone_slug_box' );
    add_filter( 'envira_albums_metabox_ids', 'envira_standalone_slug_box' );
    add_action( 'admin_head', 'envira_standalone_hide_slug_box' );
    
    add_action( 'pre_get_posts', 'envira_standalone_pre_get_posts' );
    add_action( 'wp_head', 'envira_standalone_maybe_insert_shortcode' );

}

/**
 * Load admin CSS for Quick Edit Support
 *
 * @since 1.0.5
 */
function envira_standalone_admin_css() {

    // Load necessary admin styles.
    wp_register_style( ENVIRA_STANDALONE_PLUGIN_SLUG . '-admin-style', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), ENVIRA_STANDALONE_PLUGIN_VERSION );
    wp_enqueue_style( ENVIRA_STANDALONE_PLUGIN_SLUG . '-admin-style' );	
    
}

/**
 * Add a tab to the Envira Gallery Settings screen
 *
 * @since 1.0.1
 *
 * @param array $tabs Existing tabs
 * @return array New tabs
 */
function envira_standalone_settings_tabs( $tabs ) {

	$tabs['standalone'] = __( 'Standalone', 'envira-standalone' );

	return $tabs;

}

/**
 * Callback for displaying the UI for standalone settings tab.
 *
 * @since 1.0.1
 */
function envira_standalone_settings_standalone_tab() {

	// Get slugs
	$slug = envira_standalone_get_slug( 'gallery' );
	$albumSlug = envira_standalone_get_slug( 'albums' );

    ?>
    <div id="envira-settings-standalone">
        <table class="form-table">
            <tbody>
            	<form action="edit.php?post_type=envira&page=envira-gallery-settings#!envira-tab-standalone" method="post">
	                <tr id="envira-settings-slug-box">
	                    <th scope="row">
	                        <label for="envira-gallery-slug"><?php _e( 'Gallery Slug ', 'envira-standalone' ); ?></label>
	                    </th>
	                    <td>
                            <input type="text" name="envira-gallery-slug" id="envira-gallery-slug" value="<?php echo $slug; ?>" />
                            <?php wp_nonce_field( 'envira-standalone-nonce', 'envira-standalone-nonce' ); ?>
                            <p class="description"><?php _e( 'The slug to prefix to all Envira Galleries.', 'envira-standalone' ); ?></p>
	                    </td>
	                </tr>

	                <tr id="envira-settings-slug-box">
	                    <th scope="row">
	                        <label for="envira-albums-slug"><?php _e( 'Album Slug ', 'envira-standalone' ); ?></label>
	                    </th>
	                    <td>
                            <input type="text" name="envira-albums-slug" id="envira-albums-slug" value="<?php echo $albumSlug; ?>" />
                            <p class="description"><?php _e( 'The slug to prefix to all Envira Albums.', 'envira-standalone' ); ?></p>
	                    </td>
	                </tr>

	                <tr>
	                	<th scope="row"><?php submit_button( __( 'Save', 'envira-gallery' ), 'primary', 'envira-gallery-verify-submit', false ); ?></th>
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
 * @since 1.0.1
 */
function envira_standalone_settings_save() {

	// Check we saved some settings
	if ( !isset($_POST) ) {
		return;
	}

	// Check nonce exists
	if ( !isset($_POST['envira-standalone-nonce']) ) {
		return;
	}

	// Check nonce is valid
	if ( !wp_verify_nonce( $_POST['envira-standalone-nonce'], 'envira-standalone-nonce' ) ) {
		add_action( 'admin_notices', 'envira_standalone_settings_nonce_notice' );
		return;
	}

	// Get reserved slugs
	$slugs = envira_standalone_get_reserved_slugs();

	// Determine which slug(s) to check - include albums if the Albums addon is enabled
	$slugsToCheck = array(
		'gallery',
	);
	if ( isset ( $_POST['envira-albums-slug'] ) ) {
		$slugsToCheck[] = 'albums';
	}

	// Go through each slug
	foreach ( $slugsToCheck as $slug ) {

		// Check slug is valid
		if ( empty( $_POST['envira-' . $slug . '-slug']) ) {
			add_action( 'admin_notices', 'envira_standalone_settings_slug_notice' );
			return;
		}
		if ( !preg_match("/^[a-zA-Z0-9_\-]+$/", $_POST['envira-' . $slug . '-slug'] ) ) {
			add_action( 'admin_notices', 'envira_standalone_settings_slug_notice' );
			return;
		}

		// Check slug is not reserved
		if ( !is_array($slugs) ) {
			add_action( 'admin_notices', 'envira_standalone_settings_slug_notice' );
			return;
		}

		if ( in_array( $_POST['envira-' . $slug . '-slug'], $slugs) ) {
			add_action( 'admin_notices', 'envira_standalone_settings_slug_notice' );
			return;
		}

		// If we reach this point, the slugs are good to use
		update_option( 'envira-' . $slug . '-slug', $_POST['envira-' . $slug . '-slug'] );

	}

	// Set envira-standalone-flushed = false, so on the next page load, rewrite
	// rules are flushed to prevent 404s
	update_option( 'envira-standalone-flushed', false );

	// Output success notice
	add_action( 'admin_notices', 'envira_standalone_settings_saved_notice' );

}

/**
 * Iterates through all Post Types, returning an array of reserved slugs
 *
 * @since 1.0.1
 */
function envira_standalone_get_reserved_slugs() {

	$postTypes = get_post_types();
	if ( !is_array($postTypes) ) {
		return; // Something went wrong fetching Post Types
	}

	$slugs = array();
	foreach ( $postTypes as $postType ) {
		// Skip our own post type
		if ( $postType == 'envira' || $postType == 'envira_album' ) {
			continue;
		}

		$postTypeObj = get_post_type_object( $postType );

		if ( !isset($postTypeObj->rewrite['slug']) ) {
			continue;
		}

		// Add slug to array
		$slugs[] = $postTypeObj->rewrite['slug'];
	}

	return $slugs;

}

/**
 * Outputs a message to tell the user that the nonce field is invalid
 *
 * @since 1.0.1
 */
function envira_standalone_settings_nonce_notice() {

	?>
    <div class="error">
        <p><?php echo ( __( 'The nonce field is invalid.', 'envira-standalone' ) ); ?></p>
    </div>
    <?php

}

/**
 * Outputs a message to tell the user that the slug has been saved
 *
 * @since 1.0.1
 */
function envira_standalone_settings_saved_notice() {

	?>
    <div class="updated">
        <p><?php echo ( __( 'Slug updated successfully!', 'envira-standalone' ) ); ?></p>
    </div>
    <?php

}

/**
 * Outputs a message to tell the user that the slug is missing, contains invalid characters or is already taken
 *
 * @since 1.0.1
 */
function envira_standalone_settings_slug_notice() {

	?>
    <div class="error">
        <p><?php echo ( __( 'The slug is either missing, contains invalid characters or used by a Post Type. Please enter a different slug.', 'envira-standalone' ) ); ?></p>
    </div>
    <?php

}

/**
 * Initializes the addon updater.
 *
 * @since 1.0.0
 *
 * @param string $key The user license key.
 */
function envira_standalone_updater( $key ) {

    $args = array(
        'plugin_name' => ENVIRA_STANDALONE_PLUGIN_NAME,
        'plugin_slug' => ENVIRA_STANDALONE_PLUGIN_SLUG,
        'plugin_path' => plugin_basename( __FILE__ ),
        'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . ENVIRA_STANDALONE_PLUGIN_SLUG,
        'remote_url'  => 'http://enviragallery.com/',
        'version'     => ENVIRA_STANDALONE_PLUGIN_VERSION,
        'key'         => $key
    );

    $updater = new Envira_Gallery_Updater( $args );

}

/**
 * Modifies the Envira Gallery post type so that it is visible to the public.
 *
 * @since 1.0.0
 *
 * @param array $args  Default post type args.
 * @return array $args Amended array of default post type args.
 */
function envira_gallery_post_type( $args ) {

	// Get slug
	$slug = envira_standalone_get_slug( 'gallery' );

    // Change the default post type args so that it can be publicly accessible.
    $args['rewrite']   = array( 'with_front' => false, 'slug' => $slug );
    $args['query_var'] = true;
    $args['public']    = true;
    $args['supports']  = array( 'title', 'slug', 'author' );

    return apply_filters( 'envira_standalone_post_type_args', $args );

}

/**
 * Modifies the Envira Albums post type so that it is visible to the public.
 *
 * @since 1.0.0
 *
 * @param array $args  Default post type args.
 * @return array $args Amended array of default post type args.
 */
function envira_albums_post_type( $args ) {

	// Get slug
	$slug = envira_standalone_get_slug( 'albums' );

    // Change the default post type args so that it can be publicly accessible.
    $args['rewrite']   = array( 'with_front' => false, 'slug' => $slug );
    $args['query_var'] = true;
    $args['public']    = true;
    $args['supports']  = array( 'title', 'slug' );

    return apply_filters( 'envira_standalone_post_type_args', $args );

}

/**
 * Gets the slug from the options table. If blank or does not exist, defaults
 * to 'envira'
 *
 * @since 1.0.1
 *
 * @param string $type Type (gallery|albums)
 * @return string $slug Slug.
 */
function envira_standalone_get_slug( $type ) {

	// Get slug
	switch ($type) {
		case 'gallery':
			$slug = get_option( 'envira-gallery-slug');
			if ( !$slug OR empty( $slug ) ) {
				// Fallback to check for previous version option name.
				$slug = get_option( 'envira_standalone_slug' );
				if ( ! $slug || empty( $slug ) ) {
					$slug = 'envira';
				}
			}
			break;

		case 'albums':
			$slug = get_option( 'envira-albums-slug');
			if ( !$slug OR empty( $slug ) ) {
				$slug = 'envira_album';
			}
			break;

		default:
			$slug = 'envira'; // Fallback
			break;
	}

	return $slug;
}

/**
 * Allows the following metaboxes to be output for managing gallery and album post names:
 * - slugdiv
 * - wpseo_meta
 *
 * @since 1.0.0
 *
 * @param array $ids  Default metabox IDs to allow.
 * @return array $ids Amended metabox IDs to allow.
 */
function envira_standalone_slug_box( $ids ) {

    $ids[] = 'slugdiv';
    $ids[] = 'authordiv';
    $ids[] = 'wpseo_meta';
   
    return $ids;

}

/**
 * Hides the slug box from view since it is not needed.
 *
 * @since 1.0.0
 *
 * @return null Return early if not on an Envira post type screen.
 */
function envira_standalone_hide_slug_box() {

    if ( empty( get_current_screen()->post_type ) || isset( get_current_screen()->post_type ) && 'envira' !== get_current_screen()->post_type ) {
        return;
    }

    ?>
    <style type="text/css">
    	#slugdiv { display: none; }
    	.misc-pub-section { display: block !important; }
    </style>
    <?php

}

/**
 * Run Gallery/Album Query if on an Envira Gallery or Album
 *
 * @since 1.0.0
 *
 * @param object $query The query object passed by reference.
 * @return null         Return early if in admin or not the main query or not a single post.
 */
function envira_standalone_pre_get_posts( $query ) {

	// Return early if in the admin, not the main query or not a single post.
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_single() ) {
        return;
    }

    // If not the proper post type (Envira), return early.
    $post_type = get_query_var( 'post_type' );

    if ( 'envira' == $post_type ) {
    	do_action( 'envira_standalone_gallery_pre_get_posts', $query );
    }

    if ( 'envira_album' == $post_type ) {
    	do_action( 'envira_standalone_album_pre_get_posts', $query );
    }

}

/**
 * Maybe inserts the Envira shortcode into the content for the page being viewed.
 *
 * @since 1.0.0
 *
 * @return null         Return early if in admin or not the main query or not a single post.
*/
function envira_standalone_maybe_insert_shortcode() {

	// Check we are on a single Post
	if ( ! is_singular() ) {
		return;
	}

	// If not the proper post type (Envira), return early.
    $post_type = get_query_var( 'post_type' );

    if ( 'envira' == $post_type ) {
    	add_filter( 'the_content', 'envira_standalone_insert_gallery_shortcode' );
    }

    if ( 'envira_album' == $post_type ) {
    	add_filter( 'the_content', 'envira_standalone_insert_album_shortcode' );
    }

}

/**
 * Inserts the Envira Gallery shortcode into the content for the page being viewed.
 *
 * @since 1.0.0
 *
 * @global object $wp_query The current query object.
 * @param string $content   The content to be filtered.
 * @return string $content  Amended content with our gallery shortcode prepended.
 */
function envira_standalone_insert_gallery_shortcode( $content ) {

    // Display the gallery based on the query var available.
    $id = get_query_var( 'p' );
    if ( empty( $id ) ) {
	    // _get_gallery_by_slug() performs a LIKE search, meaning if two or more
		// Envira Galleries contain the slug's word in *any* of the metadata, the first
		// is automatically assumed to be the 'correct' gallery
		// For standalone, we already know precisely which gallery to display, so
		// we can use its post ID.
	    global $post;
	    $id = $post->ID;
    }

    $shortcode = '[envira-gallery id="' . $id . '"]';

    return $shortcode . $content;

}

/**
 * Inserts the Envira Album shortcode into the content for the page being viewed.
 *
 * @since 1.0.0
 *
 * @global object $wp_query The current query object.
 * @param string $content   The content to be filtered.
 * @return string $content  Amended content with our gallery shortcode prepended.
 */
function envira_standalone_insert_album_shortcode( $content ) {

    // Display the album based on the query var available.
    $id = get_query_var( 'p' );
    if ( empty( $id ) ) {
        // _get_album_by_slug() performs a LIKE search, meaning if two or more
		// Envira Albums contain the slug's word in *any* of the metadata, the first
		// is automatically assumed to be the 'correct' album
		// For standalone, we already know precisely which album to display, so
		// we can use its post ID.
	    global $post;
	    $id = $post->ID;
    }

	$shortcode = '[envira-album id="' . $id . '"]';

    return $shortcode . $content;

}
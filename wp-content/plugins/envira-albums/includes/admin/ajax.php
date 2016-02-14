<?php
/**
 * Handles all admin ajax interactions for the Envira Albums plugin.
 *
 * @since 1.0.0
 *
 * @package Envira_Albums
 * @author  Tim Carr
 */

add_action( 'wp_ajax_envira_albums_sort_galleries', 'envira_albums_sort_galleries' );
/**
 * Saves the sort order of galleries in an album
 *
 * @since 1.0.0
 */
function envira_albums_sort_galleries() {

    // Run a security check first.
    check_ajax_referer( 'envira-albums-sort', 'nonce' );

    // Check variables exist
    if ( ! isset( $_POST['post_id']) ) {
        echo json_encode( array (
            'success' => false,
        ) );
        die();
    }
    if ( ! isset( $_POST['galleryIDs']) ) {
        echo json_encode( array (
            'success' => false,
        ) );
        die();
    }

    // Prepare variables.
    $post_id = absint( $_POST['post_id'] );

    // Get post meta
    $settings = get_post_meta( $post_id, '_eg_album_data', true );

    // Update post meta
    if ( empty( $_POST['galleryIDs'] ) ) {
        unset( $settings['galleryIDs'] );
    } else {
        $settings['galleryIDs'] = explode( ',', $_POST['galleryIDs'] );
    }
    update_post_meta( $post_id, '_eg_album_data', $settings );

    // Send back the response.
    echo json_encode( array (
        'success'   => true,
    ) );
    die;

}

add_action( 'wp_ajax_envira_albums_search_galleries', 'envira_albums_search_galleries' );
/**
 * Searches for Galleries based on the given search terms
 *
 * @since 1.1.0.3
 */
function envira_albums_search_galleries() {

    // Run a security check first.
    check_ajax_referer( 'envira-albums-search', 'nonce' );

    // Check variables exist
    if ( ! isset( $_POST['post_id'] ) ) {
        echo 'Fail1';
        die();
    }
    if ( ! isset( $_POST['search_terms'] ) ) {
        echo 'Fail2';
        die();
    }

    // Prepare variables.
    $post_id = absint( $_POST['post_id'] );
    $search_terms = (string) $_POST['search_terms'];

    // Get post meta
    $album_data = get_post_meta( $post_id, '_eg_album_data', true );

    // Run query
    $arguments = array(
        'post_type'         => 'envira',
        'post_status'       => 'publish',
        'orderby'           => 'title',
        'order'             => 'ASC',
    );

    // Exclude galleries we already included in this album
    if ( isset( $album_data['galleryIDs'] ) ) {
        $arguments['post__not_in'] = $album_data['galleryIDs'];
    }

    // Search will be either blank (because the user has removed their search term), or at least
    // 3 characters.  If blank, just return the 10 most recent galleries. Otherwise, return all galleries
    // matching the search terms
    if ( !empty( $search_terms ) ) {
        $arguments['s']                 = $_POST['search_terms'];
        $arguments['posts_per_page']    = -1;
    } else {
        $arguments['posts_per_page']    = 10;
    }

    // Get galleries
    $galleries = new WP_Query( $arguments );
    if ( count( $galleries->posts ) == 0 ) {
        echo '<li>' . __( 'No Galleries found matching the given search terms.', 'envira-albums' ) . '</li>';
        die();
    }

    // Build output
    $instance = Envira_Gallery::get_instance();
    $metabox = Envira_Albums_Metaboxes::get_instance();

    ob_start();
    foreach ( $galleries->posts as $gallery ) {
        // Get album metadata for this gallery
        $data = $instance->get_gallery( $gallery->ID );

        // Output <li> element with media modal
        $metabox->output_gallery_li( $gallery, $data, $post_id );
    }
    $html = ob_get_clean();

    echo $html;
    die();

}

add_action( 'wp_ajax_envira_albums_update_gallery', 'envira_albums_update_gallery' );
/**
 * Saves the metadata for a gallery in an album
 *
 * @since 1.0.0
 */
function envira_albums_update_gallery() {

    // Run a security check first.
    check_ajax_referer( 'envira-albums-sort', 'nonce' );

    // Check variables exist
    if ( ! isset( $_POST['post_id']) ) {
        echo json_encode( array (
            'success'   => false,
        ) );
        die();
    }
    if ( ! isset( $_POST['gallery_id']) ) {
        echo json_encode( array (
            'success' => false,
        ) );
        die();
    }

    // Prepare variables.
    $post_id = absint( $_POST['post_id'] );
    $gallery_id = absint( $_POST['gallery_id'] );

    // Get post meta
    $settings = get_post_meta( $post_id, '_eg_album_data', true );

    // Set array if this is the first time we're saving settings
    if ( !isset( $settings['galleries'] ) ) {
        $settings['galleries'] = array();
    }
    if ( !isset( $settings['galleries'][ $gallery_id ] ) ) {
        $settings['galleries'][ $gallery_id ] = array();
    }

    // Set post meta values
    $settings['galleries'][ $gallery_id ]['title'] = sanitize_text_field( $_POST['title'] );
    $settings['galleries'][ $gallery_id ]['caption'] = trim( $_POST['caption'] );
    $settings['galleries'][ $gallery_id ]['alt'] = sanitize_text_field( $_POST['alt'] );

    // Set the cover image ID or URL, depending on what was specified
    if ( isset( $_POST['cover_image_id'] ) && ! empty( $_POST['cover_image_id'] ) ) {
        $settings['galleries'][ $gallery_id ]['cover_image_id'] = absint( $_POST['cover_image_id'] );
    }
    if ( isset( $_POST['cover_image_id'] ) && ! empty( $_POST['cover_image_url'] ) ) {
        $settings['galleries'][ $gallery_id ]['cover_image_url'] = sanitize_text_field( $_POST['cover_image_url'] );
    }

    // Save post meta
    update_post_meta( $post_id, '_eg_album_data', $settings );

    // Clear transient cache
    Envira_Albums_Common::get_instance()->flush_album_caches( $post_id );

    // Send back the response.
    echo json_encode( array (
        'success'   => true,
    ) );
    die;

}
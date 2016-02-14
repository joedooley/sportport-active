<?php
/**
 * Export class.
 *
 * @since 1.2.4.5
 *
 * @package Envira_Albums
 * @author  Tim Carr
 */
class Envira_Albums_Export {

    /**
     * Holds the class object.
     *
     * @since 1.2.4.5
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.2.4.5
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.2.4.5
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 1.2.4.5
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Envira_Albums::get_instance();

        // Export an album.
        $this->export_album();

    }

    /**
     * Exports an Envira album.
     *
     * @since 1.2.4.5
     *
     * @return null Return early if failing proper checks to export the album.
     */
    public function export_album() {

        if ( ! $this->has_exported_album() ) {
            return;
        }

        if ( ! $this->verify_exported_album() ) {
            return;
        }

        if ( ! $this->can_export_album() ) {
            return;
        }

        // Ignore the user aborting the action.
        ignore_user_abort( true );

        // Grab the proper data.
        $post_id = absint( $_POST['envira_post_id'] );
        $data    = get_post_meta( $post_id, '_eg_album_data', true );

        // Set the proper headers.
        nocache_headers();
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=envira-album-' . $post_id . '-' . date( 'm-d-Y' ) . '.json' );
        header( 'Expires: 0' );

        // Make the settings downloadable to a JSON file and die.
        die( json_encode( $data ) );

    }

    /**
     * Helper method to determine if an album export is available.
     *
     * @since 1.2.4.5
     *
     * @return bool True if an exported album is available, false otherwise.
     */
    public function has_exported_album() {

        return ! empty( $_POST['envira_export'] );

    }

    /**
     * Helper method to determine if an album export nonce is valid and verified.
     *
     * @since 1.2.4.5
     *
     * @return bool True if the nonce is valid, false otherwise.
     */
    public function verify_exported_album() {

        return isset( $_POST['envira-albums-export'] ) && wp_verify_nonce( $_POST['envira-albums-export'], 'envira-albums-export' );

    }

    /**
     * Helper method to determine if the user can actually export the album.
     *
     * @since 1.2.4.5
     *
     * @return bool True if the user can export the album, false otherwise.
     */
    public function can_export_album() {

        $manage_options = current_user_can( 'manage_options' );
        return apply_filters( 'envira_albums_export_cap', $manage_options );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.2.4.5
     *
     * @return object The Envira_Albums_Export object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Albums_Export ) ) {
            self::$instance = new Envira_Albums_Export();
        }

        return self::$instance;

    }

}

// Load the export class.
$envira_albums_export = Envira_Albums_Export::get_instance();
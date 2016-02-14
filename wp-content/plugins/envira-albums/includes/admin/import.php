<?php
/**
 * Import class.
 *
 * @since 1.2.4.5
 *
 * @package Envira_Albums
 * @author  Tim Carr
 */
class Envira_Albums_Import {

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
     * Holds any plugin error messages.
     *
     * @since 1.2.4.5
     *
     * @var array
     */
    public $errors = array();

    /**
     * Primary class constructor.
     *
     * @since 1.2.4.5
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Envira_Albums::get_instance();

        // Import a album.
        add_action( 'init', array( $this, 'import_album' ) );
        add_action( 'admin_notices', array( $this, 'notices' ) );

    }

    /**
     * Imports an Envira album.
     *
     * @since 1.2.4.5
     *
     * @return null Return early (possibly setting errors) if failing proper checks to import the album.
     */
    public function import_album() {

        if ( ! $this->has_imported_album() ) {
            return;
        }

        if ( ! $this->verify_imported_album() ) {
            return;
        }

        if ( ! $this->can_import_album() ) {
            $this->errors[] = __( 'Sorry, but you lack the permissions to import an album to this post.', 'envira-albums' );
            return;
        }

        if ( ! $this->post_can_handle_album() ) {
            $this->errors[] = __( 'Sorry, but the post ID you are attempting to import the album to cannot handle an album.', 'envira-album' );
            return;
        }

        if ( ! $this->has_imported_album_files() ) {
            $this->errors[] = __( 'Sorry, but there are no files available to import an album.', 'envira-albums' );
            return;
        }

        if ( ! $this->has_correct_filename() ) {
            $this->errors[] = __( 'Sorry, but you have attempted to upload an album import file with an incompatible filename. Envira Album import files must begin with "envira-album".', 'envira-albums' );
            return;
        }

        if ( ! $this->has_json_extension() ) {
            $this->errors[] = __( 'Sorry, but Envira Album import files must be in <code>.json</code> format.', 'envira-albums' );
            return;
        }

        // Retrieve the JSON contents of the file. If that fails, return an error.
        $contents = $this->get_file_contents();
        if ( ! $contents ) {
            $this->errors[] = __( 'Sorry, but there was an error retrieving the contents of the album export file. Please try again.', 'envira-albums' );
            return;
        }

        // Decode the settings and start processing.
        $data    = json_decode( $contents, true );
        $post_id = absint( $_POST['envira_post_id'] );

        // If the post is an auto-draft (new post), make sure to save as draft first before importing.
        $this->maybe_save_draft( $post_id );

        // Delete any previous album data (if any) from the post that is receiving the new album.
        $this->remove_existing_album( $post_id );

        // Update the ID in the album data to point to the new post.
        $data['id'] = $post_id;

        // Prepare import.
        $this->prepare_import();

        // Import the album.
        $album = $this->run_import( $data, $post_id );

        // Cleanup import.
        $this->cleanup_import();

        // Update the album title and slug to avoid any confusion if importing on same site.
        $album['config']['title'] = sprintf( __( 'Imported Album #%s', 'envira-albums' ), $post_id );
        $album['config']['slug']  = 'imported-album-' . $post_id;

        // Update the meta for the post that is receiving the album.
        update_post_meta( $post_id, '_eg_album_data', $album );

    }

    /**
     * Determines if an album import is available.
     *
     * @since 1.2.4.5
     *
     * @return bool True if an imported album is available, false otherwise.
     */
    public function has_imported_album() {

        return ! empty( $_POST['envira_albums_import'] );

    }

    /**
     * Determines if an albums import nonce is valid and verified.
     *
     * @since 1.2.4.5
     *
     * @return bool True if the nonce is valid, false otherwise.
     */
    public function verify_imported_album() {

        return isset( $_POST['envira-albums-import'] ) && wp_verify_nonce( $_POST['envira-albums-import'], 'envira-albums-import' );

    }

    /**
     * Determines if the user can actually import the albums.
     *
     * @since 1.2.4.5
     *
     * @return bool True if the user can import the albums, false otherwise.
     */
    public function can_import_album() {

        $manage_options = current_user_can( 'manage_options' );
        return apply_filters( 'envira_albums_import_cap', $manage_options );

    }

    /**
     * Determines if the post ID can handle an albums (revision or not).
     *
     * @since 1.2.4.5
     *
     * @return bool True if the post ID is not a revision, false otherwise.
     */
    public function post_can_handle_album() {

        return isset( $_POST['envira_post_id'] ) && ! wp_is_post_revision( $_POST['envira_post_id'] );

    }

    /**
     * Determines if albums import files are available.
     *
     * @since 1.2.4.5
     *
     * @return bool True if the imported albums files are available, false otherwise.
     */
    public function has_imported_album_files() {

        var_dump($_FILES);

        return ! empty( $_FILES['envira_import_album']['name'] ) || ! empty( $_FILES['envira_import_album']['tmp_name'] );

    }

    /**
     * Determines if an album import file has a proper filename.
     *
     * @since 1.2.4.5
     *
     * @return bool True if the imported album file has a proper filename, false otherwise.
     */
    public function has_correct_filename() {

        return preg_match( '#^envira-album#i', $_FILES['envira_import_album']['name'] );

    }

    /**
     * Determines if an album import file has a proper file extension.
     *
     * @since 1.2.4.5
     *
     * @return bool True if the imported album file has a proper file extension, false otherwise.
     */
    public function has_json_extension() {

        $file_array = explode( '.', $_FILES['envira_import_album']['name'] );
        $extension  = end( $file_array );
        return 'json' === $extension;

    }

    /**
     * Retrieve the contents of the imported album file.
     *
     * @since 1.2.4.5
     *
     * @return string|bool JSON contents string if successful, false otherwise.
     */
    public function get_file_contents() {

        $file = $_FILES['envira_import_album']['tmp_name'];
        return @file_get_contents( $file );

    }

    /**
     * Move a new post to draft mode before importing an album.
     *
     * @since 1.2.4.5
     *
     * @param int $post_id The current post ID handling the album import.
     */
    public function maybe_save_draft( $post_id ) {

        $post = get_post( $post_id );
        if ( 'auto-draft' == $post->post_status ) {
            $draft = array(
                'ID'          => $post_id,
                'post_status' => 'draft'
            );
            wp_update_post( $draft );
        }

    }

    /**
     * Helper method to remove existing album data when an album is imported.
     *
     * @since 1.2.4.5
     *
     * @param int $post_id The current post ID handling the album import.
     */
    public function remove_existing_album( $post_id ) {

        delete_post_meta( $post_id, '_eg_album_data' );

    }

    /**
     * Set timeout to 0 and suspend cache invalidation while importing an album.
     *
     * @since 1.2.4.5
     */
    public function prepare_import() {

        set_time_limit( $this->get_max_execution_time() );
        wp_suspend_cache_invalidation( true );

    }

    /**
     * Loops through the data provided and imports items into the album.
     *
     * @since 1.2.4.5
     *
     * @param array     $data       Array of album data being imported.
     * @param int       $post_id    The post ID the album is being imported to.
     * @return array                Modified album data based on imports.
     */
    public function run_import( $data, $post_id ) {

        return $data;

    }

    /**
     * Reset cache invalidation and flush the internal cache after importing an album.
     *
     * @since 1.2.4.5
     */
    public function cleanup_import() {

        wp_suspend_cache_invalidation( false );
        wp_cache_flush();

    }

    /**
     * Helper method to return the max execution time for scripts.
     *
     * @since 1.2.4.5
     *
     * @param int $time The max execution time available for PHP scripts.
     */
    public function get_max_execution_time() {

        $time = ini_get( 'max_execution_time' );
        return ! $time || empty( $time ) ? (int) 0 : $time;

    }

    /**
     * Outputs any errors or notices generated by the class.
     *
     * @since 1.2.4.5
     */
    public function notices() {

        if ( ! empty( $this->errors ) ) {
            ?>
            <div id="message" class="error">
                <p><?php echo implode( '<br />', $this->errors ); ?></p>
            </div>
            <?php
        }

        // If an album has been imported, create a notice for the import status.
        if ( isset( $_GET['envira-albums-imported'] ) && $_GET['envira-albums-imported'] ) :
        ?>
        <div id="message" class="updated">
            <p><?php _e( 'Envira Album imported. Please check to ensure all galleries and data have been imported properly.', 'envira-albums' ); ?></p>
        </div>
        <?php
        endif;

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.2.4.5
     *
     * @return object The Envira_Albums_Import object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Albums_Import ) ) {
            self::$instance = new Envira_Albums_Import();
        }

        return self::$instance;

    }

}

// Load the import class.
$envira_albums_import = Envira_Albums_Import::get_instance();
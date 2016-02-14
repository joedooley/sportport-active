<?php
/**
 * AJAX class.
 *
 * @since 1.0.3
 *
 * @package Envira_Defaults
 * @author  Tim Carr
 */
class Envira_Defaults_Ajax {

    /**
     * Holds the class object.
     *
     * @since 1.0.3
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.0.3
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.0.3
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 1.0.3
     */
    public function __construct() {

        // Actions
        add_action( 'wp_ajax_envira_defaults_gallery_config_modal', array( $this, 'gallery_config_modal' ) );
        add_action( 'wp_ajax_envira_defaults_album_config_modal', array( $this, 'album_config_modal' ) );
        add_action( 'wp_ajax_envira_defaults_gallery_apply_modal', array( $this, 'gallery_apply_modal' ) );
        add_action( 'wp_ajax_envira_defaults_album_apply_modal', array( $this, 'album_apply_modal' ) );
        add_action( 'wp_ajax_envira_defaults_apply', array( $this, 'apply' ) );

    }

    /**
    * The markup to display in the Thickbox modal when a user clicks 'Add New'
    * Allows the user to choose which Gallery, if any, to inherit the configuration from
    * when creating a new Gallery.
    *
    * @since 1.0.3
    */
    public function gallery_config_modal() {

        // Get instances
        $base = Envira_Gallery::get_instance();
        
        // Get galleries
        $galleries = $base->get_galleries();
        ?>
        <div class="wrap">
            <form action="" method="get" id="envira-defaults-config">
                <label for="gallery_id"><?php _e( 'Inherit Config from:', 'envira-defaults' ); ?></label>
                <select name="gallery_id" size="1" id="gallery_id">
                    <option value="<?php echo get_option( 'envira_default_gallery' ); ?>"><?php _e( '(Use Envira Default Settings)', 'envira-defaults' ); ?></option>
                    <?php
                    foreach ( (array) $galleries as $gallery ) {
                        // Get title
                        $title = $gallery['config']['title'];
                        ?>
                        <option value="<?php echo $gallery['id']; ?>"><?php echo $title; ?></option>
                        <?php
                    }
                    ?>
                </select>
                <input type="submit" name="submit" value="<?php _e( 'Create Gallery', 'envira-defaults' ); ?>" class="button button-primary" />
            </form>
        </div>
        <?php

        die();

    }

    /**
    * The markup to display in the Thickbox modal when a user clicks 'Add New'
    * Allows the user to choose which Gallery, if any, to inherit the configuration from
    * when creating a new Gallery.
    *
    * @since 1.0.3
    */
    public function album_config_modal() {

        // Get instances
        $base = Envira_Albums::get_instance();
        
        // Get albums
        $albums = $base->get_albums();
        ?>
        <div class="wrap">
            <form action="" method="get" id="envira-defaults-config">
                <label for="album_id"><?php _e( 'Inherit Config from:', 'envira-defaults' ); ?></label>
                <select name="album_id" size="1" id="album_id">
                    <option value="<?php echo get_option( 'envira_default_album' ); ?>"><?php _e( '(Use Envira Default Settings)', 'envira-defaults' ); ?></option>
                    <?php
                    foreach ( (array) $albums as $album ) {
                        // Get title
                        $title = $album['config']['title'];
                        ?>
                        <option value="<?php echo $album['id']; ?>"><?php echo $title; ?></option>
                        <?php
                    }
                    ?>
                </select>
                <input type="submit" name="submit" value="<?php _e( 'Create Album', 'envira-defaults' ); ?>" class="button button-primary" />
            </form>
        </div>
        <?php

        die();

    }

    /**
    * The markup to display in the Thickbox modal when a user clicks 'Apply Defaults'
    * Allows the user to choose which Gallery, if any, to apply the configuration from
    * when bulk updating galleries
    *
    * @since 1.0.6
    */
    public function gallery_apply_modal() {

        // Get instances
        $base = Envira_Gallery::get_instance();
        
        // Get galleries
        $galleries = $base->get_galleries();
        ?>
        <div class="wrap">
            <form action="" method="get" id="envira-defaults-apply-config" data-post-type="envira">
                <label for="gallery_id"><?php _e( 'Apply Config from:', 'envira-defaults' ); ?></label>
                <select name="gallery_id" size="1" id="gallery_id">
                    <option value="<?php echo get_option( 'envira_default_gallery' ); ?>"><?php _e( '(Use Envira Default Settings)', 'envira-defaults' ); ?></option>
                    <?php
                    foreach ( (array) $galleries as $gallery ) {
                        // Get title
                        $title = $gallery['config']['title'];
                        ?>
                        <option value="<?php echo $gallery['id']; ?>"><?php echo $title; ?></option>
                        <?php
                    }
                    ?>
                </select>
                <input type="submit" name="submit" value="<?php _e( 'Apply Config to Selected Galleries', 'envira-defaults' ); ?>" class="button button-primary" />
            </form>
        </div>
        <?php

        die();

    }

    /**
    * The markup to display in the Thickbox modal when a user clicks 'Apply Defaults'
    * Allows the user to choose which Album, if any, to inherit the configuration from
    * when bulk updating albums
    *
    * @since 1.0.3
    */
    public function album_apply_modal() {

        // Get instances
        $base = Envira_Albums::get_instance();
        
        // Get albums
        $albums = $base->get_albums();
        ?>
        <div class="wrap">
            <form action="" method="get" id="envira-defaults-apply-config" data-post-type="envira_album">
                <label for="album_id"><?php _e( 'Inherit Config from:', 'envira-defaults' ); ?></label>
                <select name="album_id" size="1" id="album_id">
                    <option value="<?php echo get_option( 'envira_default_album' ); ?>"><?php _e( '(Use Envira Default Settings)', 'envira-defaults' ); ?></option>
                    <?php
                    foreach ( (array) $albums as $album ) {
                        // Get title
                        $title = $album['config']['title'];
                        ?>
                        <option value="<?php echo $album['id']; ?>"><?php echo $title; ?></option>
                        <?php
                    }
                    ?>
                </select>
                <input type="submit" name="submit" value="<?php _e( 'Apply Config to Selected Albums', 'envira-defaults' ); ?>" class="button button-primary" />
            </form>
        </div>
        <?php

        die();

    }

    /**
     * Applies configuration settings to the POSTed Galleries/Albums based on the chosen
     * Gallery/Album
     *
     * @since 1.0.6
     */
    public function apply() {

        // Run a security check first.
        check_ajax_referer( 'envira-defaults', 'nonce' );

        // Check for required vars
        $id         = absint( $_POST['id'] );
        $post_ids   = stripslashes_deep( $_POST['post_ids'] );
        $post_type  = sanitize_text_field( $_POST['post_type'] );

        // Get the config for the chosen Gallery/Album
        switch ( $post_type ) {

            /**
            * Gallery
            */
            case 'envira':
                // Get config, and unset some parameters we don't want to map to the chosen galleries
                $gallery = Envira_Gallery::get_instance()->get_gallery( $id );
                $config = $gallery['config'];
                unset( $config['type'] );
                unset( $config['title'] );
                unset( $config['slug'] );
                unset( $config['classes'] );

                // Iterate through chosen Galleries, updating config with the above gallery config
                foreach ( ( array ) $post_ids as $post_id ) {
                    // Get post meta
                    $gallery = get_post_meta( $post_id, '_eg_gallery_data', true );

                    foreach ( $config as $key => $value ) {
                        $gallery['config'][ $key ] = $value;
                    }

                    // Update
                    update_post_meta( $post_id, '_eg_gallery_data', $gallery );

                    // Flush cache
                    Envira_Gallery_Common::get_instance()->flush_gallery_caches( $post_id, $gallery['config']['slug'] );
                }

                break;

            /**
            * Album
            */
            case 'envira_album':
                // Get config, and unset some parameters we don't want to map to the chosen albums
                $album = Envira_Albums::get_instance()->get_album( $id );
                $config = $album['config'];
                unset( $config['type'] );
                unset( $config['title'] );
                unset( $config['slug'] );
                unset( $config['classes'] );

                // Iterate through chosen Albums, updating config with the above album config
                foreach ( ( array ) $post_ids as $post_id ) {
                    // Get post meta
                    $album = get_post_meta( $post_id, '_eg_album_data', true );

                    foreach ( $config as $key => $value ) {
                        $album['config'][ $key ] = $value;
                    }

                    // Update
                    update_post_meta( $post_id, '_eg_album_data', $album );
                }
                break;

        }

        // Send success message
        wp_send_json_success();

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Defaults_Ajax object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Defaults_Ajax ) ) {
            self::$instance = new Envira_Defaults_Ajax();
        }

        return self::$instance;

    }

}

// Load the AJAX class.
$envira_defaults_ajax = Envira_Defaults_Ajax::get_instance();
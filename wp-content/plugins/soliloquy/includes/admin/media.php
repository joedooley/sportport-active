<?php
/**
 * Media class.
 *
 * @since 1.0.0
 *
 * @package Soliloquy
 * @author  Thomas Griffin
 */
class Soliloquy_Media {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Soliloquy::get_instance();

    }

    /**
     * Prepares a custom media upload form that allows multiple forms on one page.
     *
     * @since 1.0.0
     *
     * @param int $post_id Post ID
     * @return null Return early if the form cannot be output.
     */
    public function media_upload_form( $post_id ) {

        ?>
        <!-- Errors -->
        <div id="soliloquy-upload-error"></div>

        <!-- WP Media Upload Form -->
        <?php media_upload_form(); ?>
        <script type="text/javascript">
            var post_id = <?php echo $post_id; ?>, shortform = 3;
        </script>
        <input type="hidden" name="post_id" id="post_id" value="<?php echo $post_id; ?>" />

        <!-- Add from Media Library -->
        <a href="#" class="soliloquy-media-library button button-primary"  title="<?php _e( 'Click Here to Insert from Other Image Sources', 'soliloquy' ); ?>" style="vertical-align: baseline;">
            <?php _e( 'Click Here to Insert from Other Image Sources', 'soliloquy' ); ?>
        </a>
        <?php

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Soliloquy_Media object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Soliloquy_Media ) ) {
            self::$instance = new Soliloquy_Media();
        }

        return self::$instance;

    }

}

// Load the media class.
$soliloquy_media = Soliloquy_Media::get_instance();
<?php
/**
 * Editor class.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @author  Thomas Griffin
 */
class Envira_Gallery_Editor {

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
     * Flag to determine if media modal is loaded.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $loaded = false;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Envira_Gallery::get_instance();

        // Add a custom media button to the editor.
        add_filter( 'media_buttons_context', array( $this, 'media_button' ) );
        add_action( 'save_post', array( $this, 'save_gallery_ids' ), 9999 );
        add_action( 'before_delete_post', array( $this, 'remove_gallery_ids' ) );

    }

    /**
     * Adds a custom gallery insert button beside the media uploader button.
     *
     * @since 1.0.0
     *
     * @param string $buttons  The media buttons context HTML.
     * @return string $buttons Amended media buttons context HTML.
     */
    public function media_button( $buttons ) {

        // Create the media button.
        $button  = '<style type="text/css">@media only screen and (-webkit-min-device-pixel-ratio: 2),only screen and (min--moz-device-pixel-ratio: 2),only screen and (-o-min-device-pixel-ratio: 2/1),only screen and (min-device-pixel-ratio: 2),only screen and (min-resolution: 192dpi),only screen and (min-resolution: 2dppx) { #envira-media-modal-button .envira-media-icon[style] { background-image: url(' . plugins_url( 'assets/css/images/menu-icon@2x.png', $this->base->file ) . ') !important; background-size: 16px 16px !important; } }</style>';
        $button .= '<a id="envira-media-modal-button" href="#" class="button envira-gallery-choose-gallery" title="' . esc_attr__( 'Add Gallery', 'envira-gallery' ) . '" style="padding-left: .4em;"><span class="envira-media-icon" style="background: transparent url(' . plugins_url( 'assets/css/images/menu-icon.png', $this->base->file ) . ') no-repeat scroll 0 0; width: 16px; height: 16px; display: inline-block; vertical-align: text-top;"></span> ' . __( 'Add Gallery', 'envira-gallery' ) . '</a>';

        // Enqueue the script that will trigger the editor button.
        wp_enqueue_script( $this->base->plugin_slug . '-editor-script', plugins_url( 'assets/js/editor.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );

        // Add the action to the footer to output the modal window.
        add_action( 'admin_footer', array( $this, 'gallery_selection_modal' ) );

        // Filter the button
        $button = apply_filters( 'envira_gallery_media_button', $button, $buttons );

        // Append the button.
        return $buttons . $button;

    }

    /**
     * Outputs the gallery selection modal to insert a gallery into an editor.
     *
     * @since 1.0.0
     */
    public function gallery_selection_modal() {

        echo $this->get_gallery_selection_modal();

    }

    /**
     * Returns the gallery selection modal to insert a gallery into an editor.
     *
     * @since 1.0.0
     *
     * @global object $post The current post object.
     * @return string Empty string if no galleries are found, otherwise modal UI.
     */
    public function get_gallery_selection_modal() {

        // Return early if already loaded.
        if ( $this->loaded ) {
            return '';
        }

        // Set the loaded flag to true.
        $this->loaded = true;

        global $post;
        $galleries = $this->base->get_galleries( false );

        ob_start();
        ?>
        <div class="envira-gallery-default-ui-wrapper" style="display: none;">
            <div class="envira-gallery-default-ui envira-gallery-image-meta">
                <div class="media-modal wp-core-ui">
                    <a class="media-modal-close" href="#"><span class="media-modal-icon"></span>
                    </a>
                    <div class="media-modal-content">
                        <div class="media-frame wp-core-ui hide-menu hide-router envira-gallery-meta-wrap">
                            <div class="media-frame-title">
                                <h1><?php _e( 'Choose Your Gallery', 'envira-gallery' ); ?></h1>
                            </div>
                            <div class="media-frame-content">
                                <div class="attachments-browser">
                                    <ul class="envira-gallery-meta attachments" style="padding-left: 8px; top: 1em;">
                                        <?php foreach ( (array) $galleries as $gallery ) : if ( $post->ID == $gallery['id'] ) continue; ?>
                                        <li class="attachment" data-envira-gallery-id="<?php echo absint( $gallery['id'] ); ?>" style="margin: 8px;">
                                            <div class="attachment-preview landscape">
                                                <div class="thumbnail" style="display: table;">
                                                    <div class="inside">
                                                        <?php
                                                        if ( ! empty( $gallery['config']['title'] ) ) {
                                                            $title = $gallery['config']['title'];
                                                        } else if ( ! empty( $gallery['config']['slug'] ) ) {
                                                            $title = $gallery['config']['title'];
                                                        } else {
                                                            $title = sprintf( __( 'Gallery ID #%s', 'envira-gallery' ), $gallery['id'] );
                                                        }
                                                        ?>
                                                        <h3 style="margin: 0;"><?php echo $title; ?></h3>
                                                        <code>[envira-gallery id="<?php echo absint( $gallery['id'] ); ?>"]</code>
                                                    </div>
                                                </div>
                                                <a class="check" href="#"><div class="media-modal-icon"></div></a>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <!-- end .envira-gallery-meta -->
                                    <div class="media-sidebar">
                                        <div class="envira-gallery-meta-sidebar">
                                            <h3 style="margin: 1.4em 0 1em;"><?php _e( 'Helpful Tips', 'envira-gallery' ); ?></h3>
                                            <strong><?php _e( 'Choosing Your Gallery', 'envira-gallery' ); ?></strong>
                                            <p style="margin: 0 0 1.5em;"><?php _e( 'To choose your gallery, simply click on one of the boxes to the left. The "Insert Gallery" button will be activated once you have selected a gallery.', 'envira-gallery' ); ?></p>
                                            <strong><?php _e( 'Inserting Your Gallery', 'envira-gallery' ); ?></strong>
                                            <p style="margin: 0 0 1.5em;"><?php _e( 'To insert your gallery into the editor, click on the "Insert Gallery" button below.', 'envira-gallery' ); ?></p>
                                        </div>
                                        <!-- end .envira-gallery-meta-sidebar -->
                                    </div>
                                    <!-- end .media-sidebar -->
                                </div>
                                <!-- end .attachments-browser -->
                            </div>
                            <!-- end .media-frame-content -->
                            <div class="media-frame-toolbar">
                                <div class="media-toolbar">
                                    <div class="media-toolbar-secondary">
                                        <a href="#" class="envira-gallery-cancel-insertion button media-button button-large button-secondary media-button-insert" title="<?php esc_attr_e( 'Cancel Gallery Insertion', 'envira-gallery' ); ?>"><?php _e( 'Cancel Gallery Insertion', 'envira-gallery' ); ?></a>
                                    </div>
                                    <div class="media-toolbar-primary">
                                        <a href="#" class="envira-gallery-insert-gallery button media-button button-large button-primary media-button-insert" disabled="disabled" title="<?php esc_attr_e( 'Insert Gallery', 'envira-gallery' ); ?>"><?php _e( 'Insert Gallery', 'envira-gallery' ); ?></a>
                                    </div>
                                    <!-- end .media-toolbar-primary -->
                                </div>
                                <!-- end .media-toolbar -->
                            </div>
                            <!-- end .media-frame-toolbar -->
                        </div>
                        <!-- end .media-frame -->
                    </div>
                    <!-- end .media-modal-content -->
                </div>
                <!-- end .media-modal -->
                <div class="media-modal-backdrop"></div>
            </div><!-- end #envira-gallery-default-ui -->
        </div><!-- end #envira-gallery-default-ui-wrapper -->
        <?php
        return ob_get_clean();

    }

    /**
     * Checks for the existience of any Envira Gallery shortcodes in the Post's content,
     * storing this Post's ID in each Envira Gallery.
     *
     * This allows Envira's WP_List_Table to tell the user which Post(s) the Gallery is
     * included in.
     *
     * @since 1.3.3.6
     *
     * @param int $post_id Post ID
     */
    public function save_gallery_ids( $post_id ) {

        $this->update_gallery_post_ids( $post_id, false );

    }

    /**
     * Removes the given Post ID from all Envira Galleries that contain the Post ID
     *
     * @since 1.3.3.6
     *
     * @param int $post_id Post ID
     */
    public function remove_gallery_ids( $post_id ) {

        $this->update_gallery_post_ids( $post_id, true );

    }

    /**
     * Checks for Envira Gallery shortcodes in the given content.
     *
     * If found, adds or removes those shortcode IDs to the given Post ID
     *
     * @since 1.3.3.6
     *
     * @param int $post_id Post ID
     * @param bool $remove Remove Post ID from Gallery Meta (false)
     * @return bool
     */
    private function update_gallery_post_ids( $post_id, $remove = false ) {

        // Get post
        $post = get_post( $post_id );
        if ( ! $post ) {
            return;
        }

        // Don't do anything if we are saving a Gallery or Album
        if ( in_array( $post->post_type, array( 'envira', 'envira_album' ) ) ) {
            return;
        }

        // Don't do anything if this is a Post Revision
        if ( wp_is_post_revision( $post ) ) {
            return false;
        }

        // Check content for shortcodes
        if ( ! has_shortcode( $post->post_content, 'envira-gallery' ) ) {
            return false;
        }

        // Content has Envira shortcode(s)
        // Extract them to get Gallery IDs
        $pattern = '\[(\[?)(envira\-gallery)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
        if ( ! preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches ) ) {
            return false;
        }
        if ( ! is_array( $matches[3] ) ) {
            return false;
        }

        // Iterate through shortcode matches, extracting the gallery ID and storing it in the meta
        $gallery_ids = array();
        foreach ( $matches[3] as $shortcode ) {
            // Grab ID
            $gallery_ids[] = preg_replace( "/[^0-9]/", "", $shortcode ); 
        }

        // Check we found gallery IDs
        if ( ! $gallery_ids ) {
            return false;
        }

        // Iterate through each gallery
        foreach ( $gallery_ids as $gallery_id ) {
            // Get Post IDs this Gallery is included in
            $post_ids = get_post_meta( $gallery_id, '_eg_in_posts', true );
            if ( ! is_array( $post_ids ) ) {
                $post_ids = array();
            }

            
            if ( $remove ) {
                // Remove the Post ID
                if ( isset( $post_ids[ $post_id ] ) ) {
                    unset( $post_ids[ $post_id ] );
                }
            } else {
                // Add the Post ID
                $post_ids[ $post_id ] = $post_id;
            }

            // Save
            update_post_meta( $gallery_id, '_eg_in_posts', $post_ids );
        }

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Gallery_Editor object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Gallery_Editor ) ) {
            self::$instance = new Envira_Gallery_Editor();
        }

        return self::$instance;

    }

}

// Load the editor class.
$envira_gallery_editor = Envira_Gallery_Editor::get_instance();
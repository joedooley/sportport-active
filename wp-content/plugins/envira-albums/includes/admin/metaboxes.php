<?php
/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Albums
 * @author  Tim Carr
 */
class Envira_Albums_Metaboxes {

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
        $this->base = Envira_Albums::get_instance();

        // Load metabox assets.
        add_action( 'admin_enqueue_scripts', array( $this, 'meta_box_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'meta_box_scripts' ) );

        // Load the metabox hooks and filters.
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 100 );

        // Load all tabs.
        add_action( 'envira_albums_tab_galleries', array( $this, 'galleries_tab' ) );
        add_action( 'envira_albums_tab_config', array( $this, 'config_tab' ) );
        add_action( 'envira_albums_tab_lightbox', array( $this, 'lightbox_tab' ) );
        add_action( 'envira_albums_tab_thumbnails', array( $this, 'thumbnails_tab' ) );
        add_action( 'envira_albums_tab_mobile', array( $this, 'mobile_tab' ) );
        add_action( 'envira_albums_tab_misc', array( $this, 'misc_tab' ) );

        // Add action to save metabox config options.
        add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2 );

    }

    /**
     * Loads styles for our metaboxes.
     *
     * @since 1.0.0
     *
     * @return null Return early if not on the proper screen.
     */
    public function meta_box_styles() {

        // Get Envira Gallery instance
        $instance = Envira_Gallery::get_instance();

        if ( 'post' !== get_current_screen()->base ) {
            return;
        }

        if ( isset( get_current_screen()->post_type ) && in_array( get_current_screen()->post_type, $this->get_skipped_posttypes() ) ) {
            return;
        }

        // Load necessary metabox styles from Envira Gallery
        wp_register_style( $instance->plugin_slug . '-metabox-style', plugins_url( 'assets/css/metabox.css', $instance->file ), array(), $instance->version );
        wp_enqueue_style( $instance->plugin_slug . '-metabox-style' );
        wp_enqueue_style( 'media-views' );

        // If WordPress version < 4.0, add attachment-details-modal-support.css
        // This contains the 4.0 CSS to make the attachment window display correctly
        $version = (float) get_bloginfo( 'version' );
        if ( $version < 4 ) {
            wp_register_style( $instance->plugin_slug . '-attachment-details-modal-support', plugins_url( 'assets/css/attachment-details-modal-support.css', $instance->file ), array(), $instance->version );
            wp_enqueue_style( $instance->plugin_slug . '-attachment-details-modal-support' );
        }

        // Load necessary metabox styles.
        wp_enqueue_style( $this->base->plugin_slug . '-metabox-style', plugins_url( 'assets/css/metabox.css', $this->base->file ), array(), $this->base->version );

        // Fire a hook to load in custom metabox styles.
        do_action( 'envira_album_metabox_styles' );
        
    }

    /**
     * Loads scripts for our metaboxes.
     *
     * @since 1.0.0
     *
     * @global int $id      The current post ID.
     * @global object $post The current post object.
     * @return null         Return early if not on the proper screen.
     */
    public function meta_box_scripts( $hook ) {

        global $id, $post;

        // Get Envira Gallery instance
        $instance = Envira_Gallery::get_instance();

        // Get screen details
        $screen = get_current_screen();

        // Don't load metabox scripts if not on a post type
        if ( !isset( $screen->post_type ) ) {
            return;
        }

        // Don't load metabox scripts if not on our CPT
        if ( $screen->post_type != 'envira_album') {
            return;
        }

        // Set the post_id for localization.
        $post_id = isset( $post->ID ) ? $post->ID : (int) $id;

        // Load WordPress necessary scripts.
        wp_enqueue_script( 'jquery-ui-draggable' );
        wp_enqueue_script( 'jquery-ui-droppable' );

        // Image Uploader (to get Yoast 3.x working)
        if ( $post_id > 0 ) {
            wp_enqueue_media( array( 
                'post' => $post_id, 
            ) );
        }

        // Tabs
        wp_register_script( $instance->plugin_slug . '-tabs-script', plugins_url( 'assets/js/tabs.js', $instance->file ), array( 'jquery' ), $instance->version, true );
        wp_enqueue_script( $instance->plugin_slug . '-tabs-script' );

        // Metabox
        wp_enqueue_script( $this->base->plugin_slug . '-metabox-script', plugins_url( 'assets/js/min/metabox-min.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
        wp_localize_script(
            $this->base->plugin_slug . '-metabox-script',
            'envira_albums_metabox',
            array(
                'ajax'           => admin_url( 'admin-ajax.php' ),
                'remove'         => __( 'Are you sure you want to remove this gallery from the album?', 'envira-albums' ),
                'id'             => $post_id,
                'sort'           => wp_create_nonce( 'envira-albums-sort' ),
                'saving'         => __( 'Saving', 'envira-albums' ),
                'search'         => wp_create_nonce( 'envira-albums-search' ),
            )
        );

        // If on an Envira post type, add custom CSS for hiding specific things.
        if ( isset( get_current_screen()->post_type ) && 'envira_album' == get_current_screen()->post_type ) {
            add_action( 'admin_head', array( $this, 'meta_box_css' ) );
        } 

        // Fire a hook to load in custom metabox scripts.
        do_action( 'envira_albums_metabox_scripts' );

    }

    /**
     * Returns the post types to skip for loading Envira metaboxes.
     *
     * @since 1.0.7
     *
     * @return array Array of skipped posttypes.
     */
    public function get_skipped_posttypes() {

        return apply_filters( 'envira_album_skipped_posttypes', array( 'attachment', 'revision', 'nav_menu_item', 'soliloquy', 'soliloquyv2' ) );

    }

    /**
     * Hides unnecessary meta box items on Envira post type screens.
     *
     * @since 1.0.0
     */
    public function meta_box_css() {

        ?>
        <style type="text/css">.misc-pub-section:not(.misc-pub-post-status) { display: none; }</style>
        <?php

        // Fire action for CSS on Envira post type screens.
        do_action( 'envira_gallery_admin_css' );

    }

    /**
     * Creates metaboxes for handling and managing galleries.
     *
     * @since 1.0.0
     */
    public function add_meta_boxes() {

        // Let's remove all of those dumb metaboxes from our post type screen to control the experience.
        $this->remove_all_the_metaboxes();

        add_meta_box( 'envira-albums', __( 'Envira Album Settings', 'envira-albums' ), array( $this, 'meta_box_callback' ), 'envira_album', 'normal', 'high' );

    }

     /**
     * Removes all the metaboxes except the ones I want on MY POST TYPE. RAGE.
     *
     * @since 1.0.0
     *
     * @global array $wp_meta_boxes Array of registered metaboxes.
     * @return smile $for_my_buyers Happy customers with no spammy metaboxes!
     */
    public function remove_all_the_metaboxes() {

        global $wp_meta_boxes;

        // This is the post type you want to target. Adjust it to match yours.
        $post_type  = 'envira_album';

        // These are the metabox IDs you want to pass over. They don't have to match exactly. preg_match will be run on them.
        $pass_over  = apply_filters( 'envira_albums_metabox_ids', array( 'submitdiv', 'envira' ) );

        // All the metabox contexts you want to check.
        $contexts   = apply_filters( 'envira_albums_metabox_contexts', array( 'normal', 'advanced', 'side' ) );

        // All the priorities you want to check.
        $priorities = apply_filters( 'envira_albums_metabox_priorities', array( 'high', 'core', 'default', 'low' ) );

        // Loop through and target each context.
        foreach ( $contexts as $context ) {
            // Now loop through each priority and start the purging process.
            foreach ( $priorities as $priority ) {
                if ( isset( $wp_meta_boxes[$post_type][$context][$priority] ) ) {
                    foreach ( (array) $wp_meta_boxes[$post_type][$context][$priority] as $id => $metabox_data ) {
                        // If the metabox ID to pass over matches the ID given, remove it from the array and continue.
                        if ( in_array( $id, $pass_over ) ) {
                            unset( $pass_over[$id] );
                            continue;
                        }

                        // Otherwise, loop through the pass_over IDs and if we have a match, continue.
                        foreach ( $pass_over as $to_pass ) {
                            if ( preg_match( '#^' . $id . '#i', $to_pass ) ) {
                                continue;
                            }
                        }

                        // If we reach this point, remove the metabox completely.
                        unset( $wp_meta_boxes[$post_type][$context][$priority][$id] );
                    }
                }
            }
        }

    }

    /**
     * Callback for displaying content in the registered metabox.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function meta_box_callback( $post ) {

        // Keep security first.
        wp_nonce_field( 'envira-albums', 'envira-albums' );

        // Check for our meta overlay helper.
        $album_data = get_post_meta( $post->ID, '_eg_album_data', true );
        $helper       = get_post_meta( $post->ID, '_eg_just_published', true );
        $class        = '';
        if ( $helper ) {
            $class = 'envira-helper-needed';
        }

        ?>
        <div id="envira-tabs" class="envira-clear <?php echo $class; ?>">
            <?php // $this->meta_helper( $post, $album_data ); ?>
            <ul id="envira-tabs-nav" class="envira-clear">
                <?php $i = 0; foreach ( (array) $this->get_envira_tab_nav() as $id => $title ) : $class = 0 === $i ? 'envira-active' : ''; ?>
                    <li class="<?php echo $class; ?>"><a href="#envira-tab-<?php echo $id; ?>" title="<?php echo $title; ?>"><?php echo $title; ?></a></li>
                <?php $i++; endforeach; ?>
            </ul>
            <?php $i = 0; foreach ( (array) $this->get_envira_tab_nav() as $id => $title ) : $class = 0 === $i ? 'envira-active' : ''; ?>
                <div id="envira-tab-<?php echo $id; ?>" class="envira-tab envira-clear <?php echo $class; ?>">
                    <?php do_action( 'envira_albums_tab_' . $id, $post ); ?>
                </div>
            <?php $i++; endforeach; ?>
        </div>
        <?php

    }

    /**
     * Callback for getting all of the tabs for Envira galleries.
     *
     * @since 1.0.0
     *
     * @return array Array of tab information.
     */
    public function get_envira_tab_nav() {

        $tabs = array(
            'galleries' => __( 'Galleries', 'envira-albums' ),
            'config'    => __(' Config', 'envira-albums' ),
            'lightbox'  => __( 'Lightbox', 'envira-albums' ),
            'thumbnails'=> __( 'Thumbnails', 'envira-albums' ),
            'mobile'    => __( 'Mobile', 'envira-albums' ),
            'misc'      => __(' Misc', 'envira-albums' ),
        );
        $tabs = apply_filters( 'envira_albums_tab_nav', $tabs );

        return $tabs;

    }

    /**
     * Callback for displaying the UI for main galleries tab.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function galleries_tab( $post ) {

        // Nonce field
        wp_nonce_field( 'envira-albums', 'envira-albums' );

        // Get all album data
        $album_data = get_post_meta( $post->ID, '_eg_album_data', true );
        
        // Output the album type selection items.
        ?>
        <ul id="envira-gallery-types-nav" class="envira-clear">
            <li class="envira-gallery-type-label"><span><?php _e( 'Album Type', 'envira-albums' ); ?></span></li>
            <?php $i = 0; foreach ( (array) $this->get_envira_types( $post ) as $id => $title ) : ?>
                <li><label for="envira-gallery-type-<?php echo $id; ?>"><input id="envira-gallery-type-<?php echo sanitize_html_class( $id ); ?>" type="radio" name="_eg_album_data[config][type]" value="<?php echo $id; ?>" <?php checked( $this->get_config( 'type', $this->get_config_default( 'type' ) ), $id ); ?> /> <?php echo $title; ?></label></li>
            <?php $i++; endforeach; ?>
        </ul>
        
        <?php
        $this->galleries_display( $this->get_config( 'type', $this->get_config_default( 'type' ) ), $post );

    }
    
    /**
     * Returns the types of albums available.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     * @return array       Array of gallery types to choose.
     */
    public function get_envira_types( $post ) {

        $types = array(
            'default' => __( 'Default', 'envira-albums' )
        );

        return apply_filters( 'envira_albums_types', $types, $post );

    }
    
    /**
     * Determines the Images tab display based on the type of gallery selected.
     *
     * @since 1.0.9
     *
     * @param string $type The type of display to output.
     * @param object $post The current post object.
     */
    public function galleries_display( $type = 'default', $post ) {

        // Output the display based on the type of slider available.
        switch ( $type ) {
            case 'default' :
                $this->do_default_display( $post );
                break;
            default:
                do_action( 'envira_albums_display_' . $type, $post );
                break;
        }

    }
    
    /**
     * Callback for displaying the default gallery UI.
     *
     * @since 1.0.9
     *
     * @param object $post The current post object.
     */
    public function do_default_display( $post ) {
        
        // Get all album data
        $album_data = get_post_meta( $post->ID, '_eg_album_data', true );
        
        // Store existing gallery IDs in a hidden field - this is populated by JS when
        // galleries are added/reordered/removed
        ?>
        <input type="hidden" name="galleryIDs" value="<?php echo ( ( isset($album_data['galleryIDs']) ? implode( ',', $album_data['galleryIDs'] ) : '' ) ); ?>" />

        <!-- Output the drag and drop interface with all available galleries. -->
        <div class="drag-drop">
            <div id="envira-gallery-main" class="envira-clear">
                <ul id="envira-album-drag-drop-area">
                    <p class="drag-drop-info<?php echo ( ( isset( $album_data['galleryIDs'] ) && count( $album_data['galleryIDs'] ) > 0 ) ? ' hidden' : '' ); ?>">
                        <?php _e( 'Drop galleries here', 'envira-albums' ); ?>
                    </p>

                    <?php
                    // Output existing galleries
                    if ( isset( $album_data['galleryIDs'] ) ) {
                        foreach ( $album_data['galleryIDs'] as $galleryID ) {
                            // Skip blank entries
                            if ( empty ($galleryID) ) {
                                continue;
                            }

                            $data = array();
                            if ( isset( $album_data['galleries'][ $galleryID ] ) ) {
                                $data = $album_data['galleries'][ $galleryID ];
                            }
                            $this->output_gallery_li( $galleryID, $data, $post->ID );
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>

        <?php
        // Output all other galleries not assigned to this album
        // Build arguments
        $arguments = array(
            'post_type'         => 'envira',
            'post_status'       => 'publish',
            'posts_per_page'    => 10,
            'orderby'           => 'title',
            'order'             => 'ASC',
        );

        // Exclude galleries we already included in this album
        if ( isset( $album_data['galleryIDs'] ) ) {
            $arguments['post__not_in'] = $album_data['galleryIDs'];
        }

        // Get galleries and output
        $galleries = new WP_Query( $arguments );
        $instance = Envira_Gallery::get_instance();
        ?>

        <ul id="envira-gallery-search">
            <li class="left">
                <strong><?php _e( 'Available Galleries', 'envira-albums' ); ?></strong>
                <span class="instructions">
                    <?php _e( 'Displaying the most recent Envira Galleries. Please use the search box to display all matching Envira Galleries.', 'envira-albums' ); ?>
                </span>
            </li>

            <li class="right">
                <input type="search" name="search" value="" placeholder="<?php _e( 'Search Galleries', 'envira-albums'); ?>" id="envira-albums-gallery-search" />
                <a href="#" class="button button-primary envira-galleries-add"><?php _e( 'Add Selected Galleries to Album', 'envira-albums' ); ?></a>
            </li>
        </ul>

        <!-- Search -->
        <ul id="envira-album-output" class="envira-clear">
            <?php
            if ( count( $galleries->posts ) > 0 ) {
                foreach ( $galleries->posts as $gallery ) {
                    // Get album metadata for this gallery
                    $data = $instance->get_gallery( $gallery->ID );

                    // Skip Default and Dynamic Galleries
                    if ( isset( $data['config']['type'] ) ) {
                        if ( $data['config']['type'] == 'dynamic' || $data['config']['type'] == 'defaults' ) {
                            continue;
                        }
                    }

                    // Output <li> element with media modal
                    $this->output_gallery_li( $gallery, $data, $post->ID );
                }
            }
            ?>
        </ul>
        <?php
            
    }

    /**
    * Outputs the <li> element for a gallery
    *
    * @param mixed $gallery     Gallery post object or Gallery ID
    * @param array $data        Metadata for gallery item
    * @param int $post_ID       Album ID
    * @return null
    */
    public function output_gallery_li( $gallery, $data, $post_ID ) {

        // Check if $gallery is an ID or object
        if ( is_numeric( $gallery ) ) {
            // Convert from ID to post object
            $gallery = get_post( $gallery );
        }

        // Get gallery metadata
        $gallery_data = $this->get_gallery_data( $gallery->ID );

        // Get gallery's cover image thumbnail
        $thumbnail = $this->get_gallery_cover_thumbnail( $data, $gallery_data );

        // If the thumbnail is an attachment ID, get its thumbnail URL
        if ( is_numeric( $thumbnail ) ) {
            $thumbnail = wp_get_attachment_image_src( $thumbnail, 'thumbnail' );
            $thumbnail = $thumbnail[0];
        }
        
        // Output
        ?>
        <li id="envira-gallery-<?php echo $gallery->ID; ?>" class="gallery" data-envira-gallery="<?php echo $gallery->ID; ?>" data-envira-gallery-title="<?php echo $gallery->post_title; ?>">
            <?php
            if ( ! empty( $thumbnail ) ) {
                ?>
                <img src="<?php echo esc_url( $thumbnail ); ?>" />
                <?php
            }
            ?>
            <a href="#" class="check"><div class="media-modal-icon"></div></a>
            <a href="#" class="envira-album-remove-gallery" title="<?php esc_attr_e( 'Remove Gallery from Album', 'envira-albums' ); ?>"></a>
            <a href="#" class="envira-album-modify-gallery" title="<?php esc_attr_e( 'Modify Gallery', 'envira-albums' ); ?>"></a>
            <span><?php echo $gallery->post_title; ?></span>
            <?php echo $this->get_album_item_meta( $gallery->ID, $data, $post_ID ); ?>
        </li>
        <?php

    }

    /**
    * Helper method to retrieve a Gallery, and run a filter which allows
    * Addons to populate the Gallery data if necessary - for example, the Dynamic
    * and Instagram Addons hook into this to tell us the available images
    * at the time of the query
    *
    * @since 1.2.4.3
    *
    * @param int    $gallery_id     Gallery ID
    * @return array                 Gallery
    */
    public function get_gallery_data( $gallery_id ) {

        // Get gallery data from Post Meta
        $data = get_post_meta( $gallery_id, '_eg_gallery_data', true );

        // Allow Addons to filter the information
        $data = apply_filters( 'envira_albums_metaboxes_get_gallery_data', $data, $gallery_id );

        // Return
        return $data;

    }

    /**
    * Returns the URL or Attachment ID of the gallery data's cover image.
    * If no cover image has been defined, returns the first available image's URL or Attachment ID
    * within the gallery
    *
    * @since 1.2.4.3
    *
    * @param array  $data           Album Gallery Data
    * @param array  $gallery_data   Gallery Data
    * @return mixed                 (string) Image URL / (int) Image ID
    */
    public function get_gallery_cover_thumbnail( $data, $gallery_data ) {

        // Get the first available image from the gallery, in case we need to use it
        // as the cover image
        if ( isset( $gallery_data['gallery'] ) && ! empty( $gallery_data['gallery'] ) ) {
            // Get the first image
            $images = $gallery_data['gallery'];
            reset( $images );
            $key = key( $images );
            $image = $images[ $key ];
        }

        // Depending on the type of gallery it is, get the thumbnail
        switch ( $gallery_data['config']['type'] ) {
            /**
            * Featured Content
            * Instagram
            */
            case 'fc':
            case 'instagram':
                // If a cover image URL is defined, return that
                if ( isset( $data['cover_image_url'] ) ) {
                    return $data['cover_image_url'];
                }

                // Return the first image's URL
                // Note that $image won't be populated if it's an FC/Instagram Gallery and that Addon is disabled,
                // because the Addon will populate the images array
                if ( isset( $image ) ) {
                    return $image['src'];
                }
                break;

            /**
            * Default
            */
            default:
                // Return the first image's attachment ID
                if ( isset ( $data['cover_image_id'] ) ) {
                    return $data['cover_image_id'];
                }

                // Return the first image's attachment ID
                return $key;
                break;

        }

    }

    /**
     * Inserts the meta icon for displaying useful gallery meta like shortcode and template tag.
     *
     * @since 1.0.0
     *
     * @param object $post        The current post object.
     * @param array $gallery_data Array of gallery data for the current post.
     * @return null               Return early if this is an auto-draft.
     */
    public function meta_helper( $post, $gallery_data ) {

        if ( isset( $post->post_status ) && 'auto-draft' == $post->post_status ) {
            return;
        }

        // Check for our meta overlay helper.
        $helper = get_post_meta( $post->ID, '_eg_just_published', true );
        $class  = '';
        if ( $helper ) {
            $class = 'envira-helper-active';
            delete_post_meta( $post->ID, '_eg_just_published' );
        }

        ?>
        <div class="envira-meta-helper <?php echo $class; ?>">
            <span class="envira-meta-close-text"><?php _e( '(click the icon to open and close the overlay dialog)', 'envira-albums' ); ?></span>
            <a href="#" class="envira-meta-icon" title="<?php esc_attr__( 'Click here to view meta information about this gallery.', 'envira-albums' ); ?>"></a>
            <div class="envira-meta-information">
                <p><?php _e( 'You can place this album anywhere into your posts, pages, custom post types or widgets by using <strong>one</strong> the shortcode(s) below:', 'envira-albums' ); ?></p>
                <code><?php echo '[envira-album id="' . $post->ID . '"]'; ?></code>
                <?php if ( ! empty( $album_data['config']['slug'] ) ) : ?>
                    <br><code><?php echo '[envira-album slug="' . $album_data['config']['slug'] . '"]'; ?></code>
                <?php endif; ?>
                <p><?php _e( 'You can also place this album into your template files by using <strong>one</strong> the template tag(s) below:', 'envira-albums' ); ?></p>
                <code><?php echo 'if ( function_exists( \'envira_album\' ) ) { envira_album( \'' . $post->ID . '\' ); }'; ?></code>
                <?php if ( ! empty( $album_data['config']['slug'] ) ) : ?>
                    <br><code><?php echo 'if ( function_exists( \'envira_album\' ) ) { envira_album( \'' . $album_data['config']['slug'] . '\', \'slug\' ); }'; ?></code>
                <?php endif; ?>
            </div>
        </div>
        <?php

    }

    /**
     * Helper method for retrieving the album metadata editing modal.
     *
     * @since 1.0.0
     *
     * @param int $id      The gallery ID.
     * @param array $data  Array of data for the gallery.
     * @param int $post_id The current post ID.
     * @return string      The HTML output for the album item.
     */
    public function get_album_item_meta( $id, $data, $post_id ) {

        // Get gallery data
        $gallery_data = $this->get_gallery_data( $id );

        // Get the cover image
        $current_cover_thumbnail = $this->get_gallery_cover_thumbnail( $data, $gallery_data );
        ?>
        <div id="envira-gallery-meta-<?php echo $id; ?>" class="envira-albums-meta-container" style="display:none;">
            <div class="media-modal wp-core-ui">
                <!-- Close -->
                <a class="media-modal-close" href="#"><span class="media-modal-icon"></span></a>

                <div class="media-modal-content">
                    <div class="edit-attachment-frame mode-select hide-menu hide-router envira-gallery-media-frame envira-gallery-meta-wrap">

                        <!-- Back / Next Buttons -->
                        <div class="edit-media-header">
                            <button class="left dashicons" data-attachment-id="">
                                <span class="screen-reader-text"><?php _e( 'Edit previous media item', 'envira-albums' ); ?></span>
                            </button>
                            <button class="right dashicons" data-attachment-id="">
                                <span class="screen-reader-text"><?php _e( 'Edit next media item', 'envira-albums' ); ?></span>
                            </button>
                        </div>

                        <!-- Title -->
                        <div class="media-frame-title">
                            <h1><?php _e( 'Edit Metadata', 'envira-albums' ); ?></h1>
                        </div>

                        <!-- Content -->
                        <div class="media-frame-content" id="envira-gallery-meta-table-<?php echo $id; ?>">
                            <div tabindex="0" role="checkbox" class="attachment-details save-ready">
                                <!-- Left -->
                                <div class="attachment-media-view portrait">
                                    <div class="thumbnail thumbnail-image">
                                        <!-- Hidden field to store chosen cover image -->
                                        <input type="hidden" class="envira-gallery-cover-image-id" name="_eg_album_data[galleries][<?php echo $id; ?>][cover_image_id]" value="<?php echo ( is_numeric( $current_cover_thumbnail ) ? $current_cover_thumbnail : '' ); ?>" data-envira-meta="cover_image_id" />
                                        <input type="hidden" class="envira-gallery-cover-image-url" name="_eg_album_data[galleries][<?php echo $id; ?>][cover_image_url]" value="<?php echo ( ! is_numeric( $current_cover_thumbnail ) ? $current_cover_thumbnail : '' ); ?>" data-envira-meta="cover_image_url" />

                                        <?php
                                        // Output all gallery images, so the user can choose which one is the cover
                                        if ( is_array( $gallery_data['gallery'] ) ) {
                                            ?>
                                            <ul>
                                                <?php
                                                foreach ( $gallery_data['gallery'] as $key => $image ) {
                                                    // Get the image URL for this image
                                                    switch ( $gallery_data['config']['type'] ) {
                                                        /**
                                                        * Featured Content
                                                        * Instagram
                                                        */
                                                        case 'fc':
                                                        case 'instagram':
                                                            // Get thumbnail from image src
                                                            $thumbnail = $image['src'];

                                                            // Determine if this is the selected cover thumbnail
                                                            $selected = ( ( $current_cover_thumbnail == $image['src'] ) ? true : false );

                                                            // Determine the data-key
                                                            $data_key = 'data-cover-image-url';
                                                            $data_value = $thumbnail;
                                                            break;

                                                        /**
                                                        * Default
                                                        */
                                                        default:
                                                            // Get thumbnail from attachment ID
                                                            $thumbnail = wp_get_attachment_image_src( $key, 'thumbnail' );
                                                            $thumbnail = $thumbnail[0];

                                                            // Determine if this is the selected cover thumbnail
                                                            $selected = ( ( $current_cover_thumbnail == $key ) ? true : false );

                                                            // Determine the data-key and value
                                                            $data_key = 'data-cover-image-id';
                                                            $data_value = $key;
                                                            break;

                                                    }
                                                    ?>
                                                    <li class="attachment<?php echo ( $selected ? ' details selected' : '' ); ?>" <?php echo $data_key; ?>="<?php echo $data_value; ?>">
                                                        <div class="attachment-preview landscape">
                                                            <div class="thumbnail">
                                                                <div class="inside">
                                                                    <img src="<?php echo $thumbnail; ?>" />
                                                                </div>
                                                            </div>
                                                            <a class="check" href="#">
                                                                <div class="media-modal-icon"></div>
                                                            </a>
                                                        </div>
                                                    </li>
                                                    <?php
                                                }
                                                ?>
                                            </ul>
                                            <?php
                                        }
                                        ?>

                                        <p class="description"><?php _e( 'Sets the image to display on this gallery\'s cover.', 'envira-albums' ); ?></p>
                                    </div>
                                </div>

                                <!-- Right -->
                                <div class="attachment-info">
                                    <!-- Details -->
                                    <div class="details">
                                        <div class="filename">
                                            <strong><?php _e( 'Album Cover', 'envira-albums' ); ?></strong>
                                            <p><?php _e( 'To choose the image to display as your album cover, click on an image to the left.', 'envira-albums' ); ?></p>

                                            <strong><?php _e( 'Gallery Titles', 'envira-albums' ); ?></strong>
                                            <p><?php _e( 'To display a different gallery title than its default, enter the title in the Gallery Title field.', 'envira-albums' ); ?></p>
                                        </div>
                                    </div>

                                    <?php do_action( 'envira_albums_before_meta_table', $id, $data, $post_id ); ?>
                                    <!-- Settings -->
                                    <div class="settings">
                                        <?php do_action( 'envira_albums_before_meta_settings', $id, $data, $post_id ); ?>

                                        <!-- Title -->
                                        <label class="setting">
                                            <span class="name"><?php _e( 'Title', 'envira-albums' ); ?></span>
                                            <input id="envira-gallery-title-<?php echo $id; ?>" class="envira-gallery-title" type="text" name="_eg_album_data[galleries][<?php echo $id; ?>][title]" value="<?php echo ( ! empty( $data['title'] ) ? esc_attr( $data['title'] ) : '' ); ?>" data-envira-meta="title" />
                                        </label>

                                        <!-- Caption -->
                                        <div class="setting">
                                            <span class="name"><?php _e( 'Caption', 'envira-albums' ); ?></span>                                              
                                            <?php 
                                            $caption = ( ! empty( $data['caption'] ) ? $data['caption'] : '' );
                                            wp_editor( $caption, 'envira-albums-caption-' . $id, array( 
                                                'media_buttons' => false, 
                                                'wpautop'       => false, 
                                                'tinymce'       => false, 
                                                'textarea_name' => '_eg_album_data[galleries][' . $id . '][caption]', 
                                                'quicktags' => array( 
                                                    'buttons' => 'strong,em,link,ul,ol,li,close' 
                                                ),
                                            ) ); 
                                            ?>   
                                        </div>

                                        <!-- Alt Text -->
                                        <label class="setting">
                                            <span class="name"><?php _e( 'Alt Text', 'envira-albums' ); ?></span>
                                            <input id="envira-gallery-alt-<?php echo $id; ?>" class="envira-gallery-alt" type="text" name="_eg_album_data[galleries][<?php echo $id; ?>][alt]" value="<?php echo ( ! empty( $data['alt'] ) ? esc_attr( $data['alt'] ) : '' ); ?>" data-envira-meta="alt" />
                                        </label>
                                        <?php do_action( 'envira_albums_after_meta_settings', $id, $data, $post_id ); ?>
                                    </div>
                                    <!-- /.settings -->

                                    <?php do_action( 'envira_albums_after_meta_table', $id, $data, $post_id ); ?>

                                    <!-- Actions -->
                                    <div class="actions">
                                        <a href="#" class="envira-gallery-meta-submit button media-button button-large button-primary media-button-insert" title="<?php esc_attr_e( 'Save Metadata', 'envira-albums' ); ?>" data-envira-gallery-item="<?php echo $id; ?>"><?php _e( 'Save Metadata', 'envira-albums' ); ?></a>

                                        <!-- Save Spinner -->
                                        <span class="settings-save-status">
                                            <span class="spinner"></span>
                                            <span class="saved"><?php _e( 'Saved.', 'envira-albums' ); ?></span>
                                        </span>
                                    </div>
                                    <!-- /.actions -->
                                </div>
                            </div>
                        </div>
                    </div><!-- end .media-frame -->
                </div><!-- end .media-modal-content -->
            </div><!-- end .media-modal -->

            <div class="media-modal-backdrop"></div>
        </div>

        <?php
    }

    /**
     * Callback for displaying the UI for setting album config options.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function config_tab( $post ) {

        ?>
        <div id="envira-config">
            <p class="envira-intro"><?php _e( 'The settings below adjust the basic configuration options for the album.', 'envira-albums' ); ?></p>
            <table class="form-table">
                <tbody>
                    <tr id="envira-config-columns-box">
                        <th scope="row">
                            <label for="envira-config-columns"><?php _e( 'Number of Album Columns', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <select id="envira-config-columns" name="_eg_album_data[config][columns]">
                                <?php foreach ( (array) $this->get_columns() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $this->get_config( 'columns', $this->get_config_default( 'columns' ) ) ); ?>><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Determines the number of columns in the album.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>

                    <!-- Back to Album Support -->
                    <tr id="envira-config-back-box">
                        <th scope="row">
                            <label for="envira-config-back"><?php _e( 'Display Back to Album Link?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-back" type="checkbox" name="_eg_album_data[config][back]" value="1" <?php checked( $this->get_config( 'back', $this->get_config_default( 'back' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'If enabled and Lightbox is disabled, when the visitor clicks on a Gallery in this Album, they will see a link at the top of the Gallery to return back to this Album.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>

                    <!-- Display Description -->
                    <tr id="envira-config-display-description-box">
                        <th scope="row">
                            <label for="envira-config-display-description"><?php _e( 'Display Album Description?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <select id="envira-config-display-description" name="_eg_album_data[config][description_position]">
                                <?php foreach ( (array) $this->get_display_description_options() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $this->get_config( 'description_position', $this->get_config_default( 'description_position' ) ) ); ?>><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Choose to display a description above or below this album\'s galleries.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>

                    <!-- Description -->
                    <tr id="envira-config-description-box">
                        <th scope="row">
                            <label for="envira-album-description"><?php _e( 'Album Description', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <?php
                            $description = $this->get_config( 'description' );
                            if ( empty( $description ) ) {
                                $description = $this->get_config_default( 'description' );
                            }
                            wp_editor( $description, 'envira-album-description', array(
                                'media_buttons' => false,
                                'wpautop'       => true,
                                'tinymce'       => true,
                                'textarea_name' => '_eg_album_data[config][description]',
                            ) );
                            ?>
                            <p class="description"><?php _e( 'The description to display for this album.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>

                    <!-- Display Gallery Titles -->
                    <tr id="envira-config-title-box">
                        <th scope="row">
                            <label for="envira-config-title"><?php _e( 'Display Gallery Titles?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-title" type="checkbox" name="_eg_album_data[config][display_titles]" value="1" <?php checked( $this->get_config( 'display_titles', $this->get_config_default( 'display_titles' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Displays gallery titles below each gallery image.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>

                    <!-- Display Gallery Caption -->
                    <tr id="envira-config-caption-box">
                        <th scope="row">
                            <label for="envira-config-caption"><?php _e( 'Display Gallery Captions?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-caption" type="checkbox" name="_eg_album_data[config][display_captions]" value="1" <?php checked( $this->get_config( 'display_captions', $this->get_config_default( 'display_captions' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Displays gallery captions below each gallery image.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>

                    <!-- Display Gallery Image Count -->
                    <tr id="envira-config-image-count-box">
                        <th scope="row">
                            <label for="envira-config-image-count"><?php _e( 'Display Gallery Image Count', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-image-count" type="checkbox" name="_eg_album_data[config][display_image_count]" value="1" <?php checked( $this->get_config( 'display_image_count', $this->get_config_default( 'display_image_count' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Displays the number of images in each gallery below each gallery image.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>

                    <!-- Gutter and Margin -->
                    <tr id="envira-config-gutter-box">
                        <th scope="row">
                            <label for="envira-config-gutter"><?php _e( 'Column Gutter Width', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-gutter" type="number" name="_eg_album_data[config][gutter]" value="<?php echo $this->get_config( 'gutter', $this->get_config_default( 'gutter' ) ); ?>" /> <span class="envira-unit"><?php _e( 'px', 'envira-albums' ); ?></span>
                            <p class="description"><?php _e( 'Sets the space between the columns (defaults to 10).', 'envira-albums' ); ?></p>
                        </td>
                    </tr>
                    <tr id="envira-config-margin-box">
                        <th scope="row">
                            <label for="envira-config-margin"><?php _e( 'Margin Below Each Image', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-margin" type="number" name="_eg_album_data[config][margin]" value="<?php echo $this->get_config( 'margin', $this->get_config_default( 'margin' ) ); ?>" /> <span class="envira-unit"><?php _e( 'px', 'envira-albums' ); ?></span>
                            <p class="description"><?php _e( 'Sets the space below each item in the album.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>

                    <!-- Sorting -->
                    <tr id="envira-config-sorting-box">
                        <th scope="row">
                            <label for="envira-config-sorting"><?php _e( 'Sorting', 'envira-albums' ); ?></label>
                        </th>
                        <td> 
                            <select id="envira-config-sorting" name="_eg_album_data[config][sorting]" data-envira-conditional="envira-config-sorting-direction-box">
                                <?php 
                                foreach ( (array) $this->get_sorting_options() as $i => $data ) {
                                    ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $this->get_config( 'sorting', $this->get_config_default( 'sorting' ) ) ); ?>><?php echo $data['name']; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                            <p class="description"><?php _e( 'Choose to sort the galleries in a different order than displayed on the Galleries tab.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>
                    <tr id="envira-config-sorting-direction-box">
                        <th scope="row">
                            <label for="envira-config-sorting-direction"><?php _e( 'Direction', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <select id="envira-config-sorting-direction" name="_eg_album_data[config][sorting_direction]">
                                <?php 
                                foreach ( (array) $this->get_sorting_directions() as $i => $data ) {
                                    ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $this->get_config( 'sorting_direction', $this->get_config_default( 'sorting_direction' ) ) ); ?>><?php echo $data['name']; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    
                    <!-- Image Sizes -->
                    <tr id="envira-config-crop-size-box">
                        <th scope="row">
                            <label for="envira-config-crop-width"><?php _e( 'Image Dimensions', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-crop-width" type="number" name="_eg_album_data[config][crop_width]" value="<?php echo $this->get_config( 'crop_width', $this->get_config_default( 'crop_width' ) ); ?>" /> &#215; <input id="envira-config-crop-height" type="number" name="_eg_album_data[config][crop_height]" value="<?php echo $this->get_config( 'crop_height', $this->get_config_default( 'crop_height' ) ); ?>" /> <span class="envira-unit"><?php _e( 'px', 'envira-albums' ); ?></span>
                            <p class="description"><?php _e( 'You should adjust these dimensions based on the number of columns in your album.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>
                    <tr id="envira-config-crop-box">
                        <th scope="row">
                            <label for="envira-config-crop"><?php _e( 'Crop Images?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-crop" type="checkbox" name="_eg_album_data[config][crop]" value="<?php echo $this->get_config( 'crop', $this->get_config_default( 'crop' ) ); ?>" <?php checked( $this->get_config( 'crop', $this->get_config_default( 'crop' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'If enabled, forces images to exactly match the sizes defined above for Image Dimensions.', 'envira-albums' ); ?></span>
                            <span class="description"><?php _e( 'If disabled, images will be resized to maintain their aspect ratio.', 'envira-albums' ); ?></span>
                            
                        </td>
                    </tr>
                    <tr id="envira-config-dimensions-box">
                        <th scope="row">
                            <label for="envira-config-dimensions"><?php _e( 'Set Dimensions on Images?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-dimensions" type="checkbox" name="_eg_album_data[config][dimensions]" value="<?php echo $this->get_config( 'dimensions', $this->get_config_default( 'dimensions' ) ); ?>" <?php checked( $this->get_config( 'dimensions', $this->get_config_default( 'dimensions' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables the width and height attributes on the img element. Only needs to be enabled if you need to meet Google Pagespeeds requirements.', 'envira-gallery' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-isotope-box">
                        <th scope="row">
                            <label for="envira-config-isotope"><?php _e( 'Enable Isotope?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-isotope" type="checkbox" name="_eg_album_data[config][isotope]" value="<?php echo $this->get_config( 'isotope', $this->get_config_default( 'isotope' ) ); ?>" <?php checked( $this->get_config( 'isotope', $this->get_config_default( 'isotope' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables isotope/masonry layout support for the main gallery images.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-css-animations-box">
                        <th scope="row">
                            <label for="envira-config-css-animations"><?php _e( 'Enable CSS Animations?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-css-animations" type="checkbox" name="_eg_album_data[config][css_animations]]" value="<?php echo $this->get_config( 'css_animations', $this->get_config_default( 'css_animations' ) ); ?>" <?php checked( $this->get_config( 'css_animations', $this->get_config_default( 'css_animations' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables CSS animations when loading the main gallery images.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    
                    <?php do_action( 'envira_albums_config_box', $post ); ?>
                </tbody>
            </table>
        </div>
        <?php

    }
    
    /**
     * Callback for displaying the UI for setting gallery lightbox options.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function lightbox_tab( $post ) {

        ?>
        <div id="envira-lightbox">
            <p class="envira-intro"><?php _e( 'The settings below adjust the lightbox outputs and displays.', 'envira-albums' ); ?></p>
            <table class="form-table">
                <tbody>
                    <tr id="envira-config-lightbox">
                        <th scope="row">
                            <label for="envira-config-lightbox"><?php _e( 'Enable Lightbox?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-lightbox" type="checkbox" name="_eg_album_data[config][lightbox]" value="<?php echo $this->get_config( 'lightbox', $this->get_config_default( 'lightbox' ) ); ?>" <?php checked( $this->get_config( 'lightbox', $this->get_config_default( 'lightbox' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'If checked, displays the Gallery in a lightbox when the album cover image is clicked.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-lightbox-theme">
                        <th scope="row">
                            <label for="envira-config-lightbox"><?php _e( 'Album Lightbox Theme', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <select id="envira-config-lightbox-theme" name="_eg_album_data[config][lightbox_theme]">
                                <?php foreach ( (array) $this->get_lightbox_themes() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $this->get_config( 'lightbox_theme', $this->get_config_default( 'lightbox_theme' ) ) ); ?>><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Sets the theme for the album lightbox display.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>
                    <tr id="envira-config-lightbox-title-display-box">
                        <th scope="row">
                            <label for="envira-config-lightbox-title-display"><?php _e( 'Caption Position', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <select id="envira-config-lightbox-title-display" name="_eg_album_data[config][title_display]">
                                <?php foreach ( (array) $this->get_title_displays() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $this->get_config( 'title_display', $this->get_config_default( 'title_display' ) ) ); ?>><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Sets the display of the lightbox image\'s caption.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>
                    <tr id="envira-config-lightbox-arrows-box">
                        <th scope="row">
                            <label for="envira-config-lightbox-arrows"><?php _e( 'Enable Gallery Arrows?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-lightbox-arrows" type="checkbox" name="_eg_album_data[config][arrows]" value="<?php echo $this->get_config( 'arrows', $this->get_config_default( 'arrows' ) ); ?>" <?php checked( $this->get_config( 'arrows', $this->get_config_default( 'arrows' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables the gallery lightbox navigation arrows.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-lightbox-arrows-position-box">
                        <th scope="row">
                            <label for="envira-config-lightbox-arrows-position"><?php _e( 'Gallery Arrow Position', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <select id="envira-config-lightbox-arrows-position" name="_eg_album_data[config][arrows_position]">
                                <?php foreach ( (array) $this->get_arrows_positions() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $this->get_config( 'arrows_position', $this->get_config_default( 'arrows_position' ) ) ); ?>><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Sets the position of the gallery lightbox navigation arrows.', 'envira-gallery' ); ?></p>
                        </td>
                    </tr>
                    <tr id="envira-config-lightbox-keyboard-box">
                        <th scope="row">
                            <label for="envira-config-lightbox-keyboard"><?php _e( 'Enable Keyboard Navigation?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-lightbox-keyboard" type="checkbox" name="_eg_album_data[config][keyboard]" value="<?php echo $this->get_config( 'keyboard', $this->get_config_default( 'keyboard' ) ); ?>" <?php checked( $this->get_config( 'keyboard', $this->get_config_default( 'keyboard' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables keyboard navigation in the gallery lightbox.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-lightbox-mousewheel-box">
                        <th scope="row">
                            <label for="envira-config-lightbox-mousewheel"><?php _e( 'Enable Mousewheel Navigation?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-lightbox-mousewheel" type="checkbox" name="_eg_album_data[config][mousewheel]" value="<?php echo $this->get_config( 'mousewheel', $this->get_config_default( 'mousewheel' ) ); ?>" <?php checked( $this->get_config( 'mousewheel', $this->get_config_default( 'mousewheel' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables mousewheel navigation in the gallery.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-lightbox-toolbar-box">
                        <th scope="row">
                            <label for="envira-config-lightbox-toolbar"><?php _e( 'Enable Gallery Toolbar?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-lightbox-toolbar" type="checkbox" name="_eg_album_data[config][toolbar]" value="<?php echo $this->get_config( 'toolbar', $this->get_config_default( 'toolbar' ) ); ?>" <?php checked( $this->get_config( 'toolbar', $this->get_config_default( 'toolbar' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables the gallery lightbox toolbar.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-lightbox-toolbar-title-box">
                        <th scope="row">
                            <label for="envira-config-lightbox-toolbar-title"><?php _e( 'Display Title in Gallery Toolbar?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-lightbox-toolbar-title" type="checkbox" name="_eg_album_data[config][toolbar_title]" value="<?php echo $this->get_config( 'toolbar_title', $this->get_config_default( 'toolbar_title' ) ); ?>" <?php checked( $this->get_config( 'toolbar_title', $this->get_config_default( 'toolbar_title' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Display the gallery title in the lightbox toolbar.', 'envira-albums' ); ?></span>
                        </td>
                    </tr> 
                    <tr id="envira-config-lightbox-toolbar-position-box">
                        <th scope="row">
                            <label for="envira-config-lightbox-toolbar-position"><?php _e( 'Gallery Toolbar Position', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <select id="envira-config-lightbox-toolbar-position" name="_eg_album_data[config][toolbar_position]">
                                <?php foreach ( (array) $this->get_toolbar_positions() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $this->get_config( 'toolbar_position', $this->get_config_default( 'toolbar_position' ) ) ); ?>><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Sets the position of the lightbox toolbar.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>
                    <tr id="envira-config-lightbox-aspect-box">
                        <th scope="row">
                            <label for="envira-config-lightbox-aspect"><?php _e( 'Keep Aspect Ratio?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-lightbox-toolbar" type="checkbox" name="_eg_album_data[config][aspect]" value="<?php echo $this->get_config( 'aspect', $this->get_config_default( 'aspect' ) ); ?>" <?php checked( $this->get_config( 'aspect', $this->get_config_default( 'aspect' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'If enabled, images will always resize based on the original aspect ratio.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-lightbox-loop-box">
                        <th scope="row">
                            <label for="envira-config-lightbox-loop"><?php _e( 'Loop Gallery Navigation?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-lightbox-loop" type="checkbox" name="_eg_album_data[config][loop]" value="<?php echo $this->get_config( 'loop', $this->get_config_default( 'loop' ) ); ?>" <?php checked( $this->get_config( 'loop', $this->get_config_default( 'loop' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables infinite navigation cycling of the lightbox gallery.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-lightbox-effect-box">
                        <th scope="row">
                            <label for="envira-config-lightbox-effect"><?php _e( 'Lightbox Transition Effect', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <select id="envira-config-lightbox-effect" name="_eg_album_data[config][effect]">
                                <?php foreach ( (array) $this->get_transition_effects() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $this->get_config( 'effect', $this->get_config_default( 'effect' ) ) ); ?>><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Type of transition between images in the lightbox view.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>
                    <tr id="envira-config-lightbox-html5-box">
                        <th scope="row">
                            <label for="envira-config-lightbox-html5"><?php _e( 'HTML5 Output?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-lightbox-html5" type="checkbox" name="_eg_album_data[config][html5]" value="<?php echo $this->get_config( 'html5', $this->get_config_default( 'html5' ) ); ?>" <?php checked( $this->get_config( 'html5', $this->get_config_default( 'html5' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'If enabled, uses data-envirabox-gallery instead of rel attributes for W3C HTML5 validation.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <?php do_action( 'envira_albums_lightbox_box', $post ); ?>
                </tbody>
            </table>
        </div>
        <?php

    }
    
    /**
     * Callback for displaying the UI for setting gallery thumbnail options.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function thumbnails_tab( $post ) {

        ?>
        <div id="envira-thumbnails">
            <p class="envira-intro"><?php _e( 'If enabled, thumbnails are generated automatically inside the lightbox. The settings below adjust the thumbnail views for the gallery lightbox display.', 'envira-albums' ); ?></p>
            <table class="form-table">
                <tbody>
                    <tr id="envira-config-thumbnails-box">
                        <th scope="row">
                            <label for="envira-config-thumbnails"><?php _e( 'Enable Gallery Thumbnails?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-thumbnails" type="checkbox" name="_eg_album_data[config][thumbnails]" value="<?php echo $this->get_config( 'thumbnails', $this->get_config_default( 'thumbnails' ) ); ?>" <?php checked( $this->get_config( 'thumbnails', $this->get_config_default( 'thumbnails' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables the gallery lightbox thumbnails.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-thumbnails-width-box">
                        <th scope="row">
                            <label for="envira-config-thumbnails-width"><?php _e( 'Gallery Thumbnails Width', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-thumbnails-width" type="number" name="_eg_album_data[config][thumbnails_width]" value="<?php echo $this->get_config( 'thumbnails_width', $this->get_config_default( 'thumbnails_width' ) ); ?>" /> <span class="envira-unit"><?php _e( 'px', 'envira-albums' ); ?></span>
                            <p class="description"><?php _e( 'Sets the width of each lightbox thumbnail.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>
                    <tr id="envira-config-thumbnails-height-box">
                        <th scope="row">
                            <label for="envira-config-thumbnails-height"><?php _e( 'Gallery Thumbnails Height', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-thumbnails-height" type="number" name="_eg_album_data[config][thumbnails_height]" value="<?php echo $this->get_config( 'thumbnails_height', $this->get_config_default( 'thumbnails_height' ) ); ?>" /> <span class="envira-unit"><?php _e( 'px', 'envira-albums' ); ?></span>
                            <p class="description"><?php _e( 'Sets the height of each lightbox thumbnail.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>
                    <tr id="envira-config-thumbnails-position-box">
                        <th scope="row">
                            <label for="envira-config-thumbnails-position"><?php _e( 'Gallery Thumbnails Position', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <select id="envira-config-thumbnails-position" name="_eg_album_data[config][thumbnails_position]">
                                <?php foreach ( (array) $this->get_thumbnail_positions() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $this->get_config( 'thumbnails_position', $this->get_config_default( 'thumbnails_position' ) ) ); ?>><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Sets the position of the lightbox thumbnails.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>
                    <?php do_action( 'envira_albums_thumbnails_box', $post ); ?>
                </tbody>
            </table>
        </div>
        <?php

    }

     /**
     * Callback for displaying the UI for setting mobile options.
     *
     * @since 1.2
     *
     * @param object $post The current post object.
     */
    public function mobile_tab( $post ) {

        ?>
        <div id="envira-mobile">
            <p class="envira-intro"><?php _e( 'The settings below adjust configuration options for the gallery and lightbox when viewed on a mobile device.', 'envira-gallery' ); ?></p>
            <table class="form-table">
                <tbody>
                    <!-- Mobile Images -->
                    <tr id="envira-config-mobile-box">
                        <th scope="row">
                            <label for="envira-config-mobile"><?php _e( 'Create Mobile Album Images?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-mobile" type="checkbox" name="_eg_album_data[config][mobile]" value="<?php echo $this->get_config( 'mobile', $this->get_config_default( 'mobile' ) ); ?>" <?php checked( $this->get_config( 'mobile', $this->get_config_default( 'mobile' ) ), 1 ); ?> data-envira-conditional="envira-config-mobile-size-box" />
                            <span class="description"><?php _e( 'Enables or disables creating specific images for mobile devices.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-mobile-size-box">
                        <th scope="row">
                            <label for="envira-config-mobile-width"><?php _e( 'Mobile Dimensions', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-mobile-width" type="number" name="_eg_album_data[config][mobile_width]" value="<?php echo $this->get_config( 'mobile_width', $this->get_config_default( 'mobile_width' ) ); ?>" /> &#215; <input id="envira-config-mobile-height" type="number" name="_eg_album_data[config][mobile_height]" value="<?php echo $this->get_config( 'mobile_height', $this->get_config_default( 'mobile_height' ) ); ?>" /> <span class="envira-unit"><?php _e( 'px', 'envira-gallery' ); ?></span>
                            <p class="description"><?php _e( 'These will be the sizes used for images displayed on mobile devices.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>

                    <!-- Lightbox -->
                    <tr id="envira-config-mobile-lightbox-box">
                        <th scope="row">
                            <label for="envira-config-mobile-lightbox"><?php _e( 'Enable Lightbox?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-mobile-lightbox" type="checkbox" name="_eg_album_data[config][mobile_lightbox]" value="<?php echo $this->get_config( 'mobile_lightbox', $this->get_config_default( 'mobile_lightbox' ) ); ?>" <?php checked( $this->get_config( 'mobile_lightbox', $this->get_config_default( 'mobile_lightbox' ) ), 1 ); ?> data-envira-conditional="envira-config-mobile-touchwipe-box,envira-config-mobile-touchwipe-close-box,envira-config-mobile-arrows-box,envira-config-mobile-toolbar-box,envira-config-mobile-thumbnails-box" />
                            <span class="description"><?php _e( 'Enables or disables the gallery lightbox on mobile devices.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-mobile-touchwipe-box">
                        <th scope="row">
                            <label for="envira-config-mobile-touchwipe"><?php _e( 'Enable Gallery Touchwipe?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-mobile-touchwipe" type="checkbox" name="_eg_album_data[config][mobile_touchwipe]" value="<?php echo $this->get_config( 'mobile_touchwipe', $this->get_config_default( 'mobile_touchwipe' ) ); ?>" <?php checked( $this->get_config( 'mobile_touchwipe', $this->get_config_default( 'mobile_touchwipe' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables touchwipe support for the gallery lightbox on mobile devices.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-mobile-touchwipe-close-box">
                        <th scope="row">
                            <label for="envira-config-mobile-touchwipe-close"><?php _e( 'Close Lightbox on Swipe Up?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-mobile-touchwipe-close" type="checkbox" name="_eg_album_data[config][mobile_touchwipe_close]" value="<?php echo $this->get_config( 'mobile_touchwipe_close', $this->get_config_default( 'mobile_touchwipe_close' ) ); ?>" <?php checked( $this->get_config( 'mobile_touchwipe_close', $this->get_config_default( 'mobile_touchwipe_close' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables closing the Lightbox when the user swipes up on mobile devices.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-mobile-arrows-box">
                        <th scope="row">
                            <label for="envira-config-mobile-arrows"><?php _e( 'Enable Gallery Arrows?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-mobile-arrows" type="checkbox" name="_eg_album_data[config][mobile_arrows]" value="<?php echo $this->get_config( 'mobile_arrows', $this->get_config_default( 'mobile_arrows' ) ); ?>" <?php checked( $this->get_config( 'mobile_arrows', $this->get_config_default( 'mobile_arrows' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables the gallery lightbox navigation arrows on mobile devices.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-mobile-toolbar-box">
                        <th scope="row">
                            <label for="envira-config-mobile-toolbar"><?php _e( 'Enable Gallery Toolbar?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-mobile-toolbar" type="checkbox" name="_eg_album_data[config][mobile_toolbar]" value="<?php echo $this->get_config( 'mobile_toolbar', $this->get_config_default( 'mobile_toolbar' ) ); ?>" <?php checked( $this->get_config( 'mobile_toolbar', $this->get_config_default( 'mobile_toolbar' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables the gallery lightbox toolbar on mobile devices.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-mobile-thumbnails-box">
                        <th scope="row">
                            <label for="envira-config-mobile-thumbnails"><?php _e( 'Enable Gallery Thumbnails?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-mobile-thumbnails" type="checkbox" name="_eg_album_data[config][mobile_thumbnails]" value="<?php echo $this->get_config( 'mobile_thumbnails', $this->get_config_default( 'mobile_toolbar' ) ); ?>" <?php checked( $this->get_config( 'mobile_thumbnails', $this->get_config_default( 'mobile_thumbnails' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables the gallery lightbox thumbnails on mobile devices.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    
                    <?php do_action( 'envira_albums_mobile_box', $post ); ?>
                </tbody>
            </table>
        </div>
        <?php

    }

    /**
     * Callback for displaying the UI for setting album miscellaneous options.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function misc_tab( $post ) {

        ?>
        <div id="envira-misc">
            <p class="envira-intro"><?php _e( 'The settings below adjust the miscellaneous settings for the album.', 'envira-albums' ); ?></p>
            <table class="form-table">
                <tbody>
                    <tr id="envira-config-title-box">
                        <th scope="row">
                            <label for="envira-config-title"><?php _e( 'Album Title', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-title" type="text" name="_eg_album_data[config][title]" value="<?php echo $this->get_config( 'title', $this->get_config_default( 'title' ) ); ?>" />
                            <p class="description"><?php _e( 'Internal album title for identification in the admin.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>
                    <tr id="envira-config-slug-box">
                        <th scope="row">
                            <label for="envira-config-slug"><?php _e( 'Album Slug', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-slug" type="text" name="_eg_album_data[config][slug]" value="<?php echo $this->get_config( 'slug', $this->get_config_default( 'slug' ) ); ?>" />
                            <p class="description"><?php _e( '<strong>Unique</strong> internal album slug for identification and advanced album queries.', 'envira-albums' ); ?></p>
                        </td>
                    </tr>
                    <tr id="envira-config-import-export-box">
                        <th scope="row">
                            <label for="envira-config-import-gallery"><?php _e( 'Import/Export Album', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <form></form>
                            <?php $import_url = 'auto-draft' == $post->post_status ? add_query_arg( array( 'post' => $post->ID, 'action' => 'edit', 'envira-album-imported' => true ), admin_url( 'post.php' ) ) : add_query_arg( 'envira-album-imported', true ); ?>
                            <form action="<?php echo $import_url; ?>" id="envira-config-import-album-form" class="envira-albums-import-form" method="post" enctype="multipart/form-data">
                                <input id="envira-config-import-album" type="file" name="envira_import_album" />
                                <input type="hidden" name="envira_albums_import" value="1" />
                                <input type="hidden" name="envira_post_id" value="<?php echo $post->ID; ?>" />
                                <?php wp_nonce_field( 'envira-albums-import', 'envira-albums-import' ); ?>
                                <?php submit_button( __( 'Import Album', 'envira-albums' ), 'secondary', 'envira-albums-import-submit', false ); ?>
                                <span class="spinner envira-gallery-spinner"></span>
                            </form>

                            <hr />
                            
                            <form id="envira-config-export-album-form" method="post">
                                <input type="hidden" name="envira_export" value="1" />
                                <input type="hidden" name="envira_post_id" value="<?php echo $post->ID; ?>" />
                                <?php wp_nonce_field( 'envira-albums-export', 'envira-albums-export' ); ?>
                                <?php submit_button( __( 'Export Album', 'envira-albums' ), 'secondary', 'envira-albums-export-submit', false ); ?>
                            </form>
                        </td>
                    </tr>
                    <tr id="envira-config-rtl-box">
                        <th scope="row">
                            <label for="envira-config-rtl"><?php _e( 'Enable RTL Support?', 'envira-albums' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-rtl" type="checkbox" name="_eg_album_data[config][rtl]" value="<?php echo $this->get_config( 'rtl', $this->get_config_default( 'rtl' ) ); ?>" <?php checked( $this->get_config( 'rtl', $this->get_config_default( 'rtl' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables RTL support in Envira for right-to-left languages.', 'envira-albums' ); ?></span>
                        </td>
                    </tr>
                    <?php do_action( 'envira_albums_misc_box', $post ); ?>
                </tbody>
            </table>
        </div>
        <?php

    }

    /**
     * Helper method for retrieving config values.
     *
     * @since 1.0.0
     *
     * @global int $id        The current post ID.
     * @global object $post   The current post object.
     * @param string $key     The config key to retrieve.
     * @param string $default A default value to use.
     * @return string         Key value on success, empty string on failure.
     */
    public function get_config( $key, $default = false ) {

        global $id, $post;

        // Get the current post ID. If ajax, grab it from the $_POST variable.
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            $post_id = absint( $_POST['post_id'] );
        } else {
            $post_id = isset( $post->ID ) ? $post->ID : (int) $id;
        }

        $settings = get_post_meta( $post_id, '_eg_album_data', true );
        if ( isset( $settings['config'][$key] ) ) {
            return $settings['config'][$key];
        } else {
            return $default ? $default : '';
        }

    }

     /**
     * Helper method for setting default config values.
     *
     * @since 1.0.0
     *
     * @param string $key The default config key to retrieve.
     * @return string Key value on success, false on failure.
     */
    public function get_config_default( $key ) {

        $instance = Envira_Albums_Common::get_instance();
        return $instance->get_config_default( $key );

    }

    /**
     * Helper method for retrieving columns.
     *
     * @since 1.0.0
     *
     * @return array Array of column data.
     */
    public function get_columns() {

        $instance = Envira_Gallery_Common::get_instance();
        return $instance->get_columns();

    }

    /**
     * Helper method for retrieving description options.
     *
     * @since 1.0.0
     *
     * @return array Array of description options.
     */
    public function get_display_description_options() {

        return array(
            array(
                'name'  => __( 'Do not display', 'envira-albums' ),
                'value' => 0,
            ),
            array(
                'name'  => __( 'Display above galleries', 'envira-albums' ),
                'value' => 'above',
            ),
            array(
                'name'  => __( 'Display below galleries', 'envira-albums' ),
                'value' => 'below',
            ),
        );

    }

    /**
     * Helper method for retrieving lightbox themes.
     *
     * @since 1.1.1
     *
     * @return array Array of lightbox theme data.
     */
    public function get_lightbox_themes() {

        $instance = Envira_Gallery_Common::get_instance();
        return $instance->get_lightbox_themes();

    }

    /**
     * Helper method for retrieving sorting options.
     *
     * @since 1.2.4.4
     *
     * @return array Array of sorting options.
     */
    public function get_sorting_options() {

        $instance = Envira_Albums_Common::get_instance();
        return $instance->get_sorting_options();

    }

    /**
     * Helper method for retrieving sorting directions.
     *
     * @since 1.2.4.4
     *
     * @return array Array of sorting directions.
     */
    public function get_sorting_directions() {

        $instance = Envira_Albums_Common::get_instance();
        return $instance->get_sorting_directions();

    }
    
    /**
     * Helper method for retrieving title displays.
     *
     * @since 1.0.0
     *
     * @return array Array of title display data.
     */
    public function get_title_displays() {

        $instance = Envira_Gallery_Common::get_instance();
        return $instance->get_title_displays();

    }

    /**
     * Helper method for retrieving arrow positions.
     *
     * @since 1.1.1
     *
     * @return array Array of title display data.
     */
    public function get_arrows_positions() {

        $instance = Envira_Gallery_Common::get_instance();
        return $instance->get_arrows_positions();

    }

    /**
     * Helper method for retrieving toolbar positions.
     *
     * @since 1.0.0
     *
     * @return array Array of toolbar position data.
     */
    public function get_toolbar_positions() {

        $instance = Envira_Gallery_Common::get_instance();
        return $instance->get_toolbar_positions();

    }

    /**
     * Helper method for retrieving lightbox transition effects.
     *
     * @since 1.0.0
     *
     * @return array Array of transition effect data.
     */
    public function get_transition_effects() {

        $instance = Envira_Gallery_Common::get_instance();
        return $instance->get_transition_effects();

    }

    /**
     * Helper method for retrieving thumbnail positions.
     *
     * @since 1.0.0
     *
     * @return array Array of thumbnail position data.
     */
    public function get_thumbnail_positions() {

        $instance = Envira_Gallery_Common::get_instance();
        return $instance->get_thumbnail_positions();

    }

    /**
     * Callback for saving values from Envira metaboxes.
     *
     * @since 1.0.0
     *
     * @param int $post_id The current post ID.
     * @param object $post The current post object.
     */
    public function save_meta_boxes( $post_id, $post ) {

        // Bail out if we fail a security check.
        if ( ! isset( $_POST['envira-albums'] ) || ! wp_verify_nonce( $_POST['envira-albums'], 'envira-albums' ) ) {
            return;
        }

        // Bail out if running an autosave, ajax, cron or revision.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

        if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        // Bail out if the user doesn't have the correct permissions to update the slider.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // If the post has just been published for the first time, set meta field for the album meta overlay helper.
        if ( isset( $post->post_date ) && isset( $post->post_modified ) && $post->post_date === $post->post_modified ) {
            update_post_meta( $post_id, '_eg_just_published', true );
        }

        // If the ID of the album is not set or is lost, replace it now.
        if ( empty( $settings['id'] ) || ! $settings['id'] ) {
            $settings['id'] = $post_id;
        }

        // Build $settings array, comprising of
        // - galleryIDs - an array of gallery IDs to include in this album
        // - galleries - an array of each gallery in galleryIDs above, each containing metadata (title etc)
        // - config - general configuration for this album

        // Convert gallery IDs to array
        if ( empty( $_POST['galleryIDs'] ) ) {
            unset( $settings['galleryIDs'] );
        } else {
            $settings['galleryIDs']                 = explode( ',', $_POST['galleryIDs'] );
            $settings['galleryIDs']                 = array_filter( $settings['galleryIDs'] );
        }

        // Iterate through each gallery in our album, storing the metadata
        if ( isset( $settings['galleryIDs'] ) && is_array( $settings['galleryIDs'] ) && count ( $settings['galleryIDs'] ) > 0 ) {
            foreach ( $settings['galleryIDs'] as $galleryID ) {
                // Init array
                if ( ! isset( $settings['galleries'] ) ) {
                    $settings['galleries'] = array();
                }
                if ( ! isset( $settings['galleries'][ $galleryID ] ) ) {
                    $settings['galleries'][ $galleryID ] = array();
                }

                // Add this gallery's settings
                $settings['galleries'][ $galleryID ]['title'] = sanitize_text_field( $_POST['_eg_album_data']['galleries'][ $galleryID ]['title'] );
                $settings['galleries'][ $galleryID ]['caption'] = trim( $_POST['_eg_album_data']['galleries'][ $galleryID ]['caption'] );
                $settings['galleries'][ $galleryID ]['alt'] = sanitize_text_field( $_POST['_eg_album_data']['galleries'][ $galleryID ]['alt'] );

                if ( isset( $_POST['_eg_album_data']['galleries'][ $galleryID ]['cover_image_id'] ) ) {
                    $settings['galleries'][ $galleryID ]['cover_image_id'] = absint( $_POST['_eg_album_data']['galleries'][ $galleryID ]['cover_image_id'] );
                }
                if ( isset( $_POST['_eg_album_data']['galleries'][ $galleryID ]['cover_image_url'] ) ) {
                    $settings['galleries'][ $galleryID ]['cover_image_url'] = sanitize_text_field( $_POST['_eg_album_data']['galleries'][ $galleryID ]['cover_image_url'] );
                }
            }
        }

        // Store album config
        $settings['config'] = array();
        $settings['config']['type']                = isset( $_POST['_eg_album_data']['config']['type'] ) ? $_POST['_eg_album_data']['config']['type'] : $this->get_config_default( 'type' );
        $settings['config']['columns']              = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_eg_album_data']['config']['columns'] );
        $settings['config']['back']                 = ( isset( $_POST['_eg_album_data']['config']['back'] ) ? 1 : 0 );
        $settings['config']['description_position'] = sanitize_text_field( $_POST['_eg_album_data']['config']['description_position'] );
        $settings['config']['description']          = trim( $_POST['_eg_album_data']['config']['description'] );
        $settings['config']['display_titles']       = ( isset( $_POST['_eg_album_data']['config']['display_titles'] ) ? 1 : 0 );
        $settings['config']['display_captions']     = ( isset( $_POST['_eg_album_data']['config']['display_captions'] ) ? 1 : 0 );
        $settings['config']['display_image_count']  = ( isset( $_POST['_eg_album_data']['config']['display_image_count'] ) ? 1 : 0 );
        $settings['config']['gutter']               = absint( $_POST['_eg_album_data']['config']['gutter'] );
        $settings['config']['margin']               = absint( $_POST['_eg_album_data']['config']['margin'] );
        $settings['config']['sorting']              = sanitize_text_field( $_POST['_eg_album_data']['config']['sorting'] );
        $settings['config']['sorting_direction']    = sanitize_text_field( $_POST['_eg_album_data']['config']['sorting_direction'] );
        $settings['config']['crop']                = isset( $_POST['_eg_album_data']['config']['crop'] ) ? 1 : 0;
        $settings['config']['dimensions']          = isset( $_POST['_eg_album_data']['config']['dimensions'] ) ? 1 : 0;
        $settings['config']['crop_width']          = absint( $_POST['_eg_album_data']['config']['crop_width'] );
        $settings['config']['crop_height']         = absint( $_POST['_eg_album_data']['config']['crop_height'] );
        $settings['config']['isotope']             = isset( $_POST['_eg_album_data']['config']['isotope'] ) ? 1 : 0;
        $settings['config']['css_animations']      = isset( $_POST['_eg_album_data']['config']['css_animations'] ) ? 1 : 0;

        // Lightbox
        $settings['config']['lightbox']             = isset( $_POST['_eg_album_data']['config']['lightbox'] ) ? 1 : 0;
        $settings['config']['lightbox_theme']       = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_eg_album_data']['config']['lightbox_theme'] );
        $settings['config']['title_display']        = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_eg_album_data']['config']['title_display'] );
        $settings['config']['arrows']               = isset( $_POST['_eg_album_data']['config']['arrows'] ) ? 1 : 0;
        $settings['config']['arrows_position']      = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_eg_album_data']['config']['arrows_position'] );
        $settings['config']['keyboard']             = isset( $_POST['_eg_album_data']['config']['keyboard'] ) ? 1 : 0;
        $settings['config']['mousewheel']           = isset( $_POST['_eg_album_data']['config']['mousewheel'] ) ? 1 : 0;
        $settings['config']['toolbar']              = isset( $_POST['_eg_album_data']['config']['toolbar'] ) ? 1 : 0;
        $settings['config']['toolbar_title']        = isset( $_POST['_eg_album_data']['config']['toolbar_title'] ) ? 1 : 0;
        $settings['config']['toolbar_position']     = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_eg_album_data']['config']['toolbar_position'] );
        $settings['config']['aspect']               = isset( $_POST['_eg_album_data']['config']['aspect'] ) ? 1 : 0;
        $settings['config']['loop']                 = isset( $_POST['_eg_album_data']['config']['loop'] ) ? 1 : 0;
        $settings['config']['effect']               = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_eg_album_data']['config']['effect'] );
        $settings['config']['html5']                = isset( $_POST['_eg_album_data']['config']['html5'] ) ? 1 : 0;
        
        // Thumbnails
        $settings['config']['thumbnails']           = isset( $_POST['_eg_album_data']['config']['thumbnails'] ) ? 1 : 0;
        $settings['config']['thumbnails_width']     = absint( $_POST['_eg_album_data']['config']['thumbnails_width'] );
        $settings['config']['thumbnails_height']    = absint( $_POST['_eg_album_data']['config']['thumbnails_height'] );
        $settings['config']['thumbnails_position']  = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_eg_album_data']['config']['thumbnails_position'] );
        
        // Mobile
        $settings['config']['mobile']              = isset( $_POST['_eg_album_data']['config']['mobile'] ) ? 1 : 0;
        $settings['config']['mobile_width']        = absint( $_POST['_eg_album_data']['config']['mobile_width'] );
        $settings['config']['mobile_height']       = absint( $_POST['_eg_album_data']['config']['mobile_height'] );
        $settings['config']['mobile_lightbox']     = isset( $_POST['_eg_album_data']['config']['mobile_lightbox'] ) ? 1 : 0;
        $settings['config']['mobile_touchwipe']    = isset( $_POST['_eg_album_data']['config']['mobile_touchwipe'] ) ? 1 : 0;
        $settings['config']['mobile_touchwipe_close'] = isset( $_POST['_eg_album_data']['config']['mobile_touchwipe_close'] ) ? 1 : 0;
        $settings['config']['mobile_arrows']       = isset( $_POST['_eg_album_data']['config']['mobile_arrows'] ) ? 1 : 0;
        $settings['config']['mobile_toolbar']      = isset( $_POST['_eg_album_data']['config']['mobile_toolbar'] ) ? 1 : 0;
        $settings['config']['mobile_thumbnails']   = isset( $_POST['_eg_album_data']['config']['mobile_thumbnails'] ) ? 1 : 0;

        // Store album misc
        $settings['config']['title']                = trim( strip_tags( $_POST['_eg_album_data']['config']['title'] ) );
        $settings['config']['slug']                 = sanitize_text_field( $_POST['_eg_album_data']['config']['slug'] );
        $settings['config']['classes']              = ( isset ($_POST['_eg_album_data']['config']['classes'] ) ? explode( "\n", $_POST['_eg_album_data']['config']['classes'] ) : '' );
        $settings['config']['rtl']                  = ( isset( $_POST['_eg_album_data']['config']['rtl'] ) ? 1 : 0 );

        // If on an envira post type, map the title and slug of the post object to the custom fields if no value exists yet.
        if ( isset( $post->post_type ) && 'envira_album' == $post->post_type ) {
            if ( empty( $settings['config']['title'] ) ) {
                $settings['config']['title'] = trim( strip_tags( $post->post_title ) );
            }
            if ( empty( $settings['config']['slug'] ) ) {
                $settings['config']['slug']  = sanitize_text_field( $post->post_name );
            }
        }

        // Provide a filter to override settings.
        $settings = apply_filters( 'envira_albums_save_settings', $settings, $post_id, $post );

        // Update the post meta.
        update_post_meta( $post_id, '_eg_album_data', $settings );

        // Fire a hook for addons that need to utilize the cropping feature.
        do_action( 'envira_albums_saved_settings', $settings, $post_id, $post );

        // Finally, flush all gallery caches to ensure everything is up to date.
        $this->flush_album_caches( $post_id, $settings['config']['slug'] );

    }

    /**
     * Helper method to flush gallery caches once a gallery is updated.
     *
     * @since 1.0.0
     *
     * @param int $post_id The current post ID.
     * @param string $slug The unique album slug.
     */
    public function flush_album_caches( $post_id, $slug ) {

        Envira_Albums_Common::get_instance()->flush_album_caches( $post_id, $slug );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Albums_Metaboxes object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Albums_Metaboxes ) ) {
            self::$instance = new Envira_Albums_Metaboxes();
        }

        return self::$instance;

    }

}

// Load the metabox class.
$envira_albums_metaboxes = Envira_Albums_Metaboxes::get_instance();
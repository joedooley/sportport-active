<?php
/**
 * Metabox class.
 *
 * @since 1.3.0
 *
 * @package Envira_Tags
 * @author  Tim Carr
 */
class Envira_Tags_Metaboxes {

    /**
     * Holds the class object.
     *
     * @since 1.3.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.3.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.3.0
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
        $this->base = Envira_Tags::get_instance();
        
        add_action( 'envira_gallery_metabox_scripts', array( $this, 'scripts' ) );
        add_action( 'envira_gallery_do_default_display', array( $this, 'add_tag_multiple_images_button' ) );
        add_filter( 'envira_gallery_get_gallery_item', array( $this, 'get_tags' ), 10, 3 );
        add_filter( 'envira_gallery_tab_nav', array( $this, 'tabs' ) );
        add_action( 'envira_gallery_tab_tags', array( $this, 'settings' ) );
        add_filter( 'envira_gallery_save_settings', array( $this, 'save_settings' ), 10, 2 );
        add_action( 'envira_gallery_flush_caches', array( $this, 'flush_caches' ), 10, 2 );
        
    }

    /**
     * Loads scripts for our metaboxes.
     *
     * @since 1.0.5
     *
     * @return null
    */
    function scripts() {
 
        // Load necessary metabox styles.
        wp_enqueue_style( $this->base->plugin_slug . '-tags-style', plugins_url( 'assets/css/metabox.css', plugin_basename( $this->base->file ) ), array(), $this->base->version );

        // Enqueue assets/js/metabox.js
        wp_enqueue_script( $this->base->plugin_slug . '-tags-script', plugins_url( 'assets/js/min/metabox-min.js', plugin_basename( $this->base->file ) ), array( 'jquery' ), $this->base->version, true );
        wp_localize_script( $this->base->plugin_slug . '-tags-script', 'envira_tags', array(
            'multiple'  => __( 'Enter one or more tags, separated by commas. These will be applied to the selected images.', 'envira-tags' ),
            'nonce'     => wp_create_nonce( 'envira-tags-nonce' ),
        ) );

        // Enqueue assets/js/media-edit.js
        wp_enqueue_script( $this->base->plugin_slug . '-media-edit-script', plugins_url( 'assets/js/media-edit.js', plugin_basename( $this->base->file ) ), array( 'jquery' ), $this->base->version, true );
        
    }

    /**
    * Outputs an "Add Tags" button above the Images grid on the Images tab
    *
    * @since 1.1.9
    *
    * @param WP_Post $post Gallery Post
    */
    public function add_tag_multiple_images_button( $post ) {

        ?>
        <a href="#" class="button button-primary envira-tags-multiple"><?php _e( 'Add Tag(s) to Selected Images', 'envira-tags' ); ?></a>
        <?php

    }

    /**
     * Adds the item's tags to the gallery item array, so that the Media View can pass them via JSON
     * to the modal
     *
     * @since 1.2.5
     *
     * @param array     $item       Gallery Item
     * @param int       $attach_id  Attachment ID
     * @param int       $post_id    Gallery ID
     * @return array                Gallery Item
     */
    public function get_tags( $item, $attach_id, $post_id ) {

        // Build tags by getting them from the attachment
        $item['tags'] = '';
        
        // Check tags exist
        $tags = wp_get_object_terms( $attach_id, 'envira-tag' );
        if ( is_wp_error( $tags ) || empty( $tags ) || count( $tags ) == 0 ) {
            return $item;
        }

        // Build string of tags
        foreach ( $tags as $tag ) {
            $item['tags'] .= $tag->name . ', ';
        }

        // Trim the string
        $item['tags'] = rtrim( $item['tags'], ', ' );

        // Return
        return $item;

    }


    /**
     * Adds a new tab for this addon.
     *
     * @since 1.1.0
     *
     * @param array $tabs  Array of default tab values.
     * @return array $tabs Amended array of default tab values.
     */
    public function tabs( $tabs ) {

        $tabs['tags'] = __( 'Tags', 'envira-tags' );
        return $tabs;

    }


    /**
     * Adds addon setting to the Misc tab.
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function settings( $post ) {

        $instance = Envira_Gallery_Metaboxes::get_instance();
        $tags = get_terms( 'envira-tag', array( 
            'number'    => 5, 
            'orderby'   => 'count', 
            'order'     => 'DESC' 
        ) );
        if ( is_array( $tags ) ) {
            foreach ( $tags as $key => $tag ) {
                $tags[ $key ]->link = '#';
            }
        }
        ?>
        <div id="envira-tags">
            <p class="envira-intro"><?php _e( 'The settings below adjust the Tags settings.', 'envira-tags' ); ?></p>
            <table class="form-table">
                <tbody>             
                    <tr id="envira-config-tags-box">
                        <th scope="row">
                            <label for="envira-config-tags"><?php _e( 'Enable Tag Filtering?', 'envira-tags' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-tags" type="checkbox" name="_envira_gallery[tags]" value="<?php echo $instance->get_config( 'tags', $instance->get_config_default( 'tags' ) ); ?>" <?php checked( $instance->get_config( 'tags', $instance->get_config_default( 'tags' ) ), 1 ); ?> data-envira-conditional="envira-config-tags-filtering-box,envira-config-tags-all-box" />
                            <span class="description"><?php _e( 'Enables or disables tag filtering for the gallery display.', 'envira-tags' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-tags-filtering-box">
                        <th scope="row">
                            <label for="envira-config-tags-filtering"><?php _e( 'Tags to include in Filtering', 'envira-tags' ); ?></label>
                        </th>
                        <td>
                            <?php
                            // Output tag meta box
                            post_tags_meta_box( $post, array(
                                    'args' => array(
                                        'taxonomy'  => 'envira-tag',
                                        'title'     => __( 'Image Tags', 'envira-tags' ),
                                    ),
                                    'title'     => __( 'Image Tags', 'envira-tags' ),
                                )
                            );
                
                            // Most Popular Tags
                            if ( is_array( $tags ) ) {
                                ?>
                                <p class="the-tagcloud">
                                    <?php
                                    echo wp_generate_tag_cloud( $tags, array(
                                        'filter' => 0
                                    ) );
                                    ?>
                                </p>
                                <?php
                            }
                            
                            // Output hidden field containing current taxonomy terms
                            ?>
                            <input type="hidden" class="envira-gallery-tags" name="_envira_gallery[tags_filter]" value="<?php echo $instance->get_config( 'tags_filter', $instance->get_config_default( 'tags_filter' ) ); ?>" />
                            <span class="description"><?php _e( 'Optionally define which tags to display. If none are set, the list of tags will be automatically generated based on the image tags.', 'envira-tags' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-config-tags-all-box">
                        <th scope="row">
                            <label for="envira-config-tags-all"><?php _e( 'All Tags Label', 'envira-tags' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-tags-all" type="text" name="_envira_gallery[tags_all]" value="<?php echo $instance->get_config( 'tags_all', $instance->get_config_default( 'tags_all' ) ); ?>" />
                            <p class="description"><?php _e( 'The label to display for the All Tags link.', 'envira-tags' ); ?></p>
                        </td>
                    </tr>
                    <?php
                    if ( $instance->get_config( 'type' ) == 'dynamic' ) {
                        ?>
                        <tr id="envira-config-tags-limit-box">
                            <th scope="row">
                                <label for="envira-config-tags-limit"><?php _e( 'Number of Images', 'envira-tags' ); ?></label>
                            </th>
                            <td>
                                <input id="envira-config-tags-limit" type="text" name="_envira_gallery[tags_limit]" value="<?php echo $instance->get_config( 'tags_limit', $instance->get_config_default( 'tags_limit' ) ); ?>" />
                                <p class="description"><?php _e( 'Limit the number of images to display when using the Dynamic Addon. Zero = all images will be displayed.', 'envira-tags' ); ?></p>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    <?php do_action( 'envira_tags_tag_box', $post ); ?>
                </tbody>
            </table>
        </div>
        <?php

    }

    /**
     * Saves the addon setting.
     *
     * @since 1.0.0
     *
     * @param array $settings  Array of settings to be saved.
     * @param int $post_id     The current post ID.
     * @return array $settings Amended array of settings to be saved.
     */
    function save_settings( $settings, $post_id ) {

        $settings['config']['tags'] = isset( $_POST['_envira_gallery']['tags'] ) ? 1 : 0;
        $settings['config']['tags_filter'] = sanitize_text_field( $_POST['_envira_gallery']['tags_filter'] );
        $settings['config']['tags_all'] = sanitize_text_field( $_POST['_envira_gallery']['tags_all'] );
        if ( isset( $_POST['_envira_gallery']['tags_limit'] ) ) {
            $settings['config']['tags_limit'] = absint( $_POST['_envira_gallery']['tags_limit'] );
        }
        
        return $settings;

    }

    /**
     * Flushes the tag gallery cache.
     *
     * @since 1.0.0
     *
     * @global object $wpdb The WordPress database object.
     */
    function flush_caches() {

        global $wpdb;
        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE (%s)", '%\_eg\_tags\_%' ) );

    }
            
    
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.3.0
     *
     * @return object The Envira_Tags_Metaboxes object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Metaboxes ) ) {
            self::$instance = new Envira_Tags_Metaboxes();
        }

        return self::$instance;

    }

}

// Load the metaboxes class.
$envira_tags_metaboxes = Envira_Tags_Metaboxes::get_instance();
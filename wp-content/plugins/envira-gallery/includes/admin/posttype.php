<?php
/**
 * Posttype admin class.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @author  Thomas Griffin
 */
class Envira_Gallery_Posttype_Admin {

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
     * Holds the base class object.
     *
     * @since 1.3.2.1
     *
     * @var object
     */
    public $metaboxes;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Envira_Gallery::get_instance();
        $this->metabox = Envira_Gallery_Metaboxes::get_instance();

        // Manage post type columns.
        add_filter( 'manage_edit-envira_columns', array( $this, 'envira_columns' ) );
        add_filter( 'manage_envira_posts_custom_column', array( $this, 'envira_custom_columns' ), 10, 2 );

        // Update post type messages.
        add_filter( 'post_updated_messages', array( $this, 'messages' ) );

        // Force the menu icon to be scaled to proper size (for Retina displays).
        add_action( 'admin_head', array( $this, 'menu_icon' ) );

    }

    /**
     * Customize the post columns for the Envira post type.
     *
     * @since 1.0.0
     *
     * @param array $columns  The default columns.
     * @return array $columns Amended columns.
     */
    public function envira_columns( $columns ) {

        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'title'     => __( 'Title', 'envira-gallery' ),
            'shortcode' => __( 'Shortcode', 'envira-gallery' ),
            'template'  => __( 'Function', 'envira-gallery' ),
            'images'    => __( 'Number of Images', 'envira-gallery' ),
            'posts'     => __( 'Posts', 'envira-gallery' ),
            'modified'  => __( 'Last Modified', 'envira-gallery' ),
            'date'      => __( 'Date', 'envira-gallery' )
        );

        return $columns;

    }

    /**
     * Add data to the custom columns added to the Envira post type.
     *
     * @since 1.0.0
     *
     * @global object $post  The current post object
     * @param string $column The name of the custom column
     * @param int $post_id   The current post ID
     */
    public function envira_custom_columns( $column, $post_id ) {

        global $post;
        $post_id = absint( $post_id );

        switch ( $column ) {
            case 'shortcode' :
                echo '<code>[envira-gallery id="' . $post_id . '"]</code>';

                // Hidden fields are for Quick Edit
                // class is used by assets/js/admin.js to remove these fields when a search is about to be submitted, so we dont' get long URLs
                echo '<input class="envira-quick-edit" type="hidden" name="_envira_gallery_' . $post_id . '[columns]" value="' . $this->metabox->get_config( 'columns' ) . '" />
                <input class="envira-quick-edit" type="hidden" name="_envira_gallery_' . $post_id . '[gallery_theme]" value="' . $this->metabox->get_config( 'gallery_theme' ) . '" />
                <input class="envira-quick-edit" type="hidden" name="_envira_gallery_' . $post_id . '[gutter]" value="' . $this->metabox->get_config( 'gutter' ) . '" />
                <input class="envira-quick-edit" type="hidden" name="_envira_gallery_' . $post_id . '[margin]" value="' . $this->metabox->get_config( 'margin' ) . '" />
                <input class="envira-quick-edit" type="hidden" name="_envira_gallery_' . $post_id . '[crop_width]" value="' . $this->metabox->get_config( 'crop_width' ) . '" />
                <input class="envira-quick-edit" type="hidden" name="_envira_gallery_' . $post_id . '[crop_height]" value="' . $this->metabox->get_config( 'crop_height' ) . '" />';
                break;

            case 'template' :
                echo '<code>if ( function_exists( \'envira_gallery\' ) ) { envira_gallery( \'' . $post_id . '\' ); }</code>';
                break;

            case 'images' :
                $gallery_data = get_post_meta( $post_id, '_eg_gallery_data', true );
                echo ( ! empty( $gallery_data['gallery'] ) ? count( $gallery_data['gallery'] ) : 0 );
                break;

            case 'posts':
                $posts = get_post_meta( $post_id, '_eg_in_posts', true );
                if ( is_array( $posts ) ) {
                    foreach ( $posts as $in_post_id ) {
                        echo '<a href="' . get_permalink( $in_post_id ) . '" target="_blank">' . get_the_title( $in_post_id ).'</a><br />';
                    }
                }
                break;

            case 'modified' :
                the_modified_date();
                break;
        }

    }

    /**
     * Contextualizes the post updated messages.
     *
     * @since 1.0.0
     *
     * @global object $post    The current post object.
     * @param array $messages  Array of default post updated messages.
     * @return array $messages Amended array of post updated messages.
     */
    public function messages( $messages ) {

        global $post;

        // Contextualize the messages.
        $envira_messages = array(
            0  => '',
            1  => __( 'Envira gallery updated.', 'envira-gallery' ),
            2  => __( 'Envira gallery custom field updated.', 'envira-gallery' ),
            3  => __( 'Envira gallery custom field deleted.', 'envira-gallery' ),
            4  => __( 'Envira gallery updated.', 'envira-gallery' ),
            5  => isset( $_GET['revision'] ) ? sprintf( __( 'Envira gallery restored to revision from %s.', 'envira-gallery' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6  => __( 'Envira gallery published.', 'envira-gallery' ),
            7  => __( 'Envira gallery saved.', 'envira-gallery' ),
            8  => __( 'Envira gallery submitted.', 'envira-gallery' ),
            9  => sprintf( __( 'Envira gallery scheduled for: <strong>%1$s</strong>.', 'envira-gallery' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
            10 => __( 'Envira gallery draft updated.', 'envira-gallery' ),
        );
        $messages['envira'] = apply_filters( 'envira_gallery_messages', $envira_messages );

        return $messages;

    }

    /**
     * Forces the Envira menu icon width/height for Retina devices.
     *
     * @since 1.0.0
     */
    public function menu_icon() {

        ?>
        <style type="text/css">#menu-posts-envira .wp-menu-image img { width: 16px; height: 16px; }</style>
        <?php

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Gallery_Posttype_Admin object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Gallery_Posttype_Admin ) ) {
            self::$instance = new Envira_Gallery_Posttype_Admin();
        }

        return self::$instance;

    }

}

// Load the posttype admin class.
$envira_gallery_posttype_admin = Envira_Gallery_Posttype_Admin::get_instance();
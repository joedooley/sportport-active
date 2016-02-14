<?php
/**
 * Posttype admin class.
 *
 * @since 1.0.0
 *
 * @package Envira_Gallery
 * @author  Thomas Griffin
 */
class Envira_Albums_Posttype_Admin {

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

        // Remove quick editing from the Envira post type row actions.
        // add_filter( 'post_row_actions', array( $this, 'row_actions' ), 10, 2 );

        // Manage post type columns.
        add_filter( 'manage_edit-envira_album_columns', array( $this, 'envira_columns' ) );
        add_filter( 'manage_envira_album_posts_custom_column', array( $this, 'envira_custom_columns' ), 10, 2 );

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
            'title'     => __( 'Title', 'envira-albums' ),
            'shortcode' => __( 'Shortcode', 'envira-albums' ),
            'template'  => __( 'Function', 'envira-albums' ),
            'galleries'    => __( 'Number of Galleries', 'envira-albums' ),
            'modified'  => __( 'Last Modified', 'envira-albums' ),
            'date'      => __( 'Date', 'envira-albums' )
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
                echo '<code>[envira-album id="' . $post_id . '"]</code>';
                break;

            case 'template' :
                echo '<code>if ( function_exists( \'envira_album\' ) ) { envira_album( \'' . $post_id . '\' ); }</code>';
                break;

            case 'galleries' :
            	$data = get_post_meta( $post_id, '_eg_album_data', true);
            	echo ( isset( $data['galleryIDs'] ) ? count( $data['galleryIDs'] ) : 0 );
                break;

            case 'modified' :
                the_modified_date();
                break;
        }

    }

    /**
     * Filter out unnecessary row actions from the Envira post table.
     *
     * @since 1.0.0
     *
     * @param array $actions  Default row actions.
     * @param object $post    The current post object.
     * @return array $actions Amended row actions.
     */
    public function row_actions( $actions, $post ) {

        if ( isset( get_current_screen()->post_type ) && 'envira' == get_current_screen()->post_type ) {
            unset( $actions['inline hide-if-no-js'] );
        }

        return apply_filters( 'envira_albums_row_actions', $actions, $post );

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
        $messages['envira_album'] = apply_filters( 'envira_album_messages',
            array(
                0  => '',
                1  => __( 'Envira album updated.', 'envira-album' ),
                2  => __( 'Envira album custom field updated.', 'envira-album' ),
                3  => __( 'Envira album custom field deleted.', 'envira-album' ),
                4  => __( 'Envira album updated.', 'envira-album' ),
                5  => isset( $_GET['revision'] ) ? sprintf( __( 'Envira album restored to revision from %s.', 'envira-albums' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
                6  => __( 'Envira album published.', 'envira-albums' ),
                7  => __( 'Envira album saved.', 'envira-albums' ),
                8  => __( 'Envira album submitted.', 'envira-albums' ),
                9  => sprintf( __( 'Envira album scheduled for: <strong>%1$s</strong>.', 'envira-albums' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
                10 => __( 'Envira album draft updated.', 'envira-albums' )
            )
        );

        return $messages;

    }

    /**
     * Forces the Envira menu icon width/height for Retina devices.
     *
     * @since 1.0.0
     */
    public function menu_icon() {

        ?>
        <style type="text/css">#menu-posts-envira-albums .wp-menu-image img { width: 16px; height: 16px; }</style>
        <?php

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Albums_Posttype_Admin object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Albums_Posttype_Admin ) ) {
            self::$instance = new Envira_Albums_Posttype_Admin();
        }

        return self::$instance;

    }

}

// Load the posttype admin class.
$envira_albums_posttype_admin = Envira_Albums_Posttype_Admin::get_instance();
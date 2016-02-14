<?php
/**
 * Admin class.
 *
 * @since 1.3.0
 *
 * @package Envira_Tags
 * @author  Tim Carr
 */
class Envira_Tags_Admin {

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
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        add_filter( 'manage_edit-envira-tag_columns', array( $this, 'columns' ) );
        add_action( 'admin_menu', array( $this, 'menu' ) );

    }

    /**
     * Removes the 'Count' column from the Envira Tag taxonomy in the WordPress Administration interface
     *
     * @since 1.0.5
     *
     * @param array $columns Taxonomy Columns
     * @return array New Taxonomy Columns
    */
    function columns( $columns ) {

        // Remove 'Count' column
        unset( $columns['posts'] );

        return $columns;

    }

    /**
     * Moves image taxonomy menu item from Media to Envira Gallery.
     *
     * @since 1.0.5
     *
     * @return null
    */
    function menu() {

        add_submenu_page( 'edit.php?post_type=envira', __( 'Tags', 'envira-tags'), __( 'Tags', 'envira-tags'), 'edit_others_posts', 'edit-tags.php?taxonomy=envira-tag&post_type=envira');

    }


    
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.3.0
     *
     * @return object The Envira_Tags_Admin object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Admin ) ) {
            self::$instance = new Envira_Tags_Admin();
        }

        return self::$instance;

    }

}

// Load the metaboxes class.
$envira_tags_admin = Envira_Tags_Admin::get_instance();
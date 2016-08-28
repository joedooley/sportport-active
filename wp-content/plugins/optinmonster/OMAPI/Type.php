<?php
/**
 * Type class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */
class OMAPI_Type {

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

	    // Set our object.
	    $this->set();

		// Load actions and filters.
        $this->type();

    }

    /**
     * Sets our object instance and base class instance.
     *
     * @since 1.0.0
     */
    public function set() {

        self::$instance = $this;
        $this->base 	= OMAPI::get_instance();
        $this->view     = isset( $_GET['optin_monster_api_view'] ) ? stripslashes( $_GET['optin_monster_api_view'] ) : $this->base->get_view();

    }

    /**
     * Loads the OptinMonster API post type.
     *
     * @since 1.0.0
     */
    public function type() {

        register_post_type( 'omapi',
	        array(
				'labels' => apply_filters( 'optin_monster_api_post_type_labels',
					array(
						'name'               => _x( 'Optins', 'post type general name', 'optin-monster-api' ),
						'singular_name'      => _x( 'Optin', 'post type singular name', 'optin-monster-api' ),
						'add_new'            => __( 'Add New', 'optin-monster-api' ),
						'add_new_item'       => __( 'Add New Optin', 'optin-monster-api' ),
						'edit_item'          => __( 'Edit Optin', 'optin-monster-api' ),
						'new_item'           => __( 'New Optin', 'optin-monster-api' ),
						'all_items'          => __( 'Optins', 'optin-monster-api' ),
						'view_item'          => __( 'View Optin', 'optin-monster-api' ),
						'search_items'       => __( 'Search Optins', 'optin-monster-api' ),
						'not_found'          => __( 'No Optins found', 'optin-monster-api' ),
						'not_found_in_trash' => __( 'No Optins found in trash', 'optin-monster-api' ),
						'parent_item_colon'  => '',
						'menu_name'          => __( 'Optins', 'optin-monster-api' )
					)
				),
				'public'          => false,
				'rewrite'         => false,
				'capability_type' => 'post',
				'has_archive'     => false,
				'hierarchical'    => false,
				'supports'        => array( 'title' )
			)
		);

    }

}
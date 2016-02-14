<?php
/**
 * Capabilities class.
 *
 * @since 1.3.0
 *
 * @package Envira_Albums
 * @author  Tim Carr
 */
class Envira_Albums_Capabilities {

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
     * @since 1.3.0
     */
    public function __construct() {

        // Actions
        add_action( 'admin_init', array( $this, 'add_capabilities' ) );

    }

    /**
     * Registers Envira Album capabilities for each Role, if they don't already exist.
     *
     * If capabilities don't exist, they're copied from Posts. This ensures users prior to 1.3.0
     * get like-for-like behaviour in Envira and don't notice the new capabilities.
     *
     * @since 1.3.0
     */
    public function add_capabilities() {

        // Grab the administrator role, and if it already has an Envira capability key defined, bail
        // as we only need to register our capabilities once.
        $administrator = get_role( 'administrator' );
        if ( $administrator->has_cap( 'edit_other_envira_albums' ) ) {
            return;
        }
        
        // If here, we need to assign capabilities
        // Define the roles we want to assign capabilities to
        $roles = array(
            'administrator',
            'editor',
            'author',
            'contributor',
            'subscriber',
        );

        // Iterate through roles
        foreach ( $roles as $role_name ) {
            // Properly get the role as WP_Role object
            $role = get_role( $role_name );
            if ( ! is_object( $role ) ) {
                continue;
            }

            // Map this Role's Post capabilities to our Envira Album capabilities
            $caps = array(
                'edit_envira_album'              => $role->has_cap( 'edit_posts' ),
                'read_envira_album'              => $role->has_cap( 'read' ),
                'delete_envira_album'            => $role->has_cap( 'delete_posts' ),

                'edit_envira_albums'             => $role->has_cap( 'edit_posts' ),
                'edit_other_envira_albums'       => $role->has_cap( 'edit_others_posts' ),
                'edit_others_envira_albums'      => $role->has_cap( 'edit_others_posts' ),
                'publish_envira_albums'          => $role->has_cap( 'publish_posts' ),
                'read_private_envira_albums'     => $role->has_cap( 'read_private_posts' ),
                
                'read'                           => $role->has_cap( 'read' ),
                'delete_envira_albums'           => $role->has_cap( 'delete_posts' ),
                'delete_private_envira_albums'   => $role->has_cap( 'delete_private_posts' ),
                'delete_published_envira_albums' => $role->has_cap( 'delete_published_posts' ),
                'delete_others_envira_albums'    => $role->has_cap( 'delete_others_posts' ),
                'edit_private_envira_albums'     => $role->has_cap( 'edit_private_posts' ),
                'edit_published_envira_albums'   => $role->has_cap( 'edit_published_posts' ),
                'create_envira_albums'           => $role->has_cap( 'edit_posts' ),
            );

            // Add the above Envira capabilities to this Role
            foreach ( $caps as $envira_cap => $value ) {
                // Don't add if value is false
                if ( ! $value ) {
                    continue;
                }

                $role->add_cap( $envira_cap );
            }
        }

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.3.0
     *
     * @return object The Envira_Albums_Capabilities object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Albums_Capabilities ) ) {
            self::$instance = new Envira_Albums_Capabilities();
        }

        return self::$instance;

    }

}

// Load the capabilities class.
$envira_albums_capabilities = Envira_Albums_Capabilities::get_instance();
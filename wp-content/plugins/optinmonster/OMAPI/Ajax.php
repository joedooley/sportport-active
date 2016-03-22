<?php
/**
 * Ajax class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */
class OMAPI_Ajax {

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

	    // Load non-WordPress style ajax requests.
	    if ( isset( $_REQUEST['optin-monster-ajax-route'] ) && $_REQUEST['optin-monster-ajax-route'] ) {
		    if ( isset( $_REQUEST['action'] ) ) {
			    add_action( 'init', array( $this, 'ajax' ), 999 );
		    }
	    }

		// Load actions and filters.
        add_action( 'wp_ajax_omapi_query_posts', array( $this, 'query_posts' ) );
        add_action( 'wp_ajax_omapi_query_taxonomies', array( $this, 'query_taxonomies' ) );
        add_action( 'wp_ajax_omapi_query_selected_posts', array( $this, 'query_selected_posts' ) );
        add_action( 'wp_ajax_omapi_query_selected_taxonomies', array( $this, 'query_selected_taxonomies' ) );

    }

    /**
     * Sets our object instance and base class instance.
     *
     * @since 1.0.0
     */
    public function set() {

        self::$instance = $this;
        $this->base 	= OMAPI::get_instance();
        $this->view     = 'ajax';

    }

    /**
     * Callback to process external ajax requests.
     *
     * @since 1.0.0
     */
    public function ajax() {

	    switch ( $_REQUEST['action'] ) {
		    case 'mailpoet' :
		    	$this->mailpoet();
		    break;
	    }

    }

    /**
     * Queries the posts based on search parameters.
     *
     * @since 1.0.0
     */
    public function query_posts() {

        // Run a security check first.
        check_ajax_referer( 'omapi-query-nonce', 'nonce' );

        // Prepare variables.
        $search 	= stripslashes( $_POST['q'] );
        $post_types = get_post_types( array( 'public' => true ) );
        $ret		= array();
        $args		= array(
	        'post_type' 	 => $post_types,
	        's'		    	 => $search,
	        'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'future' ),
	    );

        // Make the query.
        $posts = get_posts( $args );
        if ( empty( $posts ) ) {
	        // Maybe they entered a post ID to search. Let's try that.
	        $search = (int) $search;
	        if ( $search ) {
		        $id_args  = array(
			        'post_type' 	 => $post_types,
			        'post__in'		 => (array) $search,
			        'posts_per_page' => -1
			    );
			    $id_posts = get_posts( $id_args );

			    if ( empty( $id_posts ) ) {
				    $ret['items'] = array();
			    } else {
				    foreach ( $id_posts as $post ) {
				        $ret['items'][] = array(
				        	'id'    => $post->ID,
				        	'title' => $post->post_title
				        );
			        }
			    }
	        } else {
		        $ret['items'] = array();
	        }
        } else {
	        foreach ( $posts as $post ) {
		        $ret['items'][] = array(
		        	'id'    => $post->ID,
		        	'title' => $post->post_title
		        );
	        }
        }

        // Send back the response.
        die( json_encode( $ret ) );

    }

    /**
     * Queries the taxonomies based on search parameters.
     *
     * @since 1.0.0
     */
    public function query_taxonomies() {

        // Run a security check first.
        check_ajax_referer( 'omapi-query-nonce', 'nonce' );

        // Prepare variables.
        $search = stripslashes( $_POST['q'] );
        $terms  = get_tags( array( 'name__like' => $search ) );
        $ret	= array();

        // Make the query.
        if ( empty( $terms ) ) {
		    $ret['items'] = array();
        } else {
	        foreach ( $terms as $term ) {
		        $ret['items'][] = array(
		        	'id'    => $term->term_id,
		        	'title' => $term->name
		        );
	        }
        }

        // Send back the response.
        die( json_encode( $ret ) );

    }

    /**
     * Queries the selected items for "never" and "only" output settings
     * to show pre-selected values by the user.
     *
     * @since 1.0.0
     */
    public function query_selected_posts() {

        // Run a security check first.
        check_ajax_referer( 'omapi-query-nonce', 'nonce' );

        // Prepare variables.
        $ids  = explode( ',', stripslashes( $_POST['ids'] ) );
        $ret  = array();
        $args = array(
	        'post__in'		 => $ids,
	        'posts_per_page' => -1,
	        'post_type'      => get_post_types( array( 'public' => true ) ),
            'post_status'    => array( 'publish', 'future' ),
	    );

        // Make the query.
        $posts = get_posts( $args );
        if ( empty( $posts ) ) {
	        $ret['items'] = array();
        } else {
	        foreach ( $posts as $post ) {
		        $ret['items'][] = array(
		        	'id'    => $post->ID,
		        	'title' => $post->post_title
		        );
	        }
        }

        // Send back the response.
        die( json_encode( $ret ) );

    }

    /**
     * Queries the selected tags to show pre-selected values by the user.
     *
     * @since 1.0.0
     */
    public function query_selected_taxonomies() {

        // Run a security check first.
        check_ajax_referer( 'omapi-query-nonce', 'nonce' );

        // Prepare variables.
        $ids 		  = explode( ',', stripslashes( $_POST['ids'] ) );
        $ret 		  = array();
        $ret['items'] = array();

        // Make the query.
        foreach ( $ids as $id ) {
	        $tag = get_tag( absint( $id ) );
	        if ( $tag ) {
		        $ret['items'][] = array(
			        'id'    => $tag->term_id,
		        	'title' => $tag->name
		        );
	        }
        }

        // Send back the response.
        die( json_encode( $ret ) );

    }

    /**
     * Opts the user into MailPoet.
     *
     * @since 1.0.0
     */
    public function mailpoet() {

		// Run a security check first.
        check_ajax_referer( 'omapi', 'nonce' );

        // Prepare variables.
        $optin = $this->base->get_optin_by_slug( stripslashes( $_REQUEST['optin'] ) );
        $list  = get_post_meta( $optin->ID, '_omapi_mailpoet_list', true );
        $email = stripslashes( $_REQUEST['email'] );
        $name  = isset( $_REQUEST['name'] ) ? stripslashes( $_REQUEST['name'] ) : false;
        $user  = array();

        // Possibly split name into first and last.
        if ( $name ) {
	        $names = explode( ' ', $name );
	        if ( isset( $names[0] ) ) {
		        $user['firstname'] = $names[0];
	        }

	        if ( isset( $names[1] ) ) {
		        $user['lastname'] = $names[1];
	        }
        }

        // Save the email address.
        $user['email'] = $email;

        // Store the data.
        $data = array(
            'user' 		=> $user,
            'user_list' => array( 'list_ids' => array( $list ) ),
        );
        $data = apply_filters( 'optin_monster_pre_optin_mailpoet', $data, $_REQUEST, $list, null );

        // Save the subscriber.
        $userHelper = WYSIJA::get( 'user', 'helper' );
        $userHelper->addSubscriber( $data );

        // Send back a response.
        die( json_encode( true ) );

	}

}
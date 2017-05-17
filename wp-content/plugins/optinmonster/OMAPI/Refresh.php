<?php
/**
 * Refresh class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */
class OMAPI_Refresh {

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

		// Possibly refresh optins.
		$this->maybe_refresh();

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
     * Maybe refresh optins if the action has been requested.
     *
     * @since 1.0.0
     */
    public function maybe_refresh() {

	    // If we are missing our save action, return early.
	    if ( empty( $_POST['omapi_refresh'] ) ) {
		    return;
	    }

	    // Verify the nonce field.
	    check_admin_referer( 'omapi_nonce_' . $this->view, 'omapi_nonce_' . $this->view );

	    // Refresh the optins.
	    $this->refresh();

	    // Provide action to refresh optins.
	    do_action( 'optin_monster_api_refresh_optins', $this->view );

    }

    /**
     * Refresh the optins.
     *
     * @since 1.0.0
     */
    public function refresh() {

		$creds = $this->base->get_api_credentials();

		// Check if we have the new API and if so only use it
        if ( $creds['apikey'] ){
            $api   = new OMAPI_Api('optins', array( 'apikey' => $creds['apikey']), 'GET' );
        } else {
            $api   = new OMAPI_Api( 'optins', array( 'user' => $creds['user'], 'key' => $creds['key'] ), 'GET' );
        }

		$ret   = $api->request();
		if ( is_wp_error( $ret ) ) {
			// If no optins available, make sure they get deleted.
			if ( 'optins' == $ret->get_error_code() ) {
				$this->base->save->store_optins( array() );
			}

			// Set an error message.
			$this->error = $ret->get_error_message();
			add_action( 'optin_monster_api_messages_' . $this->view, array( $this, 'error' ) );
		} else {
			// Store the optin data.
			$this->base->save->store_optins( $ret );

			// Update the option to remove stale error messages.
			$option = $this->base->get_option();
			$option['is_invalid']  = false;
			$option['is_expired']  = false;
			$option['is_disabled'] = false;
			update_option( 'optin_monster_api', $option );

			// Set a message.
			add_action( 'optin_monster_api_messages_' . $this->view, array( $this, 'message' ) );
		}

    }

    /**
     * Output an error message.
     *
     * @since 1.0.0
     */
    public function error() {

	    ?>
	    <div class="updated error"><p><?php echo $this->error; ?></p></div>
	    <?php

    }

    /**
     * Output a refresh message.
     *
     * @since 1.0.0
     */
    public function message() {

	    ?>
	    <div class="updated"><p><?php _e( 'Your optins have been refreshed successfully.', 'optin-monster-api' ); ?></p></div>
	    <?php

    }

}
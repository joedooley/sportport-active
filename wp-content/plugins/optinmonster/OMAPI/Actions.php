<?php
/**
 * Actions class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */
class OMAPI_Actions {

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
     * Holds any action notices.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $notices = array();

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

		// Add validation messages.
		add_action( 'admin_init', array( $this, 'actions' ) );
		add_action( 'admin_notices', array( $this, 'notices' ) );

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
        $this->optin_id = isset( $_GET['optin_monster_api_id'] ) ? absint( $_GET['optin_monster_api_id'] ) : false;

    }

    /**
     * Process admin actions.
     *
     * @since 1.0.0
     */
    public function actions() {

		// Ensure action is set and correct and the optin is set.
		$action = isset( $_GET['optin_monster_api_action'] ) ? stripslashes( $_GET['optin_monster_api_action'] ) : false;
		if ( ! $action || 'edit' == $action ) {
			return;
		}

		// Verify the nonce URL.
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'omapi-action' ) ) {
			return;
		}

		switch ( $action ) {
			case 'status' :
				if ( $this->status() ) {
					$this->notices['updated'] = sprintf( __( 'The optin status was updated successfully. You can configure more specific loading requirements by <a href="%s" title="Click here to edit the output settings for the updated optin.">editing the output settings</a> for the optin.', 'optin-monster-api' ), esc_url_raw( add_query_arg( array( 'page' => 'optin-monster-api-settings', 'optin_monster_api_view' => 'optins', 'optin_monster_api_action' => 'edit', 'optin_monster_api_id' => $this->optin_id ), admin_url( 'admin.php' ) ) ) );
				} else {
					$this->notices['error'] = __( 'There was an error updating the optin status. Please try again.', 'optin-monster-api' );
				}
			break;

			case 'test' :
				if ( $this->test() ) {
					$this->notices['updated'] = __( 'You have updated test mode for the optin successfully.', 'optin-monster-api' );
				} else {
					$this->notices['error'] = __( 'There was an error updating test mode for the optin. Please try again.', 'optin-monster-api' );
				}
			break;

			case 'delete' :
				if ( $this->delete() ) {
					$this->notices['updated'] = __( 'The local optin was deleted successfully.', 'optin-monster-api' );
				} else {
					$this->notices['error'] = __( 'There was an error deleting the local optin. Please try again.', 'optin-monster-api' );
				}
			break;

			case 'cookies' :
				if ( $this->cookies() ) {
					$this->notices['updated'] = __( 'The local cookies have been cleared successfully.', 'optin-monster-api' );
				} else {
					$this->notices['error'] = __( 'There was an error clearing the local cookies. Please try again.', 'optin-monster-api' );
				}
			break;

			case 'migrate' :
				if ( $this->migrate() ) {
					$this->notices['updated'] = __( 'Your data has been migrated.', 'optin-monster-api' );
				} else {
					$this->notices['error'] = __( 'Something happened while migrating your data. Please try again.', 'optin-monster-api' );
				}
				break;

			case 'migrate-reset' :
				if ( $this->migrate_reset() ) {
					$this->notices['updated'] = __( 'Migration data has been reset.', 'optin-monster-api' );
				} else {
					$this->notices['error'] = __( 'Something happened while resetting your data. Please try again.', 'optin-monster-api' );
				}
				break;
		}

    }

    /**
     * Changes the status of an optin.
     *
     * @since 1.0.0
     */
    public function status() {

		// Prepare variables.
	    $status = (bool) get_post_meta( $this->optin_id, '_omapi_enabled', true );
	    $new	= $status ? false : true;
	    $field  = 'global';
	    $type   = get_post_meta( $this->optin_id, '_omapi_type', true );
	    if ( 'post' == $type ) {
		    $field = 'automatic';
	    } else if ( 'sidebar' == $type ) {
		    $field = false;
	    }

	    // Maybe update the global/automatic status.
	    if ( $field ) {
		    update_post_meta( $this->optin_id, '_omapi_' . $field, $new );
	    }

	    // Set enabled status.
	    return update_post_meta( $this->optin_id, '_omapi_enabled', $new );

    }

    /**
     * Changes test mode for the optin.
     *
     * @since 1.0.0
     */
    public function test() {

	    $status = (bool) get_post_meta( $this->optin_id, '_omapi_test', true );
	    $new	= $status ? false : true;
	    return update_post_meta( $this->optin_id, '_omapi_test', $new );

    }

    /**
     * Removes a local optin.
     *
     * @since 1.0.0
     */
    public function delete() {

	    return wp_delete_post( $this->optin_id, true );

    }

    /**
     * Clears the local cookies.
     *
     * @since 1.0.0
     */
    public function cookies() {

	    $optins = $this->base->get_optins();
	    foreach ( (array) $optins as $optin ) {
		    if ( $optin ) {
			    $ids = get_post_meta( $optin->ID, '_omapi_ids', true );
			    foreach ( (array) $ids as $id ) {
				    setcookie( 'om-' . $id, '', -1, COOKIEPATH, COOKIE_DOMAIN, false );
			    }
			}
	    }

	    // Also clear out global cookie.
	    setcookie( 'om-global-cookie', '', -1, COOKIEPATH, COOKIE_DOMAIN, false );

	    return true;

    }

	/**
	 * Migrates data to the SaaS
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function migrate() {

		// Create new instance w/ Api object
		$creds = $this->base->get_api_credentials();
		$api   = new OMAPI_Api( 'migrate', array( 'user' => $creds['user'], 'key' => $creds['key'] ) );

		// Run migration
		$migration = new OMAPI_Migration( $api );

		// Return status
		return $migration->run();

	}

	/**
	 * Resets migration data
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function migrate_reset() {

		return delete_option( '_om_migration_data' );

	}

    /**
     * Outputs any action notices.
     *
     * @since 1.0.0
     */
    public function notices() {

	    foreach ( $this->notices as $id => $message ) {
		    echo '<div class="' . $id . '"><p>' . $message . '</p></div>';
	    }

    }


}
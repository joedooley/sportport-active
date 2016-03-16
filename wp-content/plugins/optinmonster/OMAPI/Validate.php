<?php
/**
 * Validate class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */
class OMAPI_Validate {

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

		// Possibly validate our API credentials.
		$this->maybe_validate();

		// Add validation messages.
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

    }

    /**
     * Maybe validate our API credentials if the transient has expired.
     *
     * @since 1.0.0
     */
    public function maybe_validate() {

	    // Check if the transient has expired.
	    if ( false !== ( $transient = get_transient( '_omapi_validate' ) ) ) {
		    return;
	    }

	    // Validate API.
	    $this->validate();

	    // Provide action to refresh optins.
	    do_action( 'optin_monster_api_validate_api', $this->view );

    }

    /**
     * Validate API credentials.
     *
     * @since 1.0.0
     */
    public function validate() {

		$creds = $this->base->get_api_credentials();
	    $api   = new OMAPI_Api( 'validate', array( 'user' => $creds['user'], 'key' => $creds['key'] ) );
		$ret   = $api->request();
		if ( is_wp_error( $ret ) ) {
			$option = $this->base->get_option();
			$type	= $ret->get_error_code();
			switch ( $type ) {
				case 'missing' :
				case 'auth' :
					// Set option values.
					$option['is_invalid']  = true;
					$option['is_expired']  = false;
					$option['is_disabled'] = false;
				break;

				case 'disabled' :
					// Set option values.
					$option['is_invalid']  = false;
					$option['is_expired']  = false;
					$option['is_disabled'] = true;
				break;

				case 'expired' :
					// Set option values.
					$option['is_invalid']  = false;
					$option['is_expired']  = true;
					$option['is_disabled'] = false;
				break;
			}

			// Update option.
			update_option( 'optin_monster_api', $option );

			// Set our transient to run again in an hour.
			set_transient( '_omapi_validate', true, HOUR_IN_SECONDS );
		} else {
			set_transient( '_omapi_validate', true, DAY_IN_SECONDS );
		}

    }

    /**
     * Outputs any validation notices.
     *
     * @since 1.0.0
     */
    public function notices() {

	    $option = $this->base->get_option();
	    if ( isset( $option['is_invalid'] ) && $option['is_invalid'] ) {
		    if ( ! ( isset($_GET['page'] ) && $_GET['page'] == 'optin-monster-api-settings') && ! ( isset($_GET['page'] ) && $_GET['page'] == 'optin-monster-api-welcome') ){
			    echo '<div class="error"><p>' . __( 'There was an error verifying your OptinMonster API credentials. They are either missing or they are no longer valid.', 'optin-monster-api' ) . '</p>';
			    echo '<p><a href="' . esc_url_raw( admin_url( 'admin.php?page=optin-monster-api-settings' ) ) . '" class="button button-primary button-large omapi-new-optin" title="View API Settings" >View API Settings</a></p></div>';
		    }
	    } elseif ( isset( $option['is_disabled'] ) && $option['is_disabled'] ) {
		    echo '<div class="error"><p>' . __( 'The subscription to this OptinMonster account has been disabled, likely due to a refund or other administrator action. Please contact OptinMonster support to resolve this issue.', 'optin-monster-api' ) . '</p>';
		    echo '<p><a href="https://app.optinmonster.com/account/support/?utm_source=orgplugin&utm_medium=link&utm_campaign=wpdashboard" class="button button-primary button-large omapi-new-optin" title="Contact OptinMonster Support" target="_blank">Contact Support</a></p></div>';
	    } elseif ( isset( $option['is_expired'] ) && $option['is_expired'] ) {
		    echo '<div class="error"><p>' . __( 'The subscription to this OptinMonster account has expired. Please renew your subscription to use the OptinMonster API.', 'optin-monster-api' ) . '</p>';
		    echo '<p><a href="https://app.optinmonster.com/account/billing/?utm_source=orgplugin&utm_medium=link&utm_campaign=wpdashboard" class="button button-primary button-large omapi-new-optin" title="Renew Subscription" target="_blank">Renew Subscription</a></p></div>';
	    }

    }

}
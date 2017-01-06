<?php
/**
 * Licensing class for SuperSide Me.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2017 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me_Licensing extends SuperSide_Me_Helper {

	/**
	 * Licensing page/setting
	 * @var string $page
	 */
	protected $page = 'supersideme';

	/**
	 * Array of fields for licensing
	 * @var $fields
	 */
	protected $fields;

	/**
	 * License key
	 * @var $license
	 */
	protected $license = '';

	/** License status
	 * @var $status
	 */
	protected $status = false;

	/**
	 * Store URL for Easy Digital Downloads.
	 * @var string
	 */
	protected $store_url = 'https://robincornett.com';

	/**
	 * Plugin name for EDD.
	 * @var string
	 */
	protected $name = 'SuperSide Me';

	/**
	 * Plugin slug for license check.
	 * @var string
	 */
	protected $slug = 'superside-me';

	/**
	 * Value for the licensing nonce.
	 * @var string $action
	 */
	protected $action = 'superside_license_nonce';

	/**
	 * Value for the licensing nonce.
	 * @var string $nonce
	 */
	protected $nonce  = 'superside_license_nonce';

	/**
	 * Set up EDD licensing updates
	 * @since 1.4.0
	 */
	public function updater() {

		if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			// load our custom updater if it doesn't already exist
			include plugin_dir_path( __FILE__ ) . 'class-eddpluginupdater.php';
		}

		$this->license = get_option( 'supersidemelicense_key', '' );
		$edd_updater   = new EDD_SL_Plugin_Updater( $this->store_url, SUPERSIDEME_BASENAME, array(
			'version'   => SUPERSIDEME_VERSION,
			'license'   => trim( $this->license ),
			'item_name' => $this->name,
			'author'    => 'Robin Cornett',
		) );

		if ( is_multisite() && ! is_main_site() ) {
			return;
		}

		$sections     = $this->register_section();
		$this->fields = $this->register_fields();
		$this->register_settings();
		$this->add_sections( $sections );
		$this->add_fields( $this->fields, $sections );
		$this->activate_license();
		$this->deactivate_license();

		add_action( 'admin_notices', array( $this, 'select_error_message' ) );
	}

	/**
	 * Register plugin license settings and fields
	 * @since 1.4.0
	 */
	public function register_settings() {
		register_setting( $this->page . '_licensing', 'supersidemelicense_key', array( $this, 'sanitize_license' ) );
	}

	/**
	 * Register the licensing section.
	 * @return array
	 */
	protected function register_section() {
		return array(
			'licensing' => array(
				'id'    => 'licensing',
				'label' => __( 'SuperSide Me[nu] License', 'superside-me' ),
			),
		);
	}

	/**
	 * Register the license key field.
	 * @return array
	 */
	protected function register_fields() {
		return array(
			array(
				'setting'  => 'supersidemelicense_key',
				'label'    => __( 'License Key', 'superside-me' ),
				'callback' => 'do_license_key_field',
				'tab'      => 'licensing',
				'args'     => array(
					'setting' => 'supersidemelicense_key',
					'label'   => __( 'Enter your license key.', 'superside-me' ),
				),
			),
		);
	}

	/**
	 * License key input field
	 * @param  array $args parameters to define field
	 *
	 * @since 1.4.0
	 */
	public function do_license_key_field( $args ) {
		if ( 'valid' === $this->status ) {
			$style = 'color:white;background-color:green;border-radius:100%;margin-right:8px;vertical-align:middle;';
			printf( '<span class="dashicons dashicons-yes" style="%s"></span>',
				esc_attr( $style )
			);
		}
		printf( '<input type="password" class="regular-text" id="%1$s" name="%1$s" value="%2$s" />',
			esc_attr( $args['setting'] ),
			esc_attr( $this->license )
		);
		if ( ! empty( $this->license ) && 'valid' === $this->status ) {
			$this->add_deactivation_button();
		}
		if ( 'valid' === $this->status ) {
			return;
		}
		printf( '<p class="description"><label for="%3$s[%1$s]">%2$s</label></p>', esc_attr( $args['setting'] ), esc_html( $args['label'] ), esc_attr( $this->page ) );
	}

	/**
	 * License deactivation button
	 */
	public function add_deactivation_button() {

		if ( 'valid' !== $this->status ) {
			return;
		}

		$value = sprintf( __( 'Deactivate', 'superside-me' ) );
		$name  = 'supersideme_license_deactivate';
		$class = 'button-secondary';
		$this->print_button( $class, $name, $value );
	}

	/**
	 * Sanitize license key
	 * @param  string $new_value license key
	 * @return string license key
	 *
	 * @since 1.4.0
	 */
	public function sanitize_license( $new_value ) {
		$license = get_option( 'supersidemelicense_key' );
		$status  = get_option( 'supersidemelicense_status', '' );
		if ( ( $license && $license !== $new_value ) || empty( $new_value ) ) {
			delete_option( 'supersideme_status' );
		}
		if ( $license !== $new_value || 'valid' !== $status ) {
			$this->activate_license( $new_value );
		}
		return sanitize_text_field( $new_value );
	}

	/**
	 * Activate plugin license
	 * @param  string $new_value entered license key
	 * @uses do_remote_request()
	 *
	 * @since 1.4.0
	 */
	public function activate_license( $new_value = '' ) {

		// listen for our activate button to be clicked
		if ( isset( $_POST['supersideme_activate'] ) ) {

			// If the user doesn't have permission to save, then display an error message
			if ( ! $this->user_can_save( $this->action, $this->nonce ) ) {
				wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'superside-me' ) );
			}

			// run a quick security check
			if ( ! check_admin_referer( $this->action, $this->nonce ) ) {
				return; // get out if we didn't click the Activate button
			}

			// retrieve the license from the database
			$license = trim( $this->license );
			$license = $new_value !== $license ? trim( $new_value ) : $license;

			if ( empty( $license ) || empty( $new_value ) ) {
				delete_option( 'supersidemelicense_status' );
				return;
			}

			// data to send in our API request
			$api_params   = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->name ), // the name of our product in EDD
				'url'        => esc_url( home_url() ),
			);
			$license_data = $this->do_remote_request( $api_params );
			$status       = 'invalid';
			if ( $license_data ) {
				$status = $license_data->license;
				if ( false === $license_data->success ) {
					$status = $license_data->error;
				}
				$this->update_supersideme_data_option( $license_data );
			}
			update_option( 'supersidemelicense_status', $status );
		}
	}

	/**
	 * Deactivate license: deletes license status key and deactivates with store
	 * @uses do_remote_request()
	 *
	 * @since 1.4.0
	 */
	function deactivate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST['supersideme_license_deactivate'] ) ) {

			// If the user doesn't have permission to save, then display an error message
			if ( ! $this->user_can_save( $this->action, $this->nonce ) ) {
				wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'superside-me' ) );
			}

			// run a quick security check
		 	if ( ! check_admin_referer( $this->action, $this->nonce ) ) {
				return; // get out if we didn't click the Activate button
		 	}

			// retrieve the license from the database
			$license = trim( $this->license );

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->name ), // the name of our product in EDD
				'url'        => home_url(),
			);
			$license_data = $this->do_remote_request( $api_params );

			// $license_data->license will be either "deactivated" or "failed"
			if ( is_object( $license_data ) && 'deactivated' === $license_data->license ) {
				delete_option( 'supersidemelicense_status' );
			}
		}
	}

	/**
	 * Weekly cron job to compare activated license with the server.
	 * @uses check_license()
	 * @since 2.0.0
	 */
	public function weekly_license_check() {
		if ( apply_filters( 'supersideme_skip_license_check', false ) ) {
			return;
		}

		if ( ! empty( $_POST['supersideme_nonce'] ) ) {
			return;
		}

		$license = get_option( 'supersidemelicense_key', '' );
		if ( empty( $license ) ) {
			delete_option( 'supersidemelicense_status' );
			return;
		}

		// Update local plugin status
		$license_data = $this->check_license( $license );
		$status       = 'invalid';
		if ( $license_data ) {
			$status = $license_data->license;
			if ( false === $license_data->success ) {
				$status = $license_data->error;
			}
			$this->update_supersideme_data_option( $license_data );
		}
		if ( $status !== $this->status ) {
			update_option( 'supersidemelicense_status', $status );
		}
	}

	/**
	 * Updates supersideme_data with correct information.
	 * @param $license_data
	 */
	protected function update_supersideme_data_option( $license_data ) {
		$data_setting = 'supersidemelicense_data';
		$data         = get_option( $data_setting, false );
		if ( ! isset( $data['expires'] ) || $license_data->expires !== $data['expires'] ) {
			$this->update_settings( array(
				'expires' => $license_data->expires,
				'limit'   => (int) $license_data->license_limit,
			), $data_setting );
		}

		if ( 'valid' === $license_data->license ) {
			return;
		}

		$latest_version = $this->get_latest_version();
		if ( ! isset( $data['latest_version'] ) || $latest_version !== $data['latest_version'] ) {
			$this->update_settings( array(
				'latest_version' => $latest_version,
			), $data_setting );
		}
	}

	/**
	 * Check plugin license status
	 * @param $license string
	 * @uses do_remote_request()
	 * @return mixed data
	 *
	 * @since 1.4.0
	 */
	protected function check_license( $license = '' ) {

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->name ), // the name of our product in EDD
			'url'        => esc_url( home_url() ),
		);
		if ( empty( $api_params['license'] ) ) {
			return '';
		}
		return $this->do_remote_request( $api_params );
	}

	/**
	 * Get the latest plugin version.
	 * @uses do_remote_request()
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	protected function get_latest_version() {
		$api_params = array(
			'edd_action' => 'get_version',
			'item_name'  => $this->name,
			'slug'       => $this->slug,
		);
		$request = $this->do_remote_request( $api_params );

		if ( $request && isset( $request->sections ) ) {
			$request->sections = maybe_unserialize( $request->sections );
		} else {
			return false;
		}
		return $request->new_version;
	}

	/**
	 * Send the request to the remote server.
	 * @param $api_params array
	 * @param $timeout int
	 *
	 * @return array|bool|mixed|object
	 *
	 * @since 2.0.0
	 */
	private function do_remote_request( $api_params, $timeout = 15 ) {
		$response = wp_remote_post( $this->store_url, array(
			'timeout'   => $timeout,
			'sslverify' => false,
			'body'      => $api_params,
		) );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}
		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Pick the correct error message based on the new information from EDD.
	 * @param $status
	 * @param string $message
	 *
	 * @return string|void
	 */
	protected function license_data_error( $status, $message = '' ) {
		if ( 'valid' === $status ) {
			return $message;
		}

		switch ( $status ) {

			case 'expired':
				$license     = get_option( 'supersidemelicense_data' );
				$pretty_date = $this->pretty_date( array( 'field' => strtotime( $license['expires'] ) ) );
				$message     = sprintf( __( 'It looks like your license expired on %s.', 'superside-me' ), $pretty_date );
				$choice      = 1;
				if ( 5 === $license['limit'] ) {
					$choice = 2;
				} elseif ( 0 === $license['limit'] ) {
					$choice = 3;
				}
				$renew_url = trailingslashit( $this->store_url ) . 'checkout/?edd_action=add_to_cart&download_id=3772&discount=PASTDUE15&edd_options[price_id]=' . $choice;
				$message .= sprintf( __( ' To continue receiving updates, <a href="%s">renew now and receive a discount</a>.', 'superside-me' ), $renew_url );
				break;

			case 'disabled':
				$message = __( 'Your license key has been disabled.', 'superside-me' );
				break;

			case 'missing':
				$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'superside-me' ), $this->name );
				break;

			case 'invalid':
			case 'site_inactive':
				$message = __( 'If you\'re seeing this message and have recently migrated from another site, you should just need to reactivate your license.', 'superside-me' );
				break;

			case 'item_name_mismatch':
				$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'superside-me' ), $this->name );
				break;

			case 'no_activations_left':
				$message = __( 'Your license key has reached its activation limit.', 'superside-me' );
				break;

			default:
				$message = __( 'If you\'re seeing this message and have recently migrated from another site, you should just need to reactivate your license.', 'superside-me' );
				break;
		}

		return ' ' . $message;
	}

	/**
	 * Pick which error message to display. Based on whether license has never been activated, or is no longer valid, or has expired.
	 *
	 */
	public function select_error_message() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$this->status = get_option( 'supersidemelicense_status', false );
		if ( 'valid' === $this->status || apply_filters( 'supersideme_skip_license_check', false ) ) {
			return;
		}
		$screen   = get_current_screen();
		$haystack = array( 'appearance_page_supersideme', 'update', 'update-core', 'plugins' );
		$class    = 'notice-info';
		if ( ! in_array( $screen->id, $haystack, true ) ) {
			return;
		}
		$licensing_tab = admin_url( 'themes.php?page=supersideme&tab=licensing' );
		if ( empty( $this->license ) || false === $this->status ) {
			$message = '<p>' . sprintf( __( 'Please make sure you <a href="%s">activate your %s license</a> in order to receive automatic updates and support.', 'superside-me' ), esc_url( $licensing_tab ), esc_attr( $this->name ) ) . '</p>';
		} else {
			$message = '<p>' . sprintf( __( 'Sorry, there is an issue with your license for %s. Please check the <a href="%s">plugin license</a>.', 'superside-me' ), esc_attr( $this->name ), esc_url( $licensing_tab ) );
			if ( $this->license && ! in_array( $this->status, array( 'valid', false ), true ) ) {
				if ( 'invalid' !== $this->status ) {
					$class = 'error';
				}
				$message .= $this->license_data_error( $this->status );
			}
			$message .= '</p>';
			$data     = get_option( 'supersidemelicense_data', false );
			if ( isset( $data['latest_version'] ) && SUPERSIDEME_VERSION < $data['latest_version'] ) {
				$message .= '<p>' . sprintf( __( 'The latest version of %s is %s and you are running %s. ', 'superside-me' ), esc_attr( $this->name ), esc_attr( $data['latest_version'] ), esc_attr( SUPERSIDEME_VERSION ) ) . '</p>';
			}
		}

		$this->do_error_message( $message, $class );
	}

	/**
	 * Error messages if license is empty or invalid
	 * @param $message string
	 * @param $class void|string
	 *
	 * @since 1.4.0
	 */
	protected function do_error_message( $message, $class = '' ) {
		if ( empty( $message ) ) {
			return;
		}
		printf( '<div class="notice %s">%s</div>', esc_attr( $class ), wp_kses_post( $message ) );
	}

	/**
	 * Convert a date string to a pretty format.
	 * @param $args
	 * @param string $before
	 * @param string $after
	 *
	 * @return string
	 */
	protected function pretty_date( $args, $before = '', $after = '' ) {
		$date_format = isset( $args['date_format'] ) ? $args['date_format'] : get_option( 'date_format' );

		return $before . date_i18n( $date_format, $args['field'] ) . $after;
	}
}

<?php
/**
 * Main SuperSide Me class.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2016 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me_Licensing {

	/**
	 * Current plugin version
	 * @var string $version
	 */
	public $version = '1.9.0';

	/**
	 * Licensing page/setting
	 * @var string $page
	 */
	protected $page = 'supersidemelicensing';

	/**
	 * Array of fields for licensing
	 * @var $fields
	 */
	protected $fields;

	/**
	 * License key
	 * @var $supersideme_license
	 */
	protected $supersideme_license;

	/** License status
	 * @var $supersideme_status
	 */
	protected $supersideme_status;

	/**
	 * Set up EDD licensing updates
	 * @since 1.4.0
	 */
	public function updater() {

		$this->supersideme_license = get_option( 'supersidemelicense_key', false );
		$this->supersideme_status  = get_option( 'supersidemelicense_status', false );

		if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			// load our custom updater if it doesn't already exist
			include plugin_dir_path( __FILE__ ) . 'class-eddpluginupdater.php';
		}

		$edd_updater = new EDD_SL_Plugin_Updater( EDD_SUPERSIDEME_URL, SUPERSIDEME_BASENAME, array(
			'version'   => $this->version,
			'license'   => trim( $this->supersideme_license ),
			'item_name' => EDD_SUPERSIDEME_NAME,
			'author'    => 'Robin Cornett',
			'url'       => home_url(),
		) );

		if ( is_multisite() && ! is_main_site() ) {
			return;
		}

		$this->register_settings();
		$this->activate_license();
		$this->deactivate_license();

		add_action( 'admin_notices', array( $this, 'do_error_message' ) );

	}

	/**
	 * Register plugin license settings and fields
	 * @since 1.4.0
	 */
	public function register_settings() {

		register_setting( $this->page, 'supersidemelicense_key', array( $this, 'sanitize_license' ) );

		$sections = array(
			'main' => array(
				'id'       => 'supersideme_license_section',
				'title'    => __( 'SuperSide Me[nu] License', 'superside-me' ),
				'callback' => 'do_main_section_description',
			),
		);

		$this->fields = array(
			array(
				'id'       => 'supersidemelicense_key',
				'title'    => __( 'License Key' , 'superside-me' ),
				'callback' => 'do_license_key_field',
				'section'  => $sections['main']['id'],
				'args'     => array( 'setting' => 'supersidemelicense_key', 'label' => __( 'Enter your license key.', 'superside-me' ) ),
			),
		);

		foreach ( $sections as $section ) {
			add_settings_section(
				$section['id'],
				$section['title'],
				array( $this, $section['callback'] ),
				$this->page
			);
		}

		foreach ( $this->fields as $field ) {
			add_settings_field(
				$this->page . '[' . $field['id'] . ']',
				'<label for="' . $field['id'] . '">' . $field['title'] . '</label>',
				array( $this, $field['callback'] ),
				$this->page,
				$field['section'],
				empty( $field['args'] ) ? array() : $field['args']
			);
		}
	}

	/**
	 * Licensing section description
	 * @return description
	 *
	 * @since 1.4.0
	 */
	public function do_main_section_description() {
		$description = __( 'Licensed users of SuperSide Me receive plugin updates, support, and good vibes.', 'superside-me' );
		$description .= 'valid' === $this->supersideme_status ? __( ' Great news--your license is activated!', 'superside-me' ) : '';
		printf( '<p>%s</p>', esc_html( $description ) );
	}

	/**
	 * License key input field
	 * @param  array $args parameters to define field
	 * @return input field
	 *
	 * @since 1.4.0
	 */
	public function do_license_key_field( $args ) {
		if ( 'valid' === $this->supersideme_status ) {
			$style = 'color:white;background-color:green;border-radius:100%;margin-right:8px;vertical-align:middle;';
			printf( '<span class="dashicons dashicons-yes" style="%s"></span>',
				esc_attr( $style )
			);
		}
		printf( '<input type="text" class="regular-text" id="%1$s" name="%1$s" value="%2$s" />',
			esc_attr( $args['setting'] ),
			esc_attr( $this->supersideme_license )
		);
		if ( ! empty( $this->supersideme_license ) && 'valid' === $this->supersideme_status ) {
			$this->add_deactivation_button();
		}
		if ( 'valid' === $this->supersideme_status ) {
			return;
		}
		printf( '<p class="description"><label for="%3$s[%1$s]">%2$s</label></p>', esc_attr( $args['setting'] ), esc_html( $args['label'] ), esc_attr( $this->page ) );
	}

	/**
	 * License deactivation button
	 */
	public function add_deactivation_button() {

		if ( false === $this->supersideme_status ) {
			return;
		}

		if ( 'valid' === $this->supersideme_status ) {
			$value = sprintf( __( 'Deactivate', 'superside-me' ) );
			$name  = 'supersideme_license_deactivate';
			$class = 'button-secondary';
			printf( '<input type="submit" class="%s" name="%s" value="%s"/>',
				esc_attr( $class ),
				esc_attr( $name ),
				esc_attr( $value )
			);
		}

	}

	/**
	 * Check plugin license status, if valid
	 * @return message about expiration, licenses, etc.
	 *
	 * @since 1.4.0
	 */
	public function show_license_status( $license = '' ) {

		if ( ! $this->supersideme_status ) {
			return;
		}

		if ( false !== $this->supersideme_status && 'valid' === $this->supersideme_status ) {
			$license = $this->check_license( $license );
			if ( ! $license ) {
				return;
			}
			$date    = new DateTime( $license->expires );
			$activation = sprintf(
				__( 'Just so you know, you\'ve activated %s of your %s available licenses. You have %s licenses left.', 'superside-me' ),
				$license->site_count,
				$license->license_limit,
				$license->activations_left
			);
			$expiration = sprintf(
				__( 'And, friendly reminder: your license expires on %s.', 'superside-me' ),
				$date->format( 'F j, Y' )
			);
			printf( '<p>%s</p><p>%s</p>', wp_kses_post( $activation ), wp_kses_post( $expiration ) );
		}

	}

	/**
	 * Sanitize license key
	 * @param  string $new_value license key
	 * @return license key
	 *
	 * @since 1.4.0
	 */
	public function sanitize_license( $new_value ) {
		$license = get_option( 'supersidemelicense_key' );
		if ( ( $license && $license !== $new_value ) || empty( $new_value ) ) {
			delete_option( 'supersideme_status' );
		}
		if ( $license !== $new_value || 'valid' !== $this->supersideme_status ) {
			$this->activate_license( $new_value );
		}
		return sanitize_text_field( $new_value );
	}

	/**
	 * Activate plugin license
	 * @param  string $new_value entered license key
	 * @return valid/invalid   whether key is valid or not
	 *
	 * @since 1.4.0
	 */
	public function activate_license( $new_value = '' ) {

		// listen for our activate button to be clicked
		if ( isset( $_POST['supersideme_activate'] ) ) {

			// run a quick security check
			if ( ! check_admin_referer( 'superside_license_nonce', 'superside_license_nonce' ) ) {
				return; // get out if we didn't click the Activate button
			}

			// retrieve the license from the database
			$license = trim( $this->supersideme_license );
			$license = $new_value !== $license ? trim( $new_value ) : $license;

			if ( empty( $license ) || empty( $new_value ) ) {
				delete_option( 'supersidemelicense_status' );
				return;
			}

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( EDD_SUPERSIDEME_NAME ), // the name of our product in EDD
				'url'        => esc_url( home_url() ),
			);

			// Call the custom API.
			$response = wp_remote_post( EDD_SUPERSIDEME_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "valid" or "invalid"
			update_option( 'supersidemelicense_status', $license_data->license );
		}
	}

	/**
	 * Deactivate license
	 * @return deletes license status key and deactivates with store
	 *
	 * @since 1.4.0
	 */
	function deactivate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST['supersideme_license_deactivate'] ) ) {

			// run a quick security check
		 	if ( ! check_admin_referer( 'superside_license_nonce', 'superside_license_nonce' ) ) {
				return; // get out if we didn't click the Activate button
		 	}

			// retrieve the license from the database
			$license = trim( $this->supersideme_license );

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( EDD_SUPERSIDEME_NAME ), // the name of our product in EDD
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post( EDD_SUPERSIDEME_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if ( 'deactivated' === $license_data->license ) {
				delete_option( 'supersidemelicense_status' );
			}
		}

	}

	/**
	 * Check plugin license status
	 * @return license data
	 *
	 * @since 1.4.0
	 */
	protected function check_license( $license = '' ) {
		if ( ! $this->supersideme_license ) {
			return false;
		}
		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license ? $license : $this->supersideme_license,
			'item_name'  => urlencode( EDD_SUPERSIDEME_NAME ), // the name of our product in EDD
			'url'        => esc_url( home_url() ),
		);

		// Call the custom API.
		$response = wp_remote_post( EDD_SUPERSIDEME_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		return $license_data;

	}

	/**
	 * Error messages
	 * @return error if license is empty or invalid
	 *
	 * @since 1.4.0
	 */
	public function do_error_message() {
		$screen = get_current_screen();
		if ( 'appearance_page_supersideme' !== $screen->id ) {
			return;
		}

		if ( false !== $this->supersideme_license && 'valid' === $this->supersideme_status ) {
			return;
		}
		if ( empty( $this->supersideme_license ) || false === $this->supersideme_status ) {
			$message = __( 'Please make sure you activate your SuperSide Me license in order to receive automatic updates and support.', 'superside-me' );
		} elseif ( $this->supersideme_license && 'valid' !== $this->supersideme_status ) {
			$message = __( 'Sorry, I don\'t recognize that license key, or you may have reached your activation limit. Can you please check and re-enter it?', 'superside-me' );
		}
		printf( '<div class="error notice"><p>%s</p></div>', wp_kses_post( $message ) );
	}
}

<?php
/**
 * General settings class.
 *
 * @author      Bas Elbers
 * @category    Admin
 * @package     BE_WooCommerce_PDF_Invoices/Admin
 * @version     1.0.0
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'BEWPI_General_Settings' ) ) {

	/**
	 * Class BEWPI_General_Settings.
	 */
	class BEWPI_General_Settings extends BEWPI_Abstract_Settings {

		/**
		 * BEWPI_General_Settings constructor.
		 */
		public function __construct() {
			$this->settings_key = 'bewpi_general_settings';
			$this->settings_tab = __( 'General', 'woocommerce-pdf-invoices' );
			$this->fields = $this->get_fields();
			$this->sections = $this->get_sections();
			$this->defaults = $this->get_defaults();

			parent::__construct();
		}

		/**
		 * Get all default values from the settings array.
		 *
		 * @return array
		 */
		public function get_defaults() {
			$fields = $this->get_fields();

			// Remove multiple checkbox types from settings.
			foreach ( $fields as $index => $field ) {
				if ( array_key_exists( 'type', $field ) && 'multiple_checkbox' === $field['type'] ) {
					unset( $fields[ $index ] );
				}
			}

			return array_merge( $this->get_multiple_checkbox_defaults(), wp_list_pluck( $fields, 'default', 'name' ) );
		}

		/**
		 * Fetch all multiple checkbox option defaults from settings.
		 */
		private function get_multiple_checkbox_defaults() {
			$defaults = array();

			foreach ( $this->fields as $field ) {
				if ( array_key_exists( 'type', $field ) && 'multiple_checkbox' === $field['type'] ) {
					$defaults = array_merge( $defaults, wp_list_pluck( $field['options'], 'default', 'value' ) );
				}
			}

			return $defaults;
		}

		/**
		 * Get all sections.
		 *
		 * @return array.
		 */
		private function get_sections() {
			$sections = array(
				'email' => array(
					'title' => __( 'Email Options', 'woocommerce-pdf-invoices' ),
					'description' => sprintf( __( 'The PDF invoice will be generated when WooCommerce sends the corresponding email. The email should be <a href="%1$s">enabled</a> in order to automatically generate the PDF invoice.', 'woocommerce-pdf-invoices' ), 'admin.php?page=wc-settings&tab=email' ),
				),
				'download' => array(
					'title' => __( 'Download Options', 'woocommerce-pdf-invoices' ),
				),
				'cloud_storage' => array(
					'title' => __( 'Cloud Storage Options', 'woocommerce-pdf-invoices' ),
					'description' => sprintf( __( 'Sign-up at <a href="%1$s">Email It In</a> to send invoices to your Dropbox, OneDrive, Google Drive or Egnyte and enter your account below.', 'woocommerce-pdf-invoices' ), 'https://emailitin.com' ),
				),
				'interface' => array(
					'title' => __( 'Interface Options', 'woocommerce-pdf-invoices' ),
				),
				'debug' => array(
					'title' => __( 'Debug Options', 'woocommerce-pdf-invoices' ),
				),
			);

			return $sections;
		}

		/**
		 * Settings fields.
		 *
		 * @return array
		 */
		private function get_fields() {
			$settings = array(
				array(
					'id'       => 'bewpi-email-types',
					'name'     => $this->prefix . 'email_types',
					'title'    => __( 'Attach to Emails', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'multiple_checkbox_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'email',
					'type'     => 'multiple_checkbox',
					'desc'     => '',
					'options'  => array(
						array(
							'name'    => __( 'New order', 'woocommerce-pdf-invoices' ),
							'value'   => 'new_order',
							'default' => 1,
						),
						array(
							'name'    => __( 'Order on-hold', 'woocommerce-pdf-invoices' ),
							'value'   => 'customer_on_hold_order',
							'default' => 0,
						),
						array(
							'name'    => __( 'Processing order', 'woocommerce-pdf-invoices' ),
							'value'   => 'customer_processing_order',
							'default' => 0,
						),
						array(
							'name'    => __( 'Completed order', 'woocommerce-pdf-invoices' ),
							'value'   => 'customer_completed_order',
							'default' => 1,
						),
						array(
							'name'    => __( 'Customer invoice', 'woocommerce-pdf-invoices' ),
							'value'   => 'customer_invoice',
							'default' => 0,
						),
					),
				),
				array(
					'id'       => 'bewpi-woocommerce-subscriptions-email-types',
					'name'     => $this->prefix . 'woocommerce_subscriptions_email_types',
					'title'    => sprintf( __( 'Attach to %s Emails', 'woocommerce-pdf-invoices' ), 'WooCommerce Subscriptions' )
					              . sprintf( ' <img src="%1$s" alt="%2$s" title="%2$s" width="18"/>', WPI_URL . '/assets/images/star-icon.png', __( 'Premium', 'woocommerce-pdf-invoices' ) ),
					'callback' => array( $this, 'multiple_checkbox_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'email',
					'type'     => 'multiple_checkbox',
					'desc'     => '',
					'options'  => array(
						array(
							'name'    => __( 'New Renewal Order', 'woocommerce-subscriptions' ),
							'value'   => 'new_renewal_order',
							'default' => 0,
							'disabled' => 1,
						),
						array(
							'name'      => __( 'Subscription Switch Complete', 'woocommerce-subscriptions' ),
							'value'     => 'customer_completed_switch_order',
							'default'   => 0,
							'disabled'  => 1,
						),
						array(
							'name'      => __( 'Processing Renewal order', 'woocommerce-subscriptions' ),
							'value'     => 'customer_processing_renewal_order',
							'default'   => 0,
							'disabled'  => 1,
						),
						array(
							'name'      => __( 'Completed Renewal Order', 'woocommerce-subscriptions' ),
							'value'     => 'customer_completed_renewal_order',
							'default'   => 0,
							'disabled'  => 1,
						),
						array(
							'name'      => __( 'Customer Renewal Invoice', 'woocommerce-subscriptions' ),
							'value'     => 'customer_renewal_invoice',
							'default'   => 0,
							'disabled'  => 1,
						),
					),
				),
				array(
					'id'       => 'bewpi-disable-free-products',
					'name'     => $this->prefix . 'disable_free_products',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'email',
					'type'     => 'checkbox',
					'desc'     => __( 'Disable for free products', 'woocommerce-pdf-invoices' )
					              . '<br/><div class="bewpi-notes">'
					              . __( 'Skip automatic PDF invoice generation for orders containing only free products.', 'woocommerce-pdf-invoices' )
					              . '</div>',
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 0,
				),
				array(
					'id'       => 'bewpi-view-pdf',
					'name'     => $this->prefix . 'view_pdf',
					'title'    => __( 'View PDF', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'select_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'download',
					'type'     => 'text',
					'desc'     => '',
					'options'  => array(
						array(
							'id'  => __( 'Download', 'woocommerce-pdf-invoices' ),
							'value' => 'download',
						),
						array(
							'id'  => __( 'Open in new browser tab/window', 'woocommerce-pdf-invoices' ),
							'value' => 'browser',
						),
					),
					'default'  => 'download',
				),
				array(
					'id'       => 'bewpi-download-invoice-account',
					'name'     => $this->prefix . 'download_invoice_account',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'download',
					'type'     => 'checkbox',
					'desc'     => __( 'Enable download from my account', 'woocommerce-pdf-invoices' )
					              . '<br/><div class="bewpi-notes">'
					              . __( 'By default PDF is only downloadable when order has been paid, so order status should be Processing or Completed.', 'woocommerce-pdf-invoices' )
					              . '</div>',
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 1,
				),
				array(
					'id'       => 'bewpi-email-it-in',
					'name'     => $this->prefix . 'email_it_in',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'cloud_storage',
					'type'     => 'checkbox',
					'desc'     => __( 'Enable Email It In', 'woocommerce-pdf-invoices' ),
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 0,
				),
				array(
					'id'       => 'bewpi-email-it-in-account',
					'name'     => $this->prefix . 'email_it_in_account',
					'title'    => __( 'Email It In account', 'woocommerce-pdf-invoices' ),
					'callback' => array( $this, 'input_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'cloud_storage',
					'type'     => 'text',
					'desc'     => sprintf( __( 'Get your account from your %1$s <a href="%2$s">user account</a>.', 'woocommerce-pdf-invoices' ), 'Email It In', 'https://www.emailitin.com/user_account' ),
					'default'  => '',
				),
				array(
					'id'       => 'bewpi-invoice-number-column',
					'name'     => $this->prefix . 'invoice_number_column',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'interface',
					'type'     => 'checkbox',
					'desc'     => __( 'Enable Invoice Number column', 'woocommerce-pdf-invoices' )
					              . '<br/><div class="bewpi-notes">' . __( 'Display invoice numbers on Shop Order page.', 'woocommerce-pdf-invoices' ) . '</div>',
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 1,
				),
				array(
					'id'       => 'bewpi-mpdf-debug',
					'name'     => $this->prefix . 'mpdf_debug',
					'title'    => '',
					'callback' => array( $this, 'input_callback' ),
					'page'     => $this->settings_key,
					'section'  => 'debug',
					'type'     => 'checkbox',
					'desc'     => __( 'Enable mPDF debugging', 'woocommerce-pdf-invoices' )
					              . '<br/><div class="bewpi-notes">' . __( 'Enable if you aren\'t able to create an invoice.', 'woocommerce-pdf-invoices' ) . '</div>',
					'class'    => 'bewpi-checkbox-option-title',
					'default'  => 0,
				),
			);

			return apply_filters( 'bewpi_general_settings', $settings );
		}

		/**
		 * Sanitize settings.
		 *
		 * @param array $input settings.
		 *
		 * @return mixed|void
		 */
		public function sanitize( $input ) {
			$output = get_option( $this->settings_key );

			foreach ( $input as $key => $value ) {
				// Strip all html and properly handle quoted strings.
				$output[ $key ] = stripslashes( $input[ $key ] );
			}

			// Sanitize email.
			if ( isset( $input['email_it_in_account'] ) ) {
				$sanitized_email = sanitize_email( $input['email_it_in_account'] );
				$output['email_it_in_account'] = $sanitized_email;
			}

			return apply_filters( 'bewpi_sanitized_' . $this->settings_key, $output, $input );
		}
	}
}

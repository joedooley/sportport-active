<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class for modifying the admin pages
 */
class WC_Admin_Terms_Conditions_Popup {

	/**
	 * Constructor
	 */
	public function __construct() {

		// modify the existing email settings
		add_action( 'woocommerce_payment_gateways_settings', array( $this, 'checkout_settings' ) );
	}


	/**
	 * Add new settings to the checkout settings page
	 *
	 * @param  mixed $settings
	 * @return mixed
	 * @since  1.0
	 */
	public function checkout_settings( $settings ) {

		// configure our new settings
		$new_settings[] = array(
			'title' => __( 'Terms & Conditions Popup', 'woocommerce-terms-conditions-popup' ),
			'desc'          => __( 'Add an "Agree" and "Decline" button', 'woocommerce-terms-conditions-popup' ),
			'id'            => 'wc_tcp_agree_button',
			'default'       => 'no',
			'type'          => 'checkbox',
			'desc_tip'		=>  __( 'This forces the user to scroll to the bottom of the popup to click either the "Agree" or "Decline" button.', 'woocommerce-terms-conditions-popup' ),
			'autoload'      => false
		);

		// find the position of the "Terms and Conditions" setting
		$position = 9; // should be 9 unless another plugin modified this
		foreach ( $settings as $key => $setting ) {
			// NOTE: I tried using array_search() with unusual results so I'm manually checking this way instead.
			if ( isset( $setting['id'] ) && 'woocommerce_terms_page_id' == $setting['id'] ) {
				$position = $key;
				break;
			}
		}

		// add the new settings to the existing settings
		array_splice( $settings, $position + 1, 0, $new_settings );

		return $settings;
	}

}

new WC_Admin_Terms_Conditions_Popup();

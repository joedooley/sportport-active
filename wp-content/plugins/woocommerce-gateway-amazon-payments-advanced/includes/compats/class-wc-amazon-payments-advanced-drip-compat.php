<?php

/**
 * WooCommerce APA compatibility with Drip extension.
 *
 * @since 1.6.0
 */
class WC_Amazon_Payments_Advanced_Drip_Compat {

	public function __construct() {
		add_action( 'wc_amazon_pa_scripts_enqueued', array( $this, 'drip_compat_scripts' ) );
	}

	public function drip_compat_scripts() {
		$url = wc_apa()->plugin_url . '/assets/js/amazon-wcdrip-compat.js';
		wp_enqueue_script( 'amazon_pa_drip_compat', $url, array( 'amazon_payments_advanced' ), wc_apa()->version, true );
	}

}

<?php
/**
 * Plugin Name: WooCommerce Terms & Conditions Popup
 * Plugin URI: https://woothemes.com/
 * Description: On the checkout page launch the Terms & Conditions in a popup (lightbox) to create a seamless user experience.
 * Author: Patrick Rauland
 * Author URI: http://speakinginbytes.com/
 * Version: 1.0.2
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
* Required functions
*/
if ( ! function_exists( 'woothemes_queue_update' ) || ! function_exists( 'is_woocommerce_active' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
* Plugin updates
*/
woothemes_queue_update( plugin_basename( __FILE__ ), 'b801718d1048cebe929087c14c9e8651', '512162' );

if ( ! class_exists( 'WC_Terms_Conditions_Popup' ) ) :

class WC_Terms_Conditions_Popup {

	protected static $instance = null;
	private $id                = 'wc_terms_conditions_popup';
	private $version           = '1.0.2';
	private $terms_page_id;
	private $force_agree;

	/**
	 * Initialize the plugin.
	 *
	 * @since 1.0
	 */
	private function __construct() {
		if ( class_exists( 'WooCommerce' ) && function_exists( 'is_checkout' ) ) {

			// load content + scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		}

		if ( is_admin() ) {
			// load admin files
			$this->admin_includes();
		}
	}


	/**
	 * Load scripts
	 *
	 * @since 1.0
	 */
	public function enqueue() {
		//only run on the checkout page
		if ( is_checkout() ) {

			// get terms & conditions page
			$this->terms_page_id = wc_get_page_id( "terms" );

			// get options
			$this->agree_button = get_option( 'wc_tcp_agree_button' );

			// make sure that we have a terms page
			if ( 0 < $this->terms_page_id ) {

				// add content
				$this->load_content();

				// load the appropriate scripts
				wp_enqueue_script( 'wc-terms-conditions-popup', plugins_url( '/assets/scripts/terms-conditions-popup.js', __FILE__ ), array( 'jquery', 'thickbox' ), $this->version, true );

				$data = array( 'title' => $this->terms_page_title, 'force_agree' => $this->agree_button );
				wp_localize_script( 'wc-terms-conditions-popup', 'WC_MTC_Data', $data );

				// init thickbox
				add_thickbox();
			}

		}
	}


	/**
	 * Load & print the popup content
	 *
	 * @see   wc_get_template();
	 * @since 1.0
	 */
	public function load_content() {

		// get terms & conditions post content
		$terms_page = get_post( $this->terms_page_id );

		// save the title for future use
		$this->terms_page_title = $terms_page->post_title;

		// load the close button (if necessary);
		$button = '';
		if ( 'yes' == $this->agree_button ) {
			// add decline button
			$button = '<input type="submit" id="' . $this->id . '-decline" class="button" value="' . __( 'Decline', 'woocommerce-terms-conditions-popup' ) . '" onclick="tb_remove()">';

			// add agree button
			$button .= '&nbsp;<input type="submit" id="' . $this->id . '-agree" class="button" value="' . __( 'Agree', 'woocommerce-terms-conditions-popup' ) . '" onclick="tb_remove()">';
		}

		// load template content
		// template can be overridden by placing a template in /your-theme/woocommerce/terms-conditions-popup/popup.php
		wc_get_template( 'popup.php', array( 'id' => $this->id . '-popup', 'content' => $terms_page->post_content, 'button' => $button ), 'woocommerce/terms-conditions-popup', dirname(__FILE__) . '/templates/' );

	}


	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 * @since  1.0
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	/**
	 * Include admin classes
	 *
	 * @since  1.0
	 */
	public static function admin_includes() {
		include( 'includes/admin/class-wc-admin-terms-conditions-popup.php' );
	}

}

add_action( 'plugins_loaded', array( 'WC_Terms_Conditions_Popup', 'get_instance' ), 0 );

endif;

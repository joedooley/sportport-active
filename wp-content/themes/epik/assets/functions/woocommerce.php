<?php
/**
 * This file contains all WooCommerce specific functions
 *
 * @author     Joe Dooley
 * @package    SportPort Active Theme
 * @subpackage Customizations
 */


add_action( 'template_redirect', 'remove_sidebar_shop' );
/**
 * Remove Sidebar from Shop and Single Product pages
 *
 * @return      void
 * @author      Joe Dooley
 */
function remove_sidebar_shop() {

	if ( is_product() || is_shop() ) {
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar' );
	}

}



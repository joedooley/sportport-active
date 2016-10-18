<?php
/**
 * This template displays the Product Category and Tag taxonomy term archives
 *
 * @package genesis_connect_woocommerce
 * @version 0.9.8
 *
 * Note for customisers/users: Do not edit this file!
 * ==================================================
 * If you want to customise this template, copy this file (keep same name) and place the
 * copy in the child theme's woocommerce folder, ie themes/my-child-theme/woocommerce
 * (Your theme may not have a 'woocommerce' folder, in which case create one.)
 * The version in the child theme's woocommerce folder will override this template, and
 * any future updates to this plugin won't wipe out your customisations.
 *
 */

/** Remove default Genesis loop */
remove_action( 'genesis_loop', 'genesis_do_loop' );

/** Remove WooCommerce breadcrumbs */
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

/** Uncomment the below line of code to add back WooCommerce breadcrumbs */
//add_action( 'genesis_before_loop', 'woocommerce_breadcrumb', 10, 0 );

/** Remove Woo #container and #content divs */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );


add_action( 'genesis_loop', 'genesiswooc_product_taxonomy_loop' );
/**
 * Displays shop items for the queried taxonomy term
 *
 * This function has been refactored in 0.9.4 to provide compatibility with
 * both WooC 1.6.0 and backwards compatibility with older versions.
 * This is needed thanks to substantial changes to WooC template contents
 * introduced in WooC 1.6.0.
 *
 * @uses genesiswooc_content_product() if WooC is version 1.6.0+
 * @uses genesiswooc_product_taxonomy() for earlier WooC versions
 *
 * @since 0.9.0
 * @updated 0.9.4
 */
function genesiswooc_product_taxonomy_loop() {

	global $woocommerce;

	$new = version_compare( $woocommerce->version, '1.6.0', '>=' );

	if ( $new )
		genesiswooc_content_product();

	else
		genesiswooc_product_taxonomy();

}

genesis();

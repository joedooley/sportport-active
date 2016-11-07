<?php
/**
 * Backwards compat.
 *
 * @since 1.6.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_plugins = get_option( 'active_plugins', array() );
foreach ( $active_plugins as $key => $active_plugin ) {
	if ( strstr( $active_plugin, '/amazon-payments-advanced.php' ) ) {
		$active_plugins[ $key ] = str_replace( '/amazon-payments-advanced.php', '/woocommerce-gateway-amazon-payments-advanced.php', $active_plugin );
	}
}
update_option( 'active_plugins', $active_plugins );

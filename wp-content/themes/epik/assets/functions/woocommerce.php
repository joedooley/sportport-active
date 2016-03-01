<?php

add_action( 'template_redirect', 'remove_sidebar_shop' );
function remove_sidebar_shop() {
	if ( is_product() ) {
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar' );
	}
}

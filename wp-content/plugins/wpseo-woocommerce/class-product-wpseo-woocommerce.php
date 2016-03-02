<?php

if ( ! class_exists( 'Yoast_Product_WPSEO_WooCommerce' ) ) {

	/**
	 * Class Yoast_Product_WPSEO_WooCommerce
	 */
	class Yoast_Product_WPSEO_WooCommerce extends Yoast_Product {

		public function __construct() {
			parent::__construct(
				'https://yoast.com',
				'WooCommerce Yoast SEO',
				plugin_basename( Yoast_WooCommerce_SEO::get_plugin_file() ),
				Yoast_WooCommerce_SEO::VERSION,
				'https://yoast.com/wordpress/plugins/yoast-woocommerce-seo/',
				'admin.php?page=wpseo_licenses#top#licenses',
				'yoast-woo-seo',
				'Yoast'
			);
		}
	}
}

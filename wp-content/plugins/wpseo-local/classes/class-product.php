<?php

if( ! class_exists( 'Yoast_Product_WPSEO_Local' ) ) {

	/**
	 * Class Yoast_Product_WPSEO_Local
	 */
	class Yoast_Product_WPSEO_Local extends Yoast_Product {

		public function __construct() {
			parent::__construct(
					'https://yoast.com/edd-sl-api',
					'Local SEO for WordPress',
					plugin_basename( WPSEO_LOCAL_FILE ),
					WPSEO_LOCAL_VERSION,
					'https://yoast.com/wordpress/plugins/local-seo/',
					'admin.php?page=wpseo_licenses#top#licenses',
					'yoast-local-seo',
					'Yoast'
			);
		}

	}

}

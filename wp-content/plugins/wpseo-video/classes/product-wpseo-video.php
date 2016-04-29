<?php

// Avoid direct calls to this file
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'Yoast_Product_WPSEO_Video' ) ) {
	/**
	 * Class Yoast_Product_WPSEO_Video
	 *
	 * Our Yoast_Product_WPSEO_Video class
	 */
	class Yoast_Product_WPSEO_Video extends Yoast_Product {


		/**
		 * Set up the WPSEO_Video product
		 */
		public function __construct() {
			parent::__construct(
				'http://yoast.com/edd-sl-api',
				'Video SEO for WordPress',
				plugin_basename( WPSEO_Video_Sitemap::get_plugin_file() ),
				WPSEO_VIDEO_VERSION,
				'https://yoast.com/wordpress/plugins/video-seo/',
				'admin.php?page=wpseo_licenses#top#licenses',
				'yoast-video-seo',
				'Yoast'
			);
		}

	} /* End of class */

} /* End of class-exists wrapper */

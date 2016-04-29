<?php
/**
 * Code used when the plugin is removed (not just deactivated but actively deleted through the WordPress Admin).
 *
 * @package Video SEO for WordPress SEO by Yoast
 * @subpackage Uninstall
 * @since 1.6.4
 */

if ( ! current_user_can( 'activate_plugins' ) || ( ! defined( 'ABSPATH' ) || ! defined( 'WP_UNINSTALL_PLUGIN' ) ) ) {
	exit();
}

delete_option( 'wpseo_video' );

/* Remove video Post Meta data */
/*$wpseo_video_meta_keys = array(
	'_yoast_wpseo_videositemap-disable',
	'_yoast_wpseo_videositemap-thumbnail',
	'_yoast_wpseo_videositemap-duration',
	'_yoast_wpseo_videositemap-tags',
	'_yoast_wpseo_videositemap-category',
	'_yoast_wpseo_videositemap-rating',
	'_yoast_wpseo_videositemap-not-family-friendly',
	'_yoast_wpseo_video_meta',
	'wpseo_video_id',
);

foreach ( $wpseo_video_meta_keys as $key ) {
	delete_post_meta_by_key( $key );
}*/
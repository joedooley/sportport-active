<?php
/**
 * @package Yoast\VideoSEO
 */

/**
 * Throw an error if WordPress SEO is not installed.
 *
 * @since 0.2
 */
function yoast_wpseo_missing_error() {
	$url = add_query_arg(
		array(
			'tab'                 => 'search',
			'type'                => 'term',
			's'                   => 'wordpress+seo',
			'plugin-search-input' => 'Search+Plugins',
		),
		admin_url( 'plugin-install.php' )
	);

	/* translators: %1$s and %3$s expand to anchor tags with a link to the download page for Yoast SEO . &2$s expands to Yoast SEO.*/
	$message = sprintf( esc_html__( 'Please %1$sinstall & activate %2$s%3$s and then enable its XML sitemap functionality to allow the Video SEO module to work.', 'yoast-video-seo' ), '<a href="' . esc_url( $url ) . '">', 'Yoast SEO', '</a>' );

	yoast_wpseo_video_seo_self_deactivate( $message, false );
}


/**
 * Throw an error if WordPress is out of date.
 *
 * @since 1.5.4
 */
function yoast_wordpress_upgrade_error() {
	$message = esc_html__( 'Please upgrade WordPress to the latest version to allow WordPress and the Video SEO module to work properly.', 'yoast-video-seo' );
	yoast_wpseo_video_seo_self_deactivate( $message );
}


/**
 * Throw an error if WordPress SEO is out of date.
 *
 * @since 1.5.4
 */
function yoast_wpseo_upgrade_error() {
	/* translators: $1$s expands to Yoast SEO.*/
	$message = sprintf( esc_html__( 'Please upgrade the %1$s plugin to the latest version to allow the Video SEO module to work.', 'yoast-video-seo' ), 'Yoast SEO' );
	yoast_wpseo_video_seo_self_deactivate( $message );
}


/**
 * Throw an error if the PHP SPL extension is disabled (prevent white screens)
 *
 * @since 1.7
 */
function yoast_phpspl_missing_error() {
	$message = esc_html__( 'The PHP SPL extension seem to be unavailable. Please ask your web host to enable it.', 'yoast-video-seo' );
	yoast_wpseo_video_seo_self_deactivate( $message );
}


/**
 * Initialize the Video SEO module on plugins loaded, so WP SEO should have set its constants and loaded its main classes.
 *
 * @since 0.2
 */
function yoast_wpseo_video_seo_init() {
	if ( ! function_exists( 'spl_autoload_register' ) ) {
		add_action( 'admin_init', 'yoast_phpspl_missing_error' );
	}
	elseif ( ! version_compare( $GLOBALS['wp_version'], '3.6', '>=' ) ) {
		add_action( 'admin_init', 'yoast_wordpress_upgrade_error' );
	}
	else {
		if ( defined( 'WPSEO_VERSION' ) ) {
			// Allow beta version.
			if ( version_compare( WPSEO_VERSION, '1.4.99', '>=' ) ) {
				add_action( 'plugins_loaded', 'yoast_wpseo_video_seo_meta_init', 10 );
				add_action( 'plugins_loaded', 'yoast_wpseo_video_seo_sitemap_init', 20 );
			}
			else {
				add_action( 'admin_init', 'yoast_wpseo_upgrade_error' );
			}
		}
		else {
			add_action( 'admin_init', 'yoast_wpseo_missing_error' );
		}
	}
	add_action( 'init', array( 'WPSEO_Video_Sitemap', 'load_textdomain' ), 1 );
}


/**
 * Initialize the video meta data class
 */
function yoast_wpseo_video_seo_meta_init() {
	WPSEO_Meta_Video::init();
}


/**
 * Initialize the main plugin class
 */
function yoast_wpseo_video_seo_sitemap_init() {
	$GLOBALS['wpseo_video_xml'] = new WPSEO_Video_Sitemap();
}

/**
 * Self-deactivate plugin
 *
 * @since 1.7
 *
 * @param string $message    Error message.
 * @param bool   $use_prefix Prefix the text with Activation.
 */
function yoast_wpseo_video_seo_self_deactivate( $message, $use_prefix = true ) {
	if ( is_admin() && ( ! defined( 'IFRAME_REQUEST' ) || IFRAME_REQUEST === false ) ) {

		$prefix = ( $use_prefix ) ? __( '(Re-)Activation of Video SEO failed: ', 'yoast-video-seo' ) : '';

		$function_code = <<<EO_FUNCTION
echo '<div class="error"><p>{$prefix}{$message}</p></div>';
EO_FUNCTION;

		add_action( 'admin_notices', create_function( '', $function_code ) );

		deactivate_plugins( plugin_basename( WPSEO_VIDEO_FILE ) );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}


/**
 * Execute option cleanup actions on activate
 */
function yoast_wpseo_video_activate() {
	WPSEO_Video_Sitemap::load_textdomain();

	if ( ! defined( 'WPSEO_VERSION' ) ) {
		return;
	}

	$option_instance = WPSEO_Option_Video::get_instance();
	$option_instance->clean();

	if ( ! class_exists( 'Yoast_Plugin_License_Manager' ) ) {
		return;
	}

	// Activate the license.
	$license_manager = new Yoast_Plugin_License_Manager( new Yoast_Product_WPSEO_Video() );
	$license_manager->activate_license();
}

<?php
/**
 * Plugin Name: Yoast SEO: ACF Analysis
 * Plugin URI: https://forsberg.ax
 * Description: Adds the content of all ACF fields to the Yoast SEO score analysis.
 * Version: 1.2.0
 * Author: Marcus Forsberg & Team Yoast
 * Author URI: https://forsberg.ax
 * License: GPL v3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'YOAST_ACF_ANALYSIS_FILE ' ) ) {
	define( 'YOAST_ACF_ANALYSIS_FILE', __FILE__ );
}

/**
 * Class Yoast_ACF_Analysis
 *
 * Adds ACF data to the content analyses of WordPress SEO
 *
 */
class Yoast_ACF_Analysis {

	const VERSION = '1.2.0';

	/**
	 * Yoast_ACF_Analysis constructor.
	 *
	 * Add hooks and filters.
	 */
	function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		add_filter( 'wpseo_post_content_for_recalculation', array( $this, 'add_recalculation_data_to_post_content' ) );
		add_filter( 'wpseo_term_description_for_recalculation', array(
			$this,
			'add_recalculation_data_to_term_content'
		) );
	}

	/**
	 * Add notifications to admin if plugins ACF or WordPress SEO are not present.
	 */
	public function admin_init() {

		$notice_functions = array();

		// Check for: Yoast SEO for WordPress.
		if ( defined( 'WPSEO_VERSION' ) ) {
			// Make sure that version is >= 3.1
			if ( version_compare( substr( WPSEO_VERSION, 0, 3 ), '3.1', '<' ) ) {
				$notice_functions[] = 'wordpress_seo_requirements_not_met';
			}
		}
		else  {
			$notice_functions[] = 'wordpress_seo_requirements_not_met';
		}

		// Check for: ACF.
		if ( ! class_exists( 'acf' ) && ! is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {
			$notice_functions[] = 'acf_not_active_notification';
		}

		// Enqueue when no problems were found.
		if ( empty( $notice_functions ) ) {
			// Make sure we load very late to be able to check enqueue of scripts we depend on.
			add_filter( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 999 );
		}
		else {
			// Show notices to users who can act on them.
			if ( current_user_can( 'activate_plugins' ) ) {
				$this->show_notices( $notice_functions );

				// Deactivate this plugin if we are not a mu-plugin.
				if ( is_plugin_active( plugin_basename( YOAST_ACF_ANALYSIS_FILE ) ) ) {
					$this->deactivate();
				}
			}
		}
	}

	/**
	 * Notify that we need ACF to be installed and active.
	 */
	public function acf_not_active_notification() {

		$message = sprintf(
			__( 'Please %1$sinstall & activate Advanced Custom Fields%2$s to use Yoast SEO: ACF Analysis.', 'yoast-acf-analysis' ),
			'<a href="' . esc_url( admin_url( 'plugin-install.php?tab=search&type=term&s=advanced+custom+fields&plugin-search-input=Search+Plugins' ) ) . '">',
			'</a>'
		);

		printf( '<div class="error"><p>%s</p></div>', $message );
	}

	/**
	 * Notify that we need Yoast SEO for WordPress to be installed and active.
	 */
	public function wordpress_seo_requirements_not_met() {
		$message = sprintf(
			__( 'Please %1$sinstall & activate Yoast SEO 3.1+%2$s to use Yoast SEO: ACF Analysis.', 'yoast-acf-analysis' ),
			'<a href="' . esc_url( admin_url( 'plugin-install.php?tab=search&type=term&s=yoast+seo&plugin-search-input=Search+Plugins' ) ) . '">',
			'</a>'
		);

		printf( '<div class="error"><p>%s</p></div>', $message );
	}

	/**
	 * Enqueue JavaScript file to feed data to Yoast Content Analyses.
	 */
	public function enqueue_scripts() {

		// If the Asset Manager exists then we need to use a different prefix.
		$script_prefix = ( class_exists( 'WPSEO_Admin_Asset_Manager' ) ? 'yoast-seo' : 'wp-seo' );

		if ( wp_script_is( $script_prefix . '-post-scraper', 'enqueued' ) ) {
			// Post page enqueue.
			wp_enqueue_script(
				$script_prefix . '-analysis-post',
				plugins_url( '/js/yoast-acf-analysis.js', YOAST_ACF_ANALYSIS_FILE ),
				array(
					'jquery',
					$script_prefix . '-post-scraper',
				),
				self::VERSION
			);
		}

		if ( wp_script_is( $script_prefix . '-term-scraper', 'enqueued' ) ) {
			// Term page enqueue.
			wp_enqueue_script(
				$script_prefix . '-analysis-term',
				plugins_url( '/js/yoast-acf-analysis.js', YOAST_ACF_ANALYSIS_FILE ),
				array(
					'jquery',
					$script_prefix . '-term-scraper',
				),
				self::VERSION
			);
		}
	}

	/**
	 * Add ACF data to post content
	 *
	 * @param string  $content String of the content to add data to.
	 * @param WP_Post $post    Item the content belongs to.
	 *
	 * @return string Content with added ACF data.
	 */
	public function add_recalculation_data_to_post_content( $content, $post ) {
		// ACF defines this function.
		if ( ! function_exists( 'get_fields' ) ) {
			return '';
		}

		if ( false === ( $post instanceof WP_Post ) ) {
			return '';
		}

		$post_acf_fields = get_fields( $post->ID );
		$acf_content     = $this->get_field_data( $post_acf_fields );

		return trim( $content . ' ' . $acf_content );
	}

	/**
	 * Add custom fields to term content
	 *
	 * @param string  $content String of the content to add data to.
	 * @param WP_Term $term    The term to get the custom ffields of.
	 *
	 * @return string Content with added ACF data.
	 */
	public function add_recalculation_data_to_term_content( $content, $term ) {
		// ACF defines this function.
		if ( ! function_exists( 'get_fields' ) ) {
			return '';
		}

		if ( false === ( $term instanceof WP_Term ) ) {
			return '';
		}

		$term_acf_fields = get_fields( $term->taxonomy . '_' . $term->term_id );
		$acf_content     = $this->get_field_data( $term_acf_fields );

		return trim( $content . ' ' . $acf_content );
	}

	/**
	 * Filter what ACF Fields not to score
	 *
	 * @param array $fields ACF Fields to parse.
	 *
	 * @return string Content of all ACF fields combined.
	 */
	private function get_field_data( $fields ) {
		$output = '';

		if ( ! is_array( $fields ) ) {
			return $output;
		}

		foreach ( $fields as $key => $field ) {
			switch ( gettype( $field ) ) {
				case 'string':
					$output .= ' ' . $field;
					break;

				case 'array':
					if ( isset( $field['sizes']['thumbnail'] ) ) {
						// Put all images in img tags for scoring.
						$alt = ( isset( $field['alt'] ) ) ? $field['alt'] : '';
						$output .= ' <img src="' . esc_url( $field['sizes']['thumbnail'] ) . '" alt="' . esc_attr( $alt ) . '" />';
					}
					else {
						$output .= ' ' . $this->get_field_data( $field );
					}

					break;
			}
		}

		return trim( $output );
	}

	/**
	 * Show the notices that are queued
	 *
	 * @param array $notice_functions Array of functions to call.
	 */
	private function show_notices( $notice_functions ) {
		foreach ( $notice_functions as $function ) {
			add_action( 'admin_notices', array( $this, $function ) );
		}
	}

	/**
	 * Deactivate this plugin
	 */
	private function deactivate() {
		$file = plugin_basename( YOAST_ACF_ANALYSIS_FILE );
		deactivate_plugins( $file, false, is_network_admin() );

		// Add to recently active plugins list.
		if ( ! is_network_admin() ) {
			update_option( 'recently_activated', array( $file => $_SERVER['REQUEST_TIME'] ) + (array) get_option( 'recently_activated' ) );
		}
		else {
			update_site_option( 'recently_activated', array( $file => $_SERVER['REQUEST_TIME'] ) + (array) get_site_option( 'recently_activated' ) );
		}

		// Prevent trying again on page reload.
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}

new Yoast_ACF_Analysis();

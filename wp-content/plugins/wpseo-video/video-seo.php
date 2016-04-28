<?php
/*
Plugin Name: Video SEO for WordPress SEO by Yoast
Version: 3.2
Plugin URI: https://yoast.com/wordpress/plugins/video-seo/
Description: This Video SEO module adds all needed meta data and XML Video sitemap capabilities to the metadata capabilities of WordPress SEO to fully optimize your site for video results in the search results.
Author: Team Yoast
Author URI: https://yoast.com
Depends: WordPress SEO
Text Domain: yoast-video-seo
Domain Path: /languages/

Copyright 2012-2014 Yoast BV

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload_52.php' ) ) {
	require dirname( __FILE__ ) . '/vendor/autoload_52.php';
}

define( 'WPSEO_VIDEO_VERSION', '3.2' );
define( 'WPSEO_VIDEO_FILE', __FILE__ );

/**
 * Class WPSEO_Video_Wrappers
 */
class WPSEO_Video_Wrappers {

	/**
	 * Fallback function for WP SEO functionality, Validate INT
	 *
	 * @param $string
	 *
	 * @return mixed
	 */
	public static function yoast_wpseo_video_validate_int( $string ) {
		if( method_exists( 'WPSEO_Utils', 'validate_int' ) ){
			return WPSEO_Utils::validate_int( $string );
		}

		return WPSEO_Option::validate_int( $string );
	}

	/**
	 * Fallback function for WP SEO functionality, is_url_relative
	 *
	 * @param $url
	 *
	 * @return mixed
	 */
	public static function yoast_wpseo_video_is_url_relative( $url ){
		if( method_exists( 'WPSEO_Utils', 'is_url_relative' ) ){
			return WPSEO_Utils::is_url_relative( $url );
		}

		return wpseo_is_url_relative( $url );
	}

	/**
	 * Fallback funciton for WP SEO functionality, sanitize_url
	 *
	 * @param $string
	 *
	 * @return mixed
	 */
	public static function yoast_wpseo_video_sanitize_url( $string ) {
		if( method_exists( 'WPSEO_Utils', 'sanitize_url' ) ){
			return WPSEO_Utils::sanitize_url( $string, array( 'http', 'https', 'ftp', 'ftps' ) );
		}

		return WPSEO_Option::sanitize_url( $string, array( 'http', 'https', 'ftp', 'ftps' ) );
	}

	/**
	 * Fallback for admin_header
	 *
	 * @param bool   $form
	 * @param string $option_long_name
	 * @param string $option
	 * @param bool   $contains_files
	 *
	 * @return mixed
	 */
	public static function admin_header( $form = true, $option_long_name = 'yoast_wpseo_options', $option = 'wpseo', $contains_files = false ) {

		if ( method_exists( 'Yoast_Form', 'admin_header' ) ) {
			Yoast_Form::get_instance()->admin_header( $form, $option, $contains_files, $option_long_name );

			return;
		}

		return self::admin_pages()->admin_header( true, $option_long_name, $option );
	}

	/**
	 * Fallback for admin_footer
	 *
	 * @param bool $submit
	 * @param bool $show_sidebar
	 *
	 * @return mixed
	 */
	public static function admin_footer( $submit = true, $show_sidebar = true ) {

		if ( method_exists( 'Yoast_Form', 'admin_footer' ) ) {
			Yoast_Form::get_instance()->admin_footer( $submit, $show_sidebar );

			return;
		}

		return self::admin_pages()->admin_footer( $submit, $show_sidebar );
	}

	/**
	 * Fallback for the textinput method
	 *
	 * @param string $var
	 * @param string $label
	 * @param string $option
	 *
	 * @return mixed
	 */
	public static function textinput($var, $label, $option = '') {
		if ( method_exists( 'Yoast_Form', 'textinput' ) ) {
			if ( $option !== '' ) {
				Yoast_Form::get_instance()->set_option( $option );
			}

			Yoast_Form::get_instance()->textinput( $var, $label );

			return;
		}

		return self::admin_pages()->textinput($var, $label, $option);
	}

	/**
	 * Wrapper for select method.
	 *
	 * @param string $var
	 * @param string $label
	 * @param array  $values
	 * @param string $option
	 */
	public static function select( $var, $label, $values, $option = '' ) {
		if ( method_exists( 'Yoast_Form', 'select' ) ) {
			if ( $option !== '' ) {
				Yoast_Form::get_instance()->set_option( $option );
			}

			Yoast_Form::get_instance()->select( $var, $label, $values );
			return;
		}

		return self::admin_pages()->select($var, $label, $option);
	}

	/**
	 * Wrapper for checkbox method
	 *
	 * @param        $var
	 * @param        $label
	 * @param bool   $label_left
	 * @param string $option
	 *
	 * @return mixed
	 */
	public static function checkbox( $var, $label, $label_left = false, $option = '' ) {
		if ( method_exists( 'Yoast_Form', 'checkbox' ) ) {
			if ( $option !== '' ) {
				Yoast_Form::get_instance()->set_option( $option );
			}

			Yoast_Form::get_instance()->checkbox( $var, $label, $label_left );
			return;
		}

		return self::admin_pages()->checkbox($var, $label, $label_left, $option);
	}

	/**
	 * Returns the wpseo_admin pages global variable
	 *
	 * @return mixed
	 */
	private static function admin_pages() {
		global $wpseo_admin_pages;

		if ( ! $wpseo_admin_pages instanceof WPSEO_Admin_Pages ) {
			$wpseo_admin_pages = new WPSEO_Admin_Pages;
		}

		return $wpseo_admin_pages;
	}

	/**
	 * Returns the result of validate bool from WPSEO_Utils if this class exists, otherwise it will return the result from
	 * validate_bool from WPSEO_Option_Video
	 *
	 * @param $bool_to_validate
	 *
	 * @return bool
	 */
	public static function validate_bool( $bool_to_validate ) {
		if ( class_exists( 'WPSEO_Utils' ) &&  method_exists( 'WPSEO_Utils', 'validate_bool' ) ) {
			return WPSEO_Utils::validate_bool( $bool_to_validate );
		}

		return WPSEO_Option_Video::validate_bool( $bool_to_validate );
	}

}

/**
 * All functionality for fetching video data and creating an XML video sitemap with it.
 *
 * @link       http://codex.wordpress.org/oEmbed oEmbed Codex Article
 * @link       http://oembed.com/ oEmbed Homepage
 *
 * @package    WordPress SEO
 * @subpackage WordPress SEO Video
 */

/**
 * wpseo_video_Video_Sitemap class.
 *
 * @package WordPress SEO Video
 * @since   0.1
 */
class WPSEO_Video_Sitemap {

	/**
	 * @var int The maximum number of entries per sitemap page
	 */
	private $max_entries = 5;

	/**
	 * @var
	 */
	private $metabox_tab;

	/**
	 * @var object Option object
	 */
	protected $option_instance;

	/**
	 * @var    string    Youtube video ID regex pattern
	 */
	public static $youtube_id_pattern = '[0-9a-zA-Z_-]+';

	/**
	 * @var    string    Video extension list for use in regex pattern
	 *
	 * @todo - shouldn't this be a class constant ?
	 */
	public static $video_ext_pattern = 'mpg|mpeg|mp4|m4v|mov|ogv|wmv|asf|avi|ra|ram|rm|flv|swf';

	/**
	 * @var    string    Image extension list for use in regex pattern
	 *
	 * @todo - shouldn't this be a class constant ?
	 */
	public static $image_ext_pattern = 'jpg|jpeg|jpe|gif|png';


	/**
	 * Constructor for the WPSEO_Video_Sitemap class.
	 *
	 * @todo  Deal with upgrade from license constant WPSEO_VIDEO_LICENSE
	 * @since 0.1
	 */
	public function __construct() {

		// Initialize the options
		$this->option_instance = WPSEO_Option_Video::get_instance();

		$options = get_option( 'wpseo_video' );

		// run upgrade routine
		$this->upgrade();

		add_filter( 'wpseo_tax_meta_special_term_id_validation__video', array( $this, 'validate_video_tax_meta' ) );


		if ( ! isset( $GLOBALS['content_width'] ) && $options['content_width'] > 0 ) {
			$GLOBALS['content_width'] = $options['content_width'];
		}

		add_action( 'setup_theme', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'register_sitemap' ), 20 ); // Register sitemap after cpts have been added
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_item' ), 97 );
		add_filter( 'oembed_providers', array( $this, 'sync_oembed_providers' ) );

		if ( is_admin() ) {

			add_action( 'admin_menu', array( $this, 'register_settings_page' ) );

			add_action( 'save_post', array( $this, 'update_video_post_meta' ) );

			if ( in_array( $GLOBALS['pagenow'], array(
					'edit.php',
					'post.php',
					'post-new.php'
				) ) || apply_filters( 'wpseo_always_register_metaboxes_on_admin', false )
			) {
				$this->metabox_tab = new WPSEO_Video_Metabox();
			}

			// Licensing part
			$yoast_product   = new Yoast_Product_WPSEO_Video();
			$license_manager = new Yoast_Plugin_License_Manager( $yoast_product );

			// Setup constant name
			$license_manager->set_license_constant_name( 'WPSEO_VIDEO_LICENSE' );

			// Setup hooks
			$license_manager->setup_hooks();

			// Add form
			add_action( 'wpseo_licenses_forms', array( $license_manager, 'show_license_form' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_video_enqueue_scripts' ) );

			add_action( 'admin_init', array( $this, 'admin_video_enqueue_styles' ) );

			add_action( 'wp_ajax_index_posts', array( $this, 'index_posts_callback' ) );

			add_action( 'save_post', array( $this, 'invalidate_sitemap' ) );

			// Setting action for removing the transient on update options
			if ( method_exists( 'WPSEO_Utils', 'register_cache_clear_option' ) ) {
				WPSEO_Utils::register_cache_clear_option( 'wpseo_video', $this->video_sitemap_basename() );
			}

			// Maybe show 'Recommend re-index' admin notice
			if ( get_transient( 'video_seo_recommend_reindex' ) === '1' ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_ignore' ) );
				add_action( 'all_admin_notices', array( $this, 'recommend_force_index' ) );
				add_action( 'wp_ajax_videoseo_set_ignore', array( $this, 'set_ignore' ) );
			}
		} else {

			// OpenGraph
			add_action( 'wpseo_opengraph', array( $this, 'opengraph' ) );
			add_filter( 'wpseo_opengraph_type', array( $this, 'opengraph_type' ), 10, 1 );
			add_filter( 'wpseo_opengraph_image', array( $this, 'opengraph_image' ), 5, 1 );

			// XML Sitemap Index addition
			add_filter( 'wpseo_sitemap_index', array( $this, 'add_to_index' ) );

			// Setting stylesheet for cached sitemap
			add_action( 'wpseo_sitemap_stylesheet_cache_video', array( $this, 'set_stylesheet_cache' ) );

			// Content filter for non-detected videos
			add_filter( 'the_content', array( $this, 'content_filter' ), 5, 1 );

			if ( $options['fitvids'] === true ) {
				// Fitvids scripting
				add_action( 'wp_head', array( $this, 'fitvids' ) );
			}

			if ( $options['disable_rss'] !== true ) {
				// MRSS
				add_action( 'rss2_ns', array( $this, 'mrss_namespace' ) );
				add_action( 'rss2_item', array( $this, 'mrss_item' ), 10, 1 );
				add_filter( 'mrss_media', array( $this, 'mrss_add_video' ) );
			}
		}
		// @todo Maybe enable ?
		// Run on low prio to allow other filters to add their extensions first
		//add_filter( 'wp_video_extensions', array( $this, 'filter_video_extensions' ), 99 );
	}

	/**
	 * Method to invalidate the sitemap
	 *
	 * @param integer $post_id
	 */
	public function invalidate_sitemap( $post_id ) {
		// If this is just a revision, don't invalidate the sitemap cache yet.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		wpseo_invalidate_sitemap_cache( $this->video_sitemap_basename() );
	}


	/**
	 * Return the plugin file
	 *
	 * @return string
	 */
	public static function get_plugin_file() {
		return __FILE__;

	}

	/**
	 * When sitemap is coming out of the cache there is no stylesheet. Normally it will take the default stylesheet.
	 *
	 * This method is called by a filter that will set the video stylesheet.
	 *
	 * @param object $target_object
	 *
	 * @return object
	 */
	public function set_stylesheet_cache( $target_object ) {
		$target_object->set_stylesheet( $this->get_stylesheet_line() );

		return $target_object;

	}

	/**
	 * Getter for stylesheet url
	 *
	 * @return string
	 */
	public function get_stylesheet_line() {
		$stylesheet_url = "\n" . '<?xml-stylesheet type="text/xsl" href="' . home_url( 'video-sitemap.xsl' ) . '"?>';

		return $stylesheet_url;
	}


	/**
	 * Load translations
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'yoast-video-seo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}


	/**
	 * Add more video extensions to the list of allowed video extensions and make sure the wp list
	 * and the internally used list are in line with each other.
	 *
	 * @internal / @todo Not yet in use - uncomment the add_filter above to implement
	 *
	 * @internal WP default list as of v3.9.2 is array( 'mp4', 'm4v', 'webm', 'ogv', 'wmv', 'flv' );
	 *
	 * @param  array $exts Current list of extensions
	 *
	 * @return array        Extended list
	 */
	public function filter_video_extensions( $exts ) {
		$more_exts               = explode( '|', self::$video_ext_pattern );
		$exts                    = array_unique( array_merge( $exts, $more_exts ) );
		self::$video_ext_pattern = implode( '|', $exts );

		return $exts;
	}


	/**
	 * Adds the fitvids JavaScript to the output if there's a video on the page that's supported by this script.
	 * Prevents fitvids being added when the JWPlayer plugin is active as they are incompatible.
	 *
	 * @todo  - check if we can remove the JW6. The JWP plugin does some checking and deactivating
	 * themselves, so if we can rely on that, all the better.
	 *
	 * @since 1.5.4
	 */
	public function fitvids() {
		if ( ! is_singular() || defined( 'JWP6' ) ) {
			return;
		}

		global $post;

		$video = WPSEO_Meta::get_value( 'video_meta', $post->ID );

		if ( ! is_array( $video ) || $video === array() ) {
			return;
		}

		// Check if the current post contains a YouTube, Vimeo, Blip.tv or Viddler video, if it does, add the fitvids code.
		if ( in_array( $video['type'], array( 'youtube', 'vimeo', 'blip.tv', 'viddler', 'wistia' ) ) ) {
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				wp_enqueue_script( 'fitvids', plugins_url( 'js/jquery.fitvids.js', __FILE__ ), array( 'jquery' ) );
			} else {
				wp_enqueue_script( 'fitvids', plugins_url( 'js/jquery.fitvids.min.js', __FILE__ ), array( 'jquery' ) );
			}
		}

		add_action( 'wp_footer', array( $this, 'fitvids_footer' ) );
	}


	/**
	 * The fitvids instantiation code.
	 *
	 * @since 1.5.4
	 */
	public function fitvids_footer() {
		global $post;

		// Try and use the post class to determine the container
		$classes = get_post_class( '', $post->ID );
		$class   = 'post';
		if ( is_array( $classes ) && $classes !== array() ) {
			$class = $classes[0];
		}
		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$(".<?php echo esc_attr( $class ); ?>").fitVids({customSelector: "iframe.wistia_embed"});
			});
		</script>
	<?php
	}


	/**
	 * Make sure the Video SEO plugin receives Yoast admin styling
	 *
	 * @param array $adminpages The array of pages that have Yoast admin styling
	 *
	 * @return array $adminpages
	 *
	 * @deprecated 3.1
	 */
	public function style_admin( $adminpages ) {
		_deprecated_function( 'WPSEO_Video_Sitemap::style_admin', 'WPSEO 3.1' );
		return $adminpages;
	}


	/**
	 * Register the Video SEO submenu.
	 */
	public function register_settings_page() {
		add_submenu_page( 'wpseo_dashboard', esc_html__( 'Yoast WordPress SEO:', 'yoast-video-seo' ) . ' ' . esc_html__( 'Video SEO Settings', 'yoast-video-seo' ), esc_html__( 'Video SEO', 'yoast-video-seo' ), 'manage_options', 'wpseo_video', array(
			$this,
			'admin_panel'
		) );
	}


	/**
	 * Adds the rewrite for the video XML sitemap
	 *
	 * @since 0.1
	 */
	public function init() {
		// Get options to set the entries per page
		$options           = WPSEO_Options::get_all();
		$this->max_entries = $options['entries-per-page'];

		// Add oEmbed providers
		$this->add_oembed();

		$this->init_beacon();
	}

	/**
	 * Initializes the HelpScout beacon
	 */
	private function init_beacon() {
		$query_var = ( $page = filter_input( INPUT_GET, 'page' ) ) ? $page : '';

		// Only add the helpscout beacon on Yoast SEO pages.
		if ( substr( $query_var, 0, 5 ) === 'wpseo' ) {
			$beacon = yoast_get_helpscout_beacon( $query_var );
			$beacon->add_setting( new WPSEO_Video_Beacon_Setting() );
			$beacon->register_hooks();
		}
	}


	/**
	 * Add VideoSeo Admin bar menu item
	 *
	 * @param object $wp_admin_bar Current admin bar - passed by reference
	 */
	public function add_admin_bar_item( $wp_admin_bar ) {
		$admin_menu = false;
		if ( is_multisite() ) {
			$options = get_site_option( 'wpseo_ms' );
			if ( $options['access'] === 'superadmin' && is_super_admin() ) {
				$admin_menu = true;
			} elseif ( current_user_can( 'manage_options' ) ) {
				$admin_menu = true;
			}
		} elseif ( current_user_can( 'manage_options' ) ) {
			$admin_menu = true;
		}

		if ( $admin_menu === true ) {
			$wp_admin_bar->add_menu(
				array(
					'parent' => 'wpseo-settings',
					'id'     => 'wpseo-licenses',
					'title'  => __( 'Video SEO', 'yoast-video-seo' ),
					'href'   => admin_url( 'admin.php?page=wpseo_video' ),
				)
			);
		}
	}


	/**
	 * Register the video sitemap in the WPSEO sitemap class
	 *
	 * @since 1.7
	 */
	public function register_sitemap() {
		// Register the sitemap
		if ( isset( $GLOBALS['wpseo_sitemaps'] ) && is_object( $GLOBALS['wpseo_sitemaps'] ) ) {
			$basename = $this->video_sitemap_basename();
			$GLOBALS['wpseo_sitemaps']->register_sitemap( $basename, array( $this, 'build_video_sitemap' ) );
			if ( method_exists( $GLOBALS['wpseo_sitemaps'], 'register_xsl' ) ) {
				$GLOBALS['wpseo_sitemaps']->register_xsl( $basename, array( $this, 'build_video_sitemap_xsl' ) );
			}
		}
	}

	/**
	 * Execute upgrade actions when needed
	 */
	function upgrade() {

		$options = get_option( 'wpseo_video' );

		// early bail if dbversion is equal to current version
		if ( isset( $options['dbversion'] ) && version_compare( $options['dbversion'], WPSEO_VIDEO_VERSION, '==' ) ) {
			return;
		}

		$yoast_product   = new Yoast_Product_WPSEO_Video();
		$license_manager = new Yoast_Plugin_License_Manager( $yoast_product );

		// upgrade to license manager
		if ( $license_manager->get_license_key() === '' ) {

			if ( isset( $options['yoast-video-seo-license'] ) ) {
				$license_manager->set_license_key( $options['yoast-video-seo-license'] );
			}

			if ( isset( $options['yoast-video-seo-license-status'] ) ) {
				$license_manager->set_license_status( $options['yoast-video-seo-license-status'] );
			}
			update_option( 'wpseo_video', $options );
		}

		// upgrade to new option & meta classes
		if ( ! isset( $options['dbversion'] ) || version_compare( $options['dbversion'], '1.6', '<' ) ) {
			$this->option_instance->clean();
			WPSEO_Meta::clean_up(); // Make sure our meta values are cleaned up even if WP SEO would have been upgraded already
		}

		// Re-add missing durations
		if ( ! isset( $options['dbversion'] ) || ( version_compare( $options['dbversion'], '1.7', '<' ) && version_compare( $options['dbversion'], '1.6', '>' ) ) ) {
			WPSEO_Meta_Video::re_add_durations();
		}

		// Recommend re-index
		if ( isset( $options['dbversion'] ) && version_compare( $options['dbversion'], '1.8', '<' ) ) {
			set_transient( 'video_seo_recommend_reindex', 1 );
		}

		// Make sure version nr gets updated for any version without specific upgrades
		$options = get_option( 'wpseo_video' ); // re-get to make sure we have the latest version
		if ( version_compare( $options['dbversion'], WPSEO_VIDEO_VERSION, '<' ) ) {
			$options['dbversion'] = WPSEO_VIDEO_VERSION;
			update_option( 'wpseo_video', $options );
		}
	}

	/**
	 * Recommend re-index with force index checked
	 *
	 * @since 1.8.0
	 */
	public function recommend_force_index() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="error" id="videoseo-reindex">' .
			'<p style="float: right;"><a href="javascript:videoseo_setIgnore(\'recommend_reindex\',\'videoseo-reindex\',\'' .
			esc_js( wp_create_nonce( 'videoseo-ignore' ) ) . '\');" class="button fixit">' .
			__( 'Ignore.', 'yoast-video-seo' ) .
			'</a></p><p>' .
			sprintf(
				__( 'The VideoSEO upgrade which was just applied contains a lot of improvements. It is strongly recommended that you %sre-index the video content on your website%s with the \'force reindex\' option checked.', 'yoast-video-seo' ),
				'<a href="' . admin_url( 'admin.php?page=wpseo_video' ) . '">',
				'</a>'
			) .
			'</p></div>';
	}

	/**
	 * Function used to remove the temporary admin notices for several purposes, dies on exit.
	 */
	public function set_ignore() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( '-1' );
		}

		check_ajax_referer( 'videoseo-ignore' );
		delete_transient( 'video_seo_' . sanitize_text_field( $_POST['option'] ) );
		die( '1' );
	}

	/**
	 * Load other scripts for the admin in the Video SEO plugin
	 */
	public function admin_video_enqueue_scripts() {
		if ( isset( $_POST['reindex'] ) ) {
			wp_enqueue_script( 'videoseo-admin-progress-bar', plugins_url( 'js/videoseo-admin-progressbar' . WPSEO_CSSJS_SUFFIX . '.js', __FILE__ ), array( 'jquery' ), WPSEO_VIDEO_VERSION, true );
		}
	}

	/**
	 * Load styles for the admin in Video SEO
	 */
	public function admin_video_enqueue_styles() {
		if ( isset( $_POST['reindex'] ) ) {
			wp_enqueue_style( 'videoseo-admin-progress-bar-css', plugins_url( 'css/videoseo-admin-progressbar' . WPSEO_CSSJS_SUFFIX . '.css', __FILE__ ) );
		}
	}

	/**
	 * Load a small js file to facilitate ignoring admin messages
	 */
	public function admin_enqueue_scripts_ignore() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_enqueue_script( 'videoseo-admin-global-script', plugins_url( 'js/videoseo-admin-global' . WPSEO_CSSJS_SUFFIX . '.js', __FILE__ ), array( 'jquery' ), WPSEO_VIDEO_VERSION, true );
	}

	/**
	 * AJAX request handler for reindex posts
	 */
	public function index_posts_callback() {
		if ( wp_verify_nonce( $_POST['nonce'], 'videoseo-ajax-nonce-for-reindex' ) ) {
			if ( isset( $_POST['type'] ) && $_POST['type'] === 'total_posts' ) {
				echo wp_count_posts()->publish;
			} elseif ( isset( $_POST['type'] ) && $_POST['type'] === 'index' ) {
				$startime = time();

				if ( isset( $_POST['portion'] ) && is_numeric( $_POST['portion'] ) ) {
					$portion = (int) $_POST['portion'];
				} else {
					$portion = 5;
				}

				if ( isset( $_POST['start'] ) && is_numeric( $_POST['start'] ) ) {
					$start = (int) $_POST['start'];
				} else {
					$start = 0;
				}

				$this->reindex( $portion, $start );

				$endtime = time();

				echo ( $endtime - $startime ) + 1; // Return time in seconds that we've needed to index
			}
		}

		exit;
	}

	/**
	 * Returns the basename of the video-sitemap, the first portion of the name of the sitemap "file".
	 *
	 * Defaults to video, but it's possible to override it by using the YOAST_VIDEO_SITEMAP_BASENAME constant.
	 *
	 * @since 1.5.3
	 *
	 * @return string $basename
	 */
	public function video_sitemap_basename() {
		$basename = 'video';

		if ( post_type_exists( 'video' ) ) {
			$basename = 'yoast-video';
		}

		if ( defined( 'YOAST_VIDEO_SITEMAP_BASENAME' ) ) {
			$basename = YOAST_VIDEO_SITEMAP_BASENAME;
		}

		return $basename;
	}


	/**
	 * Return the Video Sitemap URL
	 *
	 * @since 1.2.1
	 *
	 * @return string The URL to the video Sitemap.
	 */
	public function sitemap_url() {
		$base = $GLOBALS['wp_rewrite']->using_index_permalinks() ? 'index.php/' : '';

		return home_url( $base . $this->video_sitemap_basename() . '-sitemap.xml' );
	}


	/**
	 * Adds the video XML sitemap to the Index Sitemap.
	 *
	 * @since  0.1
	 *
	 * @param string $str String with the filtered additions to the index sitemap in it.
	 *
	 * @return string $str String with the Video XML sitemap additions to the index sitemap in it.
	 */
	public function add_to_index( $str ) {
		$options = get_option( 'wpseo_video' );

		$base = $GLOBALS['wp_rewrite']->using_index_permalinks() ? 'index.php/' : '';

		if ( is_array( $options['videositemap_posttypes'] ) && $options['videositemap_posttypes'] !== array() ) {
			// Use fields => ids to limit the overhead of fetching entire post objects,
			// fetch only an array of ids instead to count
			$args = array(
				'post_type'      => $options['videositemap_posttypes'],
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'meta_key'       => '_yoast_wpseo_video_meta',
				'meta_compare'   => '!=',
				'meta_value'     => 'none',
				'fields'         => 'ids',
			);
			// Copy these args to be used and modify later
			$date_args = $args;

			$video_ids = get_posts( $args );
			$count     = count( $video_ids );

			if ( $count > 0 ) {
				$n = ( $count > $this->max_entries ) ? (int) ceil( $count / $this->max_entries ) : 1;
				for ( $i = 0; $i < $n; $i ++ ) {
					$count = ( $n > 1 ) ? ( $i + 1 ) : '';

					if ( empty( $count ) || $count == $n ) {
						$date_args['fields']         = 'all';
						$date_args['posts_per_page'] = 1;
						$date_args['offset']         = 0;
						$date_args['order']          = 'DESC';
						$date_args['orderby']        = 'modified';
					} else {
						$date_args['fields']         = 'all';
						$date_args['posts_per_page'] = 1;
						$date_args['offset']         = ( ( $this->max_entries * ( $i + 1 ) ) - 1 );
						$date_args['order']          = 'ASC';
						$date_args['orderby']        = 'modified';
					}
					$posts = get_posts( $date_args );
					$date  = date( 'c', strtotime( $posts[0]->post_modified_gmt ) );

					$text = ( $count > 1 ) ? $count : '';
					$str .= '<sitemap>' . "\n";
					$str .= '<loc>' . home_url( $base . $this->video_sitemap_basename() . '-sitemap' . $text . '.xml' ) . '</loc>' . "\n";
					$str .= '<lastmod>' . $date . '</lastmod>' . "\n";
					$str .= '</sitemap>' . "\n";
				}
			}
		}

		return $str;
	}


	/**
	 * Adds oembed endpoints for supported video platforms that are not supported by core.
	 *
	 * @since 1.3.5
	 */
	public function add_oembed() {
		// @todo - check with official plugin
		// Wistia
		$options      = get_option( 'wpseo_video' );
		$wistia_regex = '`(?:http[s]?:)?//[^/]*(wistia\.(com|net)|wi\.st#CUSTOM_URL#)/(medias|embed)/.*`i';
		if ( $options['wistia_domain'] !== '' ) {
			$wistia_regex = str_replace( '#CUSTOM_URL#', '|' . preg_quote( $options['wistia_domain'], '`' ), $wistia_regex );
		} else {
			$wistia_regex = str_replace( '#CUSTOM_URL#', '', $wistia_regex );
		}
		wp_oembed_add_provider( $wistia_regex, 'http://fast.wistia.com/oembed', true );


		// Animoto, Ted, Collegehumor - WP native support added in WP 4.0
		if ( version_compare( $GLOBALS['wp_version'], '3.9.99', '<' ) ) {
			wp_oembed_add_provider( '`http[s]?://(?:www\.)?(animoto|video214)\.com/play/.*`i', 'http://animoto.com/oembeds/create', true );
			wp_oembed_add_provider( '`http[s]?://(www\.)?collegehumor\.com/video/.*`i', 'http://www.collegehumor.com/oembed.{format}', true );
			wp_oembed_add_provider( '`http[s]?://(www\.|embed\.)?ted\.com/talks/.*`i', 'http://www.ted.com/talks/oembed.{format}', true );

		} // Viddler - WP native support removed in WP 4.0
		else {
			wp_oembed_add_provider( '`http[s]?://(?:www\.)?viddler\.com/.*`i', 'http://lab.viddler.com/services/oembed/', true );
		}

		// Screenr
		wp_oembed_add_provider( '`http[s]?://(?:www\.)?screenr\.com/.*`i', 'http://www.screenr.com/api/oembed.{format}', true );

		// EVS
		$evs_location = get_option( 'evs_location' );
		if ( $evs_location && ! empty( $evs_location ) ) {
			wp_oembed_add_provider( $evs_location . '/*', $evs_location . '/oembed.php', false );
		}
	}


	/**
	 * Synchronize the WP native oembed providers list for various WP versions.
	 *
	 * If VideoSEO users choose to stay on a lower WP version, they will still get the benefit of improved
	 * oembed regexes and provider compatibility this way.
	 *
	 * @param  array $providers
	 *
	 * @return array
	 */
	public function sync_oembed_providers( $providers ) {

		// Support SSL urls for flick shortdomain (natively added in WP4.0)
		if ( isset( $providers['http://flic.kr/*'] ) ) {
			unset( $providers['http://flic.kr/*'] );
			$providers['#https?://flic\.kr/.*#i'] = array( 'https://www.flickr.com/services/oembed/', true );
		}

		// Change to SSL for oembed provider domain (natively changed in WP4.0)
		if ( isset( $providers['#https?://(www\.)?flickr\.com/.*#i'] ) && strpos( $providers['#https?://(www\.)?flickr\.com/.*#i'][0], 'https' ) !== 0 ) {
			$providers['#https?://(www\.)?flickr\.com/.*#i'] = array( 'https://www.flickr.com/services/oembed/', true );
		}

		// Allow any vimeo subdomain (natively changed in WP3.9)
		if ( isset( $providers['#https?://(www\.)?vimeo\.com/.*#i'] ) ) {
			unset( $providers['#https?://(www\.)?vimeo\.com/.*#i'] );
			$providers['#https?://(.+\.)?vimeo\.com/.*#i'] = array( 'http://vimeo.com/api/oembed.{format}', true );
		}

		// Support SSL urls for wordpress.tv (natively added in WP4.0)
		if ( isset( $providers['http://wordpress.tv/*'] ) ) {
			unset( $providers['http://wordpress.tv/*'] );
			$providers['#https?://wordpress.tv/.*#i'] = array( 'http://wordpress.tv/oembed/', true );
		}

		return $providers;
	}


	/**
	 * Add the MRSS namespace to the RSS feed.
	 *
	 * @since 0.1
	 */
	public function mrss_namespace() {
		echo ' xmlns:media="http://search.yahoo.com/mrss/" ';
	}


	/**
	 * Add the MRSS info to the feed
	 *
	 * Based upon the MRSS plugin developed by Andy Skelton
	 *
	 * @since     0.1
	 * @copyright Andy Skelton
	 */
	public function mrss_item() {
		global $mrss_gallery_lookup;
		$media  = array();
		$lookup = array();

		// Honor the feed settings. Don't include any media that isn't in the feed.
		if ( get_option( 'rss_use_excerpt' ) || ! strlen( get_the_content() ) ) {
			ob_start();
			the_excerpt_rss();
			$content = ob_get_clean();
		} else {
			// If any galleries are processed, we need to capture the attachment IDs.
			add_filter( 'wp_get_attachment_link', array( $this, 'mrss_gallery_lookup' ), 10, 5 );
			$content = apply_filters( 'the_content', get_the_content() );
			remove_filter( 'wp_get_attachment_link', array( $this, 'mrss_gallery_lookup' ), 10, 5 );
			$lookup = $mrss_gallery_lookup;
			unset( $mrss_gallery_lookup );
		}

		// img tags
		$images = 0;
		if ( preg_match_all( '`<img ([^>]+)>`', $content, $matches ) ) {
			foreach ( $matches[1] as $attrs ) {
				$item = $img = array();
				// Construct $img array from <img> attributes
				$attributes = wp_kses_hair( $attrs, array( 'http' ) );
				foreach ( $attributes as $attr ) {
					$img[$attr['name']] = $attr['value'];
				}
				unset( $attributes );

				// Skip emoticons and images without source attribute
				if ( ! isset( $img['src'] ) || ( isset( $img['class'] ) && false !== strpos( $img['class'], 'wp-smiley' ) ) ) {
					continue;
				}

				$img['src'] = $this->mrss_url( $img['src'] );

				$id = false;
				if ( isset( $lookup[$img['src']] ) ) {
					$id = $lookup[$img['src']];
				} elseif ( isset( $img['class'] ) && preg_match( '`wp-image-(\d+)`', $img['class'], $match ) ) {
					$id = $match[1];
				}
				if ( $id ) {
					// It's an attachment, so we will get the URLs, title, and description from functions
					$attachment =& get_post( $id );
					$src        = wp_get_attachment_image_src( $id, 'full' );
					if ( ! empty( $src[0] ) ) {
						$img['src'] = $src[0];
					}
					$thumbnail = wp_get_attachment_image_src( $id, 'thumbnail' );
					if ( ! empty( $thumbnail[0] ) && $thumbnail[0] != $img['src'] ) {
						$img['thumbnail'] = $thumbnail[0];
					}
					$title = get_the_title( $id );
					if ( ! empty( $title ) ) {
						$img['title'] = trim( $title );
					}
					if ( ! empty( $attachment->post_excerpt ) ) {
						$img['description'] = trim( $attachment->post_excerpt );
					}
				}
				// If this is the first image in the markup, make it the post thumbnail
				if ( ++ $images == 1 ) {
					if ( isset( $img['thumbnail'] ) ) {
						$media[]['thumbnail']['attr']['url'] = $img['thumbnail'];
					} else {
						$media[]['thumbnail']['attr']['url'] = $img['src'];
					}
				}

				$item['content']['attr']['url']    = $img['src'];
				$item['content']['attr']['medium'] = 'image';
				if ( ! empty( $img['title'] ) ) {
					$item['content']['children']['title']['attr']['type'] = 'html';
					$item['content']['children']['title']['children'][]   = $img['title'];
				} elseif ( ! empty( $img['alt'] ) ) {
					$item['content']['children']['title']['attr']['type'] = 'html';
					$item['content']['children']['title']['children'][]   = $img['alt'];
				}
				if ( ! empty( $img['description'] ) ) {
					$item['content']['children']['description']['attr']['type'] = 'html';
					$item['content']['children']['description']['children'][]   = $img['description'];
				}
				if ( ! empty( $img['thumbnail'] ) ) {
					$item['content']['children']['thumbnail']['attr']['url'] = $img['thumbnail'];
				}
				$media[] = $item;
			}
		}

		$media = apply_filters( 'mrss_media', $media );
		$this->mrss_print( $media );
	}


	/**
	 * @todo Properly document
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function mrss_url( $url ) {
		if ( preg_match( '`^(?:http[s]?:)//`', $url ) ) {
			return $url;
		} else {
			return home_url( $url );
		}
	}


	/**
	 * @todo Properly document
	 *
	 * @param string $link
	 * @param mixed  $id
	 *
	 * @return mixed
	 */
	public function mrss_gallery_lookup( $link, $id ) {
		if ( preg_match( '` src="([^"]+)"`', $link, $matches ) ) {
			$GLOBALS['mrss_gallery_lookup'][$matches[1]] = $id;
		}

		return $link;
	}


	/**
	 * @todo Properly document
	 *
	 * @param mixed $media
	 */
	public function mrss_print( $media ) {
		if ( ! empty( $media ) ) {
			foreach ( (array) $media as $element ) {
				$this->mrss_print_element( $element );
			}
		}
		echo "\n";
	}


	/**
	 * @todo Properly document
	 *
	 * @param     $element
	 * @param int $indent
	 */
	public function mrss_print_element( $element, $indent = 2 ) {
		echo "\n";
		foreach ( (array) $element as $name => $data ) {
			echo str_repeat( "\t", $indent ) . '<media:' . esc_attr( $name );

			if ( is_array( $data['attr'] ) && $data['attr'] !== array() ) {
				foreach ( $data['attr'] as $attr => $value ) {
					echo ' ' . esc_attr( $attr ) . '="' . esc_attr( ent2ncr( $value ) ) . '"';
				}
			}
			if ( is_array( $data['children'] ) && $data['children'] !== array() ) {
				$nl = false;
				echo '>';
				foreach ( $data['children'] as $_name => $_data ) {
					if ( is_int( $_name ) ) {
						echo ent2ncr( esc_html( $_data ) );
					} else {
						$nl = true;
						$this->mrss_print_element( array( $_name => $_data ), ( $indent + 1 ) );
					}
				}
				if ( $nl ) {
					echo "\n" . str_repeat( "\t", $indent );
				}
				echo '</media:' . esc_attr( $name ) . '>';
			} else {
				echo ' />';
			}
		}
	}


	/**
	 * Add the video output to the MRSS feed.
	 *
	 * @since 0.1
	 *
	 * @param array $media
	 *
	 * @return array
	 */
	public function mrss_add_video( $media ) {
		global $post;

		$video = WPSEO_Meta::get_value( 'video_meta', $post->ID );

		if ( ! is_array( $video ) || $video === array() ) {
			return $media;
		}

		$video_duration = WPSEO_Meta::get_value( 'videositemap-duration', $post->ID );
		if ( $video_duration == 0 && isset( $video['duration'] ) ) {
			$video_duration = $video['duration'];
		}

		$item['content']['attr']['url']                             = $video['player_loc'];
		$item['content']['attr']['duration']                        = $video_duration;
		$item['content']['children']['player']['attr']['url']       = $video['player_loc'];
		$item['content']['children']['title']['attr']['type']       = 'html';
		$item['content']['children']['title']['children'][]         = esc_html( $video['title'] );
		$item['content']['children']['description']['attr']['type'] = 'html';
		$item['content']['children']['description']['children'][]   = esc_html( $video['description'] );
		$item['content']['children']['thumbnail']['attr']['url']    = $video['thumbnail_loc'];
		$item['content']['children']['keywords']['children'][]      = implode( ',', $video['tag'] );
		array_unshift( $media, $item );

		return $media;
	}


	/**
	 * Parse the content of a post or term description.
	 *
	 * @since      1.3
	 * @see        WPSEO_Video_Analyse_Post
	 *
	 * @param string $content The content to parse for videos.
	 * @param array  $vid     The video array to update.
	 * @param array  $old_vid The former video array.
	 * @param mixed  $post    The post object or the post id of the post to analyse
	 *
	 * @return array $vid
	 */
	public function index_content( $content, $vid, $old_vid = array(), $post = null ) {
		$index = new WPSEO_Video_Analyse_Post( $content, $vid, $old_vid, $post );

		return $index->get_vid_info();
	}


	/**
	 * Check and, if applicable, update video details for a term description
	 *
	 * @since 1.3
	 *
	 * @param object  $term The term to check the description and possibly update the video details for.
	 * @param boolean $echo Whether or not to echo the performed actions.
	 *
	 * @return mixed $vid The video array that was just stored, or "none" if nothing was stored
	 *                    or false if not applicable.
	 */
	public function update_video_term_meta( $term, $echo = false ) {
		$options = array_merge( WPSEO_Options::get_all(), get_option( 'wpseo_video' ) );

		if ( ! is_array( $options['videositemap_taxonomies'] ) || $options['videositemap_taxonomies'] === array() ) {
			return false;
		}

		if ( ! in_array( $term->taxonomy, $options['videositemap_taxonomies'] ) ) {
			return false;
		}

		$tax_meta = get_option( 'wpseo_taxonomy_meta' );
		$old_vid  = array();
		if ( ! isset( $_POST['force'] ) ) {
			if ( isset( $tax_meta[$term->taxonomy]['_video'][$term->term_id] ) ) {
				$old_vid = $tax_meta[$term->taxonomy]['_video'][$term->term_id];
			}
		}

		$vid = array();

		$title = WPSEO_Taxonomy_Meta::get_term_meta( $term->term_id, $term->taxonomy, 'wpseo_title' );
		if ( empty( $title ) && isset( $options['title-' . $term->taxonomy] ) && $options['title-' . $term->taxonomy] !== '' ) {
			$title = wpseo_replace_vars( $options['title-' . $term->taxonomy], (array) $term );
		}
		if ( empty( $title ) ) {
			$title = $term->name;
		}
		$vid['title'] = htmlspecialchars( $title );

		$vid['description'] = WPSEO_Taxonomy_Meta::get_term_meta( $term->term_id, $term->taxonomy, 'wpseo_metadesc' );
		if ( ! $vid['description'] ) {
			$vid['description'] = esc_attr( preg_replace( '`\s+`', ' ', wp_html_excerpt( $this->strip_shortcodes( get_term_field( 'description', $term->term_id, $term->taxonomy ) ), 300 ) ) );
		}

		$vid['publication_date'] = date( 'Y-m-d\TH:i:s+00:00' );

		// concatenate genesis intro text and term description to index the videos for both
		$genesis_term_meta = get_option( 'genesis-term-meta' );

		$content = '';
		if ( isset( $genesis_term_meta[$term->term_id]['intro_text'] ) && $genesis_term_meta[$term->term_id]['intro_text'] ) {
			$content .= $genesis_term_meta[$term->term_id]['intro_text'];
		}

		$content .= "\n" . $term->description;
		$content = stripslashes( $content );

		$vid = $this->index_content( $content, $vid, $old_vid, null );

		if ( $vid != 'none' ) {
			$tax_meta[$term->taxonomy]['_video'][$term->term_id] = $vid;
			// Don't bother with the complete tax meta validation
			$tax_meta['wpseo_already_validated'] = true;
			update_option( 'wpseo_taxonomy_meta', $tax_meta );

			if ( $echo ) {
				$link = get_term_link( $term );
				if ( ! is_wp_error( $link ) ) {
					echo 'Updated <a href="' . esc_url( $link ) . '">' . esc_html( $vid['title'] ) . '</a> - ' . esc_html( $vid['type'] ) . '<br/>';
				}
			}
		}

		return $vid;
	}


	/**
	 * (Don't) validate the _video taxonomy meta data array
	 * Doesn't actually validate it atm, but having this function hooked in *does* make sure that the
	 * _video taxonomy meta data is not removed as it otherwise would be (by the normal taxonomy meta validation).
	 *
	 * @since 1.6
	 *
	 * @param  array $tax_meta_data Received _video tax meta data
	 *
	 * @return array  Validated _video tax meta data
	 */
	public function validate_video_tax_meta( $tax_meta_data ) {
		return $tax_meta_data;
	}


	/**
	 * Check and, if applicable, update video details for a post
	 *
	 * @since 0.1
	 *
	 * @param object  $post The post to check and possibly update the video details for.
	 * @param boolean $echo Whether or not to echo the performed actions.
	 *
	 * @return mixed $vid The video array that was just stored, or "none" if nothing was stored
	 *                    or false if not applicable.
	 */
	public function update_video_post_meta( $post, $echo = false ) {
		global $wp_query;

		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		if ( ! isset( $post->ID ) ) {
			return false;
		}

		$options = array_merge( WPSEO_Options::get_all(), get_option( 'wpseo_video' ) );

		if ( ! is_array( $options['videositemap_posttypes'] ) || $options['videositemap_posttypes'] === array() ) {
			return false;
		}

		if ( ! in_array( $post->post_type, $options['videositemap_posttypes'] ) ) {
			return false;
		}

		$_GLOBALS['post'] = $post; // @todo Que ? Overwritting the global post object seems like a bad idea, quite apart from that you'd want $GLOBALS without the underscore to do so

		$old_vid = array();
		if ( ! isset( $_POST['force'] ) ) {
			$old_vid = WPSEO_Meta::get_value( 'video_meta', $post->ID );
		}

		$title = WPSEO_Meta::get_value( 'title', $post->ID );
		if ( ( ! is_string( $title ) || $title === '' ) && isset( $options['title-' . $post->post_type] ) && $options['title-' . $post->post_type] !== '' ) {
			$title = wpseo_replace_vars( $options['title-' . $post->post_type], (array) $post );
		} elseif ( ( ! is_string( $title ) || $title === '' ) && ( ! isset( $options['title-' . $post->post_type] ) || $options['title-' . $post->post_type] === '' ) ) {
			$title = wpseo_replace_vars( '%%title%% - %%sitename%%', (array) $post );
		}

		if ( ! is_string( $title ) || $title === '' ) {
			$title = $post->post_title;
		}

		$vid = array();

		// @todo [JRF->Yoast] Verify if this is really what we want. What about non-hierarchical custom post types ? and are we adjusting the main query output now ? could this cause bugs for others ?
		if ( $post->post_type == 'post' ) {
			$wp_query->is_single = true;
			$wp_query->is_page   = false;
		} else {
			$wp_query->is_single = false;
			$wp_query->is_page   = true;
		}

		$vid['post_id'] = $post->ID;

		$vid['title']            = htmlspecialchars( $title );
		$vid['publication_date'] = mysql2date( 'Y-m-d\TH:i:s+00:00', $post->post_date_gmt );

		$vid['description'] = WPSEO_Meta::get_value( 'metadesc', $post->ID );
		if ( ! is_string( $vid['description'] ) || $vid['description'] === '' ) {
			$vid['description'] = esc_attr( preg_replace( '`\s+`', ' ', wp_html_excerpt( $this->strip_shortcodes( $post->post_content ), 300 ) ) );
		}

		$vid = $this->index_content( $post->post_content, $vid, $old_vid, $post );

		if ( 'none' != $vid ) {
			// Shouldn't be needed, but just in case
			if ( isset( $vid['__add_to_content'] ) ) {
				unset( $vid['__add_to_content'] );
			}

			if ( ! isset( $vid['thumbnail_loc'] ) || empty( $vid['thumbnail_loc'] ) ) {
				$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
				if ( strpos( $img[0], 'http' ) !== 0 ) {
					$vid['thumbnail_loc'] = get_site_url( null, $img[0] );
				} else {
					$vid['thumbnail_loc'] = $img[0];
				}
			}

			// Grab the meta data from the post
			if ( isset( $_POST['yoast_wpseo_videositemap-category'] ) && ! empty( $_POST['yoast_wpseo_videositemap-category'] ) ) {
				$vid['category'] = sanitize_text_field( $_POST['yoast_wpseo_videositemap-category'] );
			} else {
				$cats = wp_get_object_terms( $post->ID, 'category', array( 'fields' => 'names' ) );
				if ( isset( $cats[0] ) ) {
					$vid['category'] = $cats[0];
				}
				unset( $cats );
			}

			$tags = wp_get_object_terms( $post->ID, 'post_tag', array( 'fields' => 'names' ) );

			if ( isset( $_POST['yoast_wpseo_videositemap-tags'] ) && ! empty( $_POST['yoast_wpseo_videositemap-tags'] ) ) {
				$extra_tags = explode( ',', sanitize_text_field( $_POST['yoast_wpseo_videositemap-tags'] ) );
				$tags       = array_merge( $extra_tags, $tags );
			}

			$tag = array();
			if ( is_array( $tags ) ) {
				foreach ( $tags as $t ) {
					$tag[] = $t;
				}
			} elseif ( isset( $cats[0] ) ) {
				$tag[] = $cats[0]->name;
			}

			$focuskw = WPSEO_Meta::get_value( 'focuskw', $post->ID );
			if ( ! empty( $focuskw ) ) {
				$tag[] = $focuskw;
			}
			$vid['tag'] = $tag;

			if ( $echo ) {
				echo 'Updated <a href="' . esc_url( add_query_arg( array( 'p' => $post->ID ), home_url() ) ) . '">' . esc_html( $post->post_title ) . '</a> - ' . esc_html( $vid['type'] ) . '<br/>';
			}
		}

		WPSEO_Meta::set_value( 'video_meta', $vid, $post->ID );

		//echo '<pre>' . print_r( $_POST, true ) . '</pre>';
		return $vid;
	}


	/**
	 * Remove both used and unused shortcodes from content.
	 *
	 * @todo     [JRF -> Yoast] Why not use the WP native strip_shortcodes function ?
	 *
	 * @internal adjusted to prevent stripping of escaped shortcodes which are meant to be displayed literally
	 *
	 * @since    1.3.3
	 *
	 * @param string $content Content to remove shortcodes from.
	 *
	 * @return string
	 */
	public function strip_shortcodes( $content ) {
		$regex   = '`(?:^|[^\[])(\[[^\]]+\])(?:.*?(\[/[^\]]+\])(?:[^\]]|$))?`s';
		$content = preg_replace( $regex, '', $content );

		return $content;
	}


	/**
	 * Check whether the current visitor is really Google or Bing's bot by doing a reverse DNS lookup
	 *
	 * @since 1.2.2
	 *
	 * @return boolean
	 */
	public function is_valid_bot() {
		if ( preg_match( '`(Google|bing)bot`', sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ), $match ) ) {
			$hostname = gethostbyaddr( sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) );

			if (
				( $match[1] === 'Google' && preg_match( '`googlebot\.com$`', $hostname ) && gethostbyname( $hostname ) == $_SERVER['REMOTE_ADDR'] ) ||
				( $match[1] === 'bing' && preg_match( '`search\.msn\.com$`', $hostname ) && gethostbyname( $hostname ) == $_SERVER['REMOTE_ADDR'] )
			) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Check to see if the video thumbnail was manually set, if so, update the $video array.
	 *
	 * @param int   $post_id The post to check for.
	 * @param array $video   The video array.
	 *
	 * @return array
	 */
	public function get_video_image( $post_id, $video ) {
		// Allow for the video's thumbnail to be overridden by the meta box input
		$videoimg = WPSEO_Meta::get_value( 'videositemap-thumbnail', $post_id );
		if ( $videoimg !== '' ) {
			$video['thumbnail_loc'] = $videoimg;
		}

		return $video;
	}


	/**
	 * Outputs the XSL file
	 */
	public function build_video_sitemap_xsl() {

		$protocol = 'HTTP/1.1';
		if ( isset( $_SERVER['SERVER_PROTOCOL'] ) && $_SERVER['SERVER_PROTOCOL'] !== '' ) {
			$protocol = sanitize_text_field( $_SERVER['SERVER_PROTOCOL'] );
		}

		// Force a 200 header and replace other status codes.
		header( $protocol . ' 200 OK', true, 200 );

		// Set the right content / mime type
		header( 'Content-Type: text/xml' );

		// Prevent the search engines from indexing the XML Sitemap.
		header( 'X-Robots-Tag: noindex, follow', true );

		// Make the browser cache this file properly.
		header( 'Pragma: public' );
		header( 'Cache-Control: maxage=' . YEAR_IN_SECONDS );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', ( time() + YEAR_IN_SECONDS ) ) . ' GMT' );

		require plugin_dir_path( __FILE__ ) . 'xml-video-sitemap.php';
		die();
	}

	/**
	 * The main function of this class: it generates the XML sitemap's contents.
	 *
	 * @since 0.1
	 */
	public function build_video_sitemap() {
		$options = get_option( 'wpseo_video' );

		$protocol = 'HTTP/1.1';
		if ( isset( $_SERVER['SERVER_PROTOCOL'] ) && $_SERVER['SERVER_PROTOCOL'] !== '' ) {
			$protocol = sanitize_text_field( $_SERVER['SERVER_PROTOCOL'] );
		}

		// Restrict access to the video sitemap to admins and valid bots
		if ( $options['cloak_sitemap'] === true && ( ! current_user_can( 'manage_options' ) && ! $this->is_valid_bot() ) ) {
			header( $protocol . ' 403 Forbidden', true, 403 );
			wp_die( "We're sorry, access to our video sitemap is restricted to site admins and valid Google & Bing bots." );
		}

		// Force a 200 header and replace other status codes.
		header( $protocol . ' 200 OK', true, 200 );

		$output = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . "\n";

		$printed_post_ids = array();

		$steps  = $this->max_entries;
		$n      = (int) get_query_var( 'sitemap_n' );
		$offset = ( $n > 1 ) ? ( ( $n - 1 ) * $this->max_entries ) : 0;
		$total  = ( $offset + $this->max_entries );

		if ( is_array( $options['videositemap_posttypes'] ) && $options['videositemap_posttypes'] !== array() ) {
			// Set the initial args array to get videos in chunks
			$args = array(
				'post_type'      => $options['videositemap_posttypes'],
				'post_status'    => 'publish',
				'posts_per_page' => $steps,
				'offset'         => $offset,
				'meta_key'       => '_yoast_wpseo_video_meta',
				'meta_compare'   => '!=',
				'meta_value'     => 'none',
				'order'          => 'ASC',
				'orderby'        => 'post_modified',
			);

			/*
				@TODO: add support to tax video to honor pages
				add a bool to the while loop to see if tax has been processed
				if $items is empty the posts are done so move on to tax

				do some math between $printed_post_ids and $this-max_entries to figure out how many from tax to add to this pagination
			*/

			// Add entries to the sitemap until the total is hit (rounded up by nearest $steps)
			while ( ( $total > $offset ) && ( $items = get_posts( $args ) ) ) {

				if ( is_array( $items ) && $items !== array() ) {
					foreach ( $items as $item ) {
						if ( ! is_object( $item ) || in_array( $item->ID, $printed_post_ids ) ) {
							continue;
						} else {
							$printed_post_ids[] = $item->ID;
						}

						if ( WPSEO_Meta::get_value( 'meta-robots-noindex', $item->ID ) == '1' ) {
							continue;
						}

						$disable = WPSEO_Meta::get_value( 'videositemap-disable', $item->ID );
						if ( $disable === 'on' ) {
							continue;
						}

						$video = WPSEO_Meta::get_value( 'video_meta', $item->ID );

						$video = $this->get_video_image( $item->ID, $video );

						// When we don't have a thumbnail and either a player_loc or a content_loc, skip this video.
						if ( ! isset( $video['thumbnail_loc'] )
							|| ( ! isset( $video['player_loc'] ) && ! isset( $video['content_loc'] ) )
						) {
							continue;
						}

						$video_duration = WPSEO_Meta::get_value( 'videositemap-duration', $item->ID );
						if ( $video_duration > 0 ) {
							$video['duration'] = $video_duration;
						}

						$video['permalink'] = get_permalink( $item );

						$rating = apply_filters( 'wpseo_video_rating', WPSEO_Meta::get_value( 'videositemap-rating', $item->ID ) );
						if ( $rating && WPSEO_Meta_Video::sanitize_rating( null, $rating, WPSEO_Meta_Video::$meta_fields['video']['videositemap-rating'] ) ) {
							$video['rating'] = number_format( $rating, 1 );
						}

						$not_family_friendly = apply_filters( 'wpseo_video_family_friendly', WPSEO_Meta::get_value( 'videositemap-not-family-friendly', $item->ID ), $item->ID );
						if ( is_string( $not_family_friendly ) && $not_family_friendly === 'on' ) {
							$video['family_friendly'] = 'no';
						} else {
							$video['family_friendly'] = 'yes';
						}

						$video['author'] = $item->post_author;

						$output .= $this->print_sitemap_line( $video );
					}
				}

				// Update these args for the next iteration
				$offset = ( $offset + $steps );
				$args['offset'] += $steps;
			}
		}

		$tax_meta = get_option( 'wpseo_taxonomy_meta' );
		$terms    = array();
		if ( is_array( $options['videositemap_taxonomies'] ) && $options['videositemap_taxonomies'] !== array() ) {
			// Below is a fix for a nasty bug in WooCommerce: https://github.com/woothemes/woocommerce/issues/3807
			$options['videositemap_taxonomies'][0] = '';
			$terms                                 = get_terms( $options['videositemap_taxonomies'] );
		}

		if ( is_array( $terms ) && $terms !== array() ) {
			foreach ( $terms as $term ) {
				if ( is_object( $term ) && isset( $tax_meta[$term->taxonomy]['_video'][$term->term_id] ) ) {
					$video = $tax_meta[$term->taxonomy]['_video'][$term->term_id];
					if ( is_array( $video ) ) {
						$video['permalink'] = get_term_link( $term, $term->taxonomy );
						$video['category']  = $term->name;
						$output .= $this->print_sitemap_line( $video );
					}
				}
			}
		}

		$output .= '</urlset>';
		$GLOBALS['wpseo_sitemaps']->set_sitemap( $output );
		$GLOBALS['wpseo_sitemaps']->set_stylesheet( $this->get_stylesheet_line() );
	}


	/**
	 * Print a full <url> line in the sitemap.
	 *
	 * @since 1.3
	 *
	 * @param array $video The video object to print out
	 *
	 * @return string The output generated
	 */
	public function print_sitemap_line( $video ) {
		if ( ! is_array( $video ) || $video === array() ) {
			return '';
		}

		$output = "\t<url>\n";
		$output .= "\t\t<loc>" . htmlspecialchars( $video['permalink'] ) . '</loc>' . "\n";
		$output .= "\t\t<video:video>\n";


		if ( empty( $video['publication_date'] ) && $this->is_valid_datetime( $video['publication_date'] ) === false ) {
			$post = get_post( $video['post_id'] );
			if ( is_object( $post ) && $post->post_date_gmt != '0000-00-00 00:00:00' && $this->is_valid_datetime( $post->post_date_gmt ) ) {
				$video['publication_date'] = mysql2date( 'Y-m-d\TH:i:s+00:00', $post->post_date_gmt );
			} elseif ( is_object( $post ) && $post->post_date != '0000-00-00 00:00:00' && $this->is_valid_datetime( $post->post_date ) ) {
				$video['publication_date'] = date( 'Y-m-d\TH:i:s+00:00', get_gmt_from_date( $post->post_date ) );
			} else {
				return '<!-- Post with ID ' . $video['post_id'] . 'skipped, because there\'s no valid date in the DB for it. -->';
			} // If we have no valid date for the post, skip the video and don't print it in the XML Video Sitemap.
		}


		foreach ( $video as $key => $val ) {
			// @todo - We should really switch to whitelist format, rather than blacklist
			if ( in_array( $key, array(
				'id',
				'url',
				'type',
				'permalink',
				'post_id',
				'hd',
				'maybe_local',
				'attachment_id',
				'file_path',
				'file_url'
			) ) ) {
				continue;
			}

			if ( $key == 'author' ) {
				$output .= "\t\t\t<video:uploader info='" . get_author_posts_url( $val ) . "'>" . ent2ncr( esc_html( get_the_author_meta( 'display_name', $val ) ) ) . "</video:uploader>\n";
				continue;
			}

			$xtra = '';
			if ( $key == 'player_loc' ) {
				$xtra = ' allow_embed="yes"';
			}

			if ( $key == 'description' && empty( $val ) ) {
				$val = $video['title'];
			}

			if ( is_scalar( $val ) && ! empty ( $val ) ) {
				$prepare_sitemap_line = $this->get_single_sitemap_line( $val, $key, $xtra );

				if ( ! is_null( $prepare_sitemap_line ) ) {
					$output .= $prepare_sitemap_line;
				}
			} elseif ( is_array( $val ) && $val !== array() ) {
				$i = 1;
				foreach ( $val as $v ) {
					// Only 32 tags are allowed
					if ( $key == 'tag' && $i > 32 ) {
						break;
					}
					$prepare_sitemap_line = $this->get_single_sitemap_line( $v, $key, $xtra );

					if ( ! is_null( $prepare_sitemap_line ) ) {
						$output .= $prepare_sitemap_line;
					}
					$i ++;
				}
			}
		}

		// Allow custom implementations with extra tags here
		$output .= apply_filters( 'wpseo_video_item', '', isset( $video['post_id'] ) ? $video['post_id'] : 0 );

		$output .= "\t\t</video:video>\n";

		$output .= "\t</url>\n";

		return $output;
	}


	/**
	 * Cleans a string for XML display purposes.
	 *
	 * @since 1.2.1
	 *
	 * @link  http://php.net/html-entity-decode#98697 Modified for WP from here.
	 *
	 * @param string $in     The string to clean.
	 * @param int    $offset Offset of the string to start the cleaning at.
	 *
	 * @return string Cleaned string.
	 */
	public function clean_string( $in, $offset = null ) {
		$out = trim( $in );
		$out = $this->strip_shortcodes( $out );
		$out = html_entity_decode( $out, ENT_QUOTES, 'ISO-8859-15' );
		$out = html_entity_decode( $out, ENT_QUOTES, get_bloginfo( 'charset' ) );
		if ( ! empty( $out ) ) {
			$entity_start = strpos( $out, '&', $offset );
			if ( $entity_start === false ) {
				// ideal
				return _wp_specialchars( $out );
			} else {
				$entity_end = strpos( $out, ';', $entity_start );
				if ( $entity_end === false ) {
					return _wp_specialchars( $out );
				} // zu lang um eine entity zu sein
				elseif ( $entity_end > ( $entity_start + 7 ) ) {
					// und weiter gehts
					$out = $this->clean_string( $out, ( $entity_start + 1 ) );
				} // gotcha!
				else {
					$clean = substr( $out, 0, $entity_start );
					$subst = substr( $out, ( $entity_start + 1 ), 1 );
					// &scaron; => "s" / &#353; => "_"
					$clean .= ( $subst != '#' ) ? $subst : '_';
					$clean .= substr( $out, ( $entity_end + 1 ) );
					// und weiter gehts
					$out = $this->clean_string( $clean, ( $entity_start + 1 ) );
				}
			}
		}

		return _wp_specialchars( $out );
	}


	/**
	 * Roughly calculate the length of an FLV video.
	 *
	 * @since 1.3.1
	 *
	 * @param string $file The path to the video file to calculate the length for
	 *
	 * @return integer Duration of the video
	 */
	public function get_flv_duration( $file ) {
		if ( is_file( $file ) && is_readable( $file ) ) {
			$flv = fopen( $file, 'rb' );
			if ( is_resource( $flv ) ) {
				fseek( $flv, - 4, SEEK_END );
				$arr             = unpack( 'N', fread( $flv, 4 ) );
				$last_tag_offset = $arr[1];
				fseek( $flv, - ( $last_tag_offset + 4 ), SEEK_END );
				fseek( $flv, 4, SEEK_CUR );
				$t0                    = fread( $flv, 3 );
				$t1                    = fread( $flv, 1 );
				$arr                   = unpack( 'N', $t1 . $t0 );
				$milliseconds_duration = $arr[1];

				return $milliseconds_duration;
			}
		}

		return 0;
	}


	/**
	 * Outputs the admin panel for the Video Sitemaps on the XML Sitemaps page with the WP SEO admin
	 *
	 * @since 0.1
	 */
	public function admin_panel() {
		$options = get_option( 'wpseo_video' );
		$xmlopt  = get_option( 'wpseo_xml' );

		WPSEO_Video_Wrappers::admin_header( true, $this->option_instance->group_name, $this->option_instance->option_name, false );

		if ( isset( $_POST['reindex'] ) ) {
			/**
			 * Load the reindex page, shows a progressbar and sents ajax calls to the server with
			 * small amounts of posts to reindex.
			 */
			require( plugin_dir_path( __FILE__ ) . 'views/reindex_page.php' );
		} else {
			if ( $xmlopt['enablexmlsitemap'] !== true ) {
				echo '<p>' . sprintf( esc_html__( 'Please enable the XML sitemap under the SEO -> %sXML Sitemaps settings%s', 'yoast-video-seo' ), '<a href="' . esc_url( add_query_arg( array( 'page' => 'wpseo_xml' ), admin_url( 'admin.php' ) ) ) . '">', '</a>' ) . '</p>';
			} else {

				echo '<h2>' . esc_html__( 'General Settings', 'yoast-video-seo' ) . '</h2>';

				if ( is_array( $options['videositemap_posttypes'] ) && $options['videositemap_posttypes'] !== array() ) {
					// Use fields => ids to limit the overhead of fetching entire post objects,
					// fetch only an array of ids instead to count
					$args          = array(
						'post_type'      => $options['videositemap_posttypes'],
						'post_status'    => 'publish',
						'posts_per_page' => - 1,
						//'offset'         => 0,
						'meta_key'       => '_yoast_wpseo_video_meta',
						'meta_compare'   => '!=',
						'meta_value'     => 'none',
						'fields'         => 'ids',
					);
					$video_ids     = get_posts( $args );
					$count         = count( $video_ids );
					$n             = ( $count > $this->max_entries ) ? (int) ceil( $count / $this->max_entries ) : '';
					$video_lastest = str_replace( 'sitemap.xml', 'sitemap' . $n . '.xml', $this->sitemap_url() );

					echo '<p>' . esc_html__( 'Please find your video sitemap here:', 'yoast-video-seo' ) . ' <a class="button" target="_blank" href="' . esc_url( $video_lastest ) . '">' . esc_html__( 'Video Sitemap', 'yoast-video-seo' ) . '</a></p>';
				} else {
					echo '<p>' . esc_html__( 'Select at least one post type to enable the video sitemap', 'yoast-video-seo' ) . '</p>';

				}


				echo WPSEO_Video_Wrappers::checkbox( 'cloak_sitemap', esc_html__( 'Hide the sitemap from normal visitors?', 'yoast-video-seo' ) );
				echo WPSEO_Video_Wrappers::checkbox( 'disable_rss', esc_html__( 'Disable Media RSS Enhancement', 'yoast-video-seo' ) );

				echo WPSEO_Video_Wrappers::textinput( 'custom_fields', esc_html__( 'Custom fields', 'yoast-video-seo' ) );
				echo '<p class="clear description">' . esc_html__( 'Custom fields the plugin should check for video content (comma separated)', 'yoast-video-seo' ) . '</p>';
				echo WPSEO_Video_Wrappers::textinput( 'embedly_api_key', esc_html__( '(Optional) Embedly API Key', 'yoast-video-seo' ) );
				echo '<p class="clear description">' . sprintf( esc_html__( 'The video SEO plugin provides where possible enriched information about your videos. A lot of %1$svideo services%2$s are supported by default. For those services which aren\'t supported, we can try to retrieve enriched video information using %3$sEmbedly%2$s. If you want to use this option, you\'ll need to sign up for a (free) %3$sEmbedly%2$s account and provide the API key you receive.', 'yoast-video-seo' ), '<a href="http://kb.yoast.com/article/95-supported-video-hosting-platforms-for-video-seo-plugin">', '</a>', '<a href="http://embed.ly/">' ) . '</p>';
				echo '<br class="clear"/>';


				echo '<h2>' . esc_html__( 'Embed Settings', 'yoast-video-seo' ) . '</h2>';

				echo WPSEO_Video_Wrappers::checkbox( 'facebook_embed', esc_html__( 'Allow videos to be played directly on Facebook.', 'yoast-video-seo' ) );
				echo WPSEO_Video_Wrappers::checkbox( 'fitvids', sprintf( esc_html__( 'Try to make videos responsive using %sFitVids.js%s?', 'yoast-video-seo' ), '<a href="http://fitvidsjs.com/">', '</a>' ) );

				echo WPSEO_Video_Wrappers::textinput( 'content_width', esc_html__( 'Content width', 'yoast-video-seo' ) );
				echo '<p class="clear description">' . esc_html__( 'This defaults to your themes content width, but if it\'s empty, setting a value here will make sure videos are embedded in the right width.', 'yoast-video-seo' ) . '</p>';

				echo WPSEO_Video_Wrappers::textinput( 'vzaar_domain', esc_html__( 'Vzaar domain', 'yoast-video-seo' ) );
				echo '<p class="clear description">' . esc_html__( 'If you use Vzaar, set this to the domain name you use for your Vzaar videos, no http: or slashes needed.', 'yoast-video-seo' ) . '</p>';
				echo WPSEO_Video_Wrappers::textinput( 'wistia_domain', esc_html__( 'Wistia domain', 'yoast-video-seo' ) );
				echo '<p class="clear description">' . esc_html__( 'If you use Wistia in combination with a custom domain, set this to the domain name you use for your Wistia videos, no http: or slashes needed.', 'yoast-video-seo' ) . '</p>';
				echo '<br class="clear"/>';


				echo '<h2>' . esc_html__( 'Post Types to include in XML Video Sitemap', 'yoast-video-seo' ) . '</h2>';
				echo '<p>' . esc_html__( 'Determine which post types on your site might contain video.', 'yoast-video-seo' ) . '</p>';

				$post_types = get_post_types( array( 'public' => true ), 'objects' );
				if ( is_array( $post_types ) && $post_types !== array() ) {
					foreach ( $post_types as $posttype ) {
						$sel = '';
						if ( is_array( $options['videositemap_posttypes'] ) && in_array( $posttype->name, $options['videositemap_posttypes'] ) ) {
							$sel = 'checked="checked" ';
						}
						echo '<input class="checkbox double" id="' . esc_attr( 'include' . $posttype->name ) . '" type="checkbox" '
							. 'name="wpseo_video[videositemap_posttypes][' . esc_attr( $posttype->name ) . ']" ' . $sel . 'value="' . esc_attr( $posttype->name ) . '"/> '
							. '<label for="' . esc_attr( 'include' . $posttype->name ) . '">' . esc_html( $posttype->labels->name ) . '</label><br class="clear">';
					}
				}
				unset( $post_types );


				echo '<h2>' . esc_html__( 'Taxonomies to include in XML Video Sitemap', 'yoast-video-seo' ) . '</h2>';
				echo '<p>' . esc_html__( 'You can also include your taxonomy archives, for instance, if you have videos on a category page.', 'yoast-video-seo' ) . '</p>';

				$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
				if ( is_array( $taxonomies ) && $taxonomies !== array() ) {
					foreach ( $taxonomies as $tax ) {
						$sel = '';
						if ( is_array( $options['videositemap_taxonomies'] ) && in_array( $tax->name, $options['videositemap_taxonomies'] ) ) {
							$sel = 'checked="checked" ';
						}
						echo '<input class="checkbox double" id="' . esc_attr( 'include' . $tax->name ) . '" type="checkbox" '
							. 'name="wpseo_video[videositemap_taxonomies][' . esc_attr( $tax->name ) . ']" ' . $sel . 'value="' . esc_attr( $tax->name ) . '"/> '
							. '<label for="' . esc_attr( 'include' . $tax->name ) . '">' . esc_html( $tax->labels->name ) . '</label><br class="clear">';
					}
				}
				unset( $taxonomies );
			}
			echo '<br class="clear"/>';
			?>

			<div class="submit">
				<input type="submit" class="button-primary" name="submit"
					   value="<?php esc_attr_e( 'Save Settings', 'yoast-video-seo' ); ?>" />
			</div>
			</form>

			<h2><?php esc_html_e( 'Indexation of videos in your content', 'yoast-video-seo' ); ?></h2>

			<p style="max-width: 600px;"><?php esc_html_e( 'This process goes through all the post types specified by you, as well as the terms of each taxonomy, to check for videos in the content. If the plugin finds a video, it updates the meta data for that piece of content, so it can add that meta data and content to the XML Video Sitemap.', 'yoast-video-seo' ); ?></p>

			<p style="max-width: 600px;"><?php esc_html_e( 'By default the plugin only checks content that hasn\'t been checked yet. However, if you check \'Force Re-Index\', it will re-check all content. This is particularly interesting if you want to check for a video embed code that wasn\'t supported before, of if you want to update thumbnail images en masse.', 'yoast-video-seo' ); ?></p>

			<form method="post" action="">
				<input class="checkbox double" type="checkbox" name="force" id="force">
				<label for="force"><?php esc_html_e( "Force reindex of already indexed videos.", 'yoast-video-seo' ); ?></label><br />
				<br />
				<input type="submit" class="button" name="reindex"
					   value="<?php esc_html_e( 'Re-Index Videos', 'yoast-video-seo' ); ?>" />
			</form>
		<?php

		}
		// Add debug info
		WPSEO_Video_Wrappers::admin_footer( false, false );
	}


	/**
	 * Based on the video type being used, this content filtering function will automatically optimize the embed codes
	 * to allow for proper recognition by search engines.
	 *
	 * This function also, since version 1.2, adds the schema.org videoObject output.
	 *
	 * @link  http://schema.org/VideoObject
	 * @link  https://developers.google.com/webmasters/videosearch/schema
	 *
	 * @since 0.1
	 *
	 * @param string $content The content of the post.
	 *
	 * @return string $content The content of the post as modified by the function, if applicable.
	 */
	public function content_filter( $content ) {
		global $post;

		if ( is_feed() || is_home() || is_archive() || is_tax() || is_tag() || is_category() ) {
			return $content;
		}

		if ( ! is_object( $post ) ) {
			return $content;
		}

		$video = WPSEO_Meta::get_value( 'video_meta', $post->ID );

		if ( ! is_array( $video ) || $video === array() ) {
			return $content;
		}

		$disable = WPSEO_Meta::get_value( 'videositemap-disable', $post->ID );
		if ( $disable === 'on' ) {
			return $content;
		}

		$content_width = $GLOBALS['content_width'];
		if ( ! is_numeric( $content_width ) ) {
			$content_width = 400;
		}

		switch ( $video['type'] ) {
			case 'vimeo':
				$content = str_replace( '<iframe src="http://player.vimeo.com', '<noframes><embed src="http://vimeo.com/moogaloop.swf?clip_id=' . $video['id'] . '" type="application/x-shockwave-flash" width="400" height="300"></embed></noframes><iframe src="http://player.vimeo.com', $content );
				break;

			case 'dailymotion':
				// If dailymotion is embedded using the Viper shortcode, we have to add a noscript version too
				if ( strpos( $content, '<iframe src="http://www.dailymotion' ) === false ) {
					$content = str_replace( '[/dailymotion]', '[/dailymotion]<noscript><iframe src="http://www.dailymotion.com/embed/video/' . $video['id'] . '" width="' . $content_width . '" height="' . floor( $content_width / 1.33 ) . '" frameborder="0"></iframe></noscript>', $content );
				}
				break;
		}

		$desc = trim( WPSEO_Meta::get_value( 'metadesc', $post->ID ) );
		if ( ! is_string( $desc ) || $desc === '' ) {
			$desc = trim( wp_html_excerpt( $this->strip_shortcodes( $post->post_content ), 300 ) );
		}

		$stripped_title = $this->strip_tags( get_the_title() );
		if ( empty( $desc ) ) {
			$desc = $stripped_title;
		}

		$video = $this->get_video_image( $post->ID, $video );

		$content .= "\n\n";
		$content .= '<span itemprop="video" itemscope itemtype="http://schema.org/VideoObject">';
		$content .= '<meta itemprop="name" content="' . esc_attr( $stripped_title ) . '">';
		$content .= '<meta itemprop="thumbnailURL" content="' . esc_attr( $video['thumbnail_loc'] ) . '">';
		$content .= '<meta itemprop="description" content="' . esc_attr( $desc ) . '">';
		$content .= '<meta itemprop="uploadDate" content="' . date( 'c', strtotime( $post->post_date ) ) . '">';

		if ( isset( $video['player_loc'] ) ) {
			$content .= '<meta itemprop="embedURL" content="' . $video['player_loc'] . '">';
		}
		if ( isset( $video['content_loc'] ) ) {
			$content .= '<meta itemprop="contentURL" content="' . $video['content_loc'] . '">';
		}

		$video_duration = WPSEO_Meta::get_value( 'videositemap-duration', $post->ID );
		if ( $video_duration == 0 && isset( $video['duration'] ) ) {
			$video_duration = $video['duration'];
		}

		if ( $video_duration ) {
			$content .= '<meta itemprop="duration" content="' . $this->iso_8601_duration( $video_duration ) . '">';
		}
		$content .= '</span>';

		return $content;
	}


	/**
	 * A better strip tags that leaves spaces intact (and rips out more code)
	 *
	 * @since 1.3.4
	 *
	 * @link  http://php.net/strip-tags#110280
	 *
	 * @param string $string string to strip tags from
	 *
	 * @return string
	 */
	public function strip_tags( $string ) {

		// ----- remove HTML TAGs -----
		$string = preg_replace( '/<[^>]*>/', ' ', $string );

		// ----- remove control characters -----
		$string = str_replace( "\r", '', $string ); // --- replace with empty space
		$string = str_replace( "\n", ' ', $string ); // --- replace with space
		$string = str_replace( "\t", ' ', $string ); // --- replace with space

		// ----- remove multiple spaces -----
		$string = trim( preg_replace( '/ {2,}/', ' ', $string ) );

		return $string;
	}


	/**
	 * Convert the duration in seconds to an ISO 8601 compatible output. Assumes the length is not over 24 hours.
	 *
	 * @link http://en.wikipedia.org/wiki/ISO_8601
	 *
	 * @param int $duration The duration in seconds.
	 *
	 * @return string $out ISO 8601 compatible output.
	 */
	public function iso_8601_duration( $duration ) {
		if ( $duration <= 0 ) {
			return '';
		}

		$out = 'PT';
		if ( $duration > HOUR_IN_SECONDS ) {
			$hours = floor( $duration / HOUR_IN_SECONDS );
			$out .= $hours . 'H';
			$duration = ( $duration - ( $hours * HOUR_IN_SECONDS ) );
		}
		if ( $duration > MINUTE_IN_SECONDS ) {
			$minutes = floor( $duration / MINUTE_IN_SECONDS );
			$out .= $minutes . 'M';
			$duration = ( $duration - ( $minutes * MINUTE_IN_SECONDS ) );
		}
		if ( $duration > 0 ) {
			$out .= $duration . 'S';
		}

		return $out;
	}


	/**
	 * Filter the OpenGraph type for the post and sets it to 'video'
	 *
	 * @since 0.1
	 *
	 * @param string $type The type, normally "article"
	 *
	 * @return string $type Value 'video'
	 */
	public function opengraph_type( $type ) {
		$options = get_option( 'wpseo_video' );

		if ( $options['facebook_embed'] !== true ) {
			return $type;
		}

		return $this->type_filter( $type, 'video.movie' );
	}


	/**
	 * Switch the Twitter card type to player if needed.
	 *
	 * @internal [JRF] This method does not seem to be hooked in anywhere
	 *
	 * @param string $type The Twitter card type
	 *
	 * @return string
	 */
	public function card_type( $type ) {
		return $this->type_filter( $type, 'player' );
	}


	/**
	 * Helper function for Twitter and OpenGraph card types
	 *
	 * @param  string $type The card type
	 * @param  string $video_output
	 *
	 * @return string
	 */
	public function type_filter( $type, $video_output ) {
		global $post;

		if ( is_singular() ) {
			if ( is_object( $post ) ) {
				$video = WPSEO_Meta::get_value( 'video_meta', $post->ID );

				if ( ! is_array( $video ) || $video === array() ) {
					return $type;
				} else {
					$disable = WPSEO_Meta::get_value( 'videositemap-disable', $post->ID );
					if ( $disable === 'on' ) {
						return $type;
					} else {
						return $video_output;
					}
				}
			}
		} else {
			if ( is_tax() || is_category() || is_tag() ) {
				$options = get_option( 'wpseo_video' );
				$term    = get_queried_object();

				if ( is_array( $options['videositemap_taxonomies'] ) && in_array( $term->taxonomy, $options['videositemap_taxonomies'] ) ) {
					$tax_meta = get_option( 'wpseo_taxonomy_meta' );
					if ( isset( $tax_meta[$term->taxonomy]['_video'][$term->term_id] ) ) {
						return $video_output;
					}
				}
			}
		}

		return $type;
	}


	/**
	 * Filter the OpenGraph image for the post and sets it to the video thumbnail
	 *
	 * @since 0.1
	 *
	 * @param  string $image URL to the image
	 *
	 * @return string $image URL to the video thumbnail image
	 */
	public function opengraph_image( $image ) {
		if ( is_string( $image ) && $image !== '' ) {
			return $image;
		}

		if ( is_singular() ) {
			global $post;

			if ( is_object( $post ) ) {
				$video = WPSEO_Meta::get_value( 'video_meta', $post->ID );

				if ( ! is_array( $video ) || $video === array() ) {
					return $image;
				}

				$disable = WPSEO_Meta::get_value( 'videositemap-disable', $post->ID );
				if ( $disable === 'on' ) {
					return $image;
				}

				return $video['thumbnail_loc'];
			}
		} else {
			if ( is_tax() || is_category() || is_tag() ) {
				$options = get_option( 'wpseo_video' );

				$term = get_queried_object();

				if ( is_array( $options['videositemap_taxonomies'] ) && in_array( $term->taxonomy, $options['videositemap_taxonomies'] ) ) {
					$tax_meta = get_option( 'wpseo_taxonomy_meta' );
					if ( isset( $tax_meta[$term->taxonomy]['_video'][$term->term_id] ) ) {
						$video = $tax_meta[$term->taxonomy]['_video'][$term->term_id];

						return $video['thumbnail_loc'];
					}
				}
			}
		}

		return $image;
	}


	/**
	 * Add OpenGraph video info if present
	 *
	 * @since 0.1
	 */
	public function opengraph() {
		$options = get_option( 'wpseo_video' );

		if ( $options['facebook_embed'] !== true ) {
			return false;
		}

		if ( is_singular() ) {
			global $post;

			if ( is_object( $post ) ) {
				$video = WPSEO_Meta::get_value( 'video_meta', $post->ID );

				if ( is_array( $video ) && $video !== array() ) {
					$disable = WPSEO_Meta::get_value( 'videositemap-disable', $post->ID );
					if ( $disable !== 'on' ) {
						$video = $this->get_video_image( $post->ID, $video );
					}
				}
			}
		} else {
			if ( is_tax() || is_category() || is_tag() ) {

				$term = get_queried_object();

				if ( is_array( $options['videositemap_taxonomies'] ) && in_array( $term->taxonomy, $options['videositemap_taxonomies'] ) ) {
					$tax_meta = get_option( 'wpseo_taxonomy_meta' );
					if ( isset( $tax_meta[$term->taxonomy]['_video'][$term->term_id] ) ) {
						$video = $tax_meta[$term->taxonomy]['_video'][$term->term_id];
					}
				}
			}
		}

		if ( ! isset( $video ) || ! is_array( $video ) || ! isset( $video['player_loc'] ) ) {
			return false;
		}

		echo '<meta property="og:video" content="' . esc_attr( $video['player_loc'] ) . '" />' . "\n";
		echo '<meta property="og:video:type" content="application/x-shockwave-flash" />' . "\n";
		if ( isset( $video['width'] ) && isset( $video['height'] ) ) {
			echo '<meta property="og:video:width" content="' . esc_attr( $video['width'] ) . '" />' . "\n";
			echo '<meta property="og:video:height" content="' . esc_attr( $video['height'] ) . '" />' . "\n";
		}
		$GLOBALS['wpseo_og']->image_output( $video['thumbnail_loc'] );
	}


	/**
	 * Make the get_terms query only return terms with a non-empty description.
	 *
	 * @since 1.3
	 *
	 * @param  array $pieces The separate pieces of the terms query to filter.
	 *
	 * @return mixed
	 */
	public function filter_terms_clauses( $pieces ) {
		$pieces['where'] .= " AND tt.description != ''";

		return $pieces;
	}

	/**
	 * Wrapper function to check if we have a valid datetime (Uses a new util in WPSEO)
	 *
	 * @param string $datetime
	 *
	 * @return bool
	 */
	private function is_valid_datetime( $datetime ) {
		if ( method_exists( 'WPSEO_Utils', 'is_valid_datetime' ) ) {
			return WPSEO_Utils::is_valid_datetime( $datetime );
		}

		return true;
	}

	/**
	 * Get a single sitemap line to output in the xml sitemap
	 *
	 * @param $val
	 * @param $key
	 * @param $xtra
	 *
	 * @return null|string
	 */
	private function get_single_sitemap_line( $val, $key, $xtra ) {
		$val = $this->clean_string( $val );
		if ( in_array( $key, array( 'description', 'category', 'tag', 'title' ) ) ) {
			$val = ent2ncr( esc_html( $val ) );
		}
		if ( ! empty ( $val ) ) {
			return "\t\t\t<video:" . $key . $xtra . '>' . wpseo_replace_vars( $val, array() ) . '</video:' . $key . ">\n";
		}

		return null;
	}

	/**
	 * Reindex the video info from posts
	 *
	 * @since 0.1
	 *
	 * @param $portion
	 * @param $start
	 */
	private function reindex( $portion, $start ) {
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$options = get_option( 'wpseo_video' );

		if ( is_array( $options['videositemap_posttypes'] ) && $options['videositemap_posttypes'] !== array() ) {
			$args = array(
				'post_type'   => $options['videositemap_posttypes'],
				'post_status' => 'publish',
				'numberposts' => $portion,
				'offset'      => $start,
			);

			if ( ! isset( $_POST['force'] ) ) {
				if ( version_compare( $GLOBALS['wp_version'], '3.5', '>=' ) ) {
					$args['meta_query'] = array(
						'key'     => '_yoast_wpseo_video_meta',
						'compare' => 'NOT EXISTS',
					);
				}
			}

			$post_count_total = 0;
			foreach ( $options['videositemap_posttypes'] as $post_type ) {
				$post_count_total += wp_count_posts( $post_type )->publish;
			}

			$results      = get_posts( $args );
			$result_count = count( $results );

			if ( is_array( $results ) && $result_count > 0 ) {
				foreach ( $results as $post ) {
					$this->update_video_post_meta( $post, false );
					flush();
				}
			}
		}

		// Get all the non-empty terms.
		add_filter( 'terms_clauses', array( $this, 'filter_terms_clauses' ) );
		$terms = array();
		if ( is_array( $options['videositemap_taxonomies'] ) && $options['videositemap_taxonomies'] !== array() ) {
			foreach ( $options['videositemap_taxonomies'] as $val ) {
				$new_terms = get_terms( $val );
				if ( is_array( $new_terms ) ) {
					$terms = array_merge( $terms, $new_terms );
				}
			}
		}
		remove_filter( 'terms_clauses', array( $this, 'filter_terms_clauses' ) );

		if ( count( $terms ) > 0 ) {

			foreach ( $terms as $term ) {
				$this->update_video_term_meta( $term, false );
				flush();
			}
		}

		// Ping the search engines with our updated XML sitemap, we ping with the index sitemap because
		// we don't know which video sitemap, or sitemaps, have been updated / added.
		wpseo_ping_search_engines();

		// Remove the admin notice
		delete_transient( 'video_seo_recommend_reindex' );
	}


	/********************** DEPRECATED METHODS **********************/


	/**
	 * Register the wpseo_video setting
	 *
	 * @deprecated 1.6.0 - now auto-handled by class WPSEO_Option_Video
	 */
	public function options_init() {
		_deprecated_function( __METHOD__, 'Video SEO 1.6.0', null );
	}


	/**
	 * Register defaults for the video sitemap
	 *
	 * @since      0.2
	 * @deprecated 1.6.0 - now auto-handled by class WPSEO_Option_Video
	 */
	public function set_defaults() {
		_deprecated_function( __METHOD__, 'Video SEO 1.6.0', null );
	}


	/**
	 * Adds the header for the Video tab in the WordPress SEO meta box on edit post pages.
	 *
	 * @since      0.1
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::tab_header()
	 * @see        WPSEO_Video_Metabox::tab_header()
	 */
	public function tab_header() {
		_deprecated_function( __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::tab_header()' );
		$this->metabox_tab->tab_header();
	}


	/**
	 * Outputs the content for the Video tab in the WordPress SEO meta box on edit post pages.
	 *
	 * @since      0.1
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::tab_content()
	 * @see        WPSEO_Video_Metabox::tab_content()
	 */
	public function tab_content() {
		_deprecated_function( __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::tab_content()' );
		$this->metabox_tab->tab_content();
	}


	/**
	 * Output a tab in the WP SEO Metabox
	 *
	 * @since      0.2
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::do_tab()
	 * @see        WPSEO_Video_Metabox::do_tab()
	 *
	 * @param string $id      CSS ID of the tab.
	 * @param string $heading Heading for the tab.
	 * @param string $content Content of the tab.
	 */
	public function do_tab( $id, $heading, $content ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::do_tab()' );
		$this->metabox_tab->do_tab( $id, $heading, $content );
	}


	/**
	 * Adds a line in the meta box
	 *
	 * @since      0.2
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::do_meta_box()
	 * @see        WPSEO_Video_Metabox::do_meta_box()
	 *
	 * @param array $meta_box Contains the vars based on which output is generated.
	 *
	 * @return string
	 */
	public function do_meta_box( $meta_box ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::do_meta_box()' );

		return $this->metabox_tab->do_meta_box( $meta_box );
	}


	/**
	 * Defines the meta box inputs
	 *
	 * @since      0.1
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Meta::get_meta_field_defs()
	 * @see        WPSEO_Meta::get_meta_field_defs()
	 *
	 * @param string $post_type The current post type
	 *
	 * @return array            Meta box inputs
	 */
	public function get_meta_boxes( $post_type = 'post' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Meta::get_meta_field_defs()' );

		return WPSEO_Meta::get_meta_field_defs( 'video', $post_type );
	}


	/**
	 * Save the values from the meta box inputs
	 *
	 * @since      0.1
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::save_meta_boxes()
	 * @see        WPSEO_Video_Metabox::save_meta_boxes()
	 *
	 * @param array $mbs meta boxes to merge the inputs with.
	 *
	 * @return array $mbs meta box inputs
	 */
	public function save_meta_boxes( $mbs ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::save_meta_boxes()' );

		return $this->metabox_tab->save_meta_boxes( $mbs );
	}


	/**
	 * Replace the default snippet with a video snippet by hooking this function into the wpseo_snippet filter.
	 *
	 * @since      0.1
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::snippet_preview()
	 * @see        WPSEO_Video_Metabox::snippet_preview()
	 *
	 * @param string $content The original snippet content.
	 * @param object $post    The post object of the post for which the snippet was generated.
	 * @param array  $vars    An array of variables for use within the snippet, containing title, description, date and slug
	 *
	 * @return string $content The new video snippet if video metadata was found for the post.
	 */
	public function snippet_preview( $content, $post, $vars ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::snippet_preview()' );

		return $this->metabox_tab->snippet_preview( $content, $post, $vars );
	}


	/**
	 * Restricts the length of the meta description in the snippet preview and throws appropriate warnings.
	 *
	 * @since      0.1
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::meta_length()
	 * @see        WPSEO_Video_Metabox::meta_length()
	 *
	 * @param int $length The snippet length as defined by default.
	 *
	 * @return int $length The max snippet length for a video snippet.
	 */
	public function meta_length( $length ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::meta_length()' );

		return $this->metabox_tab->meta_length( $length );
	}


	/**
	 * Explains the length restriction of the meta description
	 *
	 * @since      0.1
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::meta_length_reason()
	 * @see        WPSEO_Video_Metabox::meta_length_reason()
	 *
	 * @param string $reason Input string.
	 *
	 * @return string $reason  The reason why the meta description is limited.
	 */
	public function meta_length_reason( $reason ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::meta_length_reason()' );

		return $this->metabox_tab->meta_length_reason( $reason );
	}


	/**
	 * Filter the Page Analysis results to make sure we're giving the correct hints.
	 *
	 * @since      1.4
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::filter_linkdex_results()
	 * @see        WPSEO_Video_Metabox::filter_linkdex_results()
	 *
	 * @param array  $results The results array to filter and update.
	 * @param array  $job     The current jobs variables.
	 * @param object $post    The post object for the current page.
	 *
	 * @return array $results
	 */
	public function filter_linkdex_results( $results, $job, $post ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::filter_linkdex_results()' );

		return $this->metabox_tab->filter_linkdex_results( $results, $job, $post );
	}


	/**
	 * Wrapper for the WordPress internal wp_remote_get function, making sure a proper user-agent is sent along.
	 *
	 * @since      0.1
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Details::remote_get()
	 * @see        WPSEO_Video_Details::remote_get()
	 */
	public function remote_get() {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', 'WPSEO_Video_Details::remote_get()' );
	}


	/**
	 * Use the "new" post data with the old video data, to prevent the need for an external video API call when the video hasn't changed.
	 *
	 * @since      0.1
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Details::maybe_use_old_video_data()
	 * @see        WPSEO_Video_Details::maybe_use_old_video_data()
	 */
	public function use_old_video_data() {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', 'WPSEO_Video_Details::maybe_use_old_video_data()' );
	}


	/**
	 * Helper function for handling calls to the deprecated detail retrieval functions
	 *
	 * @param string $function The name of the function which was originally called
	 * @param array  $vid      The video array with all the data.
	 * @param array  $old_vid  The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb    The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	private function deprecated_details_helper( $function, $vid, $old_vid = array(), $thumb = '' ) {
		_doing_it_wrong( __METHOD__, 'This is a helper method for deprecated functions and should not be called directly', '1.8.0' );
		if ( ! isset( $vid['type'] ) ) {
			$vid['type'] = str_replace( '_details', '', $function );
		}
		if ( ! isset( $vid['thumbnail_loc'] ) && $thumb !== '' ) {
			$vid['thumbnail_loc'] = $thumb;
		}
		$index = new WPSEO_Video_Analyse_Post( '', $vid, $old_vid );

		return $index->get_video_details( $vid );
	}


	/**
	 * Retrieve video details from Animoto
	 *
	 * @since      1.4.3
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Animoto
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function animoto_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details from Blip.tv
	 *
	 * @since      0.1
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Blip
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function blip_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details from Brightcove
	 *
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Brightcove
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch" from Brightcove, if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function brightcove_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details for cincopa
	 *
	 * @since      ?
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Cincopa
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function cincopa_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details from Dailymotion
	 *
	 * @since      0.1
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Dailymotion
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function dailymotion_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details for Easy Video Suite (EVS)
	 *
	 * @since      ?
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Evs
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function evs_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details from Flickr
	 *
	 * @since      0.1
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Flickr
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function flickr_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details from Metacafe
	 *
	 * @since      0.1
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Metacafe
	 *
	 * @link       http://help.metacafe.com/?page_id=238 Metacafe API docs - no longer available.
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function metacafe_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details for Muzu.tv
	 *
	 * @since      ?
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Muzutv
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function muzutv_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details for Screencast.com
	 *
	 * @since      1.5.4.4
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Screencast
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function screencast_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details for Screenr
	 *
	 * @since      ?
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Screenr
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function screenr_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details for Veoh Videos
	 *
	 * @since      0.1
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Veoh
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function veoh_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details from Viddler
	 *
	 * @since      0.1
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Viddler
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch" from Viddler, if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function viddler_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details from VideoPress
	 *
	 * @since      0.1
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Videopress
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function videopress_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details from Vidyard
	 *
	 * @since      1.3.4.4
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Vidyard
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function vidyard_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details from Vimeo
	 *
	 * @since      0.1
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Vimeo
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function vimeo_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details from Vippy
	 *
	 * @since      1.3.4
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Vippy
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function vippy_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details from Vzaar
	 *
	 * @since      0.1
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Vzaar
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function vzaar_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details from Wistia
	 *
	 * @since      0.1
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_Wistia
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function wistia_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details from WordPress.tv (well grab the ID and then use the VideoPress API)
	 *
	 * @since      0.1
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_WordPresstv
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function wordpresstv_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details from YouTube
	 *
	 * @since      0.1
	 * @deprecated 1.7.0
	 * @deprecated use WPSEO_Video_Sitemap::get_video_details()
	 * @see        WPSEO_Video_Details_YouTube
	 *
	 * @param array  $vid     The video array with all the data.
	 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb   The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function youtube_details( $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.7.0', __CLASS__ . '::get_video_details()' );

		return $this->deprecated_details_helper( __FUNCTION__, $vid, $old_vid, $thumb );
	}


	/**
	 * Retrieve video details
	 *
	 * @since      1.7.0
	 * @deprecated 1.8.0
	 * @deprecated use WPSEO_Video_Analyse_Post::get_video_details()
	 * @see        WPSEO_Video_Analyse_Post::get_video_details
	 * @see        WPSEO_Video_Details
	 *
	 * @param string $video_type The video service the video is hosted on
	 * @param array  $vid        The video array with all the data.
	 * @param array  $old_vid    The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb      The URL to the manually set thumbnail, if available.
	 *
	 * @return array $vid     Returns an enriched video array when successful, or the original info when unsuccessful.
	 */
	public function get_video_details( $video_type, $vid, $old_vid = array(), $thumb = '' ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.8.0', 'WPSEO_Video_Analyse_Post::get_video_details()' );

		return $this->deprecated_details_helper( $video_type . '_details', $vid, $old_vid, $thumb );
	}


	/**
	 * Parse a URL and find the host name and more.
	 *
	 * @since      1.1
	 * @deprecated 1.8.0
	 * @deprecated use WPSEO_Video_Analyse_Post::parse_url()
	 * @see        WPSEO_Video_Analyse_Post::parse_url
	 *
	 * @link       http://php.net/manual/en/function.parse-url.php#83875
	 *
	 * @param string $url The URL to parse
	 *
	 * @return array
	 */
	public function parse_url( $url ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.8.0', 'WPSEO_Video_Analyse_Post::parse_url()' );

		return WPSEO_Video_Analyse_Post::parse_url( $url );
	}


	/**
	 * Returns the custom fields to check for posts.
	 *
	 * @param int $post_id The ID of the post to grab the custom fields for.
	 *
	 * @since      1.3.4
	 * @deprecated 1.8.0
	 * @deprecated use WPSEO_Video_Analyse_Post::get_vid_info()
	 * @see        WPSEO_Video_Analyse_Post::get_vid_info
	 * @see        WPSEO_Video_Analyse_Post::get_video_from_post_meta()
	 */
	public function get_custom_fields( $post_id ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.8.0', 'WPSEO_Video_Analyse_Post::get_vid_info()' );
	}


	/**
	 * Checks whether there are oembed URLs in the post that should be included in the video sitemap.
	 *
	 * @since      0.1
	 * @deprecated 1.8.0
	 * @deprecated use WPSEO_Video_Analyse_Post::get_vid_info()
	 * @see        WPSEO_Video_Analyse_Post::get_vid_info
	 *
	 * @param string $content the content of the post.
	 */
	public function grab_embeddable_urls( $content ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.8.0', 'WPSEO_Video_Analyse_Post::get_vid_info()' );
	}


	/**
	 * Checks whether there are oembed URLs in the post that should be included in the video sitemap.
	 * Uses DOMDocument and XPath to parse the content for url instead of preg matches
	 *
	 * @since      1.5.4.4
	 * @deprecated 1.8.0
	 * @deprecated use WPSEO_Video_Analyse_Post::get_vid_info()
	 * @see        WPSEO_Video_Analyse_Post::get_vid_info
	 */
	public function grab_embeddable_urls_xpath( $content ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.8.0', 'WPSEO_Video_Analyse_Post::get_vid_info()' );
	}

} /* End of class WPSEO_Video_Sitemap */


/**
 * Throw an error if WordPress SEO is not installed.
 *
 * @since 0.2
 */
function yoast_wpseo_missing_error() {
	$url     = add_query_arg(
		array(
			'tab'                 => 'search',
			'type'                => 'term',
			's'                   => 'wordpress+seo',
			'plugin-search-input' => 'Search+Plugins',
		),
		admin_url( 'plugin-install.php' )
	);
	$message = sprintf( esc_html__( 'Please %sinstall & activate WordPress SEO by Yoast%s and then enable its XML sitemap functionality to allow the Video SEO module to work.', 'yoast-video-seo' ), '<a href="' . esc_url( $url ) . '">', '</a>' );
	yoast_wpseo_video_seo_self_deactivate( $message );
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
	$message = esc_html__( 'Please upgrade the WordPress SEO plugin to the latest version to allow the Video SEO module to work.', 'yoast-video-seo' );
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
	} elseif ( ! version_compare( $GLOBALS['wp_version'], '3.6', '>=' ) ) {
		add_action( 'admin_init', 'yoast_wordpress_upgrade_error' );
	} else {
		if ( defined( 'WPSEO_VERSION' ) ) {
			if ( version_compare( WPSEO_VERSION, '1.4.99', '>=' ) ) { // Allow beta version
				add_action( 'plugins_loaded', 'yoast_wpseo_video_seo_meta_init', 10 );
				add_action( 'plugins_loaded', 'yoast_wpseo_video_seo_sitemap_init', 20 );
			} else {
				add_action( 'admin_init', 'yoast_wpseo_upgrade_error' );
			}
		} else {
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

if ( ! function_exists( 'wp_installing' ) ) {
	/**
	 * We need to define wp_installing in WordPress versions older than 4.4
	 *
	 * @return bool
	 */
	function wp_installing() {
		return defined( 'WP_INSTALLING' );
	}
}

if ( ! wp_installing() ) {
	add_action( 'plugins_loaded', 'yoast_wpseo_video_seo_init', 5 );
}

/**
 * Clear the sitemap index
 */
function yoast_wpseo_video_clear_sitemap_cache() {
	if ( class_exists( 'WPSEO_Sitemaps_Cache' ) && method_exists( 'WPSEO_Sitemaps_Cache', 'invalidate' ) ) {
		$sitemap_instance = new WPSEO_Video_Sitemap();
		WPSEO_Sitemaps_Cache::invalidate( $sitemap_instance->video_sitemap_basename() );
	}
}

/**
 * Self-deactivate plugin
 *
 * @since 1.7
 *
 * @param  string $message Error message
 *
 * @return void
 */
function yoast_wpseo_video_seo_self_deactivate( $message ) {
	if ( is_admin() && ( ! defined( 'IFRAME_REQUEST' ) || IFRAME_REQUEST === false ) ) {
		add_action( 'admin_notices', create_function( '$message', 'echo \'<div class="error"><p>\' . __( \'(Re-)Activation of Video SEO failed:\', \'yoast-video-seo\' ) . \' \' . $message . \'</p></div>\';' ) );
		trigger_error( $message );

		deactivate_plugins( plugin_basename( __FILE__ ) );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}

/**
 * Execute option cleanup actions on activate
 *
 * There are a couple of things being done on activation:
 * - Clean up te options to be sure it's set well
 * - Activating the license, because updating the plugin results in deactivating the license
 * - Clear the sitemap cache to rebuild the sitemap.
 */
function yoast_wpseo_video_activate() {
	$option_instance = WPSEO_Option_Video::get_instance();
	$option_instance->clean();

	yoast_wpseo_video_clear_sitemap_cache();

	// Activate the license
	$license_manager = new Yoast_Plugin_License_Manager( new Yoast_Product_WPSEO_Video() );
	$license_manager->activate_license();
}

/**
 * Empty sitemap cache on plugin deactivate
 */
function yoast_wpseo_video_deactivate() {
	yoast_wpseo_video_clear_sitemap_cache();
}

register_activation_hook( __FILE__, 'yoast_wpseo_video_activate' );

register_deactivation_hook( __FILE__, 'yoast_wpseo_video_deactivate' );

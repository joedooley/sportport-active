<?php
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
 * Wpseo_video_Video_Sitemap class.
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
	 * @var string Name of the metabox tab
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

	/** @var null|Yoast_Plugin_License_Manager  */
	protected $license_manager;


	/**
	 * Constructor for the WPSEO_Video_Sitemap class.
	 *
	 * @todo  Deal with upgrade from license constant WPSEO_VIDEO_LICENSE
	 * @since 0.1
	 */
	public function __construct() {

		// Initialize the options.
		$this->option_instance = WPSEO_Option_Video::get_instance();

		$options = get_option( 'wpseo_video' );

		// Run upgrade routine.
		$this->upgrade();

		add_filter( 'wpseo_tax_meta_special_term_id_validation__video', array( $this, 'validate_video_tax_meta' ) );

		// Set content_width based on theme content_width or our option value if either is available.
		$content_width = $this->get_content_width();
		if ( $content_width !== false ) {
			$GLOBALS['content_width'] = $content_width;
		}
		unset( $content_width );

		add_action( 'setup_theme', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'register_sitemap' ), 20 ); // Register sitemap after cpts have been added.
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_item' ), 97 );
		add_filter( 'oembed_providers', array( $this, 'sync_oembed_providers' ) );

		if ( is_admin() ) {

			add_action( 'admin_menu', array( $this, 'register_settings_page' ) );

			add_action( 'wp_insert_post', array( $this, 'update_video_post_meta' ), 12, 3 );

			if ( in_array( $GLOBALS['pagenow'], array(
					'edit.php',
					'post.php',
					'post-new.php',
				) ) || apply_filters( 'wpseo_always_register_metaboxes_on_admin', false )
			) {
				$this->metabox_tab = new WPSEO_Video_Metabox();
			}

			// Licensing part.
			$this->license_manager = $this->get_license_manager();

			// Add form.
			if ( $this->license_manager ) {
				add_action( 'wpseo_licenses_forms', array( $this->license_manager, 'show_license_form' ) );
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_video_enqueue_scripts' ) );

			add_action( 'admin_init', array( $this, 'admin_video_enqueue_styles' ) );

			add_action( 'wp_ajax_index_posts', array( $this, 'index_posts_callback' ) );

			add_action( 'wp_insert_post', array( $this, 'invalidate_sitemap' ) );

			// Maybe show 'Recommend re-index' admin notice.
			if ( get_transient( 'video_seo_recommend_reindex' ) === '1' ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_ignore' ) );
				add_action( 'all_admin_notices', array( $this, 'recommend_force_index' ) );
				add_action( 'wp_ajax_videoseo_set_ignore', array( $this, 'set_ignore' ) );
			}
		}
		else {

			// OpenGraph.
			add_action( 'wpseo_opengraph', array( $this, 'opengraph' ) );
			add_filter( 'wpseo_opengraph_type', array( $this, 'opengraph_type' ), 10, 1 );
			add_filter( 'wpseo_opengraph_image', array( $this, 'opengraph_image' ), 5, 1 );

			// XML Sitemap Index addition.
			add_filter( 'wpseo_sitemap_index', array( $this, 'add_to_index' ) );

			// Content filter for non-detected videos.
			add_filter( 'the_content', array( $this, 'content_filter' ), 5, 1 );

			if ( $options['fitvids'] === true ) {
				// Fitvids scripting.
				add_action( 'wp_head', array( $this, 'fitvids' ) );
			}

			if ( $options['disable_rss'] !== true ) {
				// MRSS.
				add_action( 'rss2_ns', array( $this, 'mrss_namespace' ) );
				add_action( 'rss2_item', array( $this, 'mrss_item' ), 10, 1 );
				add_filter( 'mrss_media', array( $this, 'mrss_add_video' ) );
			}
		}
	}


	/**
	 * Retrieve a value to use for content_width.
	 *
	 * @since 3.8.0
	 *
	 * @param int $default (Optional) Default value to use if value could not be determined.
	 *
	 * @return int|false Integer content width value or false if it could not be determined
	 *                   and no default was provided.
	 */
	public function get_content_width( $default = 0 ) {
		// If the theme or WP has set it, use what's already available.
		if ( ! empty( $GLOBALS['content_width'] ) ) {
			return (int) $GLOBALS['content_width'];
		}

		// If the user has set it in options, use that.
		$options              = get_option( 'wpseo_video' );
		$option_content_width = (int) $options['content_width'];
		if ( $option_content_width > 0 ) {
			return $option_content_width;
		}

		// Otherwise fall back to an arbitrary default if provided.
		// WP itself uses 500 for embeds, 640 for playlists and video shortcodes.
		if ( $default > 0 ) {
			return $default;
		}

		return false;
	}


	/**
	 * Method to invalidate the sitemap
	 *
	 * @param integer $post_id Post ID.
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
		return WPSEO_VIDEO_FILE;

	}

	/**
	 * When sitemap is coming out of the cache there is no stylesheet. Normally it will take the default stylesheet.
	 *
	 * This method is called by a filter that will set the video stylesheet.
	 *
	 * @param object $target_object Target object.
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
		$stylesheet_url = "\n" . '<?xml-stylesheet type="text/xsl" href="' . esc_url( home_url( 'video-sitemap.xsl' ) ) . '"?>';

		return $stylesheet_url;
	}


	/**
	 * Load translations
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'yoast-video-seo', false, dirname( plugin_basename( WPSEO_VIDEO_FILE ) ) . '/languages/' );
	}


	/**
	 * Add more video extensions to the list of allowed video extensions and make sure the wp list
	 * and the internally used list are in line with each other.
	 *
	 * @internal / @todo Not yet in use - uncomment the add_filter above to implement
	 *
	 * @internal WP default list as of v3.9.2 is array( 'mp4', 'm4v', 'webm', 'ogv', 'wmv', 'flv' );
	 *
	 * @param  array $exts Current list of extensions.
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
				wp_enqueue_script( 'fitvids', plugins_url( 'js/jquery.fitvids.js', WPSEO_VIDEO_FILE ), array( 'jquery' ) );
			}
			else {
				wp_enqueue_script( 'fitvids', plugins_url( 'js/jquery.fitvids.min.js', WPSEO_VIDEO_FILE ), array( 'jquery' ) );
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

		// Try and use the post class to determine the container.
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
	 * @param array $adminpages The array of pages that have Yoast admin styling.
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
		add_submenu_page( 'wpseo_dashboard', 'Video SEO - Yoast SEO', 'Video SEO', 'manage_options', 'wpseo_video', array(
			$this,
			'admin_panel',
		) );
	}


	/**
	 * Adds the rewrite for the video XML sitemap
	 *
	 * @since 0.1
	 */
	public function init() {
		// Get options to set the entries per page.
		$options           = WPSEO_Options::get_all();
		$this->max_entries = $options['entries-per-page'];

		// Add oEmbed providers.
		$this->add_oembed();

		// Only load the beacon when the License Manager is present.
		if ( $this->license_manager ) {
			$this->init_beacon();
		}
	}

	/**
	 * Initializes the HelpScout beacon
	 */
	private function init_beacon() {
		$query_var = ( $page = filter_input( INPUT_GET, 'page' ) ) ? $page : '';

		// Only add the helpscout beacon on Yoast SEO pages.
		if ( $query_var === 'wpseo_video' ) {
			$beacon = yoast_get_helpscout_beacon( $query_var );
			$beacon->add_setting( new WPSEO_Video_Beacon_Setting() );
			$beacon->register_hooks();
		}
	}


	/**
	 * Add VideoSeo Admin bar menu item
	 *
	 * @param object $wp_admin_bar Current admin bar.
	 */
	public function add_admin_bar_item( $wp_admin_bar ) {
		$admin_menu = false;
		if ( is_multisite() ) {
			$options = get_site_option( 'wpseo_ms' );
			if ( $options['access'] === 'superadmin' && is_super_admin() ) {
				$admin_menu = true;
			}
			elseif ( current_user_can( 'manage_options' ) ) {
				$admin_menu = true;
			}
		}
		elseif ( current_user_can( 'manage_options' ) ) {
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
		$basename = $this->video_sitemap_basename();

		// Register the sitemap.
		if ( isset( $GLOBALS['wpseo_sitemaps'] ) && is_object( $GLOBALS['wpseo_sitemaps'] ) ) {
			$GLOBALS['wpseo_sitemaps']->register_sitemap( $basename, array( $this, 'build_video_sitemap' ) );

			if ( method_exists( $GLOBALS['wpseo_sitemaps'], 'register_xsl' ) ) {
				$GLOBALS['wpseo_sitemaps']->register_xsl( 'video', array( $this, 'build_video_sitemap_xsl' ) );
			}
		}

		if ( is_admin() ) {
			// Setting action for removing the transient on update options.
			if ( method_exists( 'WPSEO_Utils', 'register_cache_clear_option' ) ) {
				WPSEO_Utils::register_cache_clear_option( 'wpseo_video', $basename );
			}
		}
		else {
			// Setting stylesheet for cached sitemap.
			add_action( 'wpseo_sitemap_stylesheet_cache_' . $basename, array( $this, 'set_stylesheet_cache' ) );
		}
	}


	/**
	 * Execute upgrade actions when needed
	 */
	function upgrade() {

		$options = get_option( 'wpseo_video' );

		// Early bail if dbversion is equal to current version.
		if ( isset( $options['dbversion'] ) && version_compare( $options['dbversion'], WPSEO_VIDEO_VERSION, '==' ) ) {
			return;
		}

		$yoast_product   = new Yoast_Product_WPSEO_Video();
		$license_manager = new Yoast_Plugin_License_Manager( $yoast_product );

		// Upgrade to license manager.
		if ( $license_manager->get_license_key() === '' ) {

			if ( isset( $options['yoast-video-seo-license'] ) ) {
				$license_manager->set_license_key( $options['yoast-video-seo-license'] );
			}

			if ( isset( $options['yoast-video-seo-license-status'] ) ) {
				$license_manager->set_license_status( $options['yoast-video-seo-license-status'] );
			}
			update_option( 'wpseo_video', $options );
		}

		// Upgrade to new option & meta classes.
		if ( ! isset( $options['dbversion'] ) || version_compare( $options['dbversion'], '1.6', '<' ) ) {
			$this->option_instance->clean();
			// Make sure our meta values are cleaned up even if WP SEO would have been upgraded already.
			WPSEO_Meta::clean_up();
		}

		// Re-add missing durations.
		if ( ! isset( $options['dbversion'] ) || ( version_compare( $options['dbversion'], '1.7', '<' ) && version_compare( $options['dbversion'], '1.6', '>' ) ) ) {
			WPSEO_Meta_Video::re_add_durations();
		}

		// Recommend force re-index.
		if ( isset( $options['dbversion'] ) && version_compare( $options['dbversion'], '4.0', '<' ) ) {
			set_transient( 'video_seo_recommend_reindex', 1 );
		}

		// Make sure version nr gets updated for any version without specific upgrades.
		// Re-get to make sure we have the latest version.
		$options = get_option( 'wpseo_video' );
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
				/* translators: 1: link open tag, 2: link close tag. */
				__( 'The VideoSEO upgrade which was just applied contains a lot of improvements. It is strongly recommended that you %1$sre-index the video content on your website%2$s with the \'force reindex\' option checked.', 'yoast-video-seo' ),
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
			wp_enqueue_script( 'videoseo-admin-progress-bar', plugins_url( 'js/videoseo-admin-progressbar' . WPSEO_CSSJS_SUFFIX . '.js', WPSEO_VIDEO_FILE ), array( 'jquery' ), WPSEO_VIDEO_VERSION, true );
		}
	}

	/**
	 * Load styles for the admin in Video SEO
	 */
	public function admin_video_enqueue_styles() {
		if ( isset( $_POST['reindex'] ) ) {
			wp_enqueue_style( 'videoseo-admin-progress-bar-css', plugins_url( 'css/videoseo-admin-progressbar' . WPSEO_CSSJS_SUFFIX . '.css', WPSEO_VIDEO_FILE ) );
		}
	}

	/**
	 * Load a small js file to facilitate ignoring admin messages
	 */
	public function admin_enqueue_scripts_ignore() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_enqueue_script( 'videoseo-admin-global-script', plugins_url( 'js/videoseo-admin-global' . WPSEO_CSSJS_SUFFIX . '.js', WPSEO_VIDEO_FILE ), array( 'jquery' ), WPSEO_VIDEO_VERSION, true );
	}

	/**
	 * AJAX request handler for reindex posts
	 */
	public function index_posts_callback() {
		if ( wp_verify_nonce( $_POST['nonce'], 'videoseo-ajax-nonce-for-reindex' ) ) {
			if ( isset( $_POST['type'] ) && $_POST['type'] === 'total_posts' ) {
				$options = get_option( 'wpseo_video' );
				$total   = 0;
				foreach ( $options['videositemap_posttypes'] as $post_type ) {
					$total += wp_count_posts( $post_type )->publish;
				}
				echo $total;
			}
			elseif ( isset( $_POST['type'] ) && $_POST['type'] === 'index' ) {
				$start_time = time();

				$post_defaults = array(
					'portion' => 5,
					'start'   => 0,
					'total'   => 0,
				);

				foreach ( $post_defaults as $key => $default ) {
					if ( isset( $_POST[ $key ] ) && is_numeric( $_POST[ $key ] ) ) {
						${$key} = (int) $_POST[ $key ];
					}
					else {
						${$key} = $default;
					}
				}

				$this->reindex( $portion, $start, $total );

				$end_time = time();

				// Return time in seconds that we've needed to index.
				echo (( $end_time - $start_time ) + 1);
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
	 * @since 3.8.0 The $extra parameter was added.
	 *
	 * @param string $extra Optionally suffix to add to the filename part of the sitemap url.
	 *
	 * @return string The URL to the video Sitemap.
	 */
	public function sitemap_url( $extra = '' ) {
		$sitemap = $this->video_sitemap_basename() . '-sitemap' . $extra. '.xml';

		// WPSEO 3.2+.
		if ( method_exists( 'WPSEO_Sitemaps_Router', 'get_base_url' ) ) {
			return WPSEO_Sitemaps_Router::get_base_url( $sitemap );
		}

		return wpseo_xml_sitemaps_base_url( $sitemap );
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
			// Use fields => ids to limit the overhead of fetching entire post objects, fetch only an array of ids instead to count.
			$args = array(
				'post_type'      => $options['videositemap_posttypes'],
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_key'       => '_yoast_wpseo_video_meta',
				'meta_compare'   => '!=',
				'meta_value'     => 'none',
				'fields'         => 'ids',
			);
			// Copy these args to be used and modify later.
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
					}
					else {
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
					$str .= '<loc>' . $this->sitemap_url( $text ) . '</loc>' . "\n";
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
		// @todo - check with official plugin.
		// Wistia.
		$options      = get_option( 'wpseo_video' );
		$wistia_regex = '`(?:http[s]?:)?//[^/]*(wistia\.(com|net)|wi\.st#CUSTOM_URL#)/(medias|embed)/.*`i';
		if ( $options['wistia_domain'] !== '' ) {
			$wistia_regex = str_replace( '#CUSTOM_URL#', '|' . preg_quote( $options['wistia_domain'], '`' ), $wistia_regex );
		}
		else {
			$wistia_regex = str_replace( '#CUSTOM_URL#', '', $wistia_regex );
		}
		wp_oembed_add_provider( $wistia_regex, 'http://fast.wistia.com/oembed', true );

		 // Viddler - WP native support removed in WP 4.0.
		wp_oembed_add_provider( '`http[s]?://(?:www\.)?viddler\.com/.*`i', 'http://lab.viddler.com/services/oembed/', true );

		// Screenr.
		wp_oembed_add_provider( '`http[s]?://(?:www\.)?screenr\.com/.*`i', 'http://www.screenr.com/api/oembed.{format}', true );

		// EVS.
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
	 * @param  array $providers Providers.
	 *
	 * @return array
	 */
	public function sync_oembed_providers( $providers ) {

		// Support SSL urls for flick shortdomain (natively added in WP4.0).
		if ( isset( $providers['http://flic.kr/*'] ) ) {
			unset( $providers['http://flic.kr/*'] );
			$providers['#https?://flic\.kr/.*#i'] = array( 'https://www.flickr.com/services/oembed/', true );
		}

		// Change to SSL for oembed provider domain (natively changed in WP4.0).
		if ( isset( $providers['#https?://(www\.)?flickr\.com/.*#i'] ) && strpos( $providers['#https?://(www\.)?flickr\.com/.*#i'][0], 'https' ) !== 0 ) {
			$providers['#https?://(www\.)?flickr\.com/.*#i'] = array( 'https://www.flickr.com/services/oembed/', true );
		}

		// Allow any vimeo subdomain (natively changed in WP3.9).
		if ( isset( $providers['#https?://(www\.)?vimeo\.com/.*#i'] ) ) {
			unset( $providers['#https?://(www\.)?vimeo\.com/.*#i'] );
			$providers['#https?://(.+\.)?vimeo\.com/.*#i'] = array( 'http://vimeo.com/api/oembed.{format}', true );
		}

		// Support SSL urls for wordpress.tv (natively added in WP4.0).
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
		}
		else {
			// If any galleries are processed, we need to capture the attachment IDs.
			add_filter( 'wp_get_attachment_link', array( $this, 'mrss_gallery_lookup' ), 10, 5 );
			$content = apply_filters( 'the_content', get_the_content() );
			remove_filter( 'wp_get_attachment_link', array( $this, 'mrss_gallery_lookup' ), 10, 5 );
			$lookup = $mrss_gallery_lookup;
			unset( $mrss_gallery_lookup );
		}

		$images = 0;
		if ( preg_match_all( '`<img ([^>]+)>`', $content, $matches ) ) {
			foreach ( $matches[1] as $attrs ) {
				$item = $img = array();
				// Construct $img array from <img> attributes.
				$attributes = wp_kses_hair( $attrs, array( 'http' ) );
				foreach ( $attributes as $attr ) {
					$img[ $attr['name'] ] = $attr['value'];
				}
				unset( $attributes );

				// Skip emoticons and images without source attribute.
				if ( ! isset( $img['src'] ) || ( isset( $img['class'] ) && false !== strpos( $img['class'], 'wp-smiley' ) ) ) {
					continue;
				}

				$img['src'] = $this->mrss_url( $img['src'] );

				$id = false;
				if ( isset( $lookup[ $img['src'] ] ) ) {
					$id = $lookup[ $img['src'] ];
				}
				elseif ( isset( $img['class'] ) && preg_match( '`wp-image-(\d+)`', $img['class'], $match ) ) {
					$id = $match[1];
				}
				if ( $id ) {
					// It's an attachment, so we will get the URLs, title, and description from functions.
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
				// If this is the first image in the markup, make it the post thumbnail.
				if ( ++ $images == 1 ) {
					if ( isset( $img['thumbnail'] ) ) {
						$media[]['thumbnail']['attr']['url'] = $img['thumbnail'];
					}
					else {
						$media[]['thumbnail']['attr']['url'] = $img['src'];
					}
				}

				$item['content']['attr']['url']    = $img['src'];
				$item['content']['attr']['medium'] = 'image';
				if ( ! empty( $img['title'] ) ) {
					$item['content']['children']['title']['attr']['type'] = 'html';
					$item['content']['children']['title']['children'][]   = $img['title'];
				}
				elseif ( ! empty( $img['alt'] ) ) {
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
	 * @param string $url Variable to evaluate for URL.
	 *
	 * @return string
	 */
	public function mrss_url( $url ) {
		if ( preg_match( '`^(?:http[s]?:)//`', $url ) ) {
			return $url;
		}
		else {
			return home_url( $url );
		}
	}


	/**
	 * @todo Properly document
	 *
	 * @param string $link Link tag.
	 * @param mixed  $id   ID to lookup.
	 *
	 * @return mixed
	 */
	public function mrss_gallery_lookup( $link, $id ) {
		if ( preg_match( '` src="([^"]+)"`', $link, $matches ) ) {
			$GLOBALS['mrss_gallery_lookup'][ $matches[1] ] = $id;
		}

		return $link;
	}


	/**
	 * @todo Properly document
	 *
	 * @param mixed $media Media.
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
	 * @param array $element Element.
	 * @param int   $indent  Ident.
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
					}
					else {
						$nl = true;
						$this->mrss_print_element( array( $_name => $_data ), ( $indent + 1 ) );
					}
				}
				if ( $nl ) {
					echo "\n" . str_repeat( "\t", $indent );
				}
				echo '</media:' . esc_attr( $name ) . '>';
			}
			else {
				echo ' />';
			}
		}
	}


	/**
	 * Add the video output to the MRSS feed.
	 *
	 * @since 0.1
	 *
	 * @param array $media Media.
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
	 * @param mixed  $post    The post object or the post id of the post to analyse.
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
			if ( isset( $tax_meta[ $term->taxonomy ]['_video'][ $term->term_id ] ) ) {
				$old_vid = $tax_meta[ $term->taxonomy ]['_video'][ $term->term_id ];
			}
		}

		$vid = array();

		$title = WPSEO_Taxonomy_Meta::get_term_meta( $term->term_id, $term->taxonomy, 'wpseo_title' );
		if ( empty( $title ) && isset( $options[ 'title-' . $term->taxonomy ] ) && $options[ 'title-' . $term->taxonomy ] !== '' ) {
			$title = wpseo_replace_vars( $options[ 'title-' . $term->taxonomy ], (array) $term );
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

		// Concatenate genesis intro text and term description to index the videos for both.
		$genesis_term_meta = get_option( 'genesis-term-meta' );

		$content = '';
		if ( isset( $genesis_term_meta[ $term->term_id ]['intro_text'] ) && $genesis_term_meta[ $term->term_id ]['intro_text'] ) {
			$content .= $genesis_term_meta[ $term->term_id ]['intro_text'];
		}

		$content .= "\n" . $term->description;
		$content = stripslashes( $content );

		$vid = $this->index_content( $content, $vid, $old_vid, null );

		if ( $vid != 'none' ) {
			$tax_meta[ $term->taxonomy ]['_video'][ $term->term_id ] = $vid;
			// Don't bother with the complete tax meta validation.
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
	 * @param  array $tax_meta_data Received _video tax meta data.
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
	 * @since 3.8 The $echo parameter was removed and the $post and $update parameters
	 *            added to be in line with the parameters received from the hook this
	 *            method is tied to.
	 *
	 * @param int      $post_ID The post ID to check and possibly update the video details for.
	 * @param \WP_Post $post    The post object.
	 * @param boolean  $update  Whether this is an existing post being updated or not.
	 *
	 * @return mixed $vid The video array that was just stored, string "none" if nothing was stored
	 *                    or false if not applicable.
	 */
	public function update_video_post_meta( $post_ID, $post = null, $update = null ) {
		global $wp_query;

		if ( ( ! isset( $post ) || ! ( $post instanceof WP_Post ) ) && is_numeric( $post_ID ) ) {
			$post = get_post( $post_ID );
		}

		if ( isset( $post ) && ( ! ( $post instanceof WP_Post ) || ! isset( $post->ID ) ) ) {
			return false;
		}

		$options = array_merge( WPSEO_Options::get_all(), get_option( 'wpseo_video' ) );

		if ( ! is_array( $options['videositemap_posttypes'] ) || $options['videositemap_posttypes'] === array() ) {
			return false;
		}

		if ( ! in_array( $post->post_type, $options['videositemap_posttypes'] ) ) {
			return false;
		}

		$old_vid = array();
		if ( ! isset( $_POST['force'] ) ) {
			$old_vid = WPSEO_Meta::get_value( 'video_meta', $post->ID );
		}

		$title = WPSEO_Meta::get_value( 'title', $post->ID );
		if ( ! is_string( $title ) || $title === '' ) {
			if ( isset( $options[ 'title-' . $post->post_type ] ) && $options[ 'title-' . $post->post_type ] !== '' ) {
				$title = wpseo_replace_vars( $options[ 'title-' . $post->post_type ], (array) $post );
			}
			else {
				$title = wpseo_replace_vars( '%%title%% - %%sitename%%', (array) $post );
			}
		}

		if ( ! is_string( $title ) || $title === '' ) {
			$title = $post->post_title;
		}

		$vid = array();

		// @todo [JRF->Yoast] Verify if this is really what we want. What about non-hierarchical custom post types ? and are we adjusting the main query output now ? could this cause bugs for others ?
		if ( $post->post_type == 'post' ) {
			$wp_query->is_single = true;
			$wp_query->is_page   = false;
		}
		else {
			$wp_query->is_single = false;
			$wp_query->is_page   = true;
		}

		$vid['post_id'] = $post->ID;

		$vid['title']            = htmlspecialchars( $title );
		$vid['publication_date'] = mysql2date( 'Y-m-d\TH:i:s+00:00', $post->post_date_gmt );

		$vid['description'] = WPSEO_Meta::get_value( 'metadesc', $post->ID );
		if ( ! is_string( $vid['description'] ) || $vid['description'] === '' ) {
			if ( isset( $options[ 'metadesc-' . $post->post_type ] ) && $options[ 'metadesc-' . $post->post_type ] !== '' ) {
				$vid['description'] = wpseo_replace_vars( $options[ 'metadesc-' . $post->post_type ], (array) $post );
			}
			else {
				$vid['description'] = esc_attr( preg_replace( '`\s+`', ' ', wp_html_excerpt( $this->strip_shortcodes( $post->post_content ), 300 ) ) );
			}
		}

		$vid = $this->index_content( $post->post_content, $vid, $old_vid, $post );

		if ( 'none' !== $vid ) {
			// Shouldn't be needed, but just in case.
			if ( isset( $vid['__add_to_content'] ) ) {
				unset( $vid['__add_to_content'] );
			}

			if ( ! isset( $vid['thumbnail_loc'] ) || empty( $vid['thumbnail_loc'] ) ) {
				$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
				if ( strpos( $img[0], 'http' ) !== 0 ) {
					$vid['thumbnail_loc'] = get_site_url( null, $img[0] );
				}
				else {
					$vid['thumbnail_loc'] = $img[0];
				}
			}

			// Grab the meta data from the post.
			if ( isset( $_POST['yoast_wpseo_videositemap-category'] ) && ! empty( $_POST['yoast_wpseo_videositemap-category'] ) ) {
				$vid['category'] = sanitize_text_field( $_POST['yoast_wpseo_videositemap-category'] );
			}
			else {
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
			}
			elseif ( isset( $cats[0] ) ) {
				$tag[] = $cats[0]->name;
			}

			$focuskw = WPSEO_Meta::get_value( 'focuskw', $post->ID );
			if ( ! empty( $focuskw ) ) {
				$tag[] = $focuskw;
			}
			$vid['tag'] = $tag;

			// WPSEO_Utils::is_development_mode() is WPSEO 3.0+.
			if ( method_exists( 'WPSEO_Utils', 'is_development_mode' ) && WPSEO_Utils::is_development_mode() ) {
				error_log( 'Updated [' . esc_html( $post->post_title ) . '](' . esc_url( add_query_arg( array( 'p' => $post->ID ), home_url() ) ) . ') - ' . esc_html( $vid['type'] ) );
			}
		}

		WPSEO_Meta::set_value( 'video_meta', $vid, $post->ID );

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
		// Allow for the video's thumbnail to be overridden by the meta box input.
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

		// Set the right content / mime type.
		header( 'Content-Type: text/xml' );

		// Prevent the search engines from indexing the XML Sitemap.
		header( 'X-Robots-Tag: noindex, follow', true );

		// Make the browser cache this file properly.
		header( 'Pragma: public' );
		header( 'Cache-Control: maxage=' . YEAR_IN_SECONDS );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', ( time() + YEAR_IN_SECONDS ) ) . ' GMT' );

		require plugin_dir_path( WPSEO_VIDEO_FILE ) . 'xml-video-sitemap.php';
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

		// Restrict access to the video sitemap to admins and valid bots.
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
			// Set the initial args array to get videos in chunks.
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

			// Add entries to the sitemap until the total is hit (rounded up by nearest $steps).
			while ( ( $total > $offset ) && ( $items = get_posts( $args ) ) ) {

				if ( is_array( $items ) && $items !== array() ) {
					foreach ( $items as $item ) {
						if ( ! is_object( $item ) || in_array( $item->ID, $printed_post_ids ) ) {
							continue;
						}
						else {
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
						}
						else {
							$video['family_friendly'] = 'yes';
						}

						$video['author'] = $item->post_author;

						$output .= $this->print_sitemap_line( $video );
					}
				}

				// Update these args for the next iteration.
				$offset = ( $offset + $steps );
				$args['offset'] += $steps;
			}
		}

		$tax_meta = get_option( 'wpseo_taxonomy_meta' );
		$terms    = array();
		if ( is_array( $options['videositemap_taxonomies'] ) && $options['videositemap_taxonomies'] !== array() ) {
			// Below is a fix for a nasty bug in WooCommerce: https://github.com/woothemes/woocommerce/issues/3807.
			$options['videositemap_taxonomies'][0] = '';
			$terms                                 = get_terms( $options['videositemap_taxonomies'] );
		}

		if ( is_array( $terms ) && $terms !== array() ) {
			foreach ( $terms as $term ) {
				if ( is_object( $term ) && isset( $tax_meta[ $term->taxonomy ]['_video'][ $term->term_id ] ) ) {
					$video = $tax_meta[ $term->taxonomy ]['_video'][ $term->term_id ];
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
	 * @param array $video The video object to print out.
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


		if ( empty( $video['publication_date'] ) || $this->is_valid_datetime( $video['publication_date'] ) === false ) {
			$post = get_post( $video['post_id'] );
			if ( is_object( $post ) && $post->post_date_gmt !== '0000-00-00 00:00:00' && $this->is_valid_datetime( $post->post_date_gmt ) ) {
				$video['publication_date'] = mysql2date( 'Y-m-d\TH:i:s+00:00', $post->post_date_gmt );
			}
			elseif ( is_object( $post ) && $post->post_date !== '0000-00-00 00:00:00' && $this->is_valid_datetime( $post->post_date ) ) {
				$video['publication_date'] = date( 'Y-m-d\TH:i:s+00:00', get_gmt_from_date( $post->post_date ) );
			}
			else {
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
				'file_url',
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

			if ( is_scalar( $val ) && ! empty( $val ) ) {
				$prepare_sitemap_line = $this->get_single_sitemap_line( $val, $key, $xtra );

				if ( ! is_null( $prepare_sitemap_line ) ) {
					$output .= $prepare_sitemap_line;
				}
			}
			elseif ( is_array( $val ) && $val !== array() ) {
				$i = 1;
				foreach ( $val as $v ) {
					// Only 32 tags are allowed.
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

		// Allow custom implementations with extra tags here.
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
				return _wp_specialchars( $out );
			}
			else {
				$entity_end = strpos( $out, ';', $entity_start );
				if ( $entity_end === false ) {
					return _wp_specialchars( $out );
				}
				elseif ( $entity_end > ( $entity_start + 7 ) ) {
					$out = $this->clean_string( $out, ( $entity_start + 1 ) );
				}
				else {
					$clean = substr( $out, 0, $entity_start );
					$subst = substr( $out, ( $entity_start + 1 ), 1 );
					$clean .= ( $subst != '#' ) ? $subst : '_';
					$clean .= substr( $out, ( $entity_end + 1 ) );
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
	 * @param string $file The path to the video file to calculate the length for.
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

		if ( $this->is_video_page( filter_input( INPUT_GET, 'page' ) ) ) {
			$this->register_i18n_promo_class();
		}

		WPSEO_Video_Wrappers::admin_header( true, $this->option_instance->group_name, $this->option_instance->option_name, false );

		if ( isset( $_POST['reindex'] ) ) {
			/**
			 * Load the reindex page, shows a progressbar and sents ajax calls to the server with
			 * small amounts of posts to reindex.
			 */
			require( plugin_dir_path( WPSEO_VIDEO_FILE ) . 'views/reindex-page.php' );
		}
		else {
			if ( $xmlopt['enablexmlsitemap'] !== true ) {
				/* translators: 1: link open tag, 2: link close tag. */
				echo '<p>' . sprintf( esc_html__( 'Please enable the XML sitemap under the SEO -> %1$sXML Sitemaps settings%2$s', 'yoast-video-seo' ), '<a href="' . esc_url( add_query_arg( array( 'page' => 'wpseo_xml' ), admin_url( 'admin.php' ) ) ) . '">', '</a>' ) . '</p>';
			}
			else {

				echo '<h2>' . esc_html__( 'General Settings', 'yoast-video-seo' ) . '</h2>';

				if ( is_array( $options['videositemap_posttypes'] ) && $options['videositemap_posttypes'] !== array() ) {
					// Use fields => ids to limit the overhead of fetching entire post objects, fetch only an array of ids instead to count.
					$args          = array(
						'post_type'      => $options['videositemap_posttypes'],
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'meta_key'       => '_yoast_wpseo_video_meta',
						'meta_compare'   => '!=',
						'meta_value'     => 'none',
						'fields'         => 'ids',
					);
					$video_ids     = get_posts( $args );
					$count         = count( $video_ids );
					$n             = ( $count > $this->max_entries ) ? (int) ceil( $count / $this->max_entries ) : '';
					$video_lastest = $this->sitemap_url( $n );

					echo '<p>' . esc_html__( 'Please find your video sitemap here:', 'yoast-video-seo' ) . ' <a target="_blank" href="' . esc_url( $video_lastest ) . '">' . esc_html__( 'XML Video Sitemap', 'yoast-video-seo' ) . '</a></p>';
				}
				else {
					echo '<p>' . esc_html__( 'Select at least one post type to enable the video sitemap', 'yoast-video-seo' ) . '</p>';

				}


				echo WPSEO_Video_Wrappers::checkbox( 'cloak_sitemap', esc_html__( 'Hide the sitemap from normal visitors?', 'yoast-video-seo' ) );
				echo WPSEO_Video_Wrappers::checkbox( 'disable_rss', esc_html__( 'Disable Media RSS Enhancement', 'yoast-video-seo' ) );
				echo '<br class="clear"/>';

				echo WPSEO_Video_Wrappers::textinput( 'custom_fields', esc_html__( 'Custom fields', 'yoast-video-seo' ) );
				echo '<p class="clear description desc label">' . esc_html__( 'Custom fields the plugin should check for video content (comma separated)', 'yoast-video-seo' ) . '</p>';
				echo WPSEO_Video_Wrappers::textinput( 'embedly_api_key', esc_html__( '(Optional) Embedly API Key', 'yoast-video-seo' ) );
				echo '<p class="clear description desc label">' . sprintf( esc_html__( 'The video SEO plugin provides where possible enriched information about your videos. A lot of %1$svideo services%2$s are supported by default. For those services which aren\'t supported, we can try to retrieve enriched video information using %3$sEmbedly%2$s. If you want to use this option, you\'ll need to sign up for a (free) %3$sEmbedly%2$s account and provide the API key you receive.', 'yoast-video-seo' ), '<a href="http://kb.yoast.com/article/95-supported-video-hosting-platforms-for-video-seo-plugin">', '</a>', '<a href="http://embed.ly/">' ) . '</p>';


				echo '<h2>' . esc_html__( 'Embed Settings', 'yoast-video-seo' ) . '</h2>';

				echo WPSEO_Video_Wrappers::checkbox( 'facebook_embed', esc_html__( 'Allow videos to be played directly on Facebook.', 'yoast-video-seo' ) );
				/* translators: 1: link open tag, 2: link close tag. */
				echo WPSEO_Video_Wrappers::checkbox( 'fitvids', sprintf( esc_html__( 'Try to make videos responsive using %1$sFitVids.js%2$s?', 'yoast-video-seo' ), '<a href="http://fitvidsjs.com/">', '</a>' ) );
				echo '<br class="clear"/>';

				echo WPSEO_Video_Wrappers::textinput( 'content_width', esc_html__( 'Content width', 'yoast-video-seo' ) );
				echo '<p class="clear description desc label">' . esc_html__( 'This defaults to your themes content width, but if it\'s empty, setting a value here will make sure videos are embedded in the right width.', 'yoast-video-seo' ) . '</p>';

				echo WPSEO_Video_Wrappers::textinput( 'vzaar_domain', esc_html__( 'Vzaar domain', 'yoast-video-seo' ) );
				echo '<p class="clear description desc label">' . esc_html__( 'If you use Vzaar, set this to the domain name you use for your Vzaar videos, no http: or slashes needed.', 'yoast-video-seo' ) . '</p>';
				echo WPSEO_Video_Wrappers::textinput( 'wistia_domain', esc_html__( 'Wistia domain', 'yoast-video-seo' ) );
				echo '<p class="clear description desc label">' . esc_html__( 'If you use Wistia in combination with a custom domain, set this to the domain name you use for your Wistia videos, no http: or slashes needed.', 'yoast-video-seo' ) . '</p>';


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
						     . '<label class="checkbox" for="' . esc_attr( 'include' . $posttype->name ) . '">' . esc_html( $posttype->labels->name ) . '</label><br class="clear">';
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
						     . '<label class="checkbox" for="' . esc_attr( 'include' . $tax->name ) . '">' . esc_html( $tax->labels->name ) . '</label><br class="clear">';
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

			<p style="max-width: 600px;"><?php esc_html_e( 'By default the plugin only checks content that hasn\'t been checked yet. However, if you check \'Force Re-Index\', it will re-check all content. This is particularly interesting if you want to check for a video embed code that wasn\'t supported before, or if you want to update thumbnail images en masse.', 'yoast-video-seo' ); ?></p>

			<form method="post" action="">

				<input class="checkbox double" type="checkbox" name="force" id="force">
				<label class="checkbox" for="force"><?php esc_html_e( 'Force reindex of already indexed videos.', 'yoast-video-seo' ); ?></label><br />
				<p class="submit">
				<input type="submit" class="button" name="reindex"
				       value="<?php esc_html_e( 'Re-Index Videos', 'yoast-video-seo' ); ?>" />
				</p>
			</form>
			<?php

		}
		// Add debug info.
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

		$content_width = $this->get_content_width( 400 );

		switch ( $video['type'] ) {
			case 'vimeo':
				$content = str_replace( '<iframe src="http://player.vimeo.com', '<noframes><embed src="http://vimeo.com/moogaloop.swf?clip_id=' . $video['id'] . '" type="application/x-shockwave-flash" width="400" height="300"></embed></noframes><iframe src="http://player.vimeo.com', $content );
				break;

			case 'dailymotion':
				// If dailymotion is embedded using the Viper shortcode, we have to add a noscript version too.
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
	 * @param string $string string to strip tags from.
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
	 * @param string $type The type, normally "article".
	 *
	 * @return string $type Value 'video'
	 */
	public function opengraph_type( $type ) {
		$options = get_option( 'wpseo_video' );

		if ( $options['facebook_embed'] !== true ) {
			return $type;
		}

		return $this->type_filter( $type, 'video.other' );
	}


	/**
	 * Switch the Twitter card type to player if needed.
	 *
	 * @internal [JRF] This method does not seem to be hooked in anywhere
	 *
	 * @param string $type The Twitter card type.
	 *
	 * @return string
	 */
	public function card_type( $type ) {
		return $this->type_filter( $type, 'player' );
	}


	/**
	 * Helper function for Twitter and OpenGraph card types
	 *
	 * @param  string $type The card type.
	 * @param  string $video_output Output.
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
				}
				else {
					$disable = WPSEO_Meta::get_value( 'videositemap-disable', $post->ID );
					if ( $disable === 'on' ) {
						return $type;
					}
					else {
						return $video_output;
					}
				}
			}
		}
		else {
			if ( is_tax() || is_category() || is_tag() ) {
				$options = get_option( 'wpseo_video' );
				$term    = get_queried_object();

				if ( is_array( $options['videositemap_taxonomies'] ) && in_array( $term->taxonomy, $options['videositemap_taxonomies'] ) ) {
					$tax_meta = get_option( 'wpseo_taxonomy_meta' );
					if ( isset( $tax_meta[ $term->taxonomy ]['_video'][ $term->term_id ] ) ) {
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
	 * @param  string $image URL to the image.
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
		}
		else {
			if ( is_tax() || is_category() || is_tag() ) {
				$options = get_option( 'wpseo_video' );

				$term = get_queried_object();

				if ( is_array( $options['videositemap_taxonomies'] ) && in_array( $term->taxonomy, $options['videositemap_taxonomies'] ) ) {
					$tax_meta = get_option( 'wpseo_taxonomy_meta' );
					if ( isset( $tax_meta[ $term->taxonomy ]['_video'][ $term->term_id ] ) ) {
						$video = $tax_meta[ $term->taxonomy ]['_video'][ $term->term_id ];

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
				$disable = WPSEO_Meta::get_value( 'videositemap-disable', $post->ID );
				if ( $disable === 'on' ) {
					return false;
				}

				$video = WPSEO_Meta::get_value( 'video_meta', $post->ID );

				if ( is_array( $video ) && $video !== array() ) {
					$video = $this->get_video_image( $post->ID, $video );
				}
			}
		}
		else {
			if ( is_tax() || is_category() || is_tag() ) {

				$term = get_queried_object();

				if ( is_array( $options['videositemap_taxonomies'] ) && in_array( $term->taxonomy, $options['videositemap_taxonomies'] ) ) {
					$tax_meta = get_option( 'wpseo_taxonomy_meta' );
					if ( isset( $tax_meta[ $term->taxonomy ]['_video'][ $term->term_id ] ) ) {
						$video = $tax_meta[ $term->taxonomy ]['_video'][ $term->term_id ];
					}
				}
			}
		}

		if ( ! isset( $video ) || ! is_array( $video ) || ! isset( $video['player_loc'] ) ) {
			return false;
		}

		echo '<meta property="og:video" content="' . esc_attr( $video['player_loc'] ) . '" />' . "\n";
		echo '<meta property="og:video:type" content="text/html" />' . "\n";
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
	 * Register the promotion class for our GlotPress instance
	 *
	 * @link https://github.com/Yoast/i18n-module */
	private function register_i18n_promo_class() {
		new yoast_i18n(
			array(
				'textdomain'     => 'yoast-video-seo',
				'project_slug'   => 'yoast-video-seo',
				'plugin_name'    => 'Video SEO for WordPress SEO by Yoast',
				'hook'           => 'wpseo_admin_promo_footer',
				'glotpress_url'  => 'http://translate.yoast.com/gp/',
				'glotpress_name' => 'Yoast Translate',
				'glotpress_logo' => 'http://translate.yoast.com/gp-templates/images/Yoast_Translate.svg',
				'register_url'   => 'http://translate.yoast.com/gp/projects#utm_source=plugin&utm_medium=promo-box&utm_campaign=wpseo-video-i18n-promo',
			)
		);
	}

	/**
	 * Checks if the current page is a video seo plugin page.
	 *
	 * @param string $page The page to check for.
	 *
	 * @return bool
	 */
	private function is_video_page( $page ) {
		$video_pages = array( 'wpseo_video' );

		return in_array( $page, $video_pages );
	}

	/**
	 * Wrapper function to check if we have a valid datetime (Uses a new util in WPSEO)
	 *
	 * @param string $datetime Date Time.
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
	 * @param string $val  Value.
	 * @param string $key  Key.
	 * @param string $xtra Extra.
	 *
	 * @return null|string
	 */
	private function get_single_sitemap_line( $val, $key, $xtra ) {
		$val = $this->clean_string( $val );
		if ( in_array( $key, array( 'description', 'category', 'tag', 'title' ) ) ) {
			$val = ent2ncr( esc_html( $val ) );
		}
		if ( ! empty( $val ) ) {
			return "\t\t\t<video:" . $key . $xtra . '>' . wpseo_replace_vars( $val, array() ) . '</video:' . $key . ">\n";
		}

		return null;
	}

	/**
	 * Reindex the video info from posts
	 *
	 * @since 0.1
	 * @since 3.8 $total parameter was added.
	 *
	 * @param int $portion Number of posts.
	 * @param int $start   Offset.
	 * @param int $total   Total number of posts which will be re-indexed.
	 */
	private function reindex( $portion, $start, $total ) {
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
				$args['meta_query'] = array(
					'key'     => '_yoast_wpseo_video_meta',
					'compare' => 'NOT EXISTS',
				);
			}


			$results      = get_posts( $args );
			$result_count = count( $results );

			if ( is_array( $results ) && $result_count > 0 ) {
				foreach ( $results as $post ) {
					if ( $post instanceof WP_Post ) {
						$this->update_video_post_meta( $post->ID, $post );
					}
					elseif ( is_numeric( $post ) ) {
						$this->update_video_post_meta( $post );
					}
					flush(); // Clear system output buffer if any exist.
				}
			}
		}

		if ( ( $start + $portion ) >= $total ) {
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

			// As this is used from within an AJAX call, we don't queue the cache clearing,
			// but do a hard reset.
			if ( method_exists( 'WPSEO_Sitemaps_Cache_Validator', 'invalidate_storage' ) ) {
				WPSEO_Sitemaps_Cache_Validator::invalidate_storage( $this->video_sitemap_basename() );
			}

			// Ping the search engines with our updated XML sitemap, we ping with the index sitemap because
			// we don't know which video sitemap, or sitemaps, have been updated / added.
			wpseo_ping_search_engines();

			// Remove the admin notice.
			delete_transient( 'video_seo_recommend_reindex' );
		}
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
	 * @param string $post_type The current post type.
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
	 * @param array  $vars    An array of variables for use within the snippet, containing title, description, date and slug.
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
	 * @param string $function The name of the function which was originally called.
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
	 * @param string $video_type The video service the video is hosted on.
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
	 * @param string $url The URL to parse.
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
	 *
	 * @param string $content Deprecated.
	 */
	public function grab_embeddable_urls_xpath( $content ) {
		_deprecated_function( __METHOD__, 'Video SEO 1.8.0', 'WPSEO_Video_Analyse_Post::get_vid_info()' );
	}

	/**
	 * Get the license manager
	 *
	 * @return Yoast_Plugin_License_Manager|null
	 */
	private function get_license_manager() {
		// We only need this on admin pages.
		// We don't need this in AJAX requests.
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return null;
		}

		if ( ! class_exists( 'Yoast_Plugin_License_Manager' ) ) {
			return null;
		}

		$license_manager = new Yoast_Plugin_License_Manager( new Yoast_Product_WPSEO_Video() );

		// Setup constant name.
		$license_manager->set_license_constant_name( 'WPSEO_VIDEO_LICENSE' );

		// Setup hooks.
		$license_manager->setup_hooks();

		return $license_manager;
	}
} /* End of class WPSEO_Video_Sitemap */

<?php
/**
 * Main SuperSide Me class.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2016 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me {

	/**
	 * Class for building the SuperSide Me[nu] panel.
	 * @var SuperSide_Me_Builder $builder
	 */
	protected $builder;

	/**
	 * Class for inline CSS stylesheets.
	 * @var SuperSide_Me_CSS $css
	 */
	protected $css;

	/**
	 * Class for implementing the WP Customizer.
	 * @var SuperSide_Me_Customizer $customizer
	 */
	protected $customizer;

	/**
	 * Class to handle EDD Software Licensing updates/checks
	 * @var SuperSide_Me_Licensing $licensing
	 */
	protected $licensing;

	/**
	 * Class to register all plugin settings
	 * @var SuperSide_Me_Settings $settings
	 */
	protected $settings;

	/**
	 * SuperSide_Me constructor.
	 *
	 * @param $builder
	 * @param $css
	 * @param $customizer
	 * @param $licensing
	 * @param $settings
	 */
	public function __construct( $builder, $css, $customizer, $licensing, $settings ) {
		$this->builder   = $builder;
		$this->css       = $css;
		$this->customizer = $customizer;
		$this->licensing = $licensing;
		$this->settings  = $settings;
	}

	/**
	 * Fires up the plugin.
	 */
	public function run() {

		// EDD
		define( 'EDD_SUPERSIDEME_URL', 'http://robincornett.com/' );
		define( 'EDD_SUPERSIDEME_NAME', 'SuperSide Me' );
		add_action( 'admin_init', array( $this->licensing, 'updater' ) );

		add_action( 'admin_init', array( $this, 'check_settings' ) );
		add_action( 'admin_notices', array( $this, 'do_error_message' ) );
		add_action( 'widgets_init', array( $this->builder, 'register_widget_area' ) );
		add_action( 'after_setup_theme', array( $this->builder, 'register_superside_nav' ), 5 );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		/**
		 * Add a filter to optionally disable the settings page
		 * @uses $this->disable()
		 *            example: add_filter( 'supersideme_disable_settings_page', '__return_true' );
		 *
		 * @since 1.8.0
		 */
		$disable_settings_page = $this->disable( 'settings_page' );
		if ( ! $disable_settings_page ) {
			add_action( 'admin_menu', array ( $this->settings, 'do_submenu_page' ) );
			add_filter( 'plugin_action_links_' . SUPERSIDEME_BASENAME, array ( $this, 'add_settings_link' ) );
		}

		add_filter( 'language_attributes', array( $this->css, 'no_js_class' ) );
		add_action( 'wp_head', array( $this->css, 'add_js_class' ) );

		add_filter( 'supersideme_panel_has_content', array( $this, 'panel_has_content' ) );
		add_filter( 'supersideme_get_plugin_setting', array( $this->settings, 'get_setting' ) );
		add_filter( 'supersideme_get_navigation_options', array( $this->css, 'options' ) );

		/**
		 * Add a filter to optionally disable the customizer panel
		 * @uses $this->disable()
		 *            example: add_filter( 'supersideme_disable_customizer_panel', '__return_true' );
		 *
		 * @since 1.8.0
		 */
		$disable_customizer = $this->disable( 'customizer_panel' );
		if ( ! $disable_customizer ) {
			add_action( 'customize_register', array ( $this->customizer, 'customizer' ) );
		}

		if ( is_admin() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ) );

		if ( class_exists( 'Genesis_Simple_Menus' ) ) {
			add_filter( 'supersideme_modify_menu', array( $this->builder, 'modify_genesis_simple_menus' ), 10, 2 );
		}
		$side_tweaks = supersideme_get_navigation_options();
		if ( is_active_sidebar( 'superside' ) && null === $side_tweaks['source'] ) {
			add_action( 'wp_footer', array( $this->builder, 'do_sidebar' ) );
		}
	}

	/**
	 * decide whether to load front end scripts based on whether panel can be built
	 *
	 * @since 1.4.0
	 */
	public function maybe_enqueue() {
		if ( ! $this->panel_has_content() ) {
			return;
		}
		$this->load_styles();
		$this->load_scripts();
		add_action( 'wp_print_scripts', array( $this->builder, 'localize_scripts' ) );
		add_action( 'wp_print_scripts', array( $this->builder, 'skip_links' ) );
	}

	/**
	 * check existing settings array to see if a setting is in the array
	 * @return updated setting updates to default
	 * @since  1.3.3
	 */
	public function check_settings() {

		// return early if the option doesn't exist yet
		$supersideme = get_option( 'supersideme' );
		if ( ! $supersideme ) {
			return;
		}

		if ( empty( $supersideme['search'] ) ) {
			$this->update_settings( array(
				'search' => 0,
			) );
		}

		if ( empty( $supersideme['swipe'] ) ) {
			$this->update_settings( array(
				'swipe' => 0,
			) );
		}

	}

	/**
	 * Takes an array of new settings, merges them with the old settings, and pushes them into the database.
	 *
	 * @since 1.5.0
	 *
	 * @param string|array $new     New settings. Can be a string, or an array.
	 * @param string       $setting Optional. Settings field name. Default is displayfeaturedimagegenesis.
	 */
	protected function update_settings( $new = '', $setting = 'supersideme' ) {
		return update_option( $setting, wp_parse_args( $new, get_option( $setting ) ) );
	}

	/**
	 * determine if the superside panel has any content
	 * @return true/false return false if empty; true if populated. Can be overridden via filter.
	 *
	 * @since 1.4.0
	 */
	public function panel_has_content( $has_content = false ) {
		$setting     = $this->settings->get_setting();
		$search      = $setting['search'] ? true : false;
		$sidebar     = is_active_sidebar( 'superside' ) ? true : false;
		$menus       = $this->builder->get_menus();
		$has_content = ( $search || $sidebar || $menus ) ? true : false;
		return (bool) apply_filters( 'supersideme_override_output', $has_content );
	}

	/**
	 * admin message if panel can't be generated
	 * @return admin notice
	 *
	 * @since 1.4.0
	 */
	public function do_error_message() {
		if ( $this->panel_has_content() ) {
			return;
		}
		$message = sprintf( __( 'SuperSide Me works like magic, but you\'ve got to give it something with which to work. Please add a menu to a <a href="%s">registered menu location</a> or add a widget to the new <a href="%s">SuperSide Me widget area</a>. If those both look all right, double check your <a href="%s">Automagic Menu settings</a>.', 'superside-me' ),
			esc_url( admin_url( 'nav-menus.php?action=locations' ) ),
			esc_url( admin_url( 'widgets.php' ) ),
			esc_url( admin_url( 'themes.php?page=supersideme' ) )
		);
		printf( '<div class="error notice"><p>%s</p></div>', wp_kses_post( $message ) );
	}

	/**
	 * Set up text domain for translations
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'superside-me', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );
	}

	/**
	 * Load CSS styles
	 * @return styles plugin CSS (main and inline, Font Awesome)
	 *
	 * @since 1.6.0
	 */
	public function load_styles() {

		// Register SuperSide Me CSS
		$css_file = apply_filters( 'supersideme_default_css', plugins_url( '/includes/css/supersideme-style.css', dirname( __FILE__ ) ) );
		$main_css = 'supersideme-style';
		wp_register_style( $main_css, esc_url( $css_file ), array(), $this->licensing->version, 'screen' );

		// Register FontAwesome
		$fontawesome_file = apply_filters( 'supersideme_fontawesome_css', plugins_url( 'includes/css/supersideme-fontawesome.css', dirname( __FILE__ ) ) );
		$fontawesome_css  = 'supersideme-fontawesome';
		wp_register_style( $fontawesome_css, esc_url( $fontawesome_file ), array(), $this->licensing->version, 'screen' );

		if ( ! $this->panel_has_content() ) {
			return;
		}

		// Enqueue main CSS and append inline
		wp_enqueue_style( $main_css );
		$css = $this->css->css();
		wp_add_inline_style( $main_css, sanitize_text_field( $css ) );

		// Maybe enqueue Font Awesome
		if ( ! class_exists( 'Better_Font_Awesome_Library' ) ) {
			wp_enqueue_style( $fontawesome_css );
		}

	}

	/**
	 * Enqueue scripts for plugin
	 * @return scripts Loads plugin scripts/styles, and also FontAwesome
	 *
	 * @since 1.0.0
	 */
	public function load_scripts() {

		$minify = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register SuperSide Me JS
		wp_register_script( 'supersideme-sidr', plugins_url( "/includes/js/sidr.me{$minify}.js", dirname( __FILE__ ) ), array( 'jquery' ), '2.2.1', true );
		$dependent_scripts = array( 'supersideme-sidr' );
		$setting           = $this->settings->get_setting();
		if ( $setting['swipe'] ) {
			wp_register_script( 'supersideme-touchswipe', plugins_url( "/includes/js/touchswipe{$minify}.js", dirname( __FILE__ ) ), $dependent_scripts, '1.6.15', true );
			$dependent_scripts[] = 'supersideme-touchswipe';
		}
		wp_register_script( 'superside-init', plugins_url( "/includes/js/supersideme{$minify}.js", dirname( __FILE__ ) ), $dependent_scripts, $this->licensing->version, true );

		// Enqueue Scripts
		if ( ! $this->panel_has_content() ) {
			return;
		}

		wp_enqueue_script( 'superside-init' );

	}

	/**
	 * Add link to plugin settings page in plugin table
	 * @param $links link to settings page
	 *
	 * @since 1.5.0
	 */
	public function add_settings_link( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'themes.php?page=supersideme' ) ), esc_attr__( 'Settings', 'superside-me' ) );
		return $links;
	}

	/**
	 * Helper function to disable a setting/function
	 * @param $setting
	 *
	 * @return bool true if filter returns true, otherwise false
	 *
	 * @since 1.8.0
	 */
	protected function disable( $setting ) {
		$disable = apply_filters( "supersideme_disable_{$setting}", false );
		return true === $disable ? true : false;
	}
}

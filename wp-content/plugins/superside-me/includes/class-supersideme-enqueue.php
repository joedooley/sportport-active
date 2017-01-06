<?php
/**
 * Class to output scripts/styles.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2017 Robin Cornett
 * @license   GPL-2.0+
 */
class SuperSideMeEnqueue extends SuperSide_Me_CSS {

	/**
	 * @var $builder SuperSide_Me_Builder
	 */
	protected $builder;

	/**
	 * The plugin setting.
	 * @var $setting array
	 */
	protected $setting;

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
		add_action( 'wp_footer', array( $this->builder, 'localize_scripts' ) );
		add_action( 'wp_footer', array( $this->builder, 'skip_links' ) );
	}

	/**
	 * determine if the superside panel has any content
	 * @return true/false return false if empty; true if populated. Can be overridden via filter.
	 *
	 * @since 1.4.0
	 */
	public function panel_has_content( $has_content = false ) {
		$this->builder = new SuperSide_Me_Builder;
		$this->setting = supersideme_get_settings();
		$search        = (bool) $this->setting['search'];
		$sidebar       = (bool) is_active_sidebar( 'superside' );
		$menus         = $this->builder->get_menus();
		$has_content   = (bool) ( $search || $sidebar || $menus );
		return (bool) apply_filters( 'supersideme_override_output', $has_content );
	}

	/**
	 * Load CSS styles
	 *
	 * @since 1.6.0
	 */
	public function load_styles() {

		// Register SuperSide Me CSS
		$css_file = apply_filters( 'supersideme_default_css', plugins_url( '/includes/css/supersideme-style.css', dirname( __FILE__ ) ) );
		$main_css = 'supersideme-style';
		wp_register_style( $main_css, esc_url( $css_file ), array(), SUPERSIDEME_VERSION, 'screen' );

		// Register FontAwesome
		$plugin_file      = $this->setting['fontawesome']['css'] ? plugins_url( 'includes/css/supersideme-fontawesome.css', dirname( __FILE__ ) ) : '';
		$fontawesome_file = apply_filters( 'supersideme_fontawesome_css', $plugin_file );
		$fontawesome_css  = 'supersideme-fontawesome';
		wp_register_style( $fontawesome_css, esc_url( $fontawesome_file ), array(), '4.7', 'screen' );

		if ( ! $this->panel_has_content() ) {
			return;
		}

		// Enqueue main CSS and append inline
		wp_enqueue_style( $main_css );
		$inline_css = $this->css();
		wp_add_inline_style( $main_css, sanitize_text_field( $inline_css ) );

		// Maybe enqueue Font Awesome
		if ( ! class_exists( 'Better_Font_Awesome_Library' ) ) {
			wp_enqueue_style( $fontawesome_css );
		}

	}

	/**
	 * Enqueue scripts for plugin
	 *
	 * @since 1.0.0
	 */
	public function load_scripts() {

		$minify = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register SuperSide Me JS
		wp_register_script( 'supersideme-sidr', plugins_url( "/includes/js/sidr.me{$minify}.js", dirname( __FILE__ ) ), array( 'jquery' ), '2.2.1', true );
		$dependent_scripts = array( 'supersideme-sidr' );
		if ( $this->setting['swipe'] ) {
			wp_register_script( 'supersideme-touchswipe', plugins_url( "/includes/js/touchswipe{$minify}.js", dirname( __FILE__ ) ), $dependent_scripts, '1.6.12', true );
			$dependent_scripts[] = 'supersideme-touchswipe';
		}
		wp_register_script( 'superside-init', plugins_url( "/includes/js/supersideme{$minify}.js", dirname( __FILE__ ) ), $dependent_scripts, SUPERSIDEME_VERSION, true );

		// Enqueue Scripts
		if ( ! $this->panel_has_content() ) {
			return;
		}

		wp_enqueue_script( 'superside-init' );
	}
}

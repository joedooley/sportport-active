<?php
/**
 * SuperSide Me Customizer class.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2017 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me_Customizer extends SuperSideMeGetSetting {

	/**
	 * Panel for the Customizer.
	 * @var string $panel
	 */
	protected $panel = 'supersideme';

	/**
	 * SuperSide Me Settings class.
	 * @var $settings SuperSide_Me_Settings
	 */
	protected $settings;

	/**
	 * Default plugin settings.
	 * @var array $defaults
	 */
	protected $defaults;

	/**
	 * Class for settings definitions.
	 * @var $definitions SuperSideMeDefineSettings
	 */
	protected $definitions;

	/**
	 * Adds the individual sections, settings, and controls to the theme customizer
	 * @param $wp_customize WP_Customize_Manager
	 * @uses add_section() adds a section to the customizer
	 */
	public function customizer( $wp_customize ) {

		$this->defaults = $this->defaults();
		$setting = get_option( 'supersideme', false );
		if ( ! $setting ) {
			add_option( 'supersideme', $this->defaults );
		}

		$wp_customize->add_section(
			$this->panel,
			array(
				'title'       => __( 'SuperSide Me', 'superside-me' ),
				'description' => __( 'Only certain styling settings are available in the Customizer; more can be found on the SuperSide Me settings page.', 'superside-me' ),
				'priority'    => 105,
				'capability'  => 'manage_options',
			)
		);

		$this->build_fields( $wp_customize );

		add_action( 'customize_preview_init', array( $this, 'preview' ) );
	}

	/**
	 * Build the SuperSide Me Customizer settings panel.
	 * @param $wp_customize
	 */
	protected function build_fields( $wp_customize ) {
		$this->definitions = new SuperSideMeDefineSettings();
		$fields = $this->main_section_fields();
		foreach ( $fields as $field ) {
			$this->add_control( $wp_customize, $field );
		}

		$fields = $this->set_up_colors();
		foreach ( $fields as $field ) {
			$field['transport'] = 'postMessage';
			$field['type']      = 'color';
			$this->do_color_setting( $wp_customize, $field );
		}

		$fields = $this->optional_section_fields();
		foreach ( $fields as $field ) {
			$this->add_control( $wp_customize, $field );
		}
	}

	/**
	 * Set up the main section fields.
	 * @return array
	 */
	function main_section_fields() {
		return array(
			$this->definitions->side(),
			$this->definitions->shrink(),
			$this->definitions->position(),
			$this->definitions->navigation(),
			$this->definitions->close(),
			$this->definitions->search_button(),
			$this->definitions->search_button_text(),
			$this->definitions->maxwidth(),
			$this->definitions->panel_width(),
		);
	}

	/**
	 * Set up the optional section fields.
	 * @return array
	 */
	function optional_section_fields() {
		return array(
			$this->definitions->search(),
			$this->definitions->location(),
			$this->definitions->speed(),
			$this->definitions->outline(),
		);
	}

	/**
	 * Set up the color fields.
	 * @return array
	 */
	protected function set_up_colors() {
		return array(
			$this->definitions->background(),
			$this->definitions->link_color(),
		);
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @param $setting
	 */
	protected function do_color_setting( $wp_customize, $setting ) {

		$this->add_setting( $wp_customize, $setting );
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$this->panel . '[' . $setting['setting'] . ']',
				array(
					'description' => $setting['description'],
					'label'       => $setting['label'],
					'section'     => $this->panel,
					'settings'    => $this->panel . '[' . $setting['setting'] . ']',
				)
			)
		);
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @param $setting
	 */
	protected function add_control( $wp_customize, $setting ) {
		$this->add_setting( $wp_customize, $setting );
		$wp_customize->add_control(
			$this->panel . '[' . $setting['setting'] . ']',
			array(
				'label'       => $setting['label'],
				'section'     => $this->panel,
				'type'        => isset( $setting['type'] ) ? $setting['type'] : '',
				'description' => isset( $setting['description'] ) ? $setting['description'] : '',
				'choices'     => isset( $setting['choices'] ) ? $setting['choices'] : array(),
			)
		);
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @param $setting
	 */
	protected function add_setting( $wp_customize, $setting ) {
		$wp_customize->add_setting(
			$this->panel . '[' . $setting['setting'] . ']',
			array(
				'capability'        => 'manage_options',
				'default'           => $this->defaults[ $setting['setting'] ],
			    'sanitize_callback' => $this->sanitize_callback( $setting['type'], $setting['setting'] ),
				'type'              => 'option',
				'transport'         => isset( $setting['transport'] ) ? $setting['transport'] : 'refresh',
			)
		);
	}

	/**
	 * Define which callback to use to sanitize the customizer input
	 * @param $type string field type
	 * @param $setting string
	 *
	 * @return array|string
	 */
	protected function sanitize_callback( $type, $setting = '' ) {
		switch ( $type ) {
			case 'checkbox':
				$function = array( $this, 'one_zero' );
				break;

			case 'number':
				$function = 'absint';
				break;

			case 'text':
				$function = 'sanitize_text_field';
				break;

			case 'color':
				$function = 'sanitize_hex_color';
				break;

			case 'radio':
				$function = 'esc_attr';
				if ( 'shrink' === $setting ) {
					$function = array( $this, 'one_zero' );
				}
				break;

			default:
				$function = 'esc_attr';
				break;
		}
		return $function;
	}

	/**
	 * @param $input
	 * @return int
	 */
	public function one_zero( $input ) {
		return (int) (bool) $input;
	}

	/**
	 * Enqueue javascript for customizer preview.
	 *
	 * @since 1.8.0
	 */
	public function preview() {
		$minify = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'supersideme_customizer', plugins_url( "/includes/js/customizer.me{$minify}.js", dirname( __FILE__ ) ), array( 'jquery' ), SUPERSIDEME_VERSION, true );
	}
}

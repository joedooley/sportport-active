<?php
/**
 * SuperSide Me Customizer class.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2016 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me_Customizer {

	/**
	 * @var string
	 */
	protected $section = 'supersideme';
	/**
	 * @var
	 */
	protected $settings;
	/**
	 * @var
	 */
	protected $defaults;

	/**
	 * Adds the individual sections, settings, and controls to the theme customizer
	 * @param $wp_customize WP_Customize_Manager
	 * @uses add_section() adds a section to the customizer
	 */
	public function customizer( $wp_customize ) {

		$this->settings = new SuperSide_Me_Settings();
		$this->defaults = $this->settings->defaults();
		$setting = get_option( 'supersideme', false );
		if ( ! $setting ) {
			add_option( 'supersideme', $this->defaults );
		}
		$wp_customize->add_section(
			$this->section,
			array(
				'title'       => __( 'SuperSide Me', 'superside-me' ),
				'description' => __( 'Only certain styling settings are available in the Customizer; more can be found on the SuperSide Me settings page.', 'superside-me' ),
				'priority'    => 105,
			)
		);

		$this->set_up_side( $wp_customize );
		$this->set_up_button_width( $wp_customize );
		$this->set_up_text( $wp_customize );
		$this->set_up_numbers( $wp_customize );
		$this->set_up_colors( $wp_customize );
		$this->set_up_search( $wp_customize );

		add_action( 'customize_preview_init', array ( $this, 'preview' ) );
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @uses add_control() Adds a radio control to the customizer.
	 */
	protected function set_up_side( $wp_customize ) {
		$side = array(
			'setting'           => 'side',
			'sanitize_callback' => 'esc_attr',
		);
		$this->add_setting( $wp_customize, $side );
		$wp_customize->add_control( $this->section . '[side]', array(
			'type'        => 'radio',
			'section'     => $this->section,
			'label'       => __( 'Side', 'superside-me' ),
			'description' => __( 'Set the side for the menu panel.', 'superside-me' ),
			'choices'     => array(
				'left'  => __( 'Left', 'superside-me' ),
				'right' => __( 'Right', 'superside-me' ),
			),
		) );
	}

	/**
	 * Build text fields for the customizer.
	 * @param $wp_customize
	 */
	protected function set_up_text( $wp_customize ) {
		$text_fields = array(
			'menu_button' => array(
				'setting' => 'navigation',
				'label'   => __( 'Menu Button', 'superside-me' ),
			),
			'close_button' => array(
				'setting' => 'close',
				'label'   => __( 'Close Button', 'superside-me' ),
			),
		);
		foreach ( $text_fields as $field ) {
			$field['transport']         = 'postMessage';
			$field['sanitize_callback'] = 'sanitize_text_field';
			$field['type']              = 'text';
			$this->add_control( $wp_customize, $field );
		}
	}

	/**
	 * Build number fields for the customizer.
	 * @param $wp_customize
	 */
	protected function set_up_numbers( $wp_customize ) {
		$number_fields = array(
			'maxwidth' => array(
				'setting' => 'maxwidth',
				'label'   => __( 'SuperSide Me[nu] Appears At', 'superside-me' ),
			),
			'panel_width' => array(
				'setting' => 'panel_width',
				'label'   => __( 'Side Panel Width', 'superside-me' ),
			),
		);
		foreach ( $number_fields as $field ) {
			$field['sanitize_callback'] = 'absint';
			$field['type']              = 'number';
			$this->add_control( $wp_customize, $field );
		}
	}

	/**
	 * @param $wp_customize
	 */
	protected function set_up_colors( $wp_customize ) {
		$color_fields = array(
			'background' => array(
				'setting'     => 'background',
				'description' => __( 'Change the background color for the menu panel.', 'superside-me' ),
				'label'       => __( 'Background Color', 'superside-me' ),
			),
			'link_color' => array(
				'setting'     => 'link_color',
				'description' => __( 'Change the link color for the menu panel.', 'superside-me' ),
				'label'       => __( 'Link Color', 'superside-me' ),
			),
		);
		foreach ( $color_fields as $field ) {
			$field['transport']         = 'postMessage';
			$field['sanitize_callback'] = 'sanitize_hex_color';
			$this->do_color_setting( $wp_customize, $field );
		}
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
				$this->section . '[' . $setting['setting'] . ']',
				array(
					'description' => $setting['description'],
					'label'       => $setting['label'],
					'section'     => $this->section,
					'settings'    => $this->section . '[' . $setting['setting'] . ']',
				)
			)
		);
	}

	/**
	 * Add a search input checkbox to the customizer.
	 * @param $wp_customize
	 */
	protected function set_up_search( $wp_customize ) {
		$search = array(
			'setting'           => 'search',
			'transport'         => 'postMessage',
			'label'             => __( 'Add a search input to the beginning of the side panel.', 'superside-me' ),
			'sanitize_callback' => array( $this, 'one_zero' ),
			'type'              => 'checkbox',
		);
		$this->add_control( $wp_customize, $search );
	}

	/**
	 * Add a search input checkbox to the customizer.
	 * @param $wp_customize
	 */
	protected function set_up_button_width( $wp_customize ) {
		$search = array(
			'setting'           => 'shrink',
			'label'             => __( 'Set the button to only be as wide as its contents/text.', 'superside-me' ),
			'sanitize_callback' => array( $this, 'one_zero' ),
			'type'              => 'checkbox',
		);
		$this->add_control( $wp_customize, $search );
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @param $setting
	 */
	protected function add_control( $wp_customize, $setting ) {
		$this->add_setting( $wp_customize, $setting );
		$wp_customize->add_control(
			$this->section . '[' . $setting['setting'] . ']',
			array(
				'label'   => $setting['label'],
				'section' => $this->section,
				'type'    => isset( $setting['type'] ) ? $setting['type'] : '',
			)
		);
	}

	/**
	 * @param $wp_customize WP_Customize_Manager
	 * @param $setting
	 */
	protected function add_setting( $wp_customize, $setting ) {
		$wp_customize->add_setting(
			$this->section . '[' . $setting['setting'] . ']',
			array(
				'capability'        => 'manage_options',
				'default'           => $this->defaults[ $setting['setting'] ],
				'sanitize_callback' => $setting['sanitize_callback'],
				'type'              => 'option',
				'transport'         => isset( $setting['transport'] ) ? $setting['transport'] : 'refresh',
			)
		);
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
		wp_enqueue_script( 'supersideme_customizer', plugins_url( "/includes/js/customizer.me{$minify}.js", dirname( __FILE__ ) ), array( 'jquery' ), '1.8.2', true );
	}
}

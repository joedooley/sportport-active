<?php

/**
 * Helper class for SuperSide Me.
 *
 * @package   SuperSideMe_Helper
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2016 Robin Cornett
 * @license   GPL-2.0+
 */
class SuperSide_Me_Helper {

	/**
	 * Page/setting string
	 * @var string
	 */
	protected $page = 'supersideme';

	/**
	 * Generic function to add settings sections
	 *
	 * @since 1.8.0
	 */
	protected function add_sections( $sections ) {

		foreach ( $sections as $section ) {
			add_settings_section(
				$section['id'],
				$section['title'],
				array( $this, 'do_' . $section['id'] . '_section_description' ),
				$this->page
			);
		}
	}

	/**
	 * Generic function to add settings fields
	 * @param  array $sections registered sections
	 * @return array           all settings fields
	 *
	 * @since 1.8.0
	 */
	protected function add_fields( $fields, $sections ) {

		foreach ( $fields as $field ) {
			add_settings_field(
				'[' . $field['id'] . ']',
				sprintf( '<label for="%s">%s</label>', $field['id'], $field['title'] ),
				array( $this, $field['callback'] ),
				$this->page,
				$sections[ $field['section'] ]['id'],
				empty( $field['args'] ) ? array() : $field['args']
			);
		}

	}

	/**
	 * Echoes out the section description.
	 * @param  string $description text string for description
	 * @return string              as paragraph and escaped
	 *
	 * @since 1.8.0
	 */
	protected function print_section_description( $description ) {
		echo wp_kses_post( wpautop( $description ) );
	}

	/**
	 * generic checkbox function (for all checkbox settings)
	 * @return 0 1 checkbox
	 */
	public function do_checkbox( $args ) {
		printf( '<input type="hidden" name="%s[%s]" value="0" />', esc_attr( $this->page ), esc_attr( $args['setting'] ) );
		printf( '<label for="%4$s[%1$s]"><input type="checkbox" name="%4$s[%1$s]" id="%4$s[%1$s]" value="1" %2$s class="code" />%3$s</label>',
			esc_attr( $args['setting'] ),
			checked( 1, esc_attr( $this->supersideme_setting[ $args['setting'] ] ), false ),
			esc_attr( $args['label'] ),
			esc_attr( $this->page )
		);
		$this->do_description( $args['setting'] );
	}

	/**
	 * radio buttons
	 * @return radio Side panel can be on left or right side.
	 */
	public function do_radio_buttons( $args ) {
		foreach ( $args['buttons'] as $button ) {
			printf( '<input type="radio" id="%5$s[%1$s][%2$s]" name="%5$s[%1$s]" value="%2$s"%3$s /><label for="%5$s[%1$s][%2$s]">%4$s</label>  ',
				esc_attr( $args['id'] ),
				esc_attr( $button['option'] ),
				checked( $button['option'], $this->supersideme_setting[ $args['id'] ], false ),
				esc_attr( $button['name'] ),
				esc_attr( $this->page )
			);
		}
	}

	/**
	 * text field input
	 * @param  $args array of data passed from field setting
	 * @since 1.3.3
	 */
	public function do_text_field( $args ) {
		printf( '<input type="text" class="text" id="%3$s[%1$s]" name="%3$s[%1$s]" value="%2$s" />', esc_attr( $args['setting'] ), esc_attr( $this->supersideme_setting[ $args['setting'] ] ), esc_attr( $this->page ) );
		printf( '<p class="description"><label for="%3$s[%1$s]">%2$s</label></p>', esc_attr( $args['setting'] ), esc_html( $args['label'] ), esc_attr( $this->page ) );
	}

	/**
	 * Generic callback to create a number field setting.
	 *
	 * @since 1.8.0
	 */
	public function do_number( $args ) {
		printf( '<input type="number" step="1" min="%1$s" max="%2$s" id="%5$s[%3$s]" name="%5$s[%3$s]" value="%4$s" class="small-text" />',
			(int) $args['min'],
			(int) $args['max'],
			esc_attr( $args['setting'] ),
			esc_attr( $this->supersideme_setting[ $args['setting'] ] ),
			esc_attr( $this->page )
		);
		$this->do_description( $args['setting'] );
	}

	/**
	 * Generic callback to display a field description.
	 * @param  string $args setting name used to identify description callback
	 * @return string       Description to explain a field.
	 *
	 * @since 1.6.0
	 */
	protected function do_description( $args ) {
		$function = $args . '_description';
		if ( ! method_exists( $this, $function ) ) {
			return;
		}
		$description = $this->$function();
		printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
	}

	/**
	 * Determines if the user has permission to save the information from the submenu
	 * page.
	 *
	 * @since    2.3.0
	 * @access   protected
	 *
	 * @param    string    $action   The name of the action specified on the submenu page
	 * @param    string    $nonce    The nonce specified on the submenu page
	 *
	 * @return   bool                True if the user has permission to save; false, otherwise.
	 * @author   Tom McFarlin (https://tommcfarlin.com/save-wordpress-submenu-page-options/)
	 */
	protected function user_can_save( $action, $nonce ) {
		$is_nonce_set   = isset( $_POST[ $nonce ] );
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST[ $nonce ], $action );
		}
		return ( $is_nonce_set && $is_valid_nonce );
	}

	/**
	 * Returns a 1 or 0, for all truthy / falsy values.
	 *
	 * Uses double casting. First, we cast to bool, then to integer.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in
	 * @return integer 1 or 0.
	 */
	protected function one_zero( $new_value ) {
		return (int) (bool) $new_value;
	}
}

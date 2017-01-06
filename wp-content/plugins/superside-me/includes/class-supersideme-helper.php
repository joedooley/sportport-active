<?php

/**
 * Helper class for SuperSide Me.
 *
 * @package   SuperSideMe_Helper
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2017 Robin Cornett
 * @license   GPL-2.0+
 */
class SuperSide_Me_Helper extends SuperSideMeGetSetting {

	/**
	 * Page/setting string
	 * @var string
	 */
	protected $page = 'supersideme';

	/**
	 * The setting for the plugin.
	 * @var
	 */
	protected $setting;

	/**
	 * The class which defines the plugin settings.
	 * @var $definitions SuperSideMeDefineSettings
	 */
	protected $definitions;

	/**
	 * Generic function to add settings sections
	 * @param $sections array
	 *
	 * @since 1.8.0
	 */
	protected function add_sections( $sections ) {

		$this->definitions = new SuperSideMeDefineSettings();

		foreach ( $sections as $section ) {
			add_settings_section(
				$this->page . '_' . $section['id'],
				$section['label'],
				array( $this->definitions, 'do_' . $section['id'] . '_section_description' ),
				$this->page . '_' . $section['id']
			);
		}
	}

	/**
	 * Generic function to add settings fields
	 * @param $fields array
	 * @param  array $sections registered sections
	 *
	 * @since 1.8.0
	 */
	protected function add_fields( $fields, $sections ) {

		foreach ( $fields as $field ) {
			add_settings_field(
				$this->page . '[' . $field['setting'] . ']',
				sprintf( '<label for="%s">%s</label>', $field['setting'], $field['label'] ),
				array( $this, $field['callback'] ),
				$this->page . '_' . $sections[ $field['tab'] ]['id'],
				$this->page . '_' . $sections[ $field['tab'] ]['id'],
				empty( $field['args'] ) ? array() : $field['args']
			);
		}
	}

	/**
	 * Set which tab is considered active.
	 * @return string
	 * @since 2.0.0
	 */
	protected function get_active_tab() {
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'main';
		if ( supersideme_disable_settings_page() ) {
			$tab = 'licensing';
		}

		return $tab;
	}

	/**
	 * Output a button to the settings page.
	 * @param $class string the button class
	 * @param $name string the name of the button
	 * @param $value string the button label
	 */
	protected function print_button( $class, $name, $value ) {
		printf( '<input type="submit" class="%s" name="%s" value="%s"/>',
			esc_attr( $class ),
			esc_attr( $name ),
			esc_attr( $value )
		);
	}

	/**
	 * generic checkbox function (for all checkbox settings)
	 * @param $args array
	 */
	public function do_checkbox( $args ) {
		printf( '<input type="hidden" name="%s[%s]" value="0" />', esc_attr( $this->page ), esc_attr( $args['setting'] ) );
		printf( '<label for="%4$s[%1$s]"><input type="checkbox" name="%4$s[%1$s]" id="%4$s[%1$s]" value="1" %2$s class="code" />%3$s</label>',
			esc_attr( $args['setting'] ),
			checked( 1, esc_attr( $this->setting[ $args['setting'] ] ), false ),
			esc_attr( $args['label'] ),
			esc_attr( $this->page )
		);
		$this->do_description( $args['setting'] );
	}

	/**
	 * Generic function for radio buttons
	 * @param $args array
	 */
	public function do_radio_buttons( $args ) {
		echo '<fieldset>';
		printf( '<legend class="screen-reader-text">%s</legend>', esc_attr( $args['legend'] ) );
		foreach ( $args['buttons'] as $key => $button ) {
			printf( '<label for="%5$s[%1$s][%2$s]" style="margin-right:12px !important;"><input type="radio" id="%5$s[%1$s][%2$s]" name="%5$s[%1$s]" value="%2$s"%3$s />%4$s</label>  ',
				esc_attr( $args['id'] ),
				esc_attr( $key ),
				checked( $key, $this->setting[ $args['id'] ], false ),
				esc_attr( $button ),
				esc_attr( $this->page )
			);
		}
		echo '</fieldset>';
		$this->do_description( $args['id'] );
	}

	/**
	 * Generic callback to create a select/dropdown setting.
	 * @param $args array
	 *
	 * @since 2.0.0
	 */
	public function do_select( $args ) {
		$function = 'pick_' . $args['options'];
		$options  = $this->$function();
		printf( '<label for="%s[%s]">', esc_attr( $this->page ), esc_attr( $args['setting'] ) );
		printf( '<select id="%1$s[%2$s]" name="%1$s[%2$s]">', esc_attr( $this->page ), esc_attr( $args['setting'] ) );
		foreach ( (array) $options as $name => $key ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $name ), selected( $name, $this->setting[$args['setting']], false ), esc_attr( $key ) );
		}
		echo '</select></label>';
		$this->do_description( $args['setting'] );
	}

	/**
	 * text field input
	 * @param  $args array of data passed from field setting
	 * @since 1.3.3
	 */
	public function do_text_field( $args ) {
		$class = isset( $args['class'] ) ? $args['class'] : 'text';
		printf( '<input type="text" class="%4$s" id="%3$s[%1$s]" name="%3$s[%1$s]" value="%2$s" />', esc_attr( $args['setting'] ), esc_attr( $this->setting[ $args['setting'] ] ), esc_attr( $this->page ), esc_attr( $class ) );
		printf( '<p class="description"><label for="%3$s[%1$s]">%2$s</label></p>', esc_attr( $args['setting'] ), esc_html( $args['label'] ), esc_attr( $this->page ) );
	}

	/**
	 * Generic callback to create a number field setting.
	 * @param $args array
	 *
	 * @since 1.8.0
	 */
	public function do_number( $args ) {
		printf( '<label for="%5$s[%3$s]"><input type="number" step="%6$s" min="%1$s" max="%2$s" id="%5$s[%3$s]" name="%5$s[%3$s]" value="%4$s" class="small-text" />%7$s</label>',
			$args['min'],
			(int) $args['max'],
			esc_attr( $args['setting'] ),
			esc_attr( $this->setting[ $args['setting'] ] ),
			esc_attr( $this->page ),
			isset( $args['step'] ) ? esc_attr( $args['step'] ) : (int) 1,
			isset( $args['value'] ) ? esc_attr( $args['value'] ) : ''
		);
		$this->do_description( $args['setting'] );
	}

	/**
	 * Set up choices for checkbox array
	 * @param $args array
	 */
	public function do_checkbox_array( $args ) {
		foreach ( $args['choices'] as $key => $label ) {
			printf( '<input type="hidden" name="%s[%s][%s]" value="0" />', esc_attr( $this->page ), esc_attr( $args['setting'] ), esc_attr( $key ) );
			printf( '<input type="checkbox" name="%4$s[%5$s][%1$s]" id="%4$s[%5$s][%1$s]" value="1"%2$s class="code" /> <label for="%4$s[%5$s][%1$s]">%3$s</label><br />',
				esc_attr( $key ),
				checked( 1, $this->setting[ $args['setting'] ][ $key ], false ),
				esc_html( $label ),
				esc_attr( $this->page ),
				esc_attr( $args['setting'] )
			);
		}
		$this->do_description( $args['setting'] );
	}

	/**
	 * Generic callback to display a field description.
	 * @param  string $args setting name used to identify description callback
	 *
	 * @since 1.6.0
	 */
	protected function do_description( $args ) {
		$function = $args . '_description';
		if ( ! method_exists( $this->definitions, $function ) ) {
			return;
		}
		$description = $this->definitions->$function();
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

	/**
	 * Takes an array of new settings, merges them with the old settings, and pushes them into the database.
	 *
	 * @since 2.0.0
	 *
	 * @param string|array $new     New settings. Can be a string, or an array.
	 * @param string       $setting Optional. Settings field name. Default is supersideme.
	 * @return mixed
	 */
	protected function update_settings( $new = '', $setting = 'supersideme' ) {
		return update_option( $setting, wp_parse_args( $new, get_option( $setting ) ) );
	}
}

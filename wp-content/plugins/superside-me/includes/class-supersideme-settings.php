<?php
/**
 *
 * Settings page for SuperSide Me.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2017 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me_Settings extends SuperSide_Me_Helper {

	/**
	 * variable set for featured image option
	 * @var $page
	 */
	protected $page = 'supersideme';

	/**
	 * Main plugin setting
	 * @var $setting
	 */
	protected $setting;

	/**
	 * All settings fields
	 * @var $fields array
	 */
	protected $fields;

	/**
	 * Plugin activation status.
	 * @var $supersideme_status
	 */
	protected $supersideme_status;

	/**
	 * Nonce action name
	 * @var string $action
	 */
	protected $action = 'supersideme_save-settings';

	/**
	 * Nonce name
	 * @var string $nonce
	 */
	protected $nonce = 'supersideme_nonce';

	/**
	 * @var $sections array
	 */
	protected $sections;

	/**
	 * add a submenu page under Appearance
	 * @since 1.0.0
	 */
	public function do_submenu_page() {

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		$this->setting = $this->get_setting();

		if ( ! supersideme_do_settings_page() ) {
			return;
		}

		add_theme_page(
			__( 'SuperSide Me', 'superside-me' ),
			__( 'SuperSide Me', 'superside-me' ),
			'manage_options',
			$this->page,
			array( $this, 'do_settings_form' )
		);

		add_action( 'admin_enqueue_scripts', array( $this, 'add_color_picker' ) );

		$definitions    = new SuperSideMeDefineSettings();
		$this->sections = $definitions->register_sections();
		$this->fields   = $definitions->register_fields();
		$this->add_sections( $this->sections );
		$this->add_fields( $this->fields, $this->sections );
	}

	/**
	 * create settings form
	 *
	 * @since  1.0.0
	 */
	public function do_settings_form() {

		$this->supersideme_status = get_option( 'supersidemelicense_status', false );

		?>
		<div class="wrap">
			<h1><?php echo esc_attr( get_admin_page_title() ); ?></h1>
			<?php echo wp_kses_post( $this->do_tabs() ); ?>
			<form action="options.php" method="post">
			<?php
			$active_tab   = $this->get_active_tab();
			$sections     = 'supersideme_' . $active_tab;
			$fields       = 'supersideme';
			if ( ( ! is_multisite() || is_main_site() ) && 'licensing' === $active_tab ) {
				$sections     = 'supersideme_licensing';
				$fields       = 'supersideme_licensing';
				$this->action = 'superside_license_nonce';
				$this->nonce  = 'superside_license_nonce';
			}
			do_settings_sections( $sections );
			settings_fields( $fields );
			wp_nonce_field( $this->action, $this->nonce );
			if ( 'licensing' === $active_tab ) {
				if ( 'valid' !== $this->supersideme_status ) {
					submit_button( __( 'Activate License', 'superside-me' ), 'primary', 'supersideme_activate', true, null );
				}
			} else {
				submit_button();
			}
			settings_errors();
			?>
			</form>
		</div>
	<?php
	}

	/**
	 * Output tabs. All tabs will be output if it's a single site or the main site, and the settings page is not disabled.
	 * @return string
	 * @since 2.0.0
	 */
	protected function do_tabs() {
		$tabs = $output = '';
		if ( ! supersideme_disable_settings_page() ) {
			$tabs = $this->sections;
		}
		if ( ! is_multisite() || is_main_site() ) {
			$tabs[] = array(
				'id'    => 'licensing',
				'tab'   => __( 'License', 'superside-me' ),
			);
		}
		$active_tab = $this->get_active_tab();
		if ( ! $tabs ) {
			return $output;
		}
		$output  = '<div class="nav-tab-wrapper">';
		$output .= sprintf( '<h2 id="settings-tabs" class="screen-reader-text">%s</h2>', __( 'Settings Tabs', 'superside-me' ) );
		$output .= '<ul>';
		foreach ( $tabs as $tab ) {
			$class   = $active_tab === $tab['id'] ? ' nav-tab-active' : '';
			$output .= sprintf( '<li><a href="themes.php?page=%s&tab=%s" class="nav-tab%s">%s</a></li>', $this->page, $tab['id'], $class, $tab['tab'] );
		}
		$output .= '</ul>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Register plugin settings
	 *
	 * @since 1.5.1
	 */
	public function register_settings() {
		register_setting( 'supersideme', 'supersideme', array( $this, 'do_validation_things' ) );
	}

	/**
	 * Set color for an element
	 * @param $args array
	 * @since 1.3.0
	 */
	public function set_color( $args ) {
		printf( '<input type="text" name="%3$s[%1$s]" value="%2$s" class="color-field">',
			esc_attr( $args['setting'] ),
			esc_attr( $this->setting[ $args['setting'] ] ),
			esc_attr( $this->page )
		);
		$this->do_description( $args['setting'] );
	}

	/**
	 * settings for each registered menu location
	 * @param $args array
	 *
	 * @since 1.3.0
	 */
	public function set_menu_options( $args ) {
		$location = $args['location'];

		if ( empty( $this->setting['menus']['heading'][ $location ] ) ) {
			$this->setting['menus']['heading'][ $location ] = '';
		}
		printf( '<label for="%4$s[menus][heading][%1$s]"><input type="text" class="text" id="%4$s[menus][heading][%1$s]" name="%4$s[menus][heading][%1$s]" value="%2$s" /> %3$s</label><br />',
			esc_attr( $location ),
			esc_attr( $this->setting['menus']['heading'][ $location ] ),
			esc_html__( '[Visible] Heading', 'superside-me' ),
			esc_attr( $this->page )
		);

		if ( empty( $this->setting['menus']['skip'][ $location ] ) ) {
			$this->setting['menus']['skip'][ $location ] = 0;
		}

		printf( '<input type="hidden" name="%1$s[menus][skip][%2$s]" value="0" />', esc_attr( $this->page ), esc_attr( $location ) );
		if ( 'supersideme' === $location ) {
			return;
		}
		printf( '<input type="checkbox" name="%4$s[menus][skip][%1$s]" id="%4$s[menus][skip][%1$s]" value="1"%2$s class="code" /> <label for="%4$s[menus][skip][%1$s]">%3$s</label><br />',
			esc_attr( $location ),
			checked( 1, $this->setting['menus']['skip'][ $location ], false ),
			esc_html__( 'Do not add this menu to the panel.', 'superside-me' ),
			esc_attr( $this->page )
		);
	}

	/**
	 * validate all inputs
	 * @param  array $new_value various settings
	 * @return string            number or URL
	 *
	 * @since  1.0.0
	 */
	public function do_validation_things( $new_value = array() ) {

		// If the user doesn't have permission to save, then display an error message
		if ( ! $this->user_can_save( $this->action, $this->nonce ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'superside-me' ) );
		}

		if ( empty( $_POST['_wp_http_referer'] ) ) {
			return $this->setting;
		}

		check_admin_referer( $this->action, $this->nonce );
		$new_value = array_merge( $this->setting, $new_value );

		foreach ( $this->fields as $field ) {
			switch ( $field['callback'] ) {
				case 'do_checkbox':
					$new_value[ $field['setting'] ] = $this->one_zero( $new_value[ $field['setting'] ] );
					break;

				case 'do_select':
					$new_value[ $field['setting'] ] = esc_attr( $new_value[ $field['setting'] ] );
					break;

				case 'do_number':
					$new_value[ $field['setting'] ] = $this->check_value( $new_value[ $field['setting'] ], $this->setting[ $field['setting'] ], $field['args']['min'], $field['args']['max'] );
					break;

				case 'set_color':
					$old_value                 = $this->setting[ $field['setting'] ];
					$title                     = $field['label'];
					$new_value[ $field['setting'] ] = $this->is_color( $new_value[ $field['setting'] ], $old_value, $title );
					break;

				case 'do_text_field':
					$new_value[ $field['setting'] ] = sanitize_text_field( $new_value[ $field['setting'] ] );
					break;

				case 'do_radio_buttons':
					$new_value[ $field['setting'] ] = esc_attr( $new_value[ $field['setting'] ] );
					break;

				case 'do_checkbox_array':
					$choices = $field['args']['choices'];
					foreach ( $choices as $key => $label ) {
						$new_value[ $field['setting'] ][ $key ] = $this->one_zero( $new_value[ $field['setting'] ][ $key ] );
					}
					break;
			}
		}

		$menus = get_registered_nav_menus();
		foreach ( $menus as $location => $description ) {
			$skip    = isset( $new_value['menus']['skip'][ $location ] ) ? $new_value['menus']['skip'][ $location ] : 0;
			$heading = isset( $new_value['menus']['heading'][ $location ] ) ? $new_value['menus']['heading'][ $location ] : '';
			$new_value['menus']['skip'][ $location ]    = $this->one_zero( $skip );
			$new_value['menus']['heading'][ $location ] = sanitize_text_field( $heading );
		}

		$one_zero_options = array( 'displace', 'shrink' );
		foreach ( $one_zero_options as $option ) {
			$new_value[ $option ] = $this->one_zero( $new_value[ $option ] );
		}

		return $new_value;
	}

	/**
	 * Function that will check if value is a valid HEX color.
	 * @param $new_value string
	 * @param $old_value string
	 * @param $title string
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function is_color( $new_value, $old_value, $title ) {

		$new_value = trim( $new_value );
		$new_value = strip_tags( stripslashes( $new_value ) );

		$hex_color = '/^#[a-f0-9]{6}$/i';
		if ( preg_match( $hex_color, $new_value ) ) {
			return $new_value;
		}

		$message = sprintf( __( 'Well, that was unexpected. The %s has been reset to the last valid setting; the value you entered didn\'t work.', 'superside-me' ), $title );

		add_settings_error(
			'color',
			'not-updated',
			$message,
			'error'
		);
		return $old_value;
	}

	/**
	 * Check the numeric value against the allowed range. If it's within the range, return it; otherwise, return the old value.
	 * @param $new_value int new submitted value
	 * @param $old_value int old setting value
	 * @param $min int minimum value
	 * @param $max int maximum value
	 *
	 * @return int
	 */
	protected function check_value( $new_value, $old_value, $min, $max ) {
		if ( $new_value >= $min && $new_value <= $max ) {
			return (int) $new_value;
		}
		return (int) $old_value;
	}

	/**
	 * Add color picker to SuperSide Me settings
	 * @since 1.0.0
	 */
	public function add_color_picker() {

		$screen = get_current_screen();
		if ( 'appearance_page_supersideme' !== $screen->id ) {
			return;
		}

		// Add the color picker css file
		wp_enqueue_style( 'wp-color-picker' );

		if ( function_exists( 'wp_add_inline_script' ) ) {
			wp_enqueue_script( 'wp-color-picker' );
			$code = '( function( $ ) { \'use strict\'; $( function() { $( \'.color-field\' ).wpColorPicker(); }); })( jQuery );';
			wp_add_inline_script( 'wp-color-picker', $code );
		} else {
			wp_enqueue_script( 'supersideme-color-picker', plugins_url( '/js/color-picker.js', __FILE__ ), array( 'wp-color-picker' ), '1.0.0', true );
		}
	}
}

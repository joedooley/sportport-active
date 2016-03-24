<?php
/**
 *
 * Settings page for SuperSide Me.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2016 Robin Cornett
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
	 * @var $supersideme_setting
	 */
	protected $supersideme_setting;

	/**
	 * Registered navigation menus
	 * @var $menus array
	 */
	protected $menus;

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
	 * add a submenu page under Appearance
	 * @since 1.0.0
	 */
	public function do_submenu_page() {

		add_theme_page(
			__( 'SuperSide Me', 'superside-me' ),
			__( 'SuperSide Me', 'superside-me' ),
			'manage_options',
			$this->page,
			array( $this, 'do_settings_form' )
		);

		add_action( 'load-appearance_page_supersideme', array( $this, 'help' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_color_picker' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		$this->supersideme_setting = $this->get_setting();
		$this->register_sections();

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
			<form action="options.php" method="post">
			<?php
				settings_fields( 'supersideme' );
				do_settings_sections( 'supersideme' );
				wp_nonce_field( 'supersideme_save-settings', 'supersideme_nonce', false );
				submit_button();
				settings_errors();
			?>
			</form>
			<?php
			if ( ! is_multisite() || is_main_site() ) { ?>
			<form action="options.php" method="post">
			<?php
				settings_fields( 'supersidemelicensing' );
				do_settings_sections( 'supersidemelicensing' );
				wp_nonce_field( 'superside_license_nonce', 'superside_license_nonce' );
				if ( 'valid' !== $this->supersideme_status ) {
					submit_button( __( 'Activate License', 'superside-me' ), 'primary', 'supersideme_activate', true, null );
				}
			?>
			</form>
			<?php } ?>
		</div>
	<?php
	}

	/**
	 * Register plugin settings
	 * @return setting Main plugin setting for SuperSide Me
	 *
	 * @since 1.5.1
	 */
	public function register_settings() {
		register_setting( 'supersideme', 'supersideme', array( $this, 'do_validation_things' ) );
	}

	/**
	 * Get the plugin setting
	 * @param  boolean $setting initially set to false
	 * @return array           get_option( 'supersideme' ) with defaults
	 *
	 * @since 1.5.1
	 */
	public function get_setting( $setting = false ) {
		$defaults    = $this->defaults();
		$supersideme = get_option( 'supersideme' );
		if ( ! $supersideme ) {
			$supersidr = get_option( 'supersidrme' );
			if ( $supersidr ) {
				$supersideme = $supersidr;
			}
		}

		$setting                = $supersideme ? $supersideme : get_option( 'supersideme', $defaults );
		$setting['menus']       = isset( $setting['menus'] ) ? $setting['menus'] : array();
		$setting['search']      = isset( $setting['search'] ) ? $setting['search'] : 0;
		$setting['swipe']       = isset( $setting['swipe'] ) ? $setting['swipe'] : 0;
		$setting['panel_width'] = isset( $setting['panel_width'] ) ? $setting['panel_width'] : 260;
		$setting['shrink']      = isset( $setting['shrink'] ) ? $setting['shrink'] : 0;
		return $setting;
	}

	/**
	 * Set the default values for the supersideme option.
	 * @return array
	 */
	public function defaults() {
		return array(
			'unsimplify'  => 0,
			'side'        => 'right',
			'navigation'  => __( 'Menu', 'superside-me' ),
			'close'       => __( 'Close', 'superside-me' ),
			'background'  => '#333333',
			'link_color'  => '#fefefe',
			'maxwidth'    => 800,
			'menus'       => array(),
			'search'      => 0,
			'swipe'       => 0,
			'panel_width' => 260,
			'shrink'      => 0,
		);
	}

	/**
	 * Settings for options screen
	 * @return settings for side menu options
	 *
	 * @since 1.0.0
	 */
	public function register_sections() {

		$sections = array(
			'main' => array(
				'id'    => 'main',
				'title' => __( 'SuperSide Me[nu] Display Settings', 'superside-me' ),
			),
			'optional' => array(
				'id'    => 'optional',
				'title' => __( 'Optional Settings', 'superside-me' ),
			),
			'menus' => array(
				'id'    => 'menus',
				'title' => __( 'Automagic Menu Settings', 'superside-me' ),
			),
		);
		$this->add_sections( $sections );
		$this->register_fields( $sections );

	}

	/**
	 * Register settings fields
	 * @param  $sections array of sections
	 * @return $this->fields settings fields
	 *
	 * @since 1.5.1
	 */
	protected function register_fields( $sections ) {

		$this->fields = array(
			array(
				'id'       => 'side',
				'title'    => __( 'Set Side for Navigation' , 'superside-me' ),
				'callback' => 'do_radio_buttons',
				'section'  => 'main',
				'args'     => array(
					'id'      => 'side',
					'buttons' => array(
						array(
							'option' => 'left',
							'name'   => __( 'Left', 'superside-me' ),
						),
						array(
							'option' => 'right',
							'name'   => __( 'Right', 'superside-me' ),
						),
					),
				),
			),
			array(
				'id'       => 'shrink',
				'title'    => __( 'Automatically Size Main Menu Button', 'superside-me' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array( 'setting' => 'shrink', 'label' => __( 'Set the button to only be as wide as its contents/text.', 'superside-me' ) ),
			),
			array(
				'id'       => 'navigation',
				'title'    => __( 'Main Menu Button' , 'superside-me' ),
				'callback' => 'do_text_field',
				'section'  => 'main',
				'args'     => array( 'setting' => 'navigation', 'label' => __( '[Visible] label for main menu button.', 'superside-me' ) ),
			),
			array(
				'id'       => 'close',
				'title'    => __( 'Close Button' , 'superside-me' ),
				'callback' => 'do_text_field',
				'section'  => 'main',
				'args'     => array( 'setting' => 'close', 'label' => __( '[Visible] label for menu close button.', 'superside-me' ) ),
			),
			array(
				'id'       => 'search',
				'title'    => __( 'Search Input' , 'superside-me' ),
				'callback' => 'do_checkbox',
				'section'  => 'optional',
				'args'     => array( 'setting' => 'search', 'label' => __( 'Add a search input to the beginning of the side panel.', 'superside-me' ) ),
			),
			array(
				'id'       => 'maxwidth',
				'title'    => __( 'SuperSide Me[nu] Appears At', 'superside-me' ),
				'callback' => 'do_number',
				'section'  => 'main',
				'args'     => array( 'setting' => 'maxwidth', 'min' => 0, 'max' => 4000 ),
			),
			array(
				'id'       => 'panel_width',
				'title'    => __( 'Panel Width', 'superside-me' ),
				'callback' => 'do_number',
				'section'  => 'main',
				'args'     => array( 'setting' => 'panel_width', 'min' => 150, 'max' => 400 ),
			),
			array(
				'id'       => 'background',
				'title'    => __( 'Background Color', 'superside-me' ),
				'callback' => 'set_color',
				'section'  => 'main',
				'args'     => array( 'setting' => 'background' ),
			),
			array(
				'id'       => 'link_color',
				'title'    => __( 'Link Color', 'superside-me' ),
				'callback' => 'set_color',
				'section'  => 'main',
				'args'     => array( 'setting' => 'link_color' ),
			),
			array(
				'id'       => 'unsimplify',
				'title'    => __( 'Disable Automagic Menu', 'superside-me' ),
				'callback' => 'do_checkbox',
				'section'  => 'menus',
				'args'     => array( 'setting' => 'unsimplify', 'label' => __( 'If you check this, none of the menus listed below will display in your SuperSide Me[nu] panel, even if they have menus assigned.', 'superside-me' ) ),
			),
			array(
				'id'       => 'swipe',
				'title'    => __( 'Add Swiping', 'superside-me' ),
				'callback' => 'do_checkbox',
				'section'  => 'optional',
				'args'     => array( 'setting' => 'swipe', 'label' => __( 'Set your menu to open when the user swipes their screen.', 'superside-me' ) ),
			),
		);

		$this->menus = get_registered_nav_menus();
		if ( $this->menus ) {

			foreach ( $this->menus as $location => $description ) {
				$this->fields[] = array(
					'id'       => 'menus][' . esc_attr( $location ),
					'title'    => esc_attr( $description ),
					'callback' => 'set_menu_options',
					'section'  => 'menus',
					'args'     => array( 'location' => $location ),
				);
			}
		}
		$this->add_fields( $this->fields, $sections );
	}

	/**
	 * Section description
	 *
	 * @since 1.0.0
	 */
	public function do_main_section_description() {
		$description = __( 'Change the default behavior and style for the SuperSide Me[nu] panel.', 'superside-me' );
		$this->print_section_description( $description );
	}

	/**
	 * Description for the optional settings section.
	 */
	public function do_optional_section_description() {
		$description = __( 'Optional settings which may enhance your SuperSide Me[nu].', 'superside-me' );
		$this->print_section_description( $description );
	}

	/**
	 * Description for the registered menus section.
	 *
	 * @since  2.0.0
	 */
	public function do_menus_section_description() {
		$description  = __( 'SuperSide Me works automagically by combining every menu assigned to a location on your site and outputting them to your new mobile menu panel.', 'superside-me' );
		$description .= sprintf( ' ' . __( 'You can check which locations actually have menus assigned under <a href="%s">Appearance > Menus</a>.', 'superside-me' ), admin_url( 'nav-menus.php?action=locations' ) );
		$description .= ' ' . __( 'Here, you can set headings for each menu, or remove a certain menu from being added to the panel.', 'superside-me' );
		$this->print_section_description( $description );
	}

	/**
	 * Set color for an element
	 * @param color picker $args set any color for a setting
	 *
	 * @since 1.3.0
	 */
	public function set_color( $args ) {
		printf( '<input type="text" name="supersideme[%s]" value="%s" class="color-field" >', esc_attr( $args['setting'] ), esc_attr( $this->supersideme_setting[ $args['setting'] ] ) );
	}

	/**
	 * settings for each registered menu location
	 *
	 * @since 1.3.0
	 */
	public function set_menu_options( $args ) {
		$location = $args['location'];

		if ( empty( $this->supersideme_setting['menus']['heading'][ $location ] ) ) {
			$this->supersideme_setting['menus']['heading'][ $location ] = '';
		}
		printf( '<label for="supersideme[menus][heading][%1$s]"><input type="text" class="text" id="supersideme[menus][heading][%1$s]" name="supersideme[menus][heading][%1$s]" value="%2$s" /> %3$s</label><br />',
			esc_attr( $location ),
			esc_attr( $this->supersideme_setting['menus']['heading'][ $location ] ),
			esc_html__( '[Visible] Heading', 'superside-me' )
		);

		if ( empty( $this->supersideme_setting['menus']['skip'][ $location ] ) ) {
			$this->supersideme_setting['menus']['skip'][ $location ] = 0;
		}

		printf( '<input type="hidden" name="%1$s[menus][skip][%2$s]" value="0" />', esc_attr( $this->page ), esc_attr( $location ) );
		if ( 'supersideme' === $location ) {
			return;
		}
		printf( '<input type="checkbox" name="%4$s[menus][skip][%1$s]" id="%4$s[menus][skip][%1$s]" value="1"%2$s class="code" /> <label for="%4$s[menus][skip][%1$s]">%3$s</label><br />',
			esc_attr( $location ),
			checked( 1, $this->supersideme_setting['menus']['skip'][ $location ], false ),
			esc_html__( 'Do not add this menu to the panel.', 'superside-me' ),
			esc_attr( $this->page )
		);

	}

	/**
	 * Description for the swipe setting.
	 * @return string|void
	 */
	protected function swipe_description() {
		return __( 'Most effective if users can clearly see which side of the screen the menu is on.', 'superside-me' );
	}

	/**
	 * Description for maxwidth setting.
	 * @return string|void
	 */
	protected function maxwidth_description() {
		return __( 'This is the largest screen/browser width at which the SuperSide Me[nu] becomes active.', 'superside-me' );
	}

	/**
	 * validate all inputs
	 * @param  string $new_value various settings
	 * @return string            number or URL
	 *
	 * @since  1.0.0
	 */
	public function do_validation_things( $new_value ) {

		$action = 'supersideme_save-settings';
		$nonce  = 'supersideme_nonce';
		// If the user doesn't have permission to save, then display an error message
		if ( ! $this->user_can_save( $action, $nonce ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'superside-me' ) );
		}

		check_admin_referer( 'supersideme_save-settings', 'supersideme_nonce' );

		$new_value['side']     = esc_attr( $new_value['side'] );

		$new_value['maxwidth'] = (int) $new_value['maxwidth'];

		foreach ( $this->fields as $field ) {
			if ( 'set_color' === $field['callback'] ) {
				$old_value = $this->supersideme_setting[ $field['id'] ];
				$title     = $field['title'];
				$new_value[ $field['id'] ] = $this->is_color( $new_value[ $field['id'] ], $old_value, $title );
			} elseif ( 'do_text_field' === $field['callback'] ) {
				$new_value[ $field['id'] ] = sanitize_text_field( $new_value[ $field['id'] ] );
			} elseif ( 'do_checkbox' === $field['callback'] ) {
				$new_value[ $field['id'] ] = $this->one_zero( $new_value[ $field['id'] ] );
			} elseif ( 'do_number' === $field['callback'] ) {
				$new_value[ $field['id'] ] = (int) $new_value[ $field['id'] ];
			}
		}

		foreach ( $this->menus as $location => $description ) {
			$new_value['menus']['skip'][ $location ]    = $this->one_zero( $new_value['menus']['skip'][ $location ] );
			$new_value['menus']['heading'][ $location ] = sanitize_text_field( $new_value['menus']['heading'][ $location ] );
		}

		if ( false !== get_option( 'supersidrme', false ) ) {
			delete_option( 'supersidrme' );
		}

		return $new_value;

	}

	/**
	 * Function that will check if value is a valid HEX color.
	 *
	 * @since 1.0.0
	 */
	protected function is_color( $new_value, $old_value, $title ) {

		$new_value = trim( $new_value );
		$new_value = strip_tags( stripslashes( $new_value ) );

		if ( preg_match( '/^#[a-f0-9]{6}$/i', $new_value ) ) {
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
	 * Help tab for media screen
	 * @return help tab with verbose information for plugin
	 *
	 * @since 1.0.0
	 */
	public function help() {
		$screen = get_current_screen();

		$unsimplify_help  = '<h3>' . __( 'Disable Automagic Menu', 'superside-me' ) . '</h3>';
		$unsimplify_help .= '<p>' . __( 'By default, the plugin adds the menu assigned to all registered menu locations to your side panel. It\'s automagic. You can disable this and send only the SuperSide Me widget content to the panel. If that widget is empty, your side panel will be empty, too.', 'superside-me' ) . '</p>';

		$side_help  = '<h3>' . __( 'Set Side for Navigation', 'superside-me' ) . '</h3>';
		$side_help .= '<p>' . __( 'By default, the SuperSide Me navigation panel slides in from the right side of the site.', 'superside-me' ) . '</p>';

		$menu_button_help  = '<h3>' . __( 'Main Menu Button', 'superside-me' ) . '</h3>';
		$menu_button_help .= '<p>' . __( 'The menu button is full width by default. If you check this setting, the button will shrink, and stick to the side of the screen where the menu panel will appear.', 'superside-me' ) . '</p>';
		$menu_button_help .= '<p>' . __( 'The menu icon will show before your SuperSide Me menu button. You really should have a label for it as well.', 'superside-me' ) . '</p>';

		$colors_help  = '<h3>' . __( 'SuperSide Me Colors', 'superside-me' ) . '</h3>';
		$colors_help .= '<p>' . __( 'You can set the colors for the main SuperSide Me panel, button, and links. You can use your own CSS for other colors.', 'superside-me' ) . '</p>';

		$maxwidth_help  = '<h3>' . __( 'SuperSide Me[nu] Appears At', 'superside-me' ) . '</h3>';
		$maxwidth_help .= '<p>' . __( 'By default, the SuperSide Me menu activates when your browser window or device screen size is 800px or smaller. You can change that number to activate the menu at a larger or smaller size.', 'superside-me' ) . '</p>';
		$maxwidth_help .= '<p>' . __( 'For even more control, check the documentation for filters--for example, if you want the SuperSide Me menu at all widths, or your theme uses em/rem based media queries.', 'superside-me' );

		$panel_width_help  = '<h3>' . __( 'Panel Width', 'superside-me' ). '</h3>';
		$panel_width_help .= '<p>' . __( 'In pixels, the width of your menu panel. Even if you set a relatively large number here, the panel will not be wider than the screen.', 'superside-me' ) . '</p>';

		$search_help  = '<h3>' . __( 'Search', 'superside-me' ) . '</h3>';
		$search_help .= '<p>' . __( 'Add a small search input to the top of your side panel.', 'superside-me' ) . '</p>';

		$swipe_help  = '<h3>' . __( 'Swiping', 'superside-me' ) . '</h3>';
		$swipe_help .= '<p>' . __( 'Optionally, open the menu on touch screens by swiping (away from the side where the menu is).', 'superside-me' ) . '</p>';
		$swipe_help .= '<p>' . __( 'This will be the most effective if your menu button clearly indicates which side the menu is on.', 'superside-me' ) . '</p>';

		$menus_help  = '<h3>' . __( 'Automagic Menu Settings', 'superside-me' ) . '</h3>';
		$menus_help .= '<p>' . __( 'The list on this screen shows all menu locations registered by your theme. If you have not assigned a menu to a location, that location will not show in your SuperSide Me navigation panel', 'superside-me' ) . '</p>';
		$menus_help .= '<p>' . __( 'All menu locations with menus assigned will automagically be added to your SuperSide Me navatigation panel, unless you disable the automatic menu.', 'superside-me' ) . '</p>';

		$help_tabs = array(
			array(
				'id'      => 'supersideme-menus-help',
				'title'   => __( 'Menus', 'superside-me' ),
				'content' => wp_kses_post( $menus_help ) . wp_kses_post( $unsimplify_help ),
			),
			array(
				'id'      => 'supersideme-appearance-help',
				'title'   => __( 'Appearance', 'superside-me' ),
				'content' => wp_kses_post( $side_help ) . wp_kses_post( $menu_button_help . $maxwidth_help . $panel_width_help . $colors_help ),
			),
			array(
				'id'      => 'supersideme-optional-help',
				'title'   => __( 'Options', 'superside-me' ),
				'content' => wp_kses_post( $search_help ) . wp_kses_post( $swipe_help ),
			),
		);

		foreach ( $help_tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}

	}

	/**
	 * Add color picker to SuperSide Me settings
	 * @param color picker $hook Adds WP color picker scripts and styles
	 *
	 * @since  1.0.0
	 */
	public function add_color_picker() {

		$screen = get_current_screen();

		if ( 'appearance_page_supersideme' !== $screen->id ) {
			return;
		}

		// Add the color picker css file
		wp_enqueue_style( 'wp-color-picker' );

		// Include our custom jQuery file with WordPress Color Picker dependency
		wp_enqueue_script( 'superside-me-color-picker', plugins_url( '/js/color-picker.js', __FILE__ ), array( 'wp-color-picker' ), '1.0.0', true );

	}
}

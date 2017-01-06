<?php

/**
 *
 * Class to define all the settings for SuperSide Me.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2017 Robin Cornett
 * @license   GPL-2.0+
 */
class SuperSideMeDefineSettings {

	/**
	 * Settings for options screen
	 * @return array for side menu options
	 *
	 * @since 1.0.0
	 */
	public function register_sections() {

		$sections = array(
			'main'     => array(
				'id'    => 'main',
				'label' => __( 'General Settings', 'superside-me' ),
				'tab'   => __( 'General', 'superside-me' ),
			),
			'buttons'     => array(
				'id'    => 'buttons',
				'label' => __( 'Button Settings', 'superside-me' ),
				'tab'   => __( 'Buttons', 'superside-me' ),
			),
			'optional' => array(
				'id'    => 'optional',
				'label' => __( 'Optional Settings', 'superside-me' ),
				'tab'   => __( 'Options', 'superside-me' ),
			),
			'menus'    => array(
				'id'    => 'menus',
				'label' => __( 'Automagic Menu Settings', 'superside-me' ),
				'tab'   => __( 'Menus', 'superside-me' ),
			),
		);

		return $sections;
	}

	/**
	 * Register settings fields
	 *
	 * @return array $this->fields fields
	 *
	 * @since 1.5.1
	 */
	public function register_fields() {
		return array_merge(
			$this->define_display_fields(),
			$this->define_button_fields(),
			$this->define_optional_fields(),
			$this->define_menu_fields()
		);
	}

	/**
	 * Define the settings fields for the main/display tab.
	 * @return array
	 * @since 2.1.0
	 */
	public function define_display_fields() {
		return array(
			$this->side(),
			$this->maxwidth(),
			$this->panel_width(),
			$this->displace(),
			$this->background(),
			$this->opacity(),
			$this->link_color(),
		);
	}

	/**
	 * Define the settings fields for the buttons tab.
	 * @return array
	 * @since 2.2.0
	 */
	public function define_button_fields() {
		return array(
			$this->shrink(),
			$this->navigation(),
			$this->close(),
			$this->search_button(),
			$this->search_button_text(),
			$this->position(),
			$this->location(),
		);
	}

	/**
	 * Define the fields for the optional tab.
	 * @return array
	 * @since 2.1.0
	 */
	public function define_optional_fields() {
		return array(
			$this->search(),
			$this->swipe(),
			$this->desktop(),
			$this->speed(),
			$this->hidden(),
			$this->block(),
			$this->outline(),
			$this->fontawesome(),
		);
	}

	/**
	 * Define the fields for the menus tab.
	 * @return array
	 * @since 2.1.0
	 */
	public function define_menu_fields() {
		$fields = array(
			array(
				'setting'  => 'unsimplify',
				'label'    => __( 'Disable Automagic Menu', 'superside-me' ),
				'callback' => 'do_checkbox',
				'tab'      => 'menus',
				'args'     => array(
					'setting' => 'unsimplify',
					'label'   => __( 'If you check this, none of the menus listed below will display in your SuperSide Me panel, even if they have menus assigned.', 'superside-me' ),
				),
			),
		);

		$menus = get_registered_nav_menus();
		if ( $menus ) {

			foreach ( $menus as $location => $description ) {
				$fields[] = array(
					'setting'  => 'menus][' . esc_attr( $location ),
					'label'    => esc_attr( $description ),
					'callback' => 'set_menu_options',
					'tab'      => 'menus',
					'args'     => array( 'location' => $location ),
				);
			}
		}

		return $fields;
	}

	/**
	 * Section description
	 *
	 * @since 1.0.0
	 */
	public function do_main_section_description() {
		$description  = __( 'Change the default behavior and style for the SuperSide Me panel.', 'superside-me' );
		$description .= sprintf( __( ' Want a little more control over your menu and can handle a bit of coding? Check out the <a href="%s" target="_blank">navigation options filter</a>.', 'superside-me' ), esc_url( 'https://robincornett.com/docs/modify-navigation-options/' ) );
		$this->print_section_description( $description );
	}

	/**
	 * Description for the optional settings section.
	 */
	public function do_optional_section_description() {
		$description = __( 'Optional settings which may enhance your SuperSide Me experience.', 'superside-me' );
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
	 * Licensing section description
	 * @uses  print_section_description()
	 *
	 * @since 1.4.0
	 */
	public function do_licensing_section_description() {
		$status       = get_option( 'supersidemelicense_status', false );
		$description  = __( 'Licensed users of SuperSide Me receive plugin updates, support, and good vibes.', 'superside-me' );
		$description .= 'valid' === $status ? __( ' Great news--your license is activated!', 'superside-me' ) : '';
		$this->print_section_description( $description );
	}

	/**
	 * Buttons section description.
	 * @since 2.2.0
	 */
	public function do_buttons_section_description() {
		$this->print_section_description( __( 'Modify the settings for the menu button(s) appearance and location. Although it is a <b>really good idea</b> to have visible labels for your buttons, if you leave the labels blank, they will still have hidden labels for screen readers, so will still be accessible.', 'superside-me' ) );
	}

	/**
	 * Echoes out the section description.
	 * @param  string $description text string for description
	 *
	 * @since 1.8.0
	 */
	protected function print_section_description( $description ) {
		echo wp_kses_post( wpautop( $description ) );
	}

	/**
	 * Define array for side setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function side() {
		return array(
			'setting'  => 'side',
			'label'    => __( 'Set Side for Navigation', 'superside-me' ),
			'tab'      => 'main',
			'type'     => 'radio',
			'callback' => 'do_radio_buttons',
			'choices'  => $this->pick_side(),
			'args'     => array(
				'id'      => 'side',
				'buttons' => $this->pick_side(),
				'legend'  => __( 'Set Side for Navigation', 'superside-me' ),
			),
		);
	}

	/**
	 * Define array for the shrink setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function shrink() {
		$description = __( 'Set the button to only be as wide as its contents/text.', 'superside-me' );
		return array(
			'setting'     => 'shrink',
			'label'       => __( 'Main Menu Button Size', 'superside-me' ),
			'tab'         => 'buttons',
			'args'        => array(
				'setting' => 'shrink',
				'id'      => 'shrink',
				'buttons' => $this->pick_button_width(),
				'legend'  => $description,
			),
			'description' => $this->shrink_description(),
			'type'        => 'radio',
			'callback'    => 'do_radio_buttons',
			'choices'     => $this->pick_button_width(),
		);
	}

	/**
	 * Define position setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function position() {
		$description = __( 'Change the CSS value of the button\'s position.', 'superside-me' );
		return array(
			'setting'     => 'position',
			'label'       => __( 'Menu Button(s) Position', 'superside-me' ),
			'tab'         => 'buttons',
			'type'        => 'radio',
			'callback'    => 'do_radio_buttons',
			'description' => $description,
			'choices'     => $this->pick_position(),
			'args'        => array(
				'id'      => 'position',
				'buttons' => $this->pick_position(),
				'legend'  => $description,
			),
		);
	}

	/**
	 * Define navigation setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function navigation() {
		return array(
			'setting'   => 'navigation',
			'label'     => __( 'Main Menu Button Text', 'superside-me' ),
			'tab'       => 'buttons',
			'transport' => 'postMessage',
			'type'      => 'text',
			'callback'  => 'do_text_field',
			'args'      => array(
				'setting' => 'navigation',
				'label'   => __( '[Visible] label for main menu button.', 'superside-me' ),
			),
		);
	}

	/**
	 * Define close setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function close() {
		return array(
			'setting'   => 'close',
			'label'     => __( 'Close Button Text', 'superside-me' ),
			'tab'       => 'buttons',
			'transport' => 'postMessage',
			'type'      => 'text',
			'callback'  => 'do_text_field',
			'args'      => array(
				'setting' => 'close',
				'label'   => __( '[Visible] label for menu close button.', 'superside-me' ),
			),
		);
	}

	/**
	 * Define maxwidth setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function maxwidth() {
		return array(
			'setting'     => 'maxwidth',
			'label'       => __( 'SuperSide Me Appears At', 'superside-me' ),
			'tab'         => 'main',
			'args'        => array(
				'setting' => 'maxwidth',
				'min'     => 0,
				'max'     => 4000,
				'value'   => __( 'pixels', 'superside-me' ),
			),
			'type'        => 'number',
			'callback'    => 'do_number',
			'input_attrs' => array(
				'min' => 0,
				'max' => 4000,
			),
		);
	}

	/**
	 * Define desktop setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function desktop() {
		return array(
			'setting'  => 'desktop',
			'label'    => __( 'Desktop', 'superside-me' ),
			'callback' => 'do_checkbox',
			'tab'      => 'optional',
			'args'     => array(
				'setting' => 'desktop',
				'label'   => __( 'Replace the site menus with SuperSide Me at all sizes, including desktop.', 'superside-me' ),
			),
		);
	}

	/**
	 * Define panel width setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function panel_width() {
		return array(
			'setting'     => 'panel_width',
			'label'       => __( 'Panel Width', 'superside-me' ),
			'tab'         => 'main',
			'args'        => array(
				'setting' => 'panel_width',
				'min'     => 150,
				'max'     => 400,
				'value'   => __( 'pixels', 'superside-me' ),
			),
			'type'        => 'number',
			'callback'    => 'do_number',
			'input_attrs' => array(
				'min' => 150,
				'max' => 400,
			),
		);
	}

	/**
	 * Define displace setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function displace() {
		return array(
			'setting'  => 'displace',
			'label'    => __( 'Panel Behavior', 'superside-me' ),
			'callback' => 'do_radio_buttons',
			'tab'      => 'main',
			'args'     => array(
				'id'      => 'displace',
				'buttons' => $this->pick_displace(),
				'legend'  => __( 'If enabled, the panel will push the site upon opening. Disable and the panel will slide over the site.', 'superside-me' ),
			),
		);
	}

	/**
	 * Define background setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function background() {
		return array(
			'setting'     => 'background',
			'label'       => __( 'Background Color', 'superside-me' ),
			'callback'    => 'set_color',
			'tab'         => 'main',
			'args'        => array( 'setting' => 'background' ),
			'description' => __( 'Change the background color for the menu panel.', 'superside-me' ),
		);
	}

	/**
	 * Define opacity setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function opacity() {
		return array(
			'setting'  => 'opacity',
			'label'    => __( 'Background Opacity', 'superside-me' ),
			'callback' => 'do_number',
			'tab'      => 'main',
			'args'     => array(
				'setting' => 'opacity',
				'min'     => 75,
				'max'     => 100,
				'value'   => '%',
			),
		);
	}

	/**
	 * Define link color setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function link_color() {
		return array(
			'setting'     => 'link_color',
			'label'       => __( 'Link Color', 'superside-me' ),
			'callback'    => 'set_color',
			'tab'         => 'main',
			'args'        => array( 'setting' => 'link_color' ),
			'description' => __( 'Change the link color for the menu panel.', 'superside-me' ),
		);
	}

	/**
	 * Define search setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function search() {
		$description = __( 'Add a search input to the beginning of the side panel.', 'superside-me' );
		return array(
			'setting'     => 'search',
			'label'       => __( 'Search Input', 'superside-me' ),
			'tab'         => 'optional',
			'args'        => array(
				'setting' => 'search',
				'label'   => $description,
			),
			'transport'   => 'postMessage',
			'description' => $description,
			'type'        => 'checkbox',
			'callback'    => 'do_checkbox',
		);
	}

	/**
	 * Define the search button setting.
	 * @return array
	 * @since 2.2.0
	 */
	public function search_button() {
		$description = __( 'Add a search button next to the menu button.', 'superside-me' );
		return array(
			'setting'     => 'search_button',
			'label'       => __( 'Search Button', 'superside-me' ),
			'tab'         => 'buttons',
			'args'        => array(
				'setting' => 'search_button',
				'label'   => $description,
			),
			'transport'   => 'postMessage',
			'description' => $description,
			'type'        => 'checkbox',
			'callback'    => 'do_checkbox',
		);
	}

	/**
	 * Define the setting for the search button text.
	 * @return array
	 * @since 2.2.0
	 */
	public function search_button_text() {
		return array(
			'setting'   => 'search_button_text',
			'label'     => __( 'Search Button Text', 'superside-me' ),
			'tab'       => 'buttons',
			'transport' => 'postMessage',
			'type'      => 'text',
			'callback'  => 'do_text_field',
			'args'      => array(
				'setting' => 'search_button_text',
				'label'   => __( '[Visible] label for search button.', 'superside-me' ),
			),
		);
	}

	/**
	 * Define swipe setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function swipe() {
		return array(
			'setting'  => 'swipe',
			'label'    => __( 'Add Swiping', 'superside-me' ),
			'callback' => 'do_checkbox',
			'tab'      => 'optional',
			'args'     => array(
				'setting' => 'swipe',
				'label'   => __( 'Set your menu to open when the user swipes their screen.', 'superside-me' ),
			),
		);
	}

	/**
	 * Define location setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function location() {
		return array(
			'setting'  => 'location',
			'label'    => __( 'Menu Button(s) Location', 'superside-me' ),
			'tab'      => 'buttons',
			'type'     => 'text',
			'callback' => 'do_text_field',
			'args'     => array(
				'setting' => 'location',
				'label'   => __( 'Optional: set the location for the menu button(s). Leave blank for the default. Must be a CSS element (eg. .site-header).', 'superside-me' ),
			),
		);
	}

	/**
	 * Define speed setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function speed() {
		return array(
			'setting'     => 'speed',
			'label'       => __( 'Panel Speed', 'superside-me' ),
			'callback'    => 'do_number',
			'tab'         => 'optional',
			'args'        => array(
				'setting' => 'speed',
				'min'     => 100,
				'max'     => 10000,
				'value'   => __( 'milliseconds', 'superside-me' ),
			),
			'type'        => 'number',
			'input_attrs' => array(
				'min' => 100,
				'max' => 10000,
			),
		);
	}

	/**
	 * Define hidden elements setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function hidden() {
		return array(
			'setting'  => 'hidden',
			'label'    => __( 'Hide Elements', 'superside-me' ),
			'callback' => 'do_text_field',
			'tab'      => 'optional',
			'args'     => array(
				'setting' => 'hidden',
				'label'   => __( 'Force elements to hide using CSS. Separate multiple elements with commas. Must be a CSS element (eg. .site-header)', 'superside-me' ),
				'class'   => 'regular-text',
			),
		);
	}

	/**
	 * Define block elements setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function block() {
		return array(
			'setting'  => 'block',
			'label'    => __( 'Show Elements', 'superside-me' ),
			'callback' => 'do_text_field',
			'tab'      => 'optional',
			'args'     => array(
				'setting' => 'block',
				'label'   => __( 'Force elements to show using CSS. Separate multiple elements with commas. Must be a CSS element (eg. .site-header)', 'superside-me' ),
				'class'   => 'regular-text',
			),
		);
	}

	/**
	 * Define outline setting.
	 * @return array
	 * @since 2.1.0
	 */
	public function outline() {
		return array(
			'setting'  => 'outline',
			'label'    => __( 'Outline Style', 'superside-me' ),
			'tab'      => 'optional',
			'type'     => 'radio',
			'callback' => 'do_radio_buttons',
			'choices'  => $this->pick_outline(),
			'args'     => array(
				'id'      => 'outline',
				'buttons' => $this->pick_outline(),
				'legend'  => __( 'Set outline style', 'superside-me' ),
			),
		);
	}

	/**
	 * Define the fontawesome setting.
	 * @return array
	 */
	public function fontawesome() {
		return array(
			'setting'  => 'fontawesome',
			'label'    => __( 'Load Font Awesome', 'superside-me' ),
			'callback' => 'do_checkbox_array',
			'tab'      => 'optional',
			'args'     => array(
				'setting' => 'fontawesome',
				'choices' => array(
					'css'    => __( 'Load Fonts', 'superside-me' ),
					'glyphs' => __( 'Use Icons', 'superside-me' ),
				),
			),
		);
	}

	/**
	 * Add a description for the desktop setting.
	 * @return string|void
	 *
	 * @since 2.1.0
	 */
	public function desktop_description() {
		return __( 'This will override the screen/browser width setting.', 'superside-me' );
	}

	/**
	 * Add a description for the CSS position setting.
	 * @return string|void
	 */
	public function position_description() {
		return __( 'Change the CSS value of the button\'s position.', 'superside-me' );
	}

	/**
	 * Set options for button width/shrink setting.
	 * @return array
	 */
	public function pick_button_width() {
		return array(
			0 => __( 'Full Width/100%', 'superside-me' ),
			1 => __( 'Auto', 'superside-me' ),
		);
	}

	/**
	 * Options for radio buttons for side where menu will display.
	 * @return array
	 */
	public function pick_side() {
		return array(
			'left'  => __( 'Left', 'superside-me' ),
			'right' => __( 'Right', 'superside-me' ),
		);
	}

	/**
	 * Radio button options for CSS position.
	 * @return array
	 */
	public function pick_position() {
		return array(
			'relative' => __( 'Relative', 'superside-me' ),
			'absolute' => __( 'Absolute', 'superside-me' ),
			'fixed'    => __( 'Fixed', 'superside-me' ),
		);
	}

	/**
	 * Options for panel behavior (displace).
	 * @return array
	 */
	public function pick_displace() {
		return array(
			1 => __( 'Push Site', 'superside-me' ),
			0 => __( 'Slide Over Site', 'superside-me' ),
		);
	}

	/**
	 * Options for radio buttons for outline style.
	 * @return array
	 */
	public function pick_outline() {
		return array(
			'dotted' => __( 'dotted', 'superside-me' ),
			'dashed' => __( 'dashed', 'superside-me' ),
			'solid'  => __( 'solid', 'superside-me' ),
		);
	}

	/**
	 * Description for the swipe setting.
	 * @return string|void
	 */
	public function swipe_description() {
		return __( 'Most effective if users can clearly see which side of the screen the menu is on.', 'superside-me' );
	}

	/**
	 * Description for maxwidth setting.
	 * @return string|void
	 */
	public function maxwidth_description() {
		return __( 'This is the largest screen/browser width at which the SuperSide Me navigation becomes active.', 'superside-me' );
	}

	/**
	 * Description for opacity setting.
	 * @return string|void
	 */
	public function opacity_description() {
		return __( 'Transparency is great, but more effective if your panel slides out over your site.', 'superside-me' );
	}

	/**
	 * Description for the speed setting.
	 * @return string|void
	 */
	public function speed_description() {
		return __( 'The amount of time it takes the panel to slide open.', 'superside-me' );
	}

	/**
	 * Description for the outline setting.
	 * @return string|void
	 */
	public function outline_description() {
		return __( 'The outline provides a visual reference for when a menu element has focus.', 'superside-me' );
	}

	/**
	 * Description for the fontawesome setting.
	 * @return string|void
	 */
	public function fontawesome_description() {
		return __( 'SuperSide Me uses Font Awesome for menu icons. If you are already loading Font Awesome another way, you can disable the font from loading; if you want to replace the icons completely, disable them, too.', 'superside-me' );
	}

	/**
	 * Description for the width setting on the menu button/container.
	 * @return string|void
	 */
	public function shrink_description() {
		return __( 'Set the width for the main menu button or the container for the menu and search buttons.', 'superside-me' );
	}
}

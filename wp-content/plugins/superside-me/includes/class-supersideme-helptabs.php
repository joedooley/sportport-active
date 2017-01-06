<?php

/**
 * Set up the help tabs for the SuperSide Me Settings page.
 * Class SuperSide_Me_HelpTabs
 * @package SuperSideMe
 * @copyright 2016 Robin Cornett
 */
class SuperSide_Me_HelpTabs extends SuperSide_Me_Helper {

	/**
	 * Help tab for media screen
	 *
	 * @since 1.0.0
	 */
	public function help() {

		$screen    = get_current_screen();
		$help_tabs = $this->define_tabs();
		if ( ! $help_tabs ) {
			return;
		}
		foreach ( $help_tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}

	}

	/**
	 * Define the help tabs.
	 * @return array
	 */
	protected function define_tabs() {

		$active_tab = $this->get_active_tab();
		switch ( $active_tab ) {
			case 'menus':
				$help_tabs = array(
					array(
						'id'      => 'supersideme-menus-help',
						'title'   => __( 'Automagic Menu Settings', 'superside-me' ),
						'content' => wp_kses_post( $this->menus() ),
					),
					array(
						'id'      => 'supersideme-unsimplify-help',
						'title'   => __( 'Disable Automagic Menu', 'superside-me' ),
						'content' => wp_kses_post( $this->unsimplify() ),
					),
				);
				break;

			case 'optional':
				$help_tabs = array(
					array(
						'id'      => 'supersideme-search-help',
						'title'   => __( 'Search', 'superside-me' ),
						'content' => wp_kses_post( $this->search() ),
					),
					array(
						'id'      => 'supersideme-swipe-help',
						'title'   => __( 'Swiping', 'superside-me' ),
						'content' => wp_kses_post( $this->swipe() ),
					),
					array(
						'id'      => 'supersideme-hidden-help',
						'title'   => __( 'Display Elements', 'superside-me' ),
						'content' => wp_kses_post( $this->hidden() ),
					),
					array(
						'id'      => 'supersideme-focus-help',
						'title'   => __( 'Outline', 'superside-me' ),
						'content' => wp_kses_post( $this->focus() ),
					),
					array(
						'id'      => 'supersideme-fontawesome-help',
						'title'   => __( 'Font Awesome', 'superside-me' ),
						'content' => wp_kses_post( $this->fontawesome() ),
					),
				);
				break;

			case 'licensing':
				$help_tabs = '';
				break;

			case 'buttons':
				$help_tabs = array(
					array(
						'id'      => 'supersideme-buttons-help',
						'title'   => __( 'Main Menu Buttons', 'superside-me' ),
						'content' => wp_kses_post( $this->buttons() ),
					),
					array(
						'id'      => 'supersideme-location-help',
						'title'   => __( 'Button Location', 'superside-me' ),
						'content' => wp_kses_post( $this->location() ),
					),
				);
				break;

			default:
				$help_tabs = array(
					array(
						'id'      => 'supersideme-side-help',
						'title'   => __( 'Set Side for Navigation', 'superside-me' ),
						'content' => wp_kses_post( $this->side() ),
					),
					array(
						'id'      => 'supersideme-maxwidth-help',
						'title'   => __( 'SuperSide Me[nu] Appears At', 'superside-me' ),
						'content' => wp_kses_post( $this->maxwidth() ),
					),
					array(
						'id'      => 'supersideme-panel-help',
						'title'   => __( 'Panel Behavior', 'superside-me' ),
						'content' => wp_kses_post( $this->panel_width() ),
					),
					array(
						'id'      => 'supersideme-colors-help',
						'title'   => __( 'Colors', 'superside-me' ),
						'content' => wp_kses_post( $this->colors() ),
					),
				);
				break;
		}
		return $help_tabs;
	}

	/**
	 * Instructions for side setting.
	 * @return string
	 */
	protected function side() {
		return '<p>' . __( 'By default, the SuperSide Me navigation panel slides in from the right side of the site.', 'superside-me' ) . '</p>';
	}

	/**
	 * Instructions for main menu button width.
	 * @return string
	 */
	protected function buttons() {
		$help  = '<p>' . __( 'The menu button is full width by default. You can set the button to just be the width of its contents.', 'superside-me' ) . '</p>';
		$help .= '<p>' . __( 'The menu icon will show before your SuperSide Me menu button. You really should have a label for it as well. However, if you leave this blank, a screen reader friendly label will still be added to the button.', 'superside-me' ) . '</p>';
		$help .= '<p>' . __( 'You can optionally add a search button to display next to the main menu button. It will be display on the side of the screen opposite the menu panel, so if your menu is on the right, the search button will be on the left.', 'superside-me' ) . '</p>';

		return $help;
	}

	/**
	 * Instructions for max width setting.
	 * @return string
	 */
	protected function maxwidth() {
		$help  = '<p>' . __( 'By default, the SuperSide Me menu activates when your browser window or device screen size is 800px or smaller. You can change that number to activate the menu at a larger or smaller size.', 'superside-me' ) . '</p>';
		$help .= '<p>' . __( 'For even more control, check the documentation for filters--for example, if you want the SuperSide Me menu at all widths, or your theme uses em/rem based media queries.', 'superside-me' );

		return $help;
	}

	/**
	 * Instruction for colors.
	 * @return string
	 */
	protected function colors() {
		$help  = '<p>' . __( 'You can set the colors for the main SuperSide Me panel, button, and links. You can use your own CSS for other colors.', 'superside-me' ) . '</p>';
		$help .= '<p>' . __( 'If you want your panel to be a bit transparent, disable "Push Site with Panel", and change the Opacity of your panel. 100% is completely opaque. 75% is the minimum because that\'s pushing the limits of visibility.' , 'superside-me' ) . '</p>';

		return $help;
	}

	/**
	 * Instructions for panel width.
	 * @return string
	 */
	protected function panel_width() {
		$help  = '<p>' . __( 'In pixels, the width of your menu panel. Even if you set a relatively large number here, the panel will not be wider than the screen.', 'superside-me' ) . '</p>';
		$help .= '<p>' . __( 'By default, the panel will push the site to the side when it appears. To make the panel slide over the top of the site, change this option to "Slide Over Site".', 'superside-me' ) . '</p>';

		return $help;
	}

	/**
	 * Instruction for the automagic menus.
	 * @return string
	 */
	protected function menus() {
		$menus  = '<p>' . __( 'The list on this screen shows all menu locations registered by your theme. If you have not assigned a menu to a location, that location will not show in your SuperSide Me navigation panel.', 'superside-me' ) . '</p>';
		$menus .= '<p>' . __( 'All menu locations with menus assigned will automagically be added to your SuperSide Me navatigation panel, unless you disable the automatic menu.', 'superside-me' ) . '</p>';

		return $menus;
	}

	/**
	 * Instructions for the unsimplify setting.
	 * @return string
	 */
	protected function unsimplify() {
		return '<p>' . __( 'By default, the plugin adds the menu assigned to all registered menu locations to your side panel. It\'s automagic. You can disable this and send only the SuperSide Me widget content to the panel. If that widget is empty, your side panel will be empty, too.', 'superside-me' ) . '</p>';
	}

	/**
	 * Instructions for the search input.
	 * @return string
	 */
	protected function search() {
		return '<p>' . __( 'Add a small search input to the top of your side panel.', 'superside-me' ) . '</p>';
	}

	/**
	 * Instructions for the swipe setting.
	 * @return string
	 */
	protected function swipe() {
		$help  = '<p>' . __( 'Optionally, open the menu on touch screens by swiping (away from the side where the menu is).', 'superside-me' ) . '</p>';
		$help .= '<p>' . __( 'This will be the most effective if your menu button clearly indicates which side the menu is on.', 'superside-me' ) . '</p>';

		return $help;
	}

	/**
	 * Instructions for the button location.
	 * @return string
	 */
	protected function location() {
		$help  = '<p>' . __( 'By default, the menu button is added to the very beginning of the website. If you\'d like to place it somewhere else, enter the class or ID of the element of the location. SuperSide Me will attempt to confirm that your custom location exists; if it does not, the button will be placed in its default location.', 'superside-me' ) . '</p>';
		$help .= '<p>' . sprintf( 'Examples of valid locations are: <code>%s</code>, <code>%s</code>', '.site-header', '#site-header' ) . '</p>';

		return $help;
	}

	/**
	 * Instructions for hidden CSS elements.
	 * @return string
	 */
	protected function hidden() {
		$help  = '<p>' . __( 'General navigation elements will be hidden by default, but if your theme adds a button or other element which is still showing, add it to this list to hide it. Same with elements which might be hidden but that you would like to show. You can use classes (.site-header) or IDs (#site-header). Separate multiple elements with commas.', 'superside-me' ) . '</p>';
		$help .= '<p>' . __( 'Pro Tip: if you don\'t know what CSS is, don\'t add things here. You could hide things from your site that you didn\'t mean to. If that happens, clear this field and resave your settings.', 'superside-me' ) . '</p>';

		return $help;
	}

	/**
	 * Instructions for block CSS elements.
	 * @return string
	 */
	protected function focus() {
		return '<p>' . __( 'For accessibility, it\'s important to have a style which clearly shows which element has focus. SuperSide Me uses the standard outline; you can adjust the outline style here.', 'superside-me' ) . '</p>';
	}

	/**
	 * Instructions for the Font Awesome settings.
	 * @return string
	 */
	protected function fontawesome() {
		$help  = '<p>' . __( 'SuperSide Me loads Font Awesome for icons. If you are already loading Font Awesome another way, you can disable the font from loading.', 'superside-me' ) . '</p>';
		$help .= '<p>' . __( 'You can also disable Font Awesome entirely and replace them in your CSS. Disabling the icons will prevent any Font Awesome CSS from being output at all.', 'superside-me' ) . '</p>';

		return $help;
	}
}
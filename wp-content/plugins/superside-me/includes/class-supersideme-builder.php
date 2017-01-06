<?php
/**
 * SuperSide Me Builder class.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2017 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me_Builder {

	protected $setting;

	/**
	 * Register SuperSide widget area.
	 *
	 * @since 1.0.0
	 */
	public function register_widget_area() {

		$html5_check  = current_theme_supports( 'html5' );
		$html5_div    = $html5_check ? 'section' : 'div';
		$a11y_check   = current_theme_supports( 'genesis-accessibility', array( 'headings' ) );
		$a11y_heading = $a11y_check ? 'h3' : 'h4';

		register_sidebar( array(
			'name'          => __( 'SuperSide Me', 'superside-me' ),
			'id'            => 'superside',
			'description'   => __( 'This is the widget area for the SuperSide Me[nu] bar. Not all widgets will work here, so please use caution.', 'superside-me' ),
			'class'         => '',
			'before_widget' => '<' . $html5_div . ' id="%1$s" class="widget %2$s">',
			'after_widget'  => '</' . $html5_div . '>',
			'before_title'  => '<' . $a11y_heading . ' class="widgettitle widget-title">',
			'after_title'   => '</' . $a11y_heading . '>',
		) );

	}

	/**
	 * register SuperSide Me Menu location
	 *
	 * @since 1.2.0
	 */
	public function register_superside_nav() {
		register_nav_menu( 'supersideme', __( 'SuperSide Me Navigation Menu', 'superside-me' ) );
	}

	/**
	 * Set variables for side output
	 *
	 * @since  1.0.0
	 */
	public function localize_scripts() {

		$this->setting = supersideme_get_settings();
		$menu_settings = empty( $this->setting['menus'] ) ? array() : $this->setting['menus'];
		$options       = supersideme_get_navigation_options();

		// Set up variables to pass to our js
		$output = array(
			'location'     => esc_attr( $options['location'] ),
			'close'        => esc_attr( $options['close'] ),
			'displace'     => (bool) $options['displace'],
			'closeevent'   => esc_attr( $options['closeevent'] ),
			'side'         => esc_attr( $options['side'] ),
			'menus'        => $this->setting['unsimplify'] ? array() : $this->build_menus( $menu_settings ),
			'navigation'   => esc_attr( $this->setting['navigation'] ), // main menu button text
			'navarialabel' => esc_attr__( 'Navigation Menu', 'superside-me' ), // aria label
			'submenu'      => esc_attr__( 'Menu', 'superside-me' ), // aria label
			'subarialabel' => esc_attr__( 'Navigation Sub Menu', 'superside-me' ), // aria label
			'swipe'        => (bool) $this->setting['swipe'],
			'speed'        => (int) $options['speed'],
			'source'       => null !== $options['source'] ? esc_attr( $options['source'] ) : null,
			'function'     => esc_attr( $options['function'] ),
			'search'       => array(
				'panel'       => (bool) apply_filters( 'supersideme_do_search', $this->setting['search'] ),
				'input'       => $this->build_search(),
				'button'      => (bool) $this->setting['search_button'],
				'button_text' => esc_attr( $this->setting['search_button_text'] ),
				'button_aria' => esc_attr__( 'Search', 'superside-me' ),
			),
			'second'       => apply_filters( 'supersideme_second_panel', array() ),
			'html5'        => (bool) current_theme_supports( 'html5' ),
		);
		if ( is_customize_preview() ) {
			$output['search_on'] = (bool) apply_filters( 'supersideme_do_search', $this->setting['search'] );
		}

		// Send the variables to the script
		wp_localize_script( 'superside-init', 'SuperSideMeVar', $output );

	}

	/**
	 * Check registered menu locations to see if a menu is assigned; if so, build.
	 * @param $menu_settings
	 * @param array $output_menu
	 *
	 * @return mixed|void
	 */
	public function build_menus( $menu_settings, $output_menu = array() ) {

		if ( $this->setting['unsimplify'] ) {
			return $output_menu;
		}

		$menus = $this->get_menus();
		foreach ( $menus as $location => $description ) {

			$output_nav[ $location ]['menu'] = wp_nav_menu( array(
				'theme_location' => $location,
				'menu'           => apply_filters( 'supersideme_modify_menu', '', $location ),
				'container'      => false,
				'echo'           => 0,
				'fallback_cb'    => false,
				'items_wrap'     => '%3$s',
			) );

			$heading = $menu_settings && ! empty( $menu_settings['heading'][ $location ] ) ? $menu_settings['heading'][ $location ] : $description;
			$heading = $heading ? $heading : $description; // for a11y
			$class   = empty( $menu_settings['heading'][ $location ] ) ? ' class="screen-reader-text"' : '';

			$a11ycheck   = current_theme_supports( 'genesis-accessibility', array( 'headings' ) );
			$a11yheading = $a11ycheck ? 'h3' : 'h4';
			$output_nav[ $location ]['heading'] = sprintf( '<%1$s%2$s>%3$s</%1$s>', $a11yheading, $class, $heading );

			$output_menu[] = empty( $output_nav[ $location ]['menu'] ) ? '' : $output_nav[ $location ]['heading'] . $output_nav[ $location ]['menu'];

		}

		/**
		 * add filter to modify menus output to side panel
		 *
		 * @param array $output_menu
		 * Registered Menus with headings
		 *
		 * @since 1.2.0
		 */
		return apply_filters( 'supersideme_menu_output', $output_menu );

	}

	/**
	 * Output sidebar for panel (replaces build_sidebar)
	 *
	 * @since 1.7.0
	 */
	public function do_sidebar() {
		if ( ! is_active_sidebar( 'superside' ) ) {
			return;
		}
		echo '<div class="supersideme widget-area" style="display:none;">';
		dynamic_sidebar( 'superside' );
		echo '</div>';
	}

	/**
	 * Build search form for side panel
	 * @return string form without echo
	 *
	 * @since 1.5.0
	 */
	protected function build_search() {
		$do_search = apply_filters( 'supersideme_do_search', $this->setting['search'] );
		if ( ! $do_search && ! is_customize_preview() && ! $this->setting['search_button'] ) {
			return '';
		}
		$search_form  = '<div class="search-me">';
		$search_form .= get_search_form( false );
		$search_form .= '</div>';
		return apply_filters( 'supersideme_search_output', $search_form );
	}

	/**
	 * add support for skip links
	 *
	 * @since 1.6.1
	 */
	public function skip_links() {
		$skip_links = null;
		if ( current_theme_supports( 'genesis-accessibility', array( 'skip-links' ) ) ) {
			$skip_links = array(
				'ulClass'   => '.genesis-skip-link', // div/ul containing the skip links
				'startLink' => 'genesis-nav-primary', // the link to the primary navigation
				'contains'  => 'genesis-nav', // what all the navigation links have in common
				'unique'    => 'primary', // something unique to the primary navigation skip link
			);
		}
		$skip_links = apply_filters( 'supersideme_skiplinks', $skip_links );
		if ( ! $skip_links ) {
			return;
		}
		wp_localize_script( 'superside-init', 'supersidemeSkipLinks', array_map( 'esc_attr', $skip_links ) );
	}

	/**
	 * Modify the secondary menu
	 * @param  string $menu name of menu to use as secondary
	 * @param  string $location
	 * @return string       currently only affects Genesis Simple Menus
	 *
	 * @since 1.7.0
	 */
	public function modify_genesis_simple_menus( $menu, $location ) {

		if ( 'secondary' !== $location ) {
			return $menu;
		}
		$simple_menus = new Genesis_Simple_Menus();
		return (int) $simple_menus->menu;
	}

	/**
	 * Retrieve menus for panel
	 * @return array          registered menus, less those removed from the panel
	 *
	 * @since 1.7.0
	 */
	public function get_menus() {
		$setting = supersideme_get_settings();
		if ( $setting['unsimplify'] ) {
			return array();
		}
		$menus         = get_registered_nav_menus();
		$menu_settings = empty( $setting['menus'] ) ? array() : $setting['menus'];
		foreach ( $menus as $location => $description ) {
			$skip = isset( $menu_settings['skip'][ $location ] ) ? $menu_settings['skip'][ $location ] : 0;
			if ( $skip || ! has_nav_menu( $location ) ) {
				unset( $menus[ $location ] );
			}
		}
		return apply_filters( 'supersideme_get_menus', $menus );
	}

	/**
	 * Compile all panel items for output to localiztion
	 * @param  array  $panel empty array
	 * @return array        array including (possibly) search, menu, sidebar
	 *
	 * @since 1.5.0
	 */
	public function build_panel() {

		_deprecated_function( __FUNCTION__, '1.7.0', 'build_menus' );
		$menu_settings = empty( $this->setting['menus'] ) ? array() : $this->setting['menus'];

		$search[]   = $this->build_search();
		$navigation = $this->setting['unsimplify'] ? array() : $this->build_menus( $menu_settings );
		$sidebar[]  = $this->build_sidebar( 'superside' );
		$panel      = array_merge( $search, $navigation, $sidebar );
		return apply_filters( 'supersideme_panel_output', $panel );
	}

	/**
	 * Build widget area for SuperSide panel
	 * @param  string $sidebar Sidebar from which to build
	 * @return void|string sidebar          Full widget area
	 */
	public function build_sidebar( $sidebar = 'superside' ) {
		_deprecated_function( __FUNCTION__, '1.7.0', 'do_sidebar' );
		if ( ! is_active_sidebar( $sidebar ) ) {
			return '';
		}
		ob_start();
		echo '<div class="supersideme widget-area">';
		dynamic_sidebar( $sidebar );
		echo '</div>';
		$sidebar = ob_get_clean();
		return apply_filters( 'supersideme_sidebar_output', $sidebar );
	}
}

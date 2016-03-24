<?php
/**
 * SuperSide Me Builder class.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2016 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me_Builder {

	protected $supersideme_setting;

	/**
	 * Register SuperSide widget area.
	 * @return sidebar Optional sidebar for use with the menus.
	 *
	 * @since 1.0.0
	 */
	public function register_widget_area() {

		$a11ycheck   = current_theme_supports( 'genesis-accessibility', array( 'headings' ) );
		$a11yheading = $a11ycheck ? 'h3' : 'h4';

		register_sidebar( array(
			'name'         => __( 'SuperSide Me', 'superside-me' ),
			'id'           => 'superside',
			'description'  => __( 'This is the widget area for the SuperSide Me[nu] bar. Not all widgets will work here, so please use caution.', 'superside-me' ),
			'class'        => '',
			'before_title' => '<' . $a11yheading . ' class="widgettitle widget-title">',
			'after_title'  => '</' . $a11yheading . '>',
		) );

	}

	/**
	 * register SuperSide Me Menu location
	 *
	 * @since 1.2.0
	 */
	public function register_superside_nav() {
		register_nav_menu( 'supersideme', __( 'SuperSide Me Navigation Menu', 'superside-me' ) );
		$this->supersideme_setting = supersideme_get_settings();
	}

	/**
	 * Set variables for side output
	 * @return variable for js Sets $output with menus/labels for js
	 *
	 * @since  1.0.0
	 */
	public function localize_scripts() {

		$menu_settings = empty( $this->supersideme_setting['menus'] ) ? array() : $this->supersideme_setting['menus'];
		$side_tweaks   = supersideme_get_navigation_options();

		// Set up variables to pass to our js
		$output = array(
			'location'     => esc_attr( $side_tweaks['location'] ),
			'close'        => esc_attr( $side_tweaks['close'] ),
			'displace'     => (bool) $side_tweaks['displace'],
			'closeevent'   => esc_attr( $side_tweaks['closeevent'] ),
			'side'         => esc_attr( $side_tweaks['side'] ),
			'search'       => $this->build_search(),
			'menus'        => $this->supersideme_setting['unsimplify'] ? array() : $this->build_menus( $menu_settings ),
			'navigation'   => $this->supersideme_setting['navigation'] ? esc_attr( $this->supersideme_setting['navigation'] ) : sprintf( '<span class="screen-reader-text">%s</span>', __( 'Navigation Menu', 'superside-me' ) ), // main menu button text
			'navarialabel' => __( 'Navigation Menu', 'superside-me' ), // aria label
			'submenu'      => __( 'Menu', 'superside-me' ), // aria label
			'subarialabel' => __( 'Navigation Sub Menu', 'superside-me' ), // aria label
			'swipe'        => (bool) $this->supersideme_setting['swipe'],
			'speed'        => (int) $side_tweaks['speed'],
			'source'       => null !== $side_tweaks['source'] ? esc_attr( $side_tweaks['source'] ) : null,
		);
		if ( is_customize_preview() ) {
			$output['search_on'] = apply_filters( 'supersideme_do_search', $this->supersideme_setting['search'] );
		}

		// Send the variables to the script
		wp_localize_script( 'superside-init', 'SuperSideMeVar', $output );

	}

	/**
	 * Check registered menu locations to see if a menu is assigned; if so, build.
	 * @return $output_menu each registered menu
	 */
	public function build_menus( $menu_settings, $output_menu = array() ) {

		if ( $this->supersideme_setting['unsimplify'] ) {
			return;
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
	 * @return sidebar supersideme sidebar
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
	 * @return search form without echo
	 *
	 * @since 1.5.0
	 */
	protected function build_search() {
		$do_search = apply_filters( 'supersideme_do_search', $this->supersideme_setting['search'] );
		if ( ! $do_search && ! is_customize_preview() ) {
			return;
		}
		$search_form  = '<div class="search-me">';
		$search_form .= get_search_form( false );
		$search_form .= '</div>';
		return apply_filters( 'supersideme_search_output', $search_form );
	}

	/**
	 * add support for skip links
	 * @return array array of values to be sent to javascript
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
		wp_localize_script( 'superside-init', 'supersidemeSkipLinks', $skip_links );
	}

	/**
	 * Modify the secondary menu
	 * @param  string $menu name of menu to use as secondary
	 * @return string       currently only affects Genesis Simple Menus
	 *
	 * @since 1.7.0
	 */
	public function modify_genesis_simple_menus( $menu, $location ) {

		if ( 'secondary' !== $location ) {
			return $menu;
		}

		$menu_key = class_exists( 'Genesis_Simple_Menus' ) ? '_gsm_menu' : false;
		if ( ! $menu_key ) {
			return $menu;
		}

		if ( is_singular() ) {
			$menu = get_post_meta( get_the_ID(), $menu_key, true );
			return $menu;
		}

		$term = false;
		if ( is_category() || is_tag() || is_tax() ) {
			$term = is_tax() ? get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ) : get_queried_object();
		}

		if ( $term && isset( $term->meta[ $menu_key ] ) ) {
			$menu = $term->meta[ $menu_key ];
		}
		return $menu;
	}

	/**
	 * Retrieve menus for panel
	 * @param  boolean $menus false by default
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
	public function build_panel( $panel = array() ) {

		_deprecated_function( __FUNCTION__, '1.7.0', 'build_menus' );
		$menu_settings = empty( $this->supersideme_setting['menus'] ) ? array() : $this->supersideme_setting['menus'];

		$search[]   = $this->build_search();
		$navigation = $this->supersideme_setting['unsimplify'] ? array() : $this->build_menus( $menu_settings );
		$sidebar[]  = $this->build_sidebar( 'superside' );
		$panel      = array_merge( $search, $navigation, $sidebar );
		return apply_filters( 'supersideme_panel_output', $panel );
	}

	/**
	 * Build widget area for SuperSide panel
	 * @param  string $sidebar Sidebar from which to build
	 * @return sidebar          Full widget area
	 */
	public function build_sidebar( $sidebar = 'superside' ) {
		_deprecated_function( __FUNCTION__, '1.7.0', 'do_sidebar' );
		if ( ! is_active_sidebar( $sidebar ) ) {
			return;
		}
		ob_start();
		echo '<div class="supersideme widget-area">';
		dynamic_sidebar( $sidebar );
		echo '</div>';
		$sidebar = ob_get_clean();
		return apply_filters( 'supersideme_sidebar_output', $sidebar );
	}
}

<?php
/**
 *
 * Defines defaults and retrieves settings.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2017 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSideMeGetSetting {

	/**
	 * Get the plugin setting
	 * @param  boolean $setting initially set to false
	 * @return array           get_option( 'supersideme' ) with defaults
	 *
	 * @since 1.5.1
	 */
	public function get_setting( $setting = false ) {
		$defaults = $this->defaults();
		$setting  = get_option( 'supersideme', $defaults );
		if ( ! isset( $setting['position'] ) || empty( $setting['position'] ) ) {
			$setting['position'] = isset( $setting['shrink'] ) && $setting['shrink'] ? 'absolute' : 'relative';
		}

		return wp_parse_args( $setting, $defaults );
	}

	/**
	 * Set the default values for the supersideme option.
	 * @return array
	 */
	public function defaults() {
		/**
		 * Add a filter to modify the default plugin options.
		 * @since 2.0.0
		 */
		return apply_filters( 'supersideme_default_options', array(
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
			'displace'    => 1,
			'opacity'     => 100,
			'location'    => '',
			'speed'       => 200,
			'hidden'      => '',
			'block'       => '',
			'outline'     => 'dotted',
			'desktop'     => 0,
			'position'    => 'relative',
			'fontawesome' => array(
				'css'    => 1,
				'glyphs' => 1,
			),
			'search_button'      => 0,
			'search_button_text' => __( 'Search', 'superside-me' ),
		) );
	}
}

<?php
/**
 * CSS Output class.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2016 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me_CSS {

	/**
	 * Preemptively add a no-js class to the html element
	 * @param  class $output no-js
	 * @return string        no-js class
	 *
	 * @since 1.6.0
	 */
	public function no_js_class( $output ) {
		return $output . ' class="no-js"';
	}

	/**
	 * Use javascript to remove the no-js class from the html element.
	 *
	 * @since 1.6.0
	 */
	public function add_js_class() {
		echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
	}

	/**
	 * Custom CSS.
	 *
	 * Outputs custom CSS to control the look of the menu.
	 */
	public function css() {

		$display_css = $this->display_css();
		$menu_css    = $this->menu_css();

		// Minify a bit
		$css = str_replace( "\t", '', $display_css . $menu_css );
		$css = str_replace( array( "\n", "\r" ), ' ', $css );
		return $css;

	}

	/**
	 * Set default glyphs for the menu output
	 * @return array font awesome content values
	 */
	protected function glyphs() {
		$glyphs = apply_filters( 'supersideme_default_glyphs', array(
			'slide-nav-link'       => esc_attr( '\f0c9' ),
			'slide-nav-link-open'  => esc_attr( '\f0c9' ),
			'menu-close'           => esc_attr( '\f00d' ),
			'sub-menu-toggle'      => esc_attr( '\f107' ),
			'sub-menu-toggle-open' => esc_attr( '\f106' ),
			'search'               => esc_attr( '\f002' ),
		) );
		return $glyphs;
	}

	/**
	 * Set options from settings page/filter
	 * @return array options for inline styles and javascript
	 */
	public function options() {
		$setting     = supersideme_get_settings();
		$side_tweaks = apply_filters( 'supersideme_navigation_options', array(
			'background'   => esc_attr( $setting['background'] ),
			'button_color' => '',
			'close'        => sprintf( '<button class="menu-close" role="button" aria-pressed="false" aria-label="%1$s">%2$s</button>', __( 'Close Navigation', 'superside-me' ), $setting['close'] ),
			'closeevent'   => '.menu-close',
			'displace'     => true,
			'link_color'   => esc_attr( $setting['link_color'] ),
			'location'     => 'body',
			'maxwidth'     => esc_attr( (int) $setting['maxwidth'] . 'px' ),
			'outline'      => esc_attr( 'dotted' ),
			'panel_width'  => $setting['panel_width'] . 'px',
			'position'     => $setting['shrink'] ? esc_attr( 'absolute' ) : esc_attr( 'relative' ),
			'side'         => $setting['side'],
			'source'       => null,
			'speed'        => 200,
			'width'        => $setting['shrink'] ? esc_attr( 'auto' ) : esc_attr( '100%' ),
		) );
		return $side_tweaks;
	}

	/**
	 * Build array of elements to be hidden by inline CSS
	 * @return array general navigation elements
	 *
	 * @since 1.7.1
	 */
	protected function hidden_elements() {
		$hidden_elements = apply_filters( 'supersideme_hide_elements', array(
			'nav',
			'#nav',
			'.nav-primary',
			'.nav-secondary',
			'.supersideme .site-header .secondary-toggle',
		) );
		return is_array( $hidden_elements ) ? implode( ', ', $hidden_elements ) : $hidden_elements;
	}

	/**
	 * Set up the inline CSS for the menu (button) display
	 * @return string inline CSS
	 */
	protected function display_css() {

		$side_tweaks = $this->options();
		$hidden      = $this->hidden_elements();
		$display_css = sprintf( '
			@media only screen and (max-width: %s) {
				%s { display: none; }
				.slide-nav-link { display: block; }
			}',
			$side_tweaks['maxwidth'],
			$hidden
		);

		return apply_filters( 'supersideme_modify_display_css', $display_css, $side_tweaks, $hidden );
	}

	/**
	 * Set up the inline CSS for the panel itself
	 * @return string inline CSS for side panel
	 */
	protected function menu_css() {

		$glyphs      = $this->glyphs();
		$side_tweaks = $this->options();
		$setting     = supersideme_get_settings();

		// SuperSide menu CSS
		$menu_css = sprintf( '
			.slide-nav-link { background: %11$s; %2$s: 0; color: %3$s; position: %4$s; width: %5$s; }
			.slide-nav-link:before { content: "%6$s"; }
			.slide-nav-link.menu-open:before { content: "%7$s"; }
			.sidr { background-color: %1$s; color: %3$s; }
			.sidr h3, .sidr h4, .sidr .widget, .sidr p { color: %3$s; }
			.slide-nav-link:focus, .sidr:focus, .sidr a:focus, .menu-close:focus, .sub-menu-toggle:focus { outline: %3$s %12$s 1px; }
			.menu-close:before { content: "%8$s"; }
			.sidr a, .sidr button, .sidr .sub-menu-toggle:before { color: %3$s; }
			.sub-menu-toggle:before { content: "%9$s"; }
			.sub-menu-toggle.menu-open:before { content: "%10$s"; }
			.search-me:before { color: %1$s; content: "%13$s"; }
			.sidr { width: %14$s; }
			.sidr.left { left: -%14$s; }
			.sidr.right { right: -%14$s; }',
			$side_tweaks['background'], // %1$s
			$side_tweaks['side'], // %2$s
			$side_tweaks['link_color'], // %3$s
			$side_tweaks['position'], // %4$s
			$side_tweaks['width'], // %5$s
			$glyphs['slide-nav-link'], // %6$s
			$glyphs['slide-nav-link-open'], // %7$s
			$glyphs['menu-close'], // %8$s
			$glyphs['sub-menu-toggle'], // %9$s
			$glyphs['sub-menu-toggle-open'], // %10$s
			empty( $side_tweaks['button_color'] ) ? $side_tweaks['background'] : $side_tweaks['button_color'], // %11$s
			$side_tweaks['outline'], // %12$s
			$glyphs['search'], // %13$s
			$side_tweaks['panel_width'], // %14$s
			$setting['shrink'] ? $side_tweaks['side'] . ': 0;' : ''
		);

		$menu_css = apply_filters( 'supersideme_modify_menu_css', $menu_css, $side_tweaks, $setting, $glyphs );
		return $menu_css;
	}
}

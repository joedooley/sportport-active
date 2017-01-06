<?php
/**
 * CSS Output class.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2017 Robin Cornett
 * @license   GPL-2.0+
 */

class SuperSide_Me_CSS {

	/**
	 * The main plugin setting.
	 * @var mixed|void
	 */
	protected $setting;

	/**
	 * Preemptively add a no-js class to the html element
	 * @param string $output no-js
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
		?>
<script type="text/javascript">
	//<![CDATA[
	(function(html){
		var c = html.className;
		c = c.replace(/\bno-js\b/,'js');
		html.className = c;
	})(document.documentElement);
	//]]>
</script>
<?php
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

		return sanitize_text_field( strip_tags( $css ) );
	}

	/**
	 * Set default glyphs for the menu output
	 * @return array font awesome content values
	 */
	protected function glyphs() {
		$glyphs = apply_filters( 'supersideme_default_glyphs', array(
			'slide-nav-link'       => '\f0c9',
			'slide-nav-link-open'  => '\f0c9',
			'menu-close'           => '\f00d',
			'sub-menu-toggle'      => '\f107',
			'sub-menu-toggle-open' => '\f106',
			'search'               => '\f002',
		) );
		return array_map( 'esc_attr', $glyphs );
	}

	/**
	 * @return mixed|void
	 */
	protected function button_options() {
		$button  = apply_filters( 'supersideme_button_options', array(
			'function'     => 'prepend',
			'button_color' => '',
			'location'     => $this->setting['location'],
			'position'     => $this->setting['position'],
			'width'        => $this->setting['shrink'] ? 'auto' : '100%',
		) );
		return $button;
	}

	/**
	 * Options for the menu panel.
	 * @return mixed|void
	 */
	protected function panel_options() {
		$panel_options = apply_filters( 'supersideme_panel_options', array(
			'background'  => $this->setting['background'],
			'close'       => sprintf( '<button class="menu-close" role="button" aria-pressed="false" aria-label="%1$s">%2$s</button>', __( 'Close Navigation', 'superside-me' ), $this->setting['close'] ),
			'closeevent'  => '.menu-close',
			'desktop'     => $this->setting['desktop'],
			'displace'    => $this->setting['displace'],
			'link_color'  => $this->setting['link_color'],
			'maxwidth'    => (int) $this->setting['maxwidth'] . 'px',
			'outline'     => $this->setting['outline'],
			'panel_width' => $this->setting['panel_width'] . 'px',
			'side'        => $this->setting['side'],
			'source'      => null,
			'speed'       => $this->setting['speed'],
		) );
		return $panel_options;
	}

	/**
	 * Set options from settings page/filter
	 * @return array options for inline styles and javascript
	 */
	public function options() {
		$button_options = $this->button_options();
		$panel_options  = $this->panel_options();
		$options        = array_merge( $button_options, $panel_options );
		return apply_filters( 'supersideme_navigation_options', $options );
	}

	/**
	 * Build array of elements to be hidden by inline CSS
	 * @return string general navigation elements
	 *
	 * @since 1.7.1
	 */
	protected function hidden_elements() {
		$hidden_elements = array(
			'nav',
			'#nav',
			'.nav-primary',
			'.nav-secondary',
			'.supersideme .site-header .secondary-toggle',
			'.menu-toggle',
		);
		$hidden_elements = $this->merge( 'hidden', $hidden_elements );
		return $this->convert_arrays( $hidden_elements );
	}

	/**
	 * Build elements to display as block elements.
	 * @return string
	 * @since 2.0.0
	 */
	protected function block_elements() {
		$block_elements = array(
			'.slide-nav-link',
			'.ssme-search',
		);
		$block_elements = $this->merge( 'block', $block_elements );
		return $this->convert_arrays( $block_elements );
	}

	/**
	 * Merge the default and setting for each display element.
	 * @param $setting string the setting key
	 * @param $array array default array of elements
	 * @since 2.0.0
	 *
	 * @return mixed|void
	 */
	protected function merge( $setting, $array ) {
		if ( ! empty( $this->setting[ $setting ] ) ) {
			$array = array_merge( $array, (array) $this->setting[ $setting ] );
		}

		/**
		 * Add a filter to modify the display elements
		 * @since 2.0.0
		 */
		return apply_filters( "supersideme_{$setting}_elements", $array );
	}

	/**
	 * Convert merged arrays to a string.
	 * @param $array
	 *
	 * @return string
	 * @since 2.0.0
	 */
	protected function convert_arrays( $array ) {
		return is_array( $array ) ? implode( ',', $array ) : $array;
	}

	/**
	 * Set up the inline CSS for the menu (button) display
	 * @return string inline CSS
	 */
	protected function display_css() {

		$options     = $this->options();
		$hidden      = $this->hidden_elements();
		$block       = $this->block_elements();
		$display_css = '';
		if ( ! $options['desktop'] ) {
			$display_css .= sprintf( '@media only screen and (max-width: %s) { ', $options['maxwidth'] );
		}
		$display_css .= sprintf( ' %s { display: none; } %s { display: block; }', $hidden, $block );
		if ( ! $options['desktop'] ) {
			$display_css .= ' }';
		}

		return apply_filters( 'supersideme_modify_display_css', $display_css, $options, $hidden );
	}

	/**
	 * Set up the inline CSS for the panel itself
	 * @return string inline CSS for side panel
	 */
	protected function menu_css() {

		$options = $this->options();

		$menu_css        = sprintf( '.sidr { width: %1$s; } .sidr.left { left: -%1$s; } .sidr.right { right: -%1$s; }', $options['panel_width'] );
		$button_position = $options['position'];
		$button_width    = $options['width'];
		if ( $this->setting['search_button'] ) {
			$menu_css       .= $this->search_button_css();
			$button_position = 'relative';
			$button_width    = 'auto';
		}

		// SuperSide menu CSS
		$menu_css .= sprintf( '
			.slide-nav-link { %6$s %2$s: 0; color: %3$s; position: %4$s; width: %5$s; }
			.sidr { %8$s color: %3$s; }
			.sidr h3, .sidr h4, .sidr .widget, .sidr p { color: %3$s; }
			.slide-nav-link:focus, .sidr:focus, .sidr a:focus, .menu-close:focus, .sub-menu-toggle:focus { outline: %3$s %7$s 1px; }
			.sidr a, .sidr a:focus, .sidr a:active, .sidr button, .sidr .sub-menu-toggle:before { color: %3$s; }
			.search-me:before { color: %1$s; }',
			$options['background'], // %1$s
			$options['side'], // %2$s
			$options['link_color'], // %3$s
			$button_position, // %4$s
			$button_width, // %5$s
			empty( $options['button_color'] ) ? $this->background_color( $options['background'], $this->setting['opacity'] ) : $this->background_color( $options['button_color'] ), // %6$s
			$options['outline'], // %7$s
			$this->background_color( $options['background'], $this->setting['opacity'] ) // %8$s,
		);
		$menu_css .= $this->fontawesome_css();

		return apply_filters( 'supersideme_modify_menu_css', $menu_css, $options, $this->setting, $this->glyphs() );
	}

	/**
	 * If the search button is enabled, add the inline CSS for it.
	 * @return string
	 * @since 2.2.0
	 */
	protected function search_button_css() {
		$options  = $this->options();
		$menu_css = sprintf( '.ssme-buttons { position: %s; %s: 0; width: %s; }', $this->setting['position'], $options['side'], $options['width'] );
		if ( $this->setting['shrink'] ) {
			return $menu_css;
		}
		$elements = array();
		if ( ! $this->setting['search_button_text'] && ! $this->setting['navigation'] ) {
			$elements[] = '.ssme-buttons > button';
		} elseif ( $this->setting['navigation'] ) {
			$elements[] = 'button.slide-nav-link';
		}
		if ( $this->setting['search_button_text'] ) {
			$elements[] = 'button.ssme-search';
		}
		if ( ! empty( $elements ) ) {
			$menu_css .= sprintf( ' %s { flex-grow: 1; }', implode( ',', $elements ) );
		}
		return $menu_css;
	}

	/**
	 * Build the inline CSS for all :before elements, with their glyphs
	 * @return mixed|void
	 */
	protected function fontawesome_css() {

		if ( ! $this->setting['fontawesome']['glyphs'] ) {
			return '';
		}
		$glyphs = $this->glyphs();

		$fontawesome_css  = '.slide-nav-link:before, .search-me:before, .menu-close:before, .sidr .sub-menu-toggle:before, .ssme-search:before { display: inline-block; font: normal normal normal 20px FontAwesome; margin: 0; text-rendering: auto; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; -webkit-transform: translate(0, 0); -ms-transform: translate(0, 0); transform: translate(0, 0); letter-spacing: 0; vertical-align: middle; }';
		$fontawesome_css .= '.menu-close:before, .sidr .sub-menu-toggle:before { font-size: 16px; }';
		$fontawesome_css .= sprintf( '
			.slide-nav-link:before { content: "%1$s"; }
			.slide-nav-link.menu-open:before { content: "%2$s"; }
			.menu-close:before { content: "%3$s"; }
			.sidr .sub-menu-toggle:before { content: "%4$s"; }
			.sidr .sub-menu-toggle.menu-open:before { content: "%5$s"; }
			.search-me:before, .ssme-search:before { content: "%6$s"; }',
			$glyphs['slide-nav-link'], // %1$s
			$glyphs['slide-nav-link-open'], // %2$s
			$glyphs['menu-close'], // %3$s
			$glyphs['sub-menu-toggle'], // %4$s
			$glyphs['sub-menu-toggle-open'], // %5$s
			$glyphs['search'] // %6$s
		);

		return apply_filters( 'supersideme_modify_glyphs_css', $fontawesome_css, $glyphs );
	}

	/**
	 * Set the background color for panel/button. Hex, with optional rgba, if opacity is set.
	 * @param $background_color string background color (hex value)
	 * @param $opacity string
	 *
	 * @return string
	 * @since 2.0.0
	 */
	protected function background_color( $background_color, $opacity = '' ) {
		$background = sprintf( 'background-color: %s;', $background_color );
		if ( ! $opacity || 100 === $opacity ) {
			return $background;
		}
		$color = $this->hex2rgb( $background_color );
		if ( ! $color ) {
			return $background;
		}
		$converted   = $opacity / 100;
		$background .= sprintf( ' background-color: rgba(%s,%s);', $color, $converted );
		return $background;
	}

	/**
	 * Converts a hex color to rgb values, separated by commas
	 * @param $hex
	 *
	 * @return bool|string false if input is not a 6 digit hex color; string if converted
	 * @since 2.0.0
	 */
	protected function hex2rgb( $hex ) {
		$hex = '#' === $hex[0] ? substr( $hex, 1 ) : $hex;
		if ( 6 !== strlen( $hex ) ) {
			return false;
		}
		$r   = hexdec( substr( $hex, 0, 2 ) );
		$g   = hexdec( substr( $hex, 2, 2 ) );
		$b   = hexdec( substr( $hex, 4, 2 ) );
		$rgb = array( $r, $g, $b );

		return implode( ',', $rgb ); // returns the rgb values separated by commas
	}
}

<?php
/**
 * @package    Internals
 * @since      1.8.0
 * @version    1.8.0
 */

// Avoid direct calls to this file.
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 *****************************************************************
 * Add support for the WP YouTube Player plugin
 *
 * @see      http://wordpress.org/extend/plugins/wp-youtube-player/
 *
 * @internal Last update: July 2014 based upon v 1.7
 */
if ( ! class_exists( 'WPSEO_Video_Plugin_WP_Youtube_Player' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_WP_Youtube_Player
	 */
	class WPSEO_Video_Plugin_WP_Youtube_Player extends WPSEO_Video_Supported_Plugin {


		/**
		 * Conditionally add plugin features to analyse for video content
		 */
		public function __construct() {
			if ( function_exists( 'parseTube' ) ) {
				$this->shortcodes[] = 'tube';
			}
		}


		/**
		 * Analyse a video shortcode from the plugin for usable video information
		 *
		 * @param  string $full_shortcode Full shortcode as found in the post content.
		 * @param  string $sc             Shortcode found.
		 * @param  array  $atts           Shortcode attributes - already decoded if needed.
		 * @param  string $content        The shortcode content, i.e. the bit between [sc]content[/sc].
		 *
		 * @return array   An array with the usable information found or else an empty array.
		 */
		public function get_info_from_shortcode( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			$vid = array();

			if ( isset( $content ) && ( is_string( $content ) && $content !== '' ) ) {
				/*
				 * [tube]http://www.youtube.com/watch?v=AFVlJAi3Cso, 500, 290[/tube]
				 */

				// Split in id, width, height if applicable.
				$list      = explode( ',', $content );
				$id_or_url = trim( $list[0] );
				$atts      = $this->normalize_dimension_attributes( $list, $atts );

				if ( is_string( $id_or_url ) && $id_or_url !== '' ) {
					// Is it a url or an id.
					if ( strpos( $id_or_url, 'http' ) === 0 || strpos( $id_or_url, '//' ) === 0 ) {
						$vid['url'] = $id_or_url;
					}
					elseif ( $this->is_youtube_id( $id_or_url ) ) {
						$vid['id'] = $id_or_url;
					}
				}


				if ( $vid !== array() ) {
					$vid['type'] = 'youtube';
					$vid         = $this->maybe_get_dimensions( $vid, $atts );
				}
			}

			return $vid;
		}
	} /* End of class */

} /* End of class-exists wrapper */

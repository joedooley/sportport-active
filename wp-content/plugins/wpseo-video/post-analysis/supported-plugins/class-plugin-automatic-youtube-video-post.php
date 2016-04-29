<?php
/**
 * @package    Internals
 * @since      1.8.0
 * @version    1.8.0
 */

// Avoid direct calls to this file
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/*******************************************************************
 * Add support for the Automatic YouTube Video Post plugin
 *
 * @see http://wordpress.org/plugins/automatic-youtube-video-posts/
 *
 * @internal Last update: August 2014 based upon v 3.2
 *******************************************************************/
if ( ! class_exists( 'WPSEO_Video_Plugin_Automatic_Youtube_Video_Post' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_Automatic_Youtube_Video_Post
	 */
	class WPSEO_Video_Plugin_Automatic_Youtube_Video_Post extends WPSEO_Video_Supported_Plugin {


		/**
		 * Conditionally add plugin features to analyse for video content
		 */
		public function __construct() {
			if ( function_exists( 'WP_ayvpp_init' ) ) {
				$this->meta_keys[] = '_tern_wp_youtube_video';
			}
		}


		/**
		 * Analyse a specific post meta field for usable video information
		 *
		 * @param  string  $meta_value  The value to analyse
		 * @param  string  $meta_key    The associated meta key
		 * @param  int     $post_id     The id of the post this meta value applies to
		 *
		 * @return array   An array with the usable information found or else an empty array
		 */
		public function get_info_from_post_meta( $meta_value, $meta_key, $post_id ) {
			$vid = array();

			if ( $this->is_youtube_id( $meta_value ) ) {
				$vid['type'] = 'youtube';
				$vid['id']   = $meta_value;

				// From automatic-youtube-video-posts/core/video.php
				$vid['thumbnail_loc'] = 'http://img.youtube.com/vi/' . $meta_value . '/0.jpg';

				// Fall-back default from automatic-youtube-video-posts/conf.php
				$tern_options = get_option( 'tern_wp_youtube' );
				if ( $tern_options !== false && ! empty( $tern_options['dims'][0] ) ) {
					$vid['width'] = $tern_options['dims'][0];
				}
				elseif ( ! empty( $GLOBALS['tern_wp_youtube_options']['dims'][0] ) ) {
					$vid['width'] = $GLOBALS['tern_wp_youtube_options']['dims'][0];
				}
				else {
					$vid['width'] = 506;
				}

				if ( $tern_options !== false && ! empty( $tern_options['dims'][1] ) ) {
					$vid['height'] = $tern_options['dims'][1];
				}
				elseif ( ! empty( $GLOBALS['tern_wp_youtube_options']['dims'][1] ) ) {
					$vid['height'] = $GLOBALS['tern_wp_youtube_options']['dims'][1];
				}
				else {
					$vid['height'] = 304;
				}
			}

			return $vid;
		}

	} /* End of class */

} /* End of class-exists wrapper */

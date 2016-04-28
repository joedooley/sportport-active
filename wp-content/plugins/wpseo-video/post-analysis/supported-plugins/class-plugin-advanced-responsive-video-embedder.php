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
 * Add support for the Advanced Responsive Video Embedder plugin
 *
 * @see http://wordpress.org/plugins/advanced-responsive-video-embedder/
 *
 * @internal Last update: July 2014 based upon v 4.9.0
 *
 * Shortcode list from plugin:
 *   'shortcodes'            => array(
 *       '4players'               => '4players',
 *       'archiveorg'             => 'archiveorg',
 *       'blip'                   => 'blip',
 *       'bliptv'                 => 'bliptv', //* Deprecated
 *       'break'                  => 'break',
 *       'collegehumor'           => 'collegehumor',
 *       'comedycentral'          => 'comedycentral',
 *       'dailymotion'            => 'dailymotion',
 *       'dailymotionlist'        => 'dailymotionlist', // should not be recognized
 *       'flickr'                 => 'flickr',
 *       'funnyordie'             => 'funnyordie',
 *       'gametrailers'           => 'gametrailers',
 *       'iframe'                 => 'iframe',
 *       'ign'                    => 'ign',
 *       'kickstarter'            => 'kickstarter',
 *       'liveleak'               => 'liveleak',
 *       'metacafe'               => 'metacafe',
 *       'movieweb'               => 'movieweb',
 *       'mpora'                  => 'mpora',
 *       'myspace'                => 'myspace',
 *       'myvideo'                => 'myvideo',
 *       'snotr'                  => 'snotr',
 *       'spike'                  => 'spike',
 *       'ted'                    => 'ted',
 *       'twitch'                 => 'twitch',
 *       'ustream'                => 'ustream',
 *       'veoh'                   => 'veoh',
 *       'vevo'                   => 'vevo',
 *       'viddler'                => 'viddler',
 *       'videojug'               => 'videojug',
 *       'vine'                   => 'vine',
 *       'vimeo'                  => 'vimeo',
 *       'xtube'                  => 'xtube',
 *       'yahoo'                  => 'yahoo',
 *       'youtube'                => 'youtube',
 *       'youtubelist'            => 'youtubelist', //* Deprecated and should not be recognized
 *   ),
 *******************************************************************/
if ( ! class_exists( 'WPSEO_Video_Plugin_Advanced_Responsive_Video_Embedder' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_Advanced_Responsive_Video_Embedder
	 */
	class WPSEO_Video_Plugin_Advanced_Responsive_Video_Embedder extends WPSEO_Video_Supported_Plugin {


		/**
		 * Conditionally add plugin features to analyse for video content
		 */
		public function __construct() {
			if ( class_exists( 'Advanced_Responsive_Video_Embedder' ) && method_exists( 'Advanced_Responsive_Video_Embedder', 'get_instance' ) ) {

				// Retrieve the enabled shortcodes the ARVE way
				$arve    = Advanced_Responsive_Video_Embedder::get_instance();
				$options = $arve->get_options();

				// We don't support playlists
				unset( $options['shortcodes']['dailymotionlist'], $options['shortcodes']['youtubelist'] );

				foreach ( $options['shortcodes'] as $provider => $shortcode ) {
					$this->shortcodes[] = $provider;
				}


				$arve_embed_list = $arve->get_regex_list();
				// We don't support playlists
				unset( $arve_embed_list['dailymotionlist'] );

				foreach ( $arve_embed_list as $provider => $regex ) {
					// Fix two service names
					$service = $provider;
					if ( $service === 'youtu_be' ) {
						$service = 'youtube';
					}
					elseif ( $service === 'dai_ly' || $service === 'dailymotion_hub' ) {
						$service = 'dailymotion';
					}

					/* Add the embed keys
					   Handler name => VideoSEO service name */
					$this->video_autoembeds[ 'arve_' . $provider ] = $service;
				}
			}
		}


		/**
		 * Analyse a video shortcode from the plugin for usable video information
		 *
		 * @param  string  $full_shortcode Full shortcode as found in the post content
		 * @param  string  $sc             Shortcode found
		 * @param  array   $atts           Shortcode attributes - already decoded if needed
		 * @param  string  $content        The shortcode content, i.e. the bit between [sc]content[/sc]
		 *
		 * @return array   An array with the usable information found or else an empty array
		 */
		public function get_info_from_shortcode( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			$vid = array();

			// Deal with blip weirdness
			if ( ( $sc === 'blip' || $sc === 'bliptv' ) && ! empty( $atts['id'] ) ) {
				$vid = $this->what_the_blip( $vid, $atts['id'], $full_shortcode );
			}
			elseif ( $sc !== 'iframe' && ! empty( $atts['id'] ) ) {
				$vid['id'] = $atts['id'];
			}
			elseif ( $sc === 'iframe' && ( isset( $atts['id'] ) && is_string( $atts['id'] ) && $atts['id'] !== '' ) ) {
				$vid['url'] = $atts['id'];
			}

			if ( $vid !== array() ) {
				// Only add type if we succesfully found an id/url
				switch ( $sc ) {
					case 'bliptv':
						$vid['type'] = 'blip';
						break;

					case 'iframe':
						$vid['type']        = 'iframe'; // @todo what should this be??? - url iframe embed
						$vid['maybe_local'] = true;
						break;

					default:
						$vid['type'] = $sc;
						break;
				}
			}

			return $vid;
		}

	} /* End of class */

} /* End of class-exists wrapper */
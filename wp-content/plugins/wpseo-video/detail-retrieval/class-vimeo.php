<?php
/**
 * @package    Internals
 * @since      1.7.0
 * @version    1.7.0
 */

// Avoid direct calls to this file
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/*******************************************************************
 * Vimeo Video SEO Details
 *
 * @see https://developer.vimeo.com/
 *
 * JSON response format [2014/7/22]:
 * {
 *    "id":81276708,
 *    "title":"MotoX off-road training by Frans Verhoeven",
 *    "description":"MotoX off-road training door Frans Verhoeven - 5 juli 2013",
 *    "url":"http:\/\/vimeo.com\/81276708",
 *    "upload_date":"2013-12-07 12:25:34",
 *    "mobile_url":"http:\/\/vimeo.com\/m\/81276708",
 *    "thumbnail_small":"http:\/\/i.vimeocdn.com\/video\/457375046_100x75.jpg",
 *    "thumbnail_medium":"http:\/\/i.vimeocdn.com\/video\/457375046_200x150.jpg",
 *    "thumbnail_large":"http:\/\/i.vimeocdn.com\/video\/457375046_640.jpg",
 *    "user_id":3329492,"user_name":"Mars Publishers",
 *    "user_url":"http:\/\/vimeo.com\/marspublishers",
 *    "user_portrait_small":"http:\/\/i.vimeocdn.com\/portrait\/7555976_30x30.jpg",
 *    "user_portrait_medium":"http:\/\/i.vimeocdn.com\/portrait\/7555976_75x75.jpg",
 *    "user_portrait_large":"http:\/\/i.vimeocdn.com\/portrait\/7555976_100x100.jpg",
 *    "user_portrait_huge":"http:\/\/i.vimeocdn.com\/portrait\/7555976_300x300.jpg",
 *    "stats_number_of_likes":0,
 *    "stats_number_of_plays":85,
 *    "stats_number_of_comments":0,
 *    "duration":220,
 *    "width":1280,
 *    "height":720,
 *    "tags":"motox, frans verhoeven, enduro, offroad, off-road, ktm, yamaha",
 *    "embed_privacy":"approved"
 * }
 *******************************************************************/
if ( ! class_exists( 'WPSEO_Video_Details_Vimeo' ) ) {

	/**
	 * Class WPSEO_Video_Details_Vimeo
	 */
	class WPSEO_Video_Details_Vimeo extends WPSEO_Video_Details {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		//protected $id_regex = '';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = 'http://vimeo.com/%s';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			//'pattern'       => 'http://vimeo.com/api/oembed.json?url=%s',
			'pattern'       => 'http://vimeo.com/api/v2/video/%s.json',
			'replace_key'   => 'id',
			'response_type' => 'json',
		);


		/**
		 * Retrieve the video id from a known video url based on a regex match.
		 *
		 * @param int $match_nr The captured parenthesized sub-pattern to use from matches. Defaults to 1.
		 *
		 * @return void
		 */
		protected function determine_video_id_from_url( $match_nr = 1 ) {
			if ( isset( $this->vid['url'] ) && is_string( $this->vid['url'] ) && $this->vid['url'] !== '' ) {
				if ( preg_match( '`vimeo\.com/(?:(?:m|video|channels|groups)/(?:[a-z0-9]+/)*)?([0-9]+)(?:$|[/#\?])`i', $this->vid['url'], $match ) ) {
					$this->vid['id'] = $match[ $match_nr ];
				}
				elseif ( preg_match( '`vimeo\.com/moogaloop\.swf\?clip_id=([^&]+)`i', $this->vid['url'], $match ) ) {
					$this->vid['id'] = $match[ $match_nr ];
				}
			}
		}


		/**
		 * Decode a remote response as json
		 */
		protected function decode_as_json() {
			$response = json_decode( $this->remote_response );
			if ( is_array( $response ) && ! empty ( $response[0] ) && is_object( $response[0] ) ) {
				$this->decoded_response = $response[0];
			}
		}


		/**
		 * Check to see if this is really a video.
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			return ( ! empty( $this->decoded_response ) );
		}


		/**
		 * Set the video duration
		 */
		protected function set_duration() {
			$this->set_duration_from_json_object();
		}


		/**
		 * Set the video height
		 */
		protected function set_height() {
			$this->set_height_from_json_object();
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = $this->url_encode( 'https://www.vimeo.com/moogaloop.swf?clip_id=' . $this->vid['id'] );
			}
		}


		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			if ( isset( $this->decoded_response->thumbnail_large ) && is_string( $this->decoded_response->thumbnail_large ) && $this->decoded_response->thumbnail_large !== '' ) {
				$image = $this->make_image_local( $this->decoded_response->thumbnail_large );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			$this->set_width_from_json_object();
		}


		/**
		 * Set the video view count
		 */
		protected function set_view_count() {
			if ( ! empty( $this->decoded_response->stats_number_of_plays ) ) {
				$this->vid['view_count'] = $this->decoded_response->stats_number_of_plays;
			}
		}

	} /* End of class */

} /* End of class-exists wrapper */
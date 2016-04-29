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
 * 23video Video SEO Details
 *
 * @see http://www.23video.com/api/photo-list
 *
 * Oembed info is also available on https://yoast.23video.com/oembed?format=json&url=%s
 *
 * JSON response format [2014/08/02] - see below class.
 *******************************************************************/
if ( ! class_exists( 'WPSEO_Video_Details_23video' ) ) {

	/**
	 * Class WPSEO_Video_Details_23video
	 */
	class WPSEO_Video_Details_23video extends WPSEO_Video_Details {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`://([a-z0-9]+)\.23video\.com/(?:[^\?]+\?.*?photo(?:_|%5f)id=|(?:[a-z]+/)*)([0-9]+)(?:$|[/#\?])`i';

		/**
		 * @var string  Regular expression to retrieve the permalink part from a known video url
		 */
		protected $permalink_regex = '`://([a-z0-9]+)\.23video\.com/([a-z0-9_-]+)$`i';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		//protected $url_template = '';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'http://videos.23video.com/api/photo/list?format=json&photo_id=%s&video_p=1',
			'replace_key'   => 'id',
			'response_type' => 'json',
		);

		/**
		 * @var	array	Alternate remote url for when the video id is unknown
		 */
		protected $alternate_remote = array(
			'pattern'       => 'http://videos.23video.com/api/photo/list?format=json&search=%s&video_p=1',
			'replace_key'   => 'permalink',
			'response_type' => 'json',
		);


		/**
		 * Instantiate the class, main routine.
		 *
		 * @param array  $vid     The video array with all the data.
		 * @param array  $old_vid The video array with all the data of the previous "fetch", if available.
		 *
		 * @return \WPSEO_Video_Details_23video
		 */
		public function __construct( $vid, $old_vid = array() ) {
			// @todo Deal with custom domains
			parent::__construct( $vid, $old_vid );
		}


		/**
		 * Retrieve the video id or the permalink from a known video url based on a regex match
		 *
		 * @param  int $match_nr  The captured parenthesized sub-pattern to use from matches.
		 *
		 * @return void
		 */
		protected function determine_video_id_from_url( $match_nr = 2 ) {
			if ( ( is_string( $this->vid['url'] ) && $this->vid['url'] !== '' ) && $this->id_regex !== '' ) {
				if ( preg_match( $this->id_regex, $this->vid['url'], $match ) ) {
					$this->vid['id']        = $match[ $match_nr ];
					$this->vid['subdomain'] = $match[1];
				}
				elseif ( preg_match( $this->permalink_regex, $this->vid['url'], $match ) ) {
					$this->vid['permalink'] = $match[ $match_nr ];
					$this->vid['subdomain'] = $match[1];
				}
			}
		}


		/**
		 * Retrieve information on a video via a remote API call
		 */
		protected function get_remote_video_info() {
			if ( empty( $this->vid['id'] ) && ! empty( $this->vid['permalink'] ) ) {
				$this->remote_url = $this->alternate_remote;
			}
			if ( ! empty( $this->vid['subdomain'] ) ) {
				$replace                     = '://' . $this->vid['subdomain'] . '.';
				$this->remote_url['pattern'] = str_replace( '://videos.', $replace, $this->remote_url['pattern'] );
			}
			parent::get_remote_video_info();
		}


		/**
		 * Decode a remote response for a number of typical response types
		 */
		protected function decode_remote_video_info() {
			if ( ! empty( $this->remote_response ) ) {
				// Get rid of the 'var visual = ' string before the actual json output
				$this->remote_response = substr( $this->remote_response, strpos( $this->remote_response, '{' ) );
			}
			parent::decode_remote_video_info();
		}


		/**
		 * Check to see if this is really a video and for the right item.
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			$valid = false;

			if ( ! empty( $this->decoded_response ) ) {

				// Check whether we received a valid response based on how we retrieved it
				switch ( $this->remote_url['replace_key'] ) {

					case 'id':
						if ( ! empty( $this->decoded_response->photo->photo_id ) && $this->decoded_response->photo->photo_id === $this->vid['id'] ) {
							$valid = true;
						}
						break;

					case 'permalink':
						if ( ! empty( $this->decoded_response->photo->one ) && $this->decoded_response->photo->one === '/' . $this->vid['permalink'] ) {
							$valid = true;
						}
						elseif ( isset( $this->decoded_response->photos ) && is_array( $this->decoded_response->photos ) && $this->decoded_response->photos !== array() ) {
							// Walk through the (first page of the) search results and see if we can find a match
							foreach ( $this->decoded_response->photos as $photo ) {
								if ( $photo->one === '/' . $this->vid['permalink'] ) {
									$this->decoded_response->photo = $photo;
									$valid                         = true;
									break;
								}
							}
						}
						break;
				}
			}
			return $valid;
		}


		/**
		 * Set video details to their new values
		 */
		protected function put_video_details() {
			$this->set_subdomain();
			parent::put_video_details();
		}


		/**
		 * Set the content location
		 *
		 * @internal -> if this is changed to another property, the width/height properties need to change too
		 * Alternative set could be video_medium_download / video_medium_width / video_medium_height
		 */
		protected function set_content_loc() {
			if ( ! empty( $this->decoded_response->photo->standard_download ) && ! empty( $this->vid['subdomain'] ) ) {
				$this->vid['content_loc'] = 'http://' . rawurlencode( $this->vid['subdomain'] ) . '.23video.com' . $this->decoded_response->photo->standard_download; // extension-less, could add .mp4, should work most of the time
			}
		}


		/**
		 * Set the video duration
		 */
		protected function set_duration() {
			if ( ! empty( $this->decoded_response->photo->video_length ) ) {
				$this->vid['duration'] = ( $this->decoded_response->photo->video_length );
			}
		}


		/**
		 * Set the video height
		 */
		protected function set_height() {
			if ( ! empty( $this->decoded_response->photo->standard_height ) ) {
				$this->vid['height'] = $this->decoded_response->photo->standard_height;
			}
		}


		/**
		 * Set the video id (as it might not be set - permalink based retrieval)
		 */
		protected function set_id() {
			if ( ! empty( $this->decoded_response->photo->photo_id ) ) {
				$this->vid['id'] = $this->decoded_response->photo->photo_id;
			}
		}


		/**
		 * Set the player location
		 *
		 * @internal Alternative options:
		 * https://[subdomain].23video.com/v.swf?photo_id=[photo->photo_id]&autoPlay=1
		 * https://[subdomain].23video.com/[photo->tree_id].ihtml?photo_id=[photo->photo_id]&token=[photo->token]&autoPlay=1&defaultQuality=high
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) && ! empty( $this->vid['subdomain'] ) ) {
				$this->vid['player_loc'] = 'http://' . rawurlencode( $this->vid['subdomain'] ) . '.23video.com/v.ihtml/player.html?source=share&photo_id=' . urlencode( $this->vid['id'] ) . '&autoPlay=0';
			}
		}


		/**
		 * Verify and set the subdomain
		 */
		protected function set_subdomain() {
			if ( ! empty( $this->decoded_response->site->domain ) && $this->decoded_response->site->domain !== $this->vid['subdomain'] . '.23video.com' ) {
				$this->vid['subdomain'] = str_replace( '.23video.com', '', $this->decoded_response->site->domain );
			}
		}


		/**
		 * Set the thumbnail location
		 *
		 * @internal Possible alternative:
		 * https://[subdomain].23video.com/[photo->tree_id]/[photo->photo_id]/[photo->token]/large
		 */
		protected function set_thumbnail_loc() {
			if ( isset( $this->decoded_response->photo->video_frames_download ) && ( is_string( $this->decoded_response->photo->video_frames_download ) && $this->decoded_response->photo->video_frames_download !== '' ) && ! empty( $this->vid['subdomain'] ) ) {
				$url   = 'http://' . rawurlencode( $this->vid['subdomain'] ) . '.23video.com' . $this->decoded_response->photo->video_frames_download;
				$image = $this->make_image_local( $url );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
		}


		/**
		 * Set the video view count
		 */
		protected function set_view_count() {
			if ( ! empty( $this->decoded_response->photo->view_count ) ) {
				$this->vid['view_count'] = $this->decoded_response->photo->view_count;
			}
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			if ( ! empty( $this->decoded_response->photo->standard_width ) ) {
				$this->vid['width'] = $this->decoded_response->photo->standard_width;
			}
		}

	} /* End of class */

} /* End of class-exists wrapper */

/**
 * Remote response (JSON) format [2014/08/02]:
 *
var visual = {
  "status": "ok",
  "permission_level":"anonymous",
  "cached":"1",
  "cache_time":"1406310447",
  "photos":[
    {
		"photo_id": "7190971",
		"title": "Customization",
		"tree_id": "4959050",
		"token": "03406158f3f227a567928804fe4817f3",
		"protected_p": 0,
		"protection_method": "",
		"album_id": "454193",
		"album_title": "Product guides",
		"album_hide_p": 0,
		"all_albums": "454193",
		"published_p": 1,
		"one": "/customization",
		"publish_date_ansi": "2012-11-05 16:31:48",
		"publish_date_epoch": "1352129508",
		"publish_date__date": "November 05, 2012",
		"publish_date__time": "04:31 PM",
		"creation_date_ansi": "2012-10-18 05:29:38",
		"creation_date_epoch": "1350530978",
		"creation_date__date": "October 18, 2012",
		"creation_date__time": "05:29 AM",
		"original_date_ansi": "2012-10-18 05:29:38",
		"original_date__date": "October 18, 2012",
		"original_date__time": "05:29 AM",
		"view_count": "2604",
		"avg_playtime": "78.8439701181352",
		"number_of_comments": "0",
		"number_of_albums": "1",
		"number_of_tags": "3",
		"photo_rating": "",
		"number_of_ratings": "0",
		"video_p": 1,
		"video_encoded_p": 1,
		"audio_p": 0,
		"video_length": "47.44",
		"text_only_p": 0,
		"user_id": "7188667",
		"username": "Kure",
		"display_name": "Mathias Kure",
		"user_url": "/user/Kure/",
		"subtitles_p": 0,
		"sections_p": 0,
		"license_id": "",
		"coordinates": "",
		"absolute_url": "https://videos.23video.com/customization",
		"video_hls_size": "0",
		"video_hls_download": "",
		"original_width": "1920",
		"original_height": "1080",
		"original_size": "87677",
		"original_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/original",
		"quad16_width": "16",
		"quad16_height": "16",
		"quad16_size": "390",
		"quad16_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/quad16",
		"quad50_width": "50",
		"quad50_height": "50",
		"quad50_size": "1163",
		"quad50_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/quad50",
		"quad75_width": "75",
		"quad75_height": "75",
		"quad75_size": "1892",
		"quad75_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/quad75",
		"quad100_width": "100",
		"quad100_height": "100",
		"quad100_size": "2800",
		"quad100_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/quad100",
		"small_width": "200",
		"small_height": "113",
		"small_size": "4303",
		"small_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/small",
		"medium_width": "512",
		"medium_height": "288",
		"medium_size": "16874",
		"medium_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/medium",
		"portrait_width": "300",
		"portrait_height": "169",
		"portrait_size": "7712",
		"portrait_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/portrait",
		"standard_width": "600",
		"standard_height": "338",
		"standard_size": "21805",
		"standard_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/standard",
		"large_width": "800",
		"large_height": "450",
		"large_size": "33868",
		"large_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/large",
		"video_medium_width": "640",
		"video_medium_height": "360",
		"video_medium_size": "6391926",
		"video_medium_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_medium/customization-video.mp4",
		"video_hd_width": "1280",
		"video_hd_height": "720",
		"video_hd_size": "9941487",
		"video_hd_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_hd/customization-video.mp4",
		"video_1080p_width": "1920",
		"video_1080p_height": "1080",
		"video_1080p_size": "19111487",
		"video_1080p_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_1080p/customization-video.mp4",
		"video_4k_width": "",
		"video_4k_height": "",
		"video_4k_size": "0",
		"video_4k_download": "",
		"video_frames_width": "320",
		"video_frames_height": "180",
		"video_frames_size": "388064",
		"video_frames_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_frames/customization-frames.jpg",
		"video_wmv_width": "",
		"video_wmv_height": "",
		"video_wmv_size": "0",
		"video_wmv_download": "",
		"video_mobile_h263_amr_width": "176",
		"video_mobile_h263_amr_height": "141",
		"video_mobile_h263_amr_size": "605297",
		"video_mobile_h263_amr_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_mobile_h263_amr/customization-video.3gp",
		"video_mobile_h263_aac_width": "176",
		"video_mobile_h263_aac_height": "141",
		"video_mobile_h263_aac_size": "555106",
		"video_mobile_h263_aac_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_mobile_h263_aac/customization-video.3gp",
		"video_mobile_mpeg4_amr_width": "176",
		"video_mobile_mpeg4_amr_height": "141",
		"video_mobile_mpeg4_amr_size": "578705",
		"video_mobile_mpeg4_amr_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_mobile_mpeg4_amr/customization-video.3gp",
		"video_mobile_high_width": "320",
		"video_mobile_high_height": "180",
		"video_mobile_high_size": "2136874",
		"video_mobile_high_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_mobile_high/customization-video.mp4",
		"audio_width": "",
		"audio_height": "",
		"audio_size": "286186",
		"audio_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/audio/customization-audio.mp3",
		"video_webm_360p_width": "640",
		"video_webm_360p_height": "360",
		"video_webm_360p_size": "5034897",
		"video_webm_360p_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_webm_360p/customization-video.webm",
		"video_webm_720p_width": "1280",
		"video_webm_720p_height": "720",
		"video_webm_720p_size": "9004199",
		"video_webm_720p_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_webm_720p/customization-video.webm",
		"content": "Creating an amazing video website doesn't need to be hard. Quite the contrary - choose a theme, upload your logo, polish your branded player -- and you are good to go!", "content_text": "Creating an amazing video website doesn't need to be hard. Quite the contrary - choose a theme, upload your logo, polish your branded player -- and you are good to go!",
		"before_download_type": "",
		"before_link": "",
		"before_download_url": "",
		"after_download_type": "",
		"after_link": "",
		"after_download_url": "",
		"after_text": "",
		"tags": [
			"customization",
			"steffen tiedemann christensen",
			"themes"
		]
	}
  ],
  "photo":
  {
  	"photo_id": "7190971",
	"title": "Customization",
	"tree_id": "4959050",
	"token": "03406158f3f227a567928804fe4817f3",
	"protected_p": 0,
	"protection_method": "",
	"album_id": "454193",
	"album_title": "Product guides",
	"album_hide_p": 0,
	"all_albums": "454193",
	"published_p": 1,
	"one": "/customization",
	"publish_date_ansi": "2012-11-05 16:31:48",
	"publish_date_epoch": "1352129508",
	"publish_date__date": "November 05, 2012",
	"publish_date__time": "04:31 PM",
	"creation_date_ansi": "2012-10-18 05:29:38",
	"creation_date_epoch": "1350530978",
	"creation_date__date": "October 18, 2012",
	"creation_date__time": "05:29 AM",
	"original_date_ansi": "2012-10-18 05:29:38",
	"original_date__date": "October 18, 2012",
	"original_date__time": "05:29 AM",
	"view_count": "2604",
	"avg_playtime": "78.8439701181352",
	"number_of_comments": "0",
	"number_of_albums": "1",
	"number_of_tags": "3",
	"photo_rating": "",
	"number_of_ratings": "0",
	"video_p": 1,
	"video_encoded_p": 1,
	"audio_p": 0,
	"video_length": "47.44",
	"text_only_p": 0,
	"user_id": "7188667",
	"username": "Kure",
	"display_name": "Mathias Kure",
	"user_url": "/user/Kure/",
	"subtitles_p": 0,
	"sections_p": 0,
	"license_id": "",
	"coordinates": "",
	"absolute_url": "https://videos.23video.com/customization",
	"video_hls_size": "0",
	"video_hls_download": "",
	"original_width": "1920",
	"original_height": "1080",
	"original_size": "87677",
	"original_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/original",
	"quad16_width": "16",
	"quad16_height": "16",
	"quad16_size": "390",
	"quad16_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/quad16",
	"quad50_width": "50",
	"quad50_height": "50",
	"quad50_size": "1163",
	"quad50_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/quad50",
	"quad75_width": "75",
	"quad75_height": "75",
	"quad75_size": "1892",
	"quad75_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/quad75",
	"quad100_width": "100",
	"quad100_height": "100",
	"quad100_size": "2800",
	"quad100_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/quad100",
	"small_width": "200",
	"small_height": "113",
	"small_size": "4303",
	"small_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/small",
	"medium_width": "512",
	"medium_height": "288",
	"medium_size": "16874",
	"medium_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/medium",
	"portrait_width": "300",
	"portrait_height": "169",
	"portrait_size": "7712",
	"portrait_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/portrait",
	"standard_width": "600",
	"standard_height": "338",
	"standard_size": "21805",
	"standard_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/standard",
	"large_width": "800",
	"large_height": "450",
	"large_size": "33868",
	"large_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/large",
	"video_medium_width": "640",
	"video_medium_height": "360",
	"video_medium_size": "6391926",
	"video_medium_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_medium/customization-video.mp4",
	"video_hd_width": "1280",
	"video_hd_height": "720",
	"video_hd_size": "9941487",
	"video_hd_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_hd/customization-video.mp4",
	"video_1080p_width": "1920",
	"video_1080p_height": "1080",
	"video_1080p_size": "19111487",
	"video_1080p_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_1080p/customization-video.mp4",
	"video_4k_width": "",
	"video_4k_height": "",
	"video_4k_size": "0",
	"video_4k_download": "",
	"video_frames_width": "320",
	"video_frames_height": "180",
	"video_frames_size": "388064",
	"video_frames_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_frames/customization-frames.jpg",
	"video_wmv_width": "",
	"video_wmv_height": "",
	"video_wmv_size": "0",
	"video_wmv_download": "",
	"video_mobile_h263_amr_width": "176",
	"video_mobile_h263_amr_height": "141",
	"video_mobile_h263_amr_size": "605297",
	"video_mobile_h263_amr_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_mobile_h263_amr/customization-video.3gp",
	"video_mobile_h263_aac_width": "176",
	"video_mobile_h263_aac_height": "141",
	"video_mobile_h263_aac_size": "555106",
	"video_mobile_h263_aac_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_mobile_h263_aac/customization-video.3gp",
	"video_mobile_mpeg4_amr_width": "176",
	"video_mobile_mpeg4_amr_height": "141",
	"video_mobile_mpeg4_amr_size": "578705",
	"video_mobile_mpeg4_amr_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_mobile_mpeg4_amr/customization-video.3gp",
	"video_mobile_high_width": "320",
	"video_mobile_high_height": "180",
	"video_mobile_high_size": "2136874",
	"video_mobile_high_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_mobile_high/customization-video.mp4",
	"audio_width": "",
	"audio_height": "",
	"audio_size": "286186",
	"audio_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/audio/customization-audio.mp3",
	"video_webm_360p_width": "640",
	"video_webm_360p_height": "360",
	"video_webm_360p_size": "5034897",
	"video_webm_360p_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_webm_360p/customization-video.webm",
	"video_webm_720p_width": "1280",
	"video_webm_720p_height": "720",
	"video_webm_720p_size": "9004199",
	"video_webm_720p_download": "/4959050/7190971/03406158f3f227a567928804fe4817f3/video_webm_720p/customization-video.webm",
	"content": "Creating an amazing video website doesn't need to be hard. Quite the contrary - choose a theme, upload your logo, polish your branded player -- and you are good to go!", "content_text": "Creating an amazing video website doesn't need to be hard. Quite the contrary - choose a theme, upload your logo, polish your branded player -- and you are good to go!",
	"before_download_type": "",
	"before_link": "",
	"before_download_url": "",
	"after_download_type": "",
	"after_link": "",
	"after_download_url": "",
	"after_text": "",
	"tags":
	[
		"customization",
		"steffen tiedemann christensen",
		"themes"
	]
  },
  "p": "1",
  "size": "1",
  "total_count": "1",
  "site": {
  	"setup_date": "2009-05-11 ",
	"license_id": "0",
	"domain": "videos.23video.com",
	"product_key": "tube",
	"site_id": "454054",
	"allow_signup_p": 0,
	"secure_domain": "videos.23video.com",
	"site_name": "Videos - 23 Video",
	"site_key": "produktsite"
  },
  "endpoint": "/api/photo/list"
}
*/

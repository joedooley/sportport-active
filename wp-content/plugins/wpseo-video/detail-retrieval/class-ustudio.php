<?php
/**
 * @package    Internals
 * @since      x.x.x
 * @version    x.x.x
 */

// Avoid direct calls to this file.
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 *****************************************************************
 * uStudio Video SEO Details
 */
if ( ! class_exists( 'WPSEO_Video_Details_Ustudio' ) ) {

	/**
	 * Class WPSEO_Video_Details_Ustudio
	 */
	class WPSEO_Video_Details_Ustudio extends WPSEO_Video_Details {

		/**
		 * @var string Regular expression to retrieve a video id from a known video url. The id for use in this module is combo of {destination_id/video_id} (include '/')
		 */
		protected $id_regex = '`ustudio\.com/embed/([/a-zA-Z0-9]+)`i';


		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = 'https://app.ustudio.com/embed/%s';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'https://app.ustudio.com/embed/%s/config.json',
			'replace_key'   => 'id',
			'response_type' => 'json',
		);



		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = 'https://app.ustudio.com/embed/' . $this->vid['id'];
			}
		}

		/**
		 * Pull the first video from the details, if exists
		 */
		protected function get_decoded_video() {
			if ( ! empty( $this->decoded_response->videos ) ) {
				return $this->decoded_response->videos[0];
			}
			return false;
		}

		/**
		 * Pull the largest (widest) transcode, preferably mp4
		 */
		protected function get_transcode() {
			$transcode = false;
			if ( $video = $this->get_decoded_video() ) {
				if ( ! empty( $video->transcodes ) ) {
					foreach ( $video->transcodes as $format => $items ) {
						foreach ( $items as $item ) {
							$item->format = $format;
							if ( ! $transcode ) {
								// For starters, use the first transcode we find.
								$transcode = $item;
							}
							elseif ( $format == 'mp4' && $transcode->format != 'mp4' ) {
								// If item is mp4 and best transcode isn't, use the mp4.
								$transcode = $item;
							}
							elseif ( $item->width > $transcode->width ) {
								// If item is wider, use it.
								$transcode = $item;
							}
						}
					}
				}
			}
			return $transcode;
		}

		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			if ( $video = $this->get_decoded_video() ) {
				if ( ! empty( $video->images ) ) {
					foreach ( $video->images as $image ) {
						if ( $image->type == 'poster' ) {
							$local_img = $this->make_image_local( $image->image_url );
							if ( is_string( $local_img ) && $local_img !== '' ) {
								$this->vid['thumbnail_loc'] = $local_img;
								return;
							}
						}
					}
				}
			}
		}

		/**
		 * Set the duration
		 */
		protected function set_duration() {
			if ( $video = $this->get_decoded_video() ) {
				if ( ! empty( $video->duration ) && is_numeric( $video->duration ) ) {
					$this->vid['duration'] = $video->duration;
				}
			}
		}

		/**
		 * Set the video height
		 */
		protected function set_height() {
			if ( $transcode = $this->get_transcode() ) {
				if ( ! empty( $transcode->height ) && is_numeric( $transcode->height ) ) {
					$this->vid['height'] = $transcode->height;
				}
			}
		}

		/**
		 * Set the video width
		 */
		protected function set_width() {
			if ( $transcode = $this->get_transcode() ) {
				if ( ! empty( $transcode->width ) && is_numeric( $transcode->width ) ) {
					$this->vid['width'] = $transcode->width;
				}
			}
		}

		/**
		 * Set the location of the content
		 */
		protected function set_content_loc() {
			if ( $transcode = $this->get_transcode() ) {
				if ( ! empty( $transcode->url ) ) {
					$this->vid['content_loc'] = $transcode->url;
				}
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */

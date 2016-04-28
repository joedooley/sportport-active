<?php
/**
 * @package    Internals
 * @since      x.x.x
 * @version    x.x.x
 */

// Avoid direct calls to this file
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/*******************************************************************
 * Cloudup Video SEO Details
 *
 * Should be easy to add & get to work as uses oembed, only problem is, I haven't found sample urls with video data
 * yet, so nothing to test with.
 *
 * @see https://dev.cloudup.com/#oembed
 *
 * JSON response format [2014/7/22]:

 * }
 *******************************************************************/
if ( ! class_exists( 'WPSEO_Video_Details_Cloudup' ) ) {

	/**
	 * Class WPSEO_Video_Details_Cloudup
	 */
	class WPSEO_Video_Details_Cloudup extends WPSEO_Video_Details_Oembed {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`[/\.]cloudup\.com/([a-z0-9]+)$`i';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 * @internal Set to embed as it gives better retrieval results compared to video!
		 */
		protected $url_template = 'http://cloudup.com/%s/';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'https://cloudup.com/oembed?url=%s',
			'replace_key'   => 'url',
			'response_type' => 'json',
		);


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			/*if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = '';
			}*/
		}

	} /* End of class */

} /* End of class-exists wrapper */
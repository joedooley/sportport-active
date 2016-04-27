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
 * Flowplayer Video SEO Details
 *
 * Maybe.... The Flowplayer plugin has a class which accesses the API, but only to retrieve a list of videos.
 * There is a js API, but looks only to be to control the player, not to retrieve info.
 * Other than that, no documentation can be found at all about the API.... sigh...
 *
 * @see flowplayer5\admin\flowplayer-drive\class-flowplayer-drive.php for inspiration.
 * @see https://flowplayer.org/docs/api.html
 * @see https://flowplayer.org/forum/#!/scripting_api
 *
 *
 * JSON response format [2014/7/22]:

 * }
 *******************************************************************/
if ( ! class_exists( 'WPSEO_Video_Details_Flowplayer' ) ) {

	/**
	 * Class WPSEO_Video_Details_Flowplayer
	 */
	class WPSEO_Video_Details_Flowplayer extends WPSEO_Video_Details {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		//protected $id_regex = '``i';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 * @internal Set to embed as it gives better retrieval results compared to video!
		 */
		//protected $url_template = '';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		/*protected $remote_url = array(
			'pattern'       => 'http://videos.api.flowplayer.org/account',
			'replace_key'   => 'url',
			'response_type' => 'json',
		);*/


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			/*if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = '';
			}*/
		}

		protected function set_thumbnail_loc() {
			return;
		}

	} /* End of class */

} /* End of class-exists wrapper */
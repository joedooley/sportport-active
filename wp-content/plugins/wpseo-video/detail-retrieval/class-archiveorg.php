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
 * The Internet Archive - Archive.org Video SEO Details
 *
 * @see https://archive.org/help/video.php
 * @see http://archive.org/help/json.php
 * @see http://api-portal.anypoint.mulesoft.com/internet-archive/api/internet-archive-json-api/docs/reference
 *
 * JSON response format [2014/7/27] - see below class.
 *******************************************************************/
if ( ! class_exists( 'WPSEO_Video_Details_Archiveorg' ) ) {

	/**
	 * Class WPSEO_Video_Details_Archiveorg
	 *
	 * @internal - no $id_regex has been set as both plugins which support archive.org should return an id anyway
	 * If this changes, this may need looking into.
	 */
	class WPSEO_Video_Details_Archiveorg extends WPSEO_Video_Details {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		//protected $id_regex = '';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = 'https://archive.org/details/%s';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'https://archive.org/details/%s?output=json',
			'replace_key'   => 'id',
			'response_type' => 'json',
		);

		/**
		 * @var object  The file from the files array which contains most data we need
		 */
		private $video_file;


		/**
		 * Check if the response is for a video
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			return ( ! empty( $this->decoded_response ) && ( ( isset( $this->decoded_response->metadata->mediatype[0] ) && $this->decoded_response->metadata->mediatype[0] === 'movies' ) || ( isset( $this->decoded_response->misc->css ) && $this->decoded_response->misc->css === 'movies' ) ) );
		}


		/**
		 * Set video details to their new values
		 */
		protected function put_video_details() {
			$this->get_video_file_data();
			parent::put_video_details();
		}


		/**
		 * Determine which file in the files array contains the information we need.
		 */
		protected function get_video_file_data() {
			$video_files = array();

			// Get the video files from the files object
			if ( ! empty( $this->decoded_response->files ) ) {
				foreach ( $this->decoded_response->files as $key => $value ) {
					if ( preg_match( '`\.(' . WPSEO_Video_Sitemap::$video_ext_pattern . ')$`', $key, $match ) ) {
						$video_files[ $match[1] ]            = $value;
						$video_files[ $match[1] ]->file_name = substr( $key, 1 ); // strip off the '/' at the start
					}
				}
			}

			// Find a file with enriched data
			if ( $video_files !== array() ) {
				// Preferred extensions (sort of) in order of preference
				$video_exts = explode( '|', WPSEO_Video_Sitemap::$video_ext_pattern );

				foreach ( $video_exts as $ext ) {
					if ( isset( $video_files[ $ext ] ) ) {
						if ( ! isset( $this->video_file ) ) {
							// Set to the file with the first matched (most preferred) extension
							$this->video_file = $video_files[ $ext ];

							if ( ( ! empty( $video_files[ $ext ]->length ) || ! empty( $this->decoded_response->metadata->runtime[0] ) ) && ( ! empty( $video_files[ $ext ]->height ) || ! empty( $this->decoded_response->metadata->width[0] ) ) && ( ! empty( $video_files[ $ext ]->width ) || ! empty( $this->decoded_response->metadata->height[0] ) ) ) {
								break; // we got all the data we need
							}
						}
						else {
							if ( empty( $this->video_file->length ) && ! empty( $video_files[ $ext ]->length ) ) {
								$this->video_file->length = $video_files[ $ext ]->length;
							}

							if ( empty( $this->video_file->width ) && empty( $this->video_file->height ) ) {
								if ( ! empty( $video_files[ $ext ]->width ) ) {
									$this->video_file->width = $video_files[ $ext ]->width;
								}
								if ( ! empty( $video_files[ $ext ]->height ) ) {
									$this->video_file->height = $video_files[ $ext ]->height;
								}
							}

							if ( ! empty( $this->video_file->length ) && ( ! empty( $this->video_file->width ) || ! empty( $this->video_file->height ) ) ) {
								break; // we have as much data as we can have
							}
						}
					}
				}
			}
		}


		/**
		 * Set the content location
		 */
		protected function set_content_loc() {
			if ( ! empty( $this->vid['id'] ) && ! empty( $this->video_file->file_name ) ) {
				$this->vid['content_loc'] = sprintf(
					'https://archive.org/download/%s/%s',
					rawurlencode( $this->vid['id'] ),
					rawurlencode( $this->video_file->file_name )
				);
			}
		}


		/**
		 * Set the video duration
		 */
		protected function set_duration() {
			if ( ! empty( $this->decoded_response->metadata->runtime[0] ) ) {
				// 31 seconds
				$this->vid['duration'] = str_replace( ' seconds', '', $this->decoded_response->metadata->runtime[0] );
			}
			elseif ( ! empty( $this->video_file->length ) ) {
				$this->vid['duration'] = $this->video_file->length;
			}
		}


		/**
		 * Set the video height
		 */
		protected function set_height() {
			if ( ! empty( $this->decoded_response->metadata->height[0] ) ) {
				$this->vid['height'] = $this->decoded_response->metadata->height[0];
			}
			elseif ( ! empty( $this->video_file->height ) ) {
				$this->vid['height'] = $this->video_file->height;
			}
		}


		/**
		 * Set the video id
		 */
		protected function set_id() {
			if ( ! empty( $this->decoded_response->metadata->identifier[0] ) ) {
				$this->vid['id'] = $this->decoded_response->metadata->identifier[0];
			}
		}

		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = 'https://archive.org/embed/' . rawurlencode( $this->vid['id'] );
			}
		}


		/**
		 * Set the thumbnail location
		 *
		 * @todo decide whether the order is correct - should the thumb permalink be tried first or the misc image ?
		 */
		protected function set_thumbnail_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$image_url = sprintf( 'https://archive.org/download/%s/format=Thumbnail', $this->vid['id'] );
				$image     = $this->make_image_local( $image_url );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
				elseif ( isset( $this->decoded_response->misc->image ) && ( is_string( $this->decoded_response->misc->image ) && $this->decoded_response->misc->image !== '' ) ) {
					$image = $this->make_image_local( $this->decoded_response->misc->image );
					if ( is_string( $image ) && $image !== '' ) {
						$this->vid['thumbnail_loc'] = $image;
					}
				}
			}
		}


		/**
		 * Set the video view count
		 *
		 * @todo [JRF -> Yoast] is using the download count acceptable here ?
		 */
		protected function set_view_count() {
			if ( ! empty( $this->decoded_response->item->downloads ) ) {
				$this->vid['view_count'] = $this->decoded_response->item->downloads;
			}
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			if ( ! empty( $this->decoded_response->metadata->width[0] ) ) {
				$this->vid['width'] = $this->decoded_response->metadata->width[0];
			}
			elseif ( ! empty( $this->video_file->width ) ) {
				$this->vid['width'] = $this->video_file->width;
			}
		}

	} /* End of class */

} /* End of class-exists wrapper */

/**
 * Full (decoded) JSON response format [2014/7/22]:
 * Array
(
    [server] => ia600405.us.archive.org
    [dir] => /24/items/MARIONSTOKESINPUT47
    [metadata] => Array
        (
            [identifier] => Array
                (
                    [0] => MARIONSTOKESINPUT47
                )

            [mediatype] => Array
                (
                    [0] => movies
                )

            [boxid] => Array
                (
                    [0] => IA162306
                )

            [collection] => Array
                (
                    [0] => marionstokesinput
                    [1] => community_media
                    [2] => newsandpublicaffairs
                )

            [contributor] => Array
                (
                    [0] => Michael Metelits
                )

            [creator] => Array
                (
                    [0] => Marion Stokes
                )

            [date] => Array
                (
                    [0] => 1970-02-15
                )

            [description] => Array
                (
                    [0] => AIR DATE : 2-15-1970
REC DATE : 2-9-1970
TITLE : We, The Jailers - Part 1
Ex-Prisoner   On Parole (not named on show)
Joseph P. Liberati    President, Home of Industry for Discharged Prisoners
Ex-Prisoner   On Parole (not named on show)
Pete Seeger
Daisy Lacey   Community Organizer, Church of the Advocate
Richard L. Atkins   Attorney
Calvert Hall   Business Executive
Jean Roberts   Secretary
DIRECTOR : Jerry Chamberlain
New end credit on this episode : In Cooperation with Main Line Ministries In Higher Education
Notes : At 35:45, Seeger breaks out his guitar and sings a song, “Walking Down Death Row’

'Input' was a Philadelphia panel discussion program from the late 1960's and early 1970's, airing Sunday mornings on WCAU-TV10, produced by the Wellsprings Ecumenical Center.
                )

            [donor] => Array
                (
                    [0] => Michael Metelits
                )

            [language] => Array
                (
                    [0] => eng
                )

            [scancenter] => Array
                (
                    [0] => San Francisco
                )

            [scandate] => Array
                (
                    [0] => 20140319
                )

            [scanner] => Array
                (
                    [0] => Internet Archive HTML5 Uploader 1.5.2
                )

            [source] => Array
                (
                    [0] => Zenith L-500 Beta cassette%tape>Panasonic DMR-E30>CDR via OSX
                )

            [sponsor] => Array
                (
                    [0] => Internet Archive
                )

            [subject] => Array
                (
                    [0] => Marion Stokes
                    [1] => civil rights
                    [2] => religion
                    [3] => Philadelphia
                    [4] => Ex-Prisoner
                    [5] => Joseph P. Liberati
                    [6] => Home of Industry for Discharged Prisoners
                    [7] => Ex-Prisoner
                    [8] => Pete Seeger
                    [9] => Daisy Lacey
                    [10] => Church of the Advocate
                    [11] => Richard L. Atkins
                    [12] => Calvert Hall
                    [13] => Jean Roberts
                    [14] => Jerry Chamberlain
                    [15] => Radix Associates
                    [16] => Main Line Ministries In Higher Education
                )

            [title] => Array
                (
                    [0] => Input - #47 - We, The Jailers - Part 1
                )

            [publicdate] => Array
                (
                    [0] => 2014-03-19 23:37:36
                )

            [addeddate] => Array
                (
                    [0] => 2014-03-19 23:37:36
                )

            [segments] => Array
                (
                    [0] => Title #1, Chapters: <a href="/details/MARIONSTOKESINPUT47/MARIONSTOKESINPUT47.cdr?start=0">1</a> <a href="/details/MARIONSTOKESINPUT47/MARIONSTOKESINPUT47.cdr?start=306">2</a> <a href="/details/MARIONSTOKESINPUT47/MARIONSTOKESINPUT47.cdr?start=609">3</a> <a href="/details/MARIONSTOKESINPUT47/MARIONSTOKESINPUT47.cdr?start=910">4</a> <a href="/details/MARIONSTOKESINPUT47/MARIONSTOKESINPUT47.cdr?start=1212">5</a> <a href="/details/MARIONSTOKESINPUT47/MARIONSTOKESINPUT47.cdr?start=1516.5">6</a> <a href="/details/MARIONSTOKESINPUT47/MARIONSTOKESINPUT47.cdr?start=1822.5">7</a> <a href="/details/MARIONSTOKESINPUT47/MARIONSTOKESINPUT47.cdr?start=2126">8</a> <a href="/details/MARIONSTOKESINPUT47/MARIONSTOKESINPUT47.cdr?start=2434">9</a> <a href="/details/MARIONSTOKESINPUT47/MARIONSTOKESINPUT47.cdr?start=2739">10</a> <a href="/details/MARIONSTOKESINPUT47/MARIONSTOKESINPUT47.cdr?start=3043.5">11</a> <a href="/details/MARIONSTOKESINPUT47/MARIONSTOKESINPUT47.cdr?start=3346">12</a>  .<br/>
                )

            [closed_captioning] => Array
                (
                    [0] => no
                )

            [year] => Array
                (
                    [0] => 1970
                )

            [sound] => Array
                (
                    [0] => sound
                )

            [color] => Array
                (
                    [0] => color
                )

            [pick] => Array
                (
                    [0] => 1
                )

        )

    [files] => Array
        (
            [/MARIONSTOKESINPUT47.cdr] => Array
                (
                    [source] => original
                    [format] => ISO Image
                    [mtime] => 1395272256
                    [size] => 4281991168
                    [md5] => 6810d9838ba9b7c8bdf03edd20528985
                    [crc32] => f01293e8
                    [sha1] => 41e4598ba538b4ec9016fb8df9e96e8b208fd5ef
                )

            [/MARIONSTOKESINPUT47.gif] => Array
                (
                    [source] => derivative
                    [format] => Animated GIF
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273712
                    [size] => 496431
                    [md5] => ca8719a502d22b5e04106aff89f03a5e
                    [crc32] => 0882ae8e
                    [sha1] => 3826a8bd42f338ef52b3814e53b117f1fb1fc434
                )

            [/MARIONSTOKESINPUT47.mp4] => Array
                (
                    [source] => derivative
                    [format] => h.264
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395276072
                    [size] => 370116102
                    [md5] => 8c56359982e6700d536c19e0834ed636
                    [crc32] => 1772c612
                    [sha1] => cba6bfcdfcef8dce46c85301a93a7d7a06522762
                    [length] => 3576.58
                    [height] => 480
                    [width] => 640
                )

            [/MARIONSTOKESINPUT47.ogv] => Array
                (
                    [source] => derivative
                    [format] => Ogg Video
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395279784
                    [size] => 270340359
                    [md5] => f93e05ced7d12c9501e82eca507c1c81
                    [crc32] => 5a2ebb6e
                    [sha1] => eb0872f5d4206ddf8c8553e83f3d3c08e6c7a10e
                    [length] => 3576.58
                    [height] => 300
                    [width] => 400
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000001.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273791
                    [size] => 5291
                    [md5] => 6afd057e25960b65bf11a2a2b09ef821
                    [crc32] => 680ba724
                    [sha1] => efb9c90518fb5a909feba995944ec8ce38c4ee93
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000090.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273799
                    [size] => 4248
                    [md5] => 3593bd6027ec5746eb5f53f976c9b25d
                    [crc32] => 5bc0999c
                    [sha1] => 9369e95ca2552c25d0dd13b539220e2b6103167f
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000150.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273806
                    [size] => 3979
                    [md5] => 4e89aaa6a3f35ca74190d5aed87401bc
                    [crc32] => 3d62f8ae
                    [sha1] => 66c6f4ceae8c19e9f271290deed233aa9b09bd1c
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000210.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273813
                    [size] => 4848
                    [md5] => 073294bd48ec8814efc51e34e6b32a96
                    [crc32] => bab97236
                    [sha1] => f9abb0ba7bf540e0887bcf339bd3141fb50832cb
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000270.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273820
                    [size] => 5581
                    [md5] => f78c1f8d1def67baa5928577b97f81e2
                    [crc32] => 6122fdf6
                    [sha1] => 3aca7371b99f20f1a20be493c0cc151208a30fca
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000330.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273828
                    [size] => 8524
                    [md5] => b7754a56ad75b203ecbdc18c7cbfd2d8
                    [crc32] => fab62d65
                    [sha1] => ca50676b04049addcd0fdae29e1d130d6ef8d901
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000390.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273836
                    [size] => 7498
                    [md5] => b5255f188b610b012c47047a1b7c2115
                    [crc32] => 9bc9c5a1
                    [sha1] => c27b3797c169a3094ce52160f301dc8aecb4b99c
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000450.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273843
                    [size] => 6729
                    [md5] => 01497340c562ad63bae8515806ed94a5
                    [crc32] => bce2e523
                    [sha1] => f1e3347083be679df2662fe665ad670a3413a9f8
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000510.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273850
                    [size] => 7790
                    [md5] => d07f8b844c2b32379c7986a65ae8d597
                    [crc32] => 3766504e
                    [sha1] => ddee5ef56ef3d195d78b032fc80632dd447c796f
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000570.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273858
                    [size] => 8089
                    [md5] => 76110d3eda411261410afaecbeca87c9
                    [crc32] => 034c3528
                    [sha1] => 4fe6e80fde74f7482231b9aee4a0f499386d7e69
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000630.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273866
                    [size] => 8077
                    [md5] => bf317def076e868b2c9c78ba6239c15d
                    [crc32] => f24b9cf3
                    [sha1] => f90689e84c77d734a4a908c4e36fedfa5c4ea499
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000690.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273874
                    [size] => 8421
                    [md5] => 143baac21ba3cdc95e3b31b53d288f51
                    [crc32] => c503bf96
                    [sha1] => e2a8ce7f3430395039b38314c875fdcdf7e38508
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000750.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273881
                    [size] => 7624
                    [md5] => 00d80890e239882de927cedac3249e9c
                    [crc32] => 8d513f84
                    [sha1] => e56378f32c6e18594c2bbf82b28b203eae5abe2b
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000810.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273888
                    [size] => 6979
                    [md5] => a72297f1ccd197f6623de78fa3a2e550
                    [crc32] => 1fad945a
                    [sha1] => 9a007dbe0d3ba30e7b0ed9fc43ed1f06efc9b532
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000870.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273896
                    [size] => 7179
                    [md5] => 9d4ba56700687c2da13655d7774ca670
                    [crc32] => b450ebad
                    [sha1] => 191a887f89b9ba4a8b381097047dadaea30d5f85
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000930.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273904
                    [size] => 6775
                    [md5] => 197f1cee43a8a8209221a1b2def6f660
                    [crc32] => 3df05398
                    [sha1] => a944fd0b77e2208adca57027638b58eb8c24d6f6
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_000990.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273911
                    [size] => 7031
                    [md5] => b1e54fcc4db55c6083d8bd39b984b31e
                    [crc32] => 09ec0fbc
                    [sha1] => c849e14bc3c0d9f8764ec61a06def20d1d8e5429
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001050.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273919
                    [size] => 6825
                    [md5] => 89908044293ea77d7ce83a57332c0bc8
                    [crc32] => 30888f67
                    [sha1] => be4d07c4925553f5854e26e3a688525979f375d1
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001110.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273926
                    [size] => 7595
                    [md5] => 1a0b0b6d24462d0309cf5e494569ad9c
                    [crc32] => 34a96a39
                    [sha1] => acc91da59eae1bb50ea5cdf450a2f47c2b77a04b
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001170.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273933
                    [size] => 7017
                    [md5] => 4ecbcddbe00db048485e67c598c8b0ee
                    [crc32] => 17deb1b8
                    [sha1] => 17d84660210a8ad8f32e121bf9b5aa757f9b045a
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001230.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273941
                    [size] => 8376
                    [md5] => 7a86b735b0d72cd46af63040d503b8f3
                    [crc32] => bd6eb33b
                    [sha1] => 070a60ad6f432da368993fd69956aa39ff56aa40
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001290.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273948
                    [size] => 8022
                    [md5] => af8c7acf00bddbad7b47870a08000fde
                    [crc32] => 7f3b7d13
                    [sha1] => 8c87c6c6fd8cc3a75e8004565e2a0d494549280d
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001350.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273955
                    [size] => 7093
                    [md5] => 66920c351e14a8e86abb53c7a5dc6263
                    [crc32] => 568566d4
                    [sha1] => 95b19422e93f6230eb91eaa0026caa11a441f0b6
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001410.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273963
                    [size] => 6938
                    [md5] => 1075659f21c32cc5ff010b73faa9175a
                    [crc32] => 6879e8b7
                    [sha1] => 775cd0f9da08fa89c2b3adc8becc5d606509fcd6
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001470.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273970
                    [size] => 6805
                    [md5] => 554bb707d1d246e8992601dd32e0acc4
                    [crc32] => cbea019f
                    [sha1] => 06a58e154024281794c0f9c937872bce2bd545df
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001530.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273978
                    [size] => 6466
                    [md5] => 85dabf3d3ab7d9db6f31f16935984b5e
                    [crc32] => 1afcce13
                    [sha1] => 68ceeb7a6fcfb037d5da58082b5d00c877f4c732
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001590.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273985
                    [size] => 6580
                    [md5] => de5d38f08f4efca06c22846ddbc58f4b
                    [crc32] => 4d824350
                    [sha1] => 40bfd98f109c062570f3e3f8d34f69dc5ac906a7
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001650.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395273992
                    [size] => 7062
                    [md5] => 2fdfc00ec54fb29b2ab2b7c73e9c0cfc
                    [crc32] => 99686402
                    [sha1] => 48b759b602de4c7499dfa51909df21ec8bd582d3
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001710.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274000
                    [size] => 7987
                    [md5] => 356c9f3fde0d71f88cd7b29669a9917f
                    [crc32] => 105ce832
                    [sha1] => c0da4aac88364789b316b8b3af539df3a066160b
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001770.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274007
                    [size] => 7689
                    [md5] => d4cf02d1db81d65590c1165eb4f18da2
                    [crc32] => 66005e3b
                    [sha1] => 39f40e9ebd484ee5486bb838e0f3412dcef0b685
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001830.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274016
                    [size] => 6386
                    [md5] => d7713538567083bab6694aea3ef7e5c9
                    [crc32] => 93dd1793
                    [sha1] => 9a61e3088d887fe614e3d8de4530b6ffd10c9beb
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001890.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274023
                    [size] => 7392
                    [md5] => 9f4e35580a57fb448dd446adc42c8dfe
                    [crc32] => 75094d99
                    [sha1] => 3e0b8b7d488116267774973003beea297623e8fe
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_001950.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274031
                    [size] => 6802
                    [md5] => fd921254bf5e230236aa803ab322623a
                    [crc32] => ee287faf
                    [sha1] => 936fbe4766b52c62ea4c69d63a4e094eba05b0cd
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002010.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274039
                    [size] => 7869
                    [md5] => c705eeba517148d7af4d90c868949768
                    [crc32] => bc679694
                    [sha1] => 8e61875a045fdf1b2a48b65c0307e050a97430b0
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002070.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274046
                    [size] => 9085
                    [md5] => 4ba03c3dd15e16822da5ebcf3fee7edb
                    [crc32] => 54152f00
                    [sha1] => 9b951f5778656526d353f4a0b98df8b00b69f4aa
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002130.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274054
                    [size] => 7321
                    [md5] => ceaa78de2b64cb7738cce1cd7779e7b9
                    [crc32] => 57e68345
                    [sha1] => 86c756d55588d8bf90fa1278253ab06287aed2af
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002190.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274062
                    [size] => 7559
                    [md5] => 14b8790fb6dcb1cc0ee2c4ac5326f595
                    [crc32] => 7e934f4f
                    [sha1] => 2e8cf6a969a3e12a777595d8c57413d163854ad0
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002250.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274070
                    [size] => 8383
                    [md5] => 680b92cd7b657756848f79ec47006064
                    [crc32] => b1c44533
                    [sha1] => ed439d122b249eb54f9e8347aa0ca19c12d8688d
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002310.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274078
                    [size] => 7018
                    [md5] => 82b8e323f2f0ebffe4ad4934111e5e34
                    [crc32] => c3c15955
                    [sha1] => 68dbe39ba6319bf41d00a1b159e48d205b7b02e0
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002370.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274086
                    [size] => 7995
                    [md5] => 35f88dda62cc03bb7e571884519f38f8
                    [crc32] => d54f3cd1
                    [sha1] => a7c1f1b7d23e2f538b10fb388dca6792f28953dd
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002430.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274093
                    [size] => 7489
                    [md5] => 2a6c1f600ca90157ede60514bbf8364b
                    [crc32] => b31617fb
                    [sha1] => c2af511b4eaecab7cc1a528810fb92abef9b4a05
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002490.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274101
                    [size] => 7433
                    [md5] => 227b16253f0d2c3e54e27bac9b9327c0
                    [crc32] => 7cb21233
                    [sha1] => 1d9f38026f18bd6682cec782a1d54491dae7325c
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002550.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274109
                    [size] => 8092
                    [md5] => 5db8a42758b83c7b8e9efd930cdd3f88
                    [crc32] => 1bd02496
                    [sha1] => 89c5f3c8ac70482c18a7a6a742529246a709f545
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002610.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274116
                    [size] => 7516
                    [md5] => fbc090b102f35d20a95d4e9837e431d8
                    [crc32] => 5adf231a
                    [sha1] => 9dc9fb6efb7ba769ca1f0a4e1d6519a0a6d2bfbf
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002670.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274124
                    [size] => 6761
                    [md5] => 76d30bd8f8341bd19dc35508966fcffd
                    [crc32] => c6eac715
                    [sha1] => 951ce39d674292f5cc974e5742f14dee75c24313
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002730.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274132
                    [size] => 6740
                    [md5] => a1a87364aee4e09a4a6528f63cddf6c6
                    [crc32] => e33a1ebe
                    [sha1] => a3913ac78e36aa3a1bfa0c332a62fe391bec0ba3
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002790.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274139
                    [size] => 7324
                    [md5] => db9cf13ac9534b3efbdffed1ef681826
                    [crc32] => b659a48b
                    [sha1] => 5ee1c785811409696052f3126973bf2e1b75fec5
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002850.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274147
                    [size] => 7203
                    [md5] => 009de11aa4295ef78953cbc72f6450d6
                    [crc32] => 07700bde
                    [sha1] => 35c835294b5f1f6b04c3c8f6dcd94af2b6cfce9f
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002910.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274155
                    [size] => 6926
                    [md5] => 8326acb9e8b99567045ff3026e167db2
                    [crc32] => 49affcec
                    [sha1] => 1d965530991b5ec9054132c891fb5bc332924e2e
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_002970.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274162
                    [size] => 6906
                    [md5] => 670c96051a1c2f11fa3c09b9adda4e14
                    [crc32] => 85d1dab8
                    [sha1] => 11e5fe9f5fd40c62866c965325ae897f9292c63f
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_003030.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274169
                    [size] => 6512
                    [md5] => 60a020fe406f9a82dfaafeee0355582c
                    [crc32] => c9ada25a
                    [sha1] => a027e302e66e6e0f1d470f68d1aa64302035ed5b
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_003090.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274177
                    [size] => 7536
                    [md5] => 005ee1a79911b231683fb54f0e6e0c8a
                    [crc32] => 5194038a
                    [sha1] => b0e4e564c11827fdc8de73b6da524e9fe138f665
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_003150.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274185
                    [size] => 8535
                    [md5] => 7bd8ebb08bb536ecf8695eb45d4ece48
                    [crc32] => b98a9776
                    [sha1] => 42ae8e4da12751d9d414b9d517257d5148780289
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_003210.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274192
                    [size] => 6795
                    [md5] => b63cdaeb7a4c9ec4bd67036082238ed4
                    [crc32] => 55b42951
                    [sha1] => 22bede202e3cc594317c3dd3159d98649d726993
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_003270.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274199
                    [size] => 7106
                    [md5] => 5bca93943a75d9f71c467d7488350348
                    [crc32] => 4a3b36d6
                    [sha1] => fad2d98a1e67e1e45c6bc5cd04098a0cff4e9b4b
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_003330.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274207
                    [size] => 7115
                    [md5] => 91c622e686d813bad60d282300730434
                    [crc32] => 8e558508
                    [sha1] => 0403b3a0f333bd40124dba151c9ece71ee10da94
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_003390.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274214
                    [size] => 6736
                    [md5] => ac79eea94dece95dc70b86827b36257f
                    [crc32] => d5cdb465
                    [sha1] => 3b0d0d714e4febfd7437fc42edd14aaf0fe008a9
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_003450.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274222
                    [size] => 7100
                    [md5] => c38eb5de28b8925f3d5fb5a40258c3b8
                    [crc32] => 9cc1f515
                    [sha1] => 03e6148393944748abd23a93e24a8aea69c70af9
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_003510.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274229
                    [size] => 8657
                    [md5] => 357e112b0548eada1f2deabbcb44eebb
                    [crc32] => fe351fa5
                    [sha1] => da51950ec9f3b7a96ac34999a23bd65fb393ad4d
                )

            [/MARIONSTOKESINPUT47.thumbs/MARIONSTOKESINPUT47_003570.jpg] => Array
                (
                    [source] => derivative
                    [format] => Thumbnail
                    [original] => MARIONSTOKESINPUT47.cdr
                    [mtime] => 1395274238
                    [size] => 7120
                    [md5] => 71fdf4f2d1b174557b1b8ddf8bcdd654
                    [crc32] => bc9f399d
                    [sha1] => 16baf5741af2bf9699a0db425876e392f050b479
                )

            [/MARIONSTOKESINPUT47_01.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272267
                    [size] => 43890
                    [md5] => e669901efa4ad5715681e2bd16fe883b
                    [crc32] => 14bfe4e7
                    [sha1] => 3e741847b2b847fd97965059393c2f048cfcbaed
                )

            [/MARIONSTOKESINPUT47_01_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_01.jpg
                    [mtime] => 1395279798
                    [size] => 4765
                    [md5] => 3a0349ca9eb4544ebf27fb830dc9dad7
                    [crc32] => ff6e036e
                    [sha1] => e882f13f8590e19d2de5044f98e06a14b08ceebc
                )

            [/MARIONSTOKESINPUT47_02.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272269
                    [size] => 44091
                    [md5] => 8ebacbc4777e804e8e39c2c2283269a9
                    [crc32] => 22933b02
                    [sha1] => 8d4ceef34f8314d682c580b9913de83b17faa924
                )

            [/MARIONSTOKESINPUT47_02_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_02.jpg
                    [mtime] => 1395279799
                    [size] => 2894
                    [md5] => a3e7934cfef8e97bd55a14dbc8329546
                    [crc32] => 439ebd1c
                    [sha1] => 99c0fd54f333481cc2dfe6221526a0bacc056ced
                )

            [/MARIONSTOKESINPUT47_03.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272271
                    [size] => 42415
                    [md5] => c03be147162bd8f4368d8651bea78474
                    [crc32] => 7ea5500c
                    [sha1] => fc8a1ff0d1277e77e2db677d9ea48175db22e0eb
                )

            [/MARIONSTOKESINPUT47_03_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_03.jpg
                    [mtime] => 1395279800
                    [size] => 2867
                    [md5] => b6bc1369c13433a35b933faf80513d48
                    [crc32] => 90d61fa2
                    [sha1] => 2edbd5832383db02b42102507518214390d44417
                )

            [/MARIONSTOKESINPUT47_04.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272273
                    [size] => 57439
                    [md5] => 9bafcb376f0bdbc065faa55f3b4d2e0e
                    [crc32] => faecd652
                    [sha1] => 81151433e7a271969da99f6a91f8f7d0252bb107
                )

            [/MARIONSTOKESINPUT47_04_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_04.jpg
                    [mtime] => 1395279799
                    [size] => 3877
                    [md5] => 2d10800dbc5dcc79b80dba95e4cfed77
                    [crc32] => 05fe0a93
                    [sha1] => a0c2709e8f8db16f1e502dc81279dc481c4528bc
                )

            [/MARIONSTOKESINPUT47_05.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272275
                    [size] => 44967
                    [md5] => 53ca2b1ab8d7c0795a97ce1040ebaf1c
                    [crc32] => d4e3ba75
                    [sha1] => af511687e7131e26ad1c1315f05707cfb9adce62
                )

            [/MARIONSTOKESINPUT47_05_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_05.jpg
                    [mtime] => 1395279803
                    [size] => 3572
                    [md5] => e6890fe4f4d7247ab4dfb9bc9493cc41
                    [crc32] => 1c8dbe0b
                    [sha1] => 0891429e30da381182e7ac2ce10ca58df1d4b4d5
                )

            [/MARIONSTOKESINPUT47_06.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272278
                    [size] => 46998
                    [md5] => 63a1ca644f6689dce1212bb9a6466bd5
                    [crc32] => afb60723
                    [sha1] => 6230beaf4814223f50d53e68734b3123563317ee
                )

            [/MARIONSTOKESINPUT47_06_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_06.jpg
                    [mtime] => 1395279801
                    [size] => 4012
                    [md5] => aea193f749cb0f73812e88873bcd71d1
                    [crc32] => 1e44ef87
                    [sha1] => 261fd9aefbbda5ad3ee6f7a52b8ec8e647a76f0d
                )

            [/MARIONSTOKESINPUT47_07.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272280
                    [size] => 47502
                    [md5] => d80617dfa048bec585148e70da1aac32
                    [crc32] => c4d1fc24
                    [sha1] => 05fe1bc56018870faca1d4a7085d64001b36a529
                )

            [/MARIONSTOKESINPUT47_07_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_07.jpg
                    [mtime] => 1395279802
                    [size] => 3428
                    [md5] => a498e5bcfa084617bbd161e0b424962c
                    [crc32] => c833343b
                    [sha1] => 16817449efb5b047e2724a401cc859cb317b7054
                )

            [/MARIONSTOKESINPUT47_08.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272282
                    [size] => 45361
                    [md5] => 616fd376df07e82f65652771c9d0b155
                    [crc32] => 33d049a6
                    [sha1] => 7e91079777993c674baeb6c5f9c84b9622326133
                )

            [/MARIONSTOKESINPUT47_08_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_08.jpg
                    [mtime] => 1395279800
                    [size] => 3669
                    [md5] => 96e43d34cea7fddf8cb046b4d847a71e
                    [crc32] => 735629f5
                    [sha1] => 54d308dc34dfa34eae6add9519207f6e7b0be443
                )

            [/MARIONSTOKESINPUT47_09.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272284
                    [size] => 47051
                    [md5] => fa4b4990724f71a7b3a2b20e769bb68e
                    [crc32] => aa7c3069
                    [sha1] => 48fa7f7163940cbae80547bb84485b5e18cccffa
                )

            [/MARIONSTOKESINPUT47_09_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_09.jpg
                    [mtime] => 1395279803
                    [size] => 3442
                    [md5] => 1209b8a27bf3f6997326af3e9cb7a676
                    [crc32] => 42fb278b
                    [sha1] => 51f042668aeeb2d9bc1520b9bad382d37d336d85
                )

            [/MARIONSTOKESINPUT47_10.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272285
                    [size] => 50244
                    [md5] => 7179a389678f8ee8d343c84f73155338
                    [crc32] => 2fae0842
                    [sha1] => 8a0779b9aae815eb39874806bd6bfa5a583e72e6
                )

            [/MARIONSTOKESINPUT47_10_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_10.jpg
                    [mtime] => 1395279801
                    [size] => 3345
                    [md5] => 480fe4aafe56f6b3669aa0e0fe7997e8
                    [crc32] => 020626e2
                    [sha1] => e5fe4a4f7a09fd364b2263ddb9c33b087c6a4888
                )

            [/MARIONSTOKESINPUT47_11.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272287
                    [size] => 58212
                    [md5] => afdd21b56b6666a011f734cece234427
                    [crc32] => 1b41212f
                    [sha1] => f601a72afc32575c4e6e3e85431e5adcd6300df1
                )

            [/MARIONSTOKESINPUT47_11_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_11.jpg
                    [mtime] => 1395279804
                    [size] => 3905
                    [md5] => 1300646f95eb9dd572b5101cf5919735
                    [crc32] => 120edd97
                    [sha1] => 0c8577186c10937e1e18354c39c6ccaab2cf28c7
                )

            [/MARIONSTOKESINPUT47_12.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272288
                    [size] => 53212
                    [md5] => e308b0fa4de647e90a304fb462a1fb41
                    [crc32] => d44fbf46
                    [sha1] => c7f42af4300b9aad1b44ef7dee098c33feb7820a
                )

            [/MARIONSTOKESINPUT47_12_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_12.jpg
                    [mtime] => 1395279807
                    [size] => 3612
                    [md5] => 32e588ea0eb98ee0aef4e958d6cabd5f
                    [crc32] => d2d2e8ba
                    [sha1] => 0d5a1e192989144925914b75e62553866860f36d
                )

            [/MARIONSTOKESINPUT47_13.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272290
                    [size] => 36949
                    [md5] => 45325f556f7877d9b7e416b0bdc6ed64
                    [crc32] => 9c23db7e
                    [sha1] => 47ecfe63abfaf160324b9c74f98a04629df8a3e7
                )

            [/MARIONSTOKESINPUT47_13_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_13.jpg
                    [mtime] => 1395279805
                    [size] => 3059
                    [md5] => f114ad21b67dfe396962f02867583b29
                    [crc32] => 5ce0d841
                    [sha1] => 4ea700c9231fab27bdf615cd0dc2093bda328e9a
                )

            [/MARIONSTOKESINPUT47_14.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272292
                    [size] => 59782
                    [md5] => 758e0364be139f6cff6603e6f78e2df6
                    [crc32] => 86b60191
                    [sha1] => b8f69243777a8d4de1eb4c1699ed0d9b256e97c9
                )

            [/MARIONSTOKESINPUT47_14_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_14.jpg
                    [mtime] => 1395279806
                    [size] => 2785
                    [md5] => b4dbc66a364ce3e2c56b738277e7eb6d
                    [crc32] => 49d9cec1
                    [sha1] => 9e7e196a9b3bb7ca37d8a8a9a356642500a99252
                )

            [/MARIONSTOKESINPUT47_15.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272294
                    [size] => 52035
                    [md5] => 53d179e60782376bfac0b0ef15484ba5
                    [crc32] => 4b658dc8
                    [sha1] => 3c088a3cba866a8e9eb764e99eff83b52e139d7e
                )

            [/MARIONSTOKESINPUT47_15_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_15.jpg
                    [mtime] => 1395279805
                    [size] => 3030
                    [md5] => 07eb056396c3f262b883957ca7f18745
                    [crc32] => 8f85a6fb
                    [sha1] => 02d53df7d3f87be1db5b5e2dca57f66afa788fd4
                )

            [/MARIONSTOKESINPUT47_16.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272296
                    [size] => 57132
                    [md5] => 6686fda98965282cad9536dbf09c7095
                    [crc32] => d64f5277
                    [sha1] => 9411e3b10b5dcd38949e7084b5809df813543037
                )

            [/MARIONSTOKESINPUT47_16_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_16.jpg
                    [mtime] => 1395279806
                    [size] => 3515
                    [md5] => 45340af8d5933eecff89779068bf7a5e
                    [crc32] => 328a47e3
                    [sha1] => d0eb660ef024f36b12a9cdacacd3734ef6f1aa73
                )

            [/MARIONSTOKESINPUT47_17.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272297
                    [size] => 38135
                    [md5] => ed874d3681ef2a6d6245e8c1bfb9f871
                    [crc32] => 083d32b5
                    [sha1] => 767a083537a2489b0c4a82b9a1563c218e738b3b
                )

            [/MARIONSTOKESINPUT47_17_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_17.jpg
                    [mtime] => 1395279807
                    [size] => 2973
                    [md5] => f6de8fdb47217393060f846e57919609
                    [crc32] => 982ca31f
                    [sha1] => 3aba0f69110826252e9c9363b8cadf13e141d098
                )

            [/MARIONSTOKESINPUT47_18.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272299
                    [size] => 42398
                    [md5] => 07d74b026a1b5b6b470a68277a202646
                    [crc32] => 714aabd5
                    [sha1] => 8aad6baa4a76832c171a6873ef5774ee9cc6c7f6
                )

            [/MARIONSTOKESINPUT47_18_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_18.jpg
                    [mtime] => 1395279808
                    [size] => 3054
                    [md5] => 94a7beb573b61016ffc2d29400426f62
                    [crc32] => a4d686b8
                    [sha1] => f4e4707b3fca2f66fe4a75f7a361c8aaa38ba011
                )

            [/MARIONSTOKESINPUT47_19.jpg] => Array
                (
                    [source] => original
                    [format] => JPEG
                    [mtime] => 1395272300
                    [size] => 44910
                    [md5] => 8cdd8c378ffd088ca992276434410ab6
                    [crc32] => b2707233
                    [sha1] => 30246910a165ea5b51eeef58937a3700e34a73d0
                )

            [/MARIONSTOKESINPUT47_19_thumb.jpg] => Array
                (
                    [source] => derivative
                    [format] => JPEG Thumb
                    [original] => MARIONSTOKESINPUT47_19.jpg
                    [mtime] => 1395279808
                    [size] => 2750
                    [md5] => bad381dad0eade0d958a87a32c277845
                    [crc32] => d850fe0d
                    [sha1] => e071195584d1297f214c8063c964a04084c3ec61
                )

            [/MARIONSTOKESINPUT47_archive.torrent] => Array
                (
                    [source] => original
                    [btih] => 57166b3a5543a531b29063cfa54c55c12d03e325
                    [mtime] => 1395314711
                    [size] => 59590
                    [md5] => d8a7c91ebaf43d8640be072997f5b717
                    [crc32] => 7d69e574
                    [sha1] => 9456dee1aad21533d36e90d851f85e45fddb1ad1
                    [format] => Archive BitTorrent
                )

            [/MARIONSTOKESINPUT47_files.xml] => Array
                (
                    [source] => original
                    [format] => Metadata
                    [md5] => 7762e639adc8e6aa4ce4c231500ba598
                )

            [/MARIONSTOKESINPUT47_meta.sqlite] => Array
                (
                    [source] => original
                    [format] => Metadata
                    [mtime] => 1395272362
                    [size] => 118784
                    [md5] => bb4ac9e8d99ecf0b8a52dd315ce924c8
                    [crc32] => 92cbab74
                    [sha1] => 3ea1de843b41733436f144ed31650414278dc37e
                )

            [/MARIONSTOKESINPUT47_meta.xml] => Array
                (
                    [source] => original
                    [format] => Metadata
                    [mtime] => 1395314710
                    [size] => 3702
                    [md5] => 0000c7857df18f1ed66c774e352ee950
                    [crc32] => 1e167b19
                    [sha1] => a0d767c2978746aa95125064d1a3a8bbf861e6a1
                )

        )

    [misc] => Array
        (
            [css] => movies
            [image] => https://ia600405.us.archive.org/24/items/MARIONSTOKESINPUT47/MARIONSTOKESINPUT47.gif
            [header_image] => https://ia700502.us.archive.org/24/items/marionstokesinput/input_header.jpg
            [collection-title] => Input - Marion Stokes & John S. Stokes, Jr.
        )

    [item] => Array
        (
            [downloads] => 328
            [week] => 1
            [month] => 14
        )

)
*/

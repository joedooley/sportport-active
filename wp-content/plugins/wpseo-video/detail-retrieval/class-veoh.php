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
 * Veoh Video SEO Details
 *
 * @todo Maybe look into the API: http://www.veoh.com/rest/v2/doc.html
 * API-key: E97FCECD-875D-D5EB-035C-8EF241F184E2
 * @see http://jlorek.wordpress.com/tag/veoh/ ;-)
 *
 * Example of full remote SPI response (XML) format [2014/7/22] - see below class.
 *******************************************************************/
if ( ! class_exists( 'WPSEO_Video_Details_Veoh' ) ) {

	/**
	 * Class WPSEO_Video_Details_Veoh
	 */
	class WPSEO_Video_Details_Veoh extends WPSEO_Video_Details {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`[/\.]veoh\.com/(?:videos|watch)/([^/]+)[/]?$`i';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		//protected $url_template = '';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 * /
		protected $remote_url = array(
			'pattern'       => '',
			'replace_key'   => '',
			'response_type' => '',
		);
		*/


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = 'http://www.veoh.com/veohplayer.swf?permalinkId=' . urlencode( $this->vid['id'] );
			}
		}


		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$url   = $this->url_encode( 'http://ll-images.veoh.com/media/w300/thumb-' . $this->vid['id'] . '-1.jpg' );
				$image = $this->make_image_local( $url );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
		}

	} /* End of class */

} /* End of class-exists wrapper */


/**
 * Remote response (XML) format [2014/7/22]:
 * Call made to: http://www.veoh.com/rest/v2/execute.xml?method=veoh.video.findByPermalink&permalink=v373111wawdnrZ6&apiKey=E97FCECD-875D-D5EB-035C-8EF241F184E2
 *
 *
<?xml version="1.0" encoding="UTF-8" ?>
<rsp stat="ok" guid="0b94916b-c5ec-4926-85d8-c2737eca9f3a" echo="" requestGeo="NL" timestamp="1406235269">
	<videoList offset="0" items="1" numItems="1">
		<video videoId="373111"
			permalinkId="v373111wawdnrZ6"
			length="24 min 29 sec"
			size="215808000"
			tags="anime"
			editorTags=""
			compressor=""
			isExternalMedia="false"
				extension=".mp4"
				fileHash="h373111"
				numDownloads="1718"
				ipodLink=""
				fullHighResImagePath="http://fcache.veoh.com/file/f/th373111.jpg?h=6949da9ea028204e490521cf28c2eda7"
				fullMedResImagePath="http://fcache.veoh.com/file/f/tl373111.jpg?h=93dc959707b6904dad727e674a84a535"
				extendPath="true"
					p2pEnabled="true"
				fullPreviewHashPath="http://content.veoh.com/flash/p/2/v373111wawdnrZ6/l373111.mp4?ct=58b3ce5e2eb064bfbe0cc77bdc6ad9af1dc4234eb6339983"
				previewUrl="http://content.veoh.com/flash/p/2/v373111wawdnrZ6/l373111.mp4?ct=58b3ce5e2eb064bfbe0cc77bdc6ad9af1dc4234eb6339983"
				fullPreviewHashLowPath="http://content.veoh.com/flash/p/2/v373111wawdnrZ6/l373111.mp4?ct=58b3ce5e2eb064bfbe0cc77bdc6ad9af1dc4234eb6339983"
				fullPreviewHashHighPath="http://content.veoh.com/flash/p/2/v373111wawdnrZ6/h373111.mp4?ct=91e8fee1bbaf8f1b2a045a25ad56715812d0952ee9298337"
				fullHashPath="http://content.veoh.com/flash/f/2/v373111wawdnrZ6/l373111.mp4?ct="
				fullHashPathToken="AwEX+rG9GPVUK/EhCQFxeca3RRcKQV/xGwsdltRieTS3WdpIxUv9HpQuISswfKqY+uUc6/vcmvWWyZ5qKrGrXw=="
				fullHashPathLow="http://content.veoh.com/flash/f/2/v373111wawdnrZ6/l373111.mp4?ct="
				fullHashPathTokenLow="AwEX+rG9GPVUK/EhCQFxeca3RRcKQV/xGwsdltRieTS3WdpIxUv9HpQuISswfKqY+uUc6/vcmvWWyZ5qKrGrXw=="
				downloadUrl="http://content.veoh.com/flash/d/2/v373111wawdnrZ6/h373111.mp4?ct="
				downloadUrlToken="zp2OZlbqpR4KXTMiSPMs0jzcczWp0e7WzhMpyA1sJDeTr1YyNDhfpHkQ47A1u7ibcM0n4REZOtNqJDjzfKXHnw=="
										girafficable="true"
										seekable="true"
										hashq="true"
				fullHashPathHigh="http://content.veoh.com/flash/f/2/v373111wawdnrZ6/h373111.mp4?ct="
				fullHashPathTokenHigh="/SvgK4BS8g7RnpwUs0kjKDCaM8Aj9zsaQMXdVyuoTtZUqw6gcV8u57U08R2aMfmmh5Dy41zbSFys5EAbKor3HQ=="
				previewHash="l373111"
				previewExtension=".mp4"
				previewHashHigh="h373111"
				previewHashLow="l373111"
				previewPieceHashFile="http://content.veoh.com/pveoh/v373111wawdnrZ6/l373111.veoh?ct=f4074240f12d52e9afdd1e5c965686c0e23d73fdc92ca360"
				previewUrlRoot="http://p-cache.veoh.com/cache"
				originalHash="h373111"
				origExtension=".mp4"
				originalPieceHashFile="http://p-cache.veoh.com/cache/veoh/h373111.veoh"
				originalUrlRoot="http://p-cache.veoh.com/cache"
					embedCode="&lt;embed src=&#034;http://www.veoh.com/veohplayer.swf?permalinkId=v373111wawdnrZ6&amp;id=&amp;player=videodetailsembedded&#034; allowFullScreen=&#034;true&#034; width=&#034;410&#034; height=&#034;341&#034; bgcolor=&#034;#FFFFFF&#034; type=&#034;application/x-shockwave-flash&#034; pluginspage=&#034;http://www.macromedia.com/go/getflashplayer&#034;&gt;&lt;/embed&gt;&lt;br/&gt;&lt;font size=&#034;1&#034;&gt;Watch &lt;a href=&#034;http://www.veoh.com/videos/v373111wawdnrZ6&#034;&gt;anime&lt;/a&gt; in &lt;a href=&#034;http://www.veoh.com/browse/videos.html?category=category_animation&#034;&gt;Animation&lt;/a&gt;&amp;nbsp;&amp;nbsp;|&amp;nbsp;&amp;nbsp;View More &lt;a href=&#034;http://www.veoh.com/&#034;&gt;Free Videos Online at Veoh.com&lt;/a&gt;&lt;/font&gt;"
				ipodUrl="http://content.veoh.com/flash/i/2/v373111wawdnrZ6/l373111.mp4?ct=c5b8935e6ebfd680109087c040b21cd6c0958be785516215"
		        metadataFinalized="true"
			highResImage="http://fcache.veoh.com/file/f/th373111.jpg?h=6949da9ea028204e490521cf28c2eda7"
			medResImage="http://fcache.veoh.com/file/f/tl373111.jpg?h=93dc959707b6904dad727e674a84a535"
			username="hotlick"
			userId="1481324"
			reviewState="reviewState.active"
			geoRestrictions=""
			views="41990"
			rating="4.58618"
			numRatingVotes="68"
			numViews="41990"
			description="anime"
			title="anime"
			dateAdded="2007-04-10 19:46:06"
			age="7 years ago"
			primaryCollectionPermalink=""
			primaryCollectionTitle=""
			primaryCollectionThumb=""
			allowEmbedding="true"
			allowComments="true"
			allowBeAddedToChannel="true"
			premium="false"
			contentRatingId="2"
			numOfComments="180"
			country="UNITED STATES"
			language="English"
			subtitle=""
			ssId="0"
		    ssIdOnsite="0"
		>
			<tagList numItems="1">
				<tag tagId="1787"
					tagName="anime"
					dateAdded="2005-11-30 09:26:28.0"
					hits="1986"
					featured="false"
					selected="false"
					ratingId="1"
				 />
			</tagList>
			<categories>
					<category id="104" ancestry="104">category_animation</category>
			</categories>
		</video>
	</videoList>
</rsp>
*/
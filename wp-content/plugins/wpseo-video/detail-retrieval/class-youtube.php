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
 * YouTube Video SEO Details
 *
 * @internal
 * Currently uses the v2 API which has been deprecated since March 2014.
 * However, the v3 API requires an API key for every request. Even so, we ought to change
 * over before v2 stops being operational which is expected to be somewhere beginning of 2015.
 *
 * V3 endpoint: https://www.googleapis.com/youtube/v3/videos?id=%s&key={YOUR_API_KEY}
 * The request we'd want to make based on the info needed/available:
 * https://www.googleapis.com/youtube/v3/videos?part=contentDetails%2Cplayer%2Csnippet%2Cstatistics%2Cstatus&id=nWDfH51gvc0&key={YOUR_API_KEY}
 *
 * snippet - provides the thumbnail info
 * contentDetails - duration
 * status - check to see if the video has not been deleted or something
 * statistics - viewCount
 * snippet - width/height from embed code
 *
 * Another thing to consider is that (both in the old and the new API), we can retrieve info about playlists
 * to determine the first video of a list.
 * V2 endpoint: http://gdata.youtube.com/feeds/api/playlists/{$id}?v=2&alt=json
 *
 * And as you can see, we could in the mean time switch over to requesting v2 data as json which
 * should be better than using the regexes.
 *
 * @see https://www.youtube.com/yt/dev/
 *
 * Also available: oembed at http://www.youtube.com/oembed?url=%s
 *
 * V2 Full remote response (XML) format [2014/7/22] - see below class.
 *
 *******************************************************************/
if ( ! class_exists( 'WPSEO_Video_Details_Youtube' ) ) {

    /**
     * Class WPSEO_Video_Details_Youtube
     */
    class WPSEO_Video_Details_Youtube extends WPSEO_Video_Details {

        /**
         * @var    string    Regular expression to retrieve a video id from a known video url
         */
        //protected $id_regex = '';

        /**
         * @var    string    Sprintf template to create a url from an id
         */
        protected $url_template = 'http://www.youtube.com/v/%s';

        /**
         * @var    array    Information on the remote url to use for retrieving the video details
         */
        protected $remote_url = array(
            'pattern'       => 'https://www.googleapis.com/youtube/v3/videos?part=snippet,statistics,contentDetails&id=%1$s&fields=items&key=%2$s',
            'replace_key'   => 'id',
            'response_type' => 'json',
        );

        /**
         * @var string
         */
        protected $api_key = 'AIzaSyAAR2WKu1hRt7lE1HWkiAzGVzoodviCxOI';

        /**
         * Retrieve the video id from a known video url based on a regex match.
         * Also change the url based on the new video id.
         *
         * @param int $match_nr The captured parenthesized sub-pattern to use from matches. Defaults to 1.
         *
         * @return void
         */
        protected function determine_video_id_from_url( $match_nr = 1 ) {
            if ( isset( $this->vid['url'] ) && is_string( $this->vid['url'] ) && $this->vid['url'] !== '' ) {

                $yt_id = WPSEO_Video_Sitemap::$youtube_id_pattern;

                $patterns = array(
                    '`youtube\.(?:com|[a-z]{2})/(?:v/|(?:watch)?(?:\?|#!)(?:.*&)?v=)(' . $yt_id . ')`i',
                    '`youtube(?:-nocookie)?\.com/(?:embed|v)/(?!videoseries|playlist)(' . $yt_id . ')`i',
                    '`https?://youtu\.be/(' . $yt_id . ')`i',
                );

                foreach ( $patterns as $pattern ) {
                    if ( preg_match( $pattern, $this->vid['url'], $match ) ) {
                        $this->vid['id'] = $match[ $match_nr ];
                        break;
                    }
                }

                // @todo [JRF => Yoast] shouldn't this be checked against $youtube_id_pattern as well ?
                if ( ( ! isset( $this->vid['id'] ) || empty( $this->vid['id'] ) ) && ! preg_match( '`^(?:http|//)`', $this->vid['url'] ) ) {
                    $this->vid['id'] = $this->vid['url'];
                }
            }
        }


        /**
         * Check if the response is for a video
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
            if ( ! empty( $this->decoded_response->contentDetails->duration ) ) {
                $date = new DateTime( '00:00' );
                $date->add( new DateInterval( $this->decoded_response->contentDetails->duration ) );
                $parsed_time = $date->format( 'H:i:s' );

                $parsed  = date_parse( $parsed_time );
                $seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];

                $this->vid['duration'] = $seconds;
            }
        }


        /**
         * Set the video height
         */
        protected function set_height() {
            $this->vid['height'] = 390; // @todo - shouldn't this be 360 ?
        }


        /**
         * Set the player location
         */
        protected function set_player_loc() {
            if ( ! empty( $this->vid['id'] ) ) {
                // @todo: Check: why is htmlentities used here ? None of the other player_loc's have it
                $this->vid['player_loc'] = htmlentities( 'https://www.youtube-nocookie.com/v/' . rawurlencode( $this->vid['id'] ) );
            }
        }


        /**
         * Set the thumbnail location
         */
        protected function set_thumbnail_loc() {
            $formats = array( 'high', 'standard', 'medium', 'default' );

            foreach ( $formats as $format ) {
                $thumbnail = $this->decoded_response->snippet->thumbnails->$format;
                if ( ! empty( $thumbnail->url ) ) {
                    $image = $this->make_image_local( $thumbnail->url );
                    if ( is_string( $image ) && $image !== '' ) {
                        $this->vid['thumbnail_loc'] = $image;

                        return;
                    }
                }
            }
        }


        /**
         * Set the video view count
         */
        protected function set_view_count() {
            if ( is_object( $this->decoded_response->statistics ) && property_exists( $this->decoded_response->statistics, 'viewCount' ) ) {
                $this->vid['view_count'] = $this->decoded_response->statistics->viewCount;
            }
        }


        /**
         * Set the video width
         */
        protected function set_width() {
            $this->vid['width'] = 640;
        }

        /**
         * Extends the parent method. By letting the parent set the response and get the first item afterwards
         */
        protected function decode_as_json() {
            parent::decode_as_json();

            if ( !empty( $this->decoded_response->items[0] ) ) {
                $this->decoded_response = $this->decoded_response->items[0];
            }
        }

    } /* End of class */

} /* End of class-exists wrapper */

/**
 * Remote response (XML) format [2014/7/22]:
 *
<?xml version='1.0' encoding='UTF-8'?>
<entry xmlns='http://www.w3.org/2005/Atom' xmlns:media='http://search.yahoo.com/mrss/' xmlns:gd='http://schemas.google.com/g/2005' xmlns:yt='http://gdata.youtube.com/schemas/2007'>
	<id>http://gdata.youtube.com/feeds/api/videos/nWDfH51gvc0</id>
	<published>2006-09-26T05:55:22.000Z</published>
	<updated>2014-07-31T15:36:19.000Z</updated>
	<category scheme='http://schemas.google.com/g/2005#kind' term='http://gdata.youtube.com/schemas/2007#video'/>
	<category scheme='http://gdata.youtube.com/schemas/2007/categories.cat' term='Music' label='Music'/>
	<title type='text'>Sheryl Crow Hallelujah</title>
	<content type='text'>Sheryl Crow Live - Hallelujah</content>
	<link rel='alternate' type='text/html' href='http://www.youtube.com/watch?v=nWDfH51gvc0&amp;feature=youtube_gdata'/>
	<link rel='http://gdata.youtube.com/schemas/2007#video.related' type='application/atom+xml' href='http://gdata.youtube.com/feeds/api/videos/nWDfH51gvc0/related'/>
	<link rel='http://gdata.youtube.com/schemas/2007#mobile' type='text/html' href='http://m.youtube.com/details?v=nWDfH51gvc0'/>
	<link rel='self' type='application/atom+xml' href='http://gdata.youtube.com/feeds/api/videos/nWDfH51gvc0'/>
	<author>
		<name>angryladybug</name>
		<uri>http://gdata.youtube.com/feeds/api/users/angryladybug</uri>
	</author>
	<media:group>
		<media:category label='Music' scheme='http://gdata.youtube.com/schemas/2007/categories.cat'>Music</media:category>
		<media:content url='http://www.youtube.com/v/nWDfH51gvc0?version=3&amp;f=videos&amp;app=youtube_gdata' type='application/x-shockwave-flash' medium='video' isDefault='true' expression='full' duration='250' yt:format='5'/>
		<media:content url='rtsp://r3---sn-5hn7su7r.c.youtube.com/CiILENy73wIaGQnNvWCdH99gnRMYDSANFEgGUgZ2aWRlb3MM/0/0/0/video.3gp' type='video/3gpp' medium='video' expression='full' duration='250' yt:format='1'/>
		<media:content url='rtsp://r3---sn-5hn7su7r.c.youtube.com/CiILENy73wIaGQnNvWCdH99gnRMYESARFEgGUgZ2aWRlb3MM/0/0/0/video.3gp' type='video/3gpp' medium='video' expression='full' duration='250' yt:format='6'/>
		<media:description type='plain'>Sheryl Crow Live - Hallelujah</media:description>
		<media:keywords/>
		<media:player url='http://www.youtube.com/watch?v=nWDfH51gvc0&amp;feature=youtube_gdata_player'/>
		<media:thumbnail url='http://i.ytimg.com/vi/nWDfH51gvc0/0.jpg' height='360' width='480' time='00:02:05'/>
		<media:thumbnail url='http://i.ytimg.com/vi/nWDfH51gvc0/1.jpg' height='90' width='120' time='00:01:02.500'/>
		<media:thumbnail url='http://i.ytimg.com/vi/nWDfH51gvc0/2.jpg' height='90' width='120' time='00:02:05'/>
		<media:thumbnail url='http://i.ytimg.com/vi/nWDfH51gvc0/3.jpg' height='90' width='120' time='00:03:07.500'/>
		<media:title type='plain'>Sheryl Crow Hallelujah</media:title>
		<yt:duration seconds='250'/>
	</media:group>
	<gd:rating average='3.878795' max='5' min='1' numRaters='4381' rel='http://schemas.google.com/g/2005#overall'/>
	<yt:statistics favoriteCount='0' viewCount='4062201'/>
</entry>
 */

/**
 * Example decoded json v2 remote response - http://gdata.youtube.com/feeds/api/videos/nWDfH51gvc0?v=2&alt=json :
 *
Array
(
    [version] => 1.0
    [encoding] => UTF-8
    [entry] => Array
        (
            [xmlns] => http://www.w3.org/2005/Atom
            [xmlns$media] => http://search.yahoo.com/mrss/
            [xmlns$gd] => http://schemas.google.com/g/2005
            [xmlns$yt] => http://gdata.youtube.com/schemas/2007
            [gd$etag] => W/"Ck4DSH47eCp7I2A9XRZVGE8."
            [id] => Array
                (
                    [$t] => tag:youtube.com,2008:video:nWDfH51gvc0
                )

            [published] => Array
                (
                    [$t] => 2006-09-26T05:55:22.000Z
                )

            [updated] => Array
                (
                    [$t] => 2014-07-31T15:36:19.000Z
                )

            [category] => Array
                (
                    [0] => Array
                        (
                            [scheme] => http://schemas.google.com/g/2005#kind
                            [term] => http://gdata.youtube.com/schemas/2007#video
                        )

                    [1] => Array
                        (
                            [scheme] => http://gdata.youtube.com/schemas/2007/categories.cat
                            [term] => Music
                            [label] => Music
                        )

                )

            [title] => Array
                (
                    [$t] => Sheryl Crow Hallelujah
                )

            [content] => Array
                (
                    [type] => application/x-shockwave-flash
                    [src] => http://www.youtube.com/v/nWDfH51gvc0?version=3&f=videos&app=youtube_gdata
                )

            [link] => Array
                (
                    [0] => Array
                        (
                            [rel] => alternate
                            [type] => text/html
                            [href] => http://www.youtube.com/watch?v=nWDfH51gvc0&feature=youtube_gdata
                        )

                    [1] => Array
                        (
                            [rel] => http://gdata.youtube.com/schemas/2007#video.related
                            [type] => application/atom+xml
                            [href] => http://gdata.youtube.com/feeds/api/videos/nWDfH51gvc0/related?v=2
                        )

                    [2] => Array
                        (
                            [rel] => http://gdata.youtube.com/schemas/2007#mobile
                            [type] => text/html
                            [href] => http://m.youtube.com/details?v=nWDfH51gvc0
                        )

                    [3] => Array
                        (
                            [rel] => http://gdata.youtube.com/schemas/2007#uploader
                            [type] => application/atom+xml
                            [href] => http://gdata.youtube.com/feeds/api/users/QXsCC1_Q65Fbl80qmFvilQ?v=2
                        )

                    [4] => Array
                        (
                            [rel] => self
                            [type] => application/atom+xml
                            [href] => http://gdata.youtube.com/feeds/api/videos/nWDfH51gvc0?v=2
                        )

                )

            [author] => Array
                (
                    [0] => Array
                        (
                            [name] => Array
                                (
                                    [$t] => angryladybug
                                )

                            [uri] => Array
                                (
                                    [$t] => http://gdata.youtube.com/feeds/api/users/angryladybug
                                )

                            [yt$userId] => Array
                                (
                                    [$t] => QXsCC1_Q65Fbl80qmFvilQ
                                )

                        )

                )

            [yt$accessControl] => Array
                (
                    [0] => Array
                        (
                            [action] => comment
                            [permission] => denied
                        )

                    [1] => Array
                        (
                            [action] => commentVote
                            [permission] => allowed
                        )

                    [2] => Array
                        (
                            [action] => videoRespond
                            [permission] => moderated
                        )

                    [3] => Array
                        (
                            [action] => rate
                            [permission] => allowed
                        )

                    [4] => Array
                        (
                            [action] => embed
                            [permission] => allowed
                        )

                    [5] => Array
                        (
                            [action] => list
                            [permission] => allowed
                        )

                    [6] => Array
                        (
                            [action] => autoPlay
                            [permission] => allowed
                        )

                    [7] => Array
                        (
                            [action] => syndicate
                            [permission] => allowed
                        )

                )

            [media$group] => Array
                (
                    [media$category] => Array
                        (
                            [0] => Array
                                (
                                    [$t] => Music
                                    [label] => Music
                                    [scheme] => http://gdata.youtube.com/schemas/2007/categories.cat
                                )

                        )

                    [media$content] => Array
                        (
                            [0] => Array
                                (
                                    [url] => http://www.youtube.com/v/nWDfH51gvc0?version=3&f=videos&app=youtube_gdata
                                    [type] => application/x-shockwave-flash
                                    [medium] => video
                                    [isDefault] => true
                                    [expression] => full
                                    [duration] => 250
                                    [yt$format] => 5
                                )

                            [1] => Array
                                (
                                    [url] => rtsp://r3---sn-5hn7su7r.c.youtube.com/CiILENy73wIaGQnNvWCdH99gnRMYDSANFEgGUgZ2aWRlb3MM/0/0/0/video.3gp
                                    [type] => video/3gpp
                                    [medium] => video
                                    [expression] => full
                                    [duration] => 250
                                    [yt$format] => 1
                                )

                            [2] => Array
                                (
                                    [url] => rtsp://r3---sn-5hn7su7r.c.youtube.com/CiILENy73wIaGQnNvWCdH99gnRMYESARFEgGUgZ2aWRlb3MM/0/0/0/video.3gp
                                    [type] => video/3gpp
                                    [medium] => video
                                    [expression] => full
                                    [duration] => 250
                                    [yt$format] => 6
                                )

                        )

                    [media$credit] => Array
                        (
                            [0] => Array
                                (
                                    [$t] => angryladybug
                                    [role] => uploader
                                    [scheme] => urn:youtube
                                    [yt$display] => angryladybug
                                )

                        )

                    [media$description] => Array
                        (
                            [$t] => Sheryl Crow Live - Hallelujah
                            [type] => plain
                        )

                    [media$keywords] => Array
                        (
                        )

                    [media$license] => Array
                        (
                            [$t] => youtube
                            [type] => text/html
                            [href] => http://www.youtube.com/t/terms
                        )

                    [media$player] => Array
                        (
                            [url] => http://www.youtube.com/watch?v=nWDfH51gvc0&feature=youtube_gdata_player
                        )

                    [media$thumbnail] => Array
                        (
                            [0] => Array
                                (
                                    [url] => http://i.ytimg.com/vi/nWDfH51gvc0/default.jpg
                                    [height] => 90
                                    [width] => 120
                                    [time] => 00:02:05
                                    [yt$name] => default
                                )

                            [1] => Array
                                (
                                    [url] => http://i.ytimg.com/vi/nWDfH51gvc0/mqdefault.jpg
                                    [height] => 180
                                    [width] => 320
                                    [yt$name] => mqdefault
                                )

                            [2] => Array
                                (
                                    [url] => http://i.ytimg.com/vi/nWDfH51gvc0/hqdefault.jpg
                                    [height] => 360
                                    [width] => 480
                                    [yt$name] => hqdefault
                                )

                            [3] => Array
                                (
                                    [url] => http://i.ytimg.com/vi/nWDfH51gvc0/1.jpg
                                    [height] => 90
                                    [width] => 120
                                    [time] => 00:01:02.500
                                    [yt$name] => start
                                )

                            [4] => Array
                                (
                                    [url] => http://i.ytimg.com/vi/nWDfH51gvc0/2.jpg
                                    [height] => 90
                                    [width] => 120
                                    [time] => 00:02:05
                                    [yt$name] => middle
                                )

                            [5] => Array
                                (
                                    [url] => http://i.ytimg.com/vi/nWDfH51gvc0/3.jpg
                                    [height] => 90
                                    [width] => 120
                                    [time] => 00:03:07.500
                                    [yt$name] => end
                                )

                        )

                    [media$title] => Array
                        (
                            [$t] => Sheryl Crow Hallelujah
                            [type] => plain
                        )

                    [yt$duration] => Array
                        (
                            [seconds] => 250
                        )

                    [yt$uploaded] => Array
                        (
                            [$t] => 2006-09-26T05:55:22.000Z
                        )

                    [yt$uploaderId] => Array
                        (
                            [$t] => UCQXsCC1_Q65Fbl80qmFvilQ
                        )

                    [yt$videoid] => Array
                        (
                            [$t] => nWDfH51gvc0
                        )

                )

            [gd$rating] => Array
                (
                    [average] => 3.878795
                    [max] => 5
                    [min] => 1
                    [numRaters] => 4381
                    [rel] => http://schemas.google.com/g/2005#overall
                )

            [yt$statistics] => Array
                (
                    [favoriteCount] => 0
                    [viewCount] => 4063321
                )

            [yt$rating] => Array
                (
                    [numDislikes] => 1228
                    [numLikes] => 3153
                )

        )
)
*/

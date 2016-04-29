/*
 Service: MySpace Video
 
 Current status: maybe... would need extensive checking that the url is a video url and such
 
 Supposedly they had an API, but the urls 404 at the moment.
 By the looks of it, we could get the info from the video page itself (meta data in head tag)

 - Embedly does not recognize a video link as video
 - all the oembed / direct video link kind of templates I've found on the web, no longer seem to work
 - the url used in the below code, again, also no longer seems to work
 

 @see http://developer.myspace.com/
 @see http://wiki.developer.myspace.com/index.php?title=Category%3ARESTful_API
 
 Example url:
 https://myspace.com/myspace/video/shawn-mendes-busking/109561937
 */

/*
            case 'myspace' :

            # XML data URL
            $file_data = "http://mediaservices.myspace.com/services/rss.ashx?type=video&videoID=".self::$id;
            self::$video->xml_url = $file_data;
            
            # XML
            $xml = new SimpleXMLElement(file_get_contents($file_data));
            
            # Duration
            self::$video->duration = null;

            # Thumbnails
            $thumbnails_query = $xml->xpath('/rss/channel/item/media:thumbnail/@url');
            $thumbnail = new stdClass;
            $thumbnail->url = strval($thumbnails_query[0]);
            list($thumbnail->width, $thumbnail->height) = getimagesize($thumbnail->url);
            self::$video->thumbnails[] = $thumbnail;
            
            # Player URL
            self::$video->player_url = "http://lads.myspace.com/videos/vplayer.swf?m=" . self::$id;
            http://mediaservices.myspace.com/services/media/embed.aspx/m=' . $videoid
            
            # FLV file URL
            $flv_url_query = $xml->xpath('/rss/channel/item/media:content[@type="video/x-flv"]/@url');
            self::$video->files['video/x-flv'] = $flv_url_query ? strval($flv_url_query[0]) : null;
            
            break;
*/
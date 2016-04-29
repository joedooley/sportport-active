/*
 Service: GoogleVideo (legacy))))

 Current status: let's not

 - not sure we should as what with Google taking over YouTube, this would only be to support old links
 - Embedly does recognize these video links, but returns only minimal information

 Example urls/data:
http://video.google.com/googleplayer.swf?docId=-6006084025483872237
[google -5024787479139933029]
[googlevideo]http://video.google.com/videoplay?docid=-6006084025483872237[/googlevideo]
[googlevideo]3755578433803905218[/googlevideo]
[googlevideo]http://video.google.com/videoplay?docid=-5784010886294950089[/googlevideo]
[youtuber googlevideo="3755578433803905218" /]
[youtuber googlevideo="http://video.google.com/videoplay?docid=-5784010886294950089"][/youtuber]


Player url:
http://static.googleusercontent.com/media/video.google.com/en//googleplayer.swf?docId=-6006084025483872237


Sample code - unfortunately, the feed data retrieval does no longer work

            case 'googlevideo' :
            
            # XML data URL
            $file_data = 'http://video.google.com/videofeed?docid='.self::$id;
            self::$video->xml_url = $file_data;
            
            # XML
            $xml = new SimpleXMLElement(utf8_encode(file_get_contents($file_data)));
            $xml->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/');
            
            # Duration
            $duration_query = $xml->xpath('/rss/channel/item/media:group/media:content/@duration');
            self::$video->duration = $duration_query ? intval($duration_query[0]) : null;

            # Thumbnails
            $thumbnails_query = $xml->xpath('/rss/channel/item/media:group/media:thumbnail');
            $thumbnails_query = $thumbnails_query[0]->attributes();
            $thumbnail = new stdClass;
            $thumbnail->url = strval(preg_replace('#&amp;#', '&', $thumbnails_query['url']));
            $thumbnail->width = intval($thumbnails_query['width']);
            $thumbnail->height = intval($thumbnails_query['height']);
            self::$video->thumbnails[] = $thumbnail;

            # Player URL
            $player_url_query = $xml->xpath('/rss/channel/item/media:group/media:content[@type="application/x-shockwave-flash"]/@url');
            self::$video->player_url = $player_url_query ? strval($player_url_query[0]) : null;

            # AVI file URL
            $avi_url_query = $xml->xpath('/rss/channel/item/media:group/media:content[@type="video/x-msvideo"]/@url');
            self::$video->files['video/x-msvideo'] = $avi_url_query ? preg_replace('#&amp;#', '&', $avi_url_query[0]) : null;

            # FLV file URL
            $flv_url_query = $xml->xpath('/rss/channel/item/media:group/media:content[@type="video/x-flv"]/@url');
            self::$video->files['video/x-flv'] = $flv_url_query ? strval($flv_url_query[0]) : null;
            
            # MP4 file URL
            $mp4_url_query = $xml->xpath('/rss/channel/item/media:group/media:content[@type="video/mp4"]/@url');
            self::$video->files['video/mp4'] = $mp4_url_query ? preg_replace('#&amp;#', '&', $mp4_url_query[0]) : null;
            
            break;
            
*/
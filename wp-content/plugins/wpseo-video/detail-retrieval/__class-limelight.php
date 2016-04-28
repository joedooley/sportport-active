/*
 Service: limelight

 Current status: let's leave this for now, maybe if we have more example data (urls in posts), we can see if we can make this work.

 Single video detail call via API would result in some semi-useful information, though still not everything we need and most notably no url hints (see below). However for every such call an organization id and a media id are needed and most of the time, we won't be able to distill both from the info available before the API call.


 API endpoint:
 http://api.video.limelight.com/rest/

 Single video details - does not need authorisation:
 http://api.video.limelight.com/rest/organizations/<org id>/media/<media id>/properties.{XML,JSON}

 @see http://support.video.limelight.com/support/docs/content_api/
 @see http://support.video.limelight.com/support/docs/player_api/
 @see http://support.video.limelight.com/support/docs/api_explorer/
 */


/*
Example API call:
http://api.video.limelight.com/rest/organizations/6d4242bd0cf94083a0195bfc2083e46e/media/3ffd040b522b4485b6d84effc750cd86/properties.xml

JSON decoded response:
Array
(
    [publish_date] => 1244136834
    [category] => Entertainment
    [description] => As Harry Potter begins his 6th year at Hogwarts School of Witchcraft and Wizardry, he discovers an old book marked mysteriously "This book is the property of the Half-Blood Prince" and begins to learn more about Lord Voldemort's dark past.
    [sched_end_date] => 
    [tags] => Array
        (
            [0] => harry potter
            [1] => movie
            [2] => trailer
        )

    [title] => HaP and the HB Prince Trailer
    [media_id] => 3ffd040b522b4485b6d84effc750cd86
    [media_type] => Video
    [original_filename] => harrypotterhalfbloodprince-tlr4b_h720p_stereo
    [sched_start_date] => 
    [restrictionrule_id] => 
    [create_date] => 1244136834
    [state] => Published
    [total_storage_in_bytes] => 306062641
    [allow_ads] => 1
    [thumbnails] => Array
        (
            [0] => Array
                (
                    [url] => http://cpc.delvenetworks.com/bUJCvQz5QIMoBlb_CCD5G4/P_0EC1IrRIUtthO_8dQzYY/m77.120x50.jpeg
                    [width] => 120
                    [height] => 50
                )

            [1] => Array
                (
                    [url] => http://cpc.delvenetworks.com/bUJCvQz5QIMoBlb_CCD5G4/P_0EC1IrRIUtthO_8dQzYY/m77.540x230.jpeg
                    [width] => 540
                    [height] => 230
                )

        )

    [ref_id] => 
    [update_date] => 1378313483
    [custom_property] => Array
        (
            [Embedded On] => Javascript Sample Page
        )

    [duration_in_milliseconds] => 144230
    [closed_captions_url] => 
    [captions] => Array
        (
        )

)

*/

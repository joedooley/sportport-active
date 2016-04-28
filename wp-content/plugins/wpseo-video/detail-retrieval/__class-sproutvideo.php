/*
 Service: sproutvideo
 
 Current status: let's not

 Has an good API, but every request needs authorisation, so we'd either need to include a key
  or ask the user for their API key via the admin interface.

 "Authentication is achieved by setting the SproutVideo-Api-Key header to your api key in each request.
 You can find your api key by logging in to your account and clicking on the "Account" link in the upper right."

 Single video API call format (gives us all we need):
 https://api.sproutvideo.com/v1/videos/:id

 - Embedly does not recognize a video link as video

 @see http://sproutvideo.com/docs/api.html
 @see https://github.com/sproutvideo/sproutvideo-php API wrapper


 API endpoint:
 https://api.sproutvideo.com/v1/


 Example url:
 //videos.sproutvideo.com/embed/e898d2b5111be3c860/546cd1548010aaeb?type=sd
 */
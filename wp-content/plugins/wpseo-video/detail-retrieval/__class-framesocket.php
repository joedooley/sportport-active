/*
 Service: framesocket
 
 Current status: let's not
 
 Has a semi-useful API (does not offer all the info we need), but *every* request need authorisation and even single-use tokens need a username to be requested.

 " All requests must have the following post values to authorize the request:
    key : Your account username.
    secret : Your account API secret.
    sig : This is an md5 hash of your gatekeeper concatenated with your request action.

   For some applications it makes more sense to request a single-use token that works for only one API request.
   You can then use the returned token to sign a SINGLE API request. That request must have the following:

    key : Your account username.
    token : This is your single use token.
 "
 
 - Haven't been able to test embedly as I can't find an example url


 @see http://www.framesocket.com/for-developers/
 @see http://www.framesocket.com/for-developers/api-documentation/responses/

 */
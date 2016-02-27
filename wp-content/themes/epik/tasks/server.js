// Include Gulp.
var gulp        = require( 'gulp' );

// Include Plugins.
var browserSync = require( 'browser-sync' ).create();

var config 		= require( '../tasks/config' ).server;

// Set up BrowserSync.
gulp.task( 'server' , function() {

    // Initiate BrowserSync using the local url (defined above).
    browserSync.init({
        proxy: config.url
    });

});
// Include Gulp.
var gulp        = require( 'gulp' );

// Include Plugins.
var browserSync = require( 'browser-sync' ).create();

var config 		= require( '../tasks/config' ).watch;

// Watch Task.
gulp.task( 'watch' , function() {

    // Set the watch tasks for different file type
    gulp.watch( config.styles , ['styles'] );
    gulp.watch( config.scripts , ['scripts'] );
    gulp.watch( config.code ).on( 'change' , browserSync.reload );

});
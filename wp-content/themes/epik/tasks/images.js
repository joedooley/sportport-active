// Include Gulp.
var gulp        = require( 'gulp' );

// Include Plugins.
var imagemin    = require( 'gulp-imagemin' ),
    notify      = require( 'gulp-notify' ),
    cache       = require( 'gulp-cache' ),
    browserSync = require( 'browser-sync' ).create();

var config 		= require( '../tasks/config' ).images;

// Images task.
gulp.task( 'images' , function() {

    return gulp.src( config.src )
        .pipe( cache( imagemin( { optimizationLevel: 3, progressive: true, interlaced: true } ) ) )
        .pipe( gulp.dest( config.dest ) )
        .pipe( notify( { message: config.message } ) )
        .pipe( browserSync.stream() );

} );
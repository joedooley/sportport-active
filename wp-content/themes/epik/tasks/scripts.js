// Include Gulp.
var gulp        = require( 'gulp' );

// Include Plugins.
var sourcemaps  = require( 'gulp-sourcemaps' ),
    jshint      = require( 'gulp-jshint' ),
    notify      = require( 'gulp-notify' ),
    concat      = require( 'gulp-concat' ),
    browserSync = require( 'browser-sync' ).create();

var config      = require( '../tasks/config' ).scripts;

// Scripts task.
gulp.task( 'scripts' , function() {

    return gulp.src( config.src )
        .pipe( jshint( '.jshintrc' ) )
        .pipe( jshint.reporter( 'default' ) )
        .pipe( sourcemaps.init() )
            .pipe( concat( config.output ) )
        .pipe( sourcemaps.write() )
        .pipe( gulp.dest( config.dest ) )
        .pipe( notify( { message: config.message } ) )
        .pipe( browserSync.stream() );

} );
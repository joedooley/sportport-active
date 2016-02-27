var gulp        = require( 'gulp' );

var notify      = require( 'gulp-notify' ),
	potgen      = require( 'gulp-wp-pot' ),
    sort        = require( 'gulp-sort' );

var config      = require( '../tasks/config' ).i18n;

gulp.task( 'i18n' , function () {

    return gulp.src( config.src )
        .pipe( sort() )
        .pipe( potgen( {
            domain: config.textdomain,
            destFile: config.textdomain + '.pot'
        } ))
        .pipe(gulp.dest( config.dest ))
        .pipe( notify( { message: config.message } ) );

});
// Include Gulp.
var gulp        = require( 'gulp' ),
    requireDir 	= require('require-dir');

requireDir('./tasks', { recurse: true } );

// Default Tasks.
gulp.task( 'default' , [ 'watch' ] );
gulp.task( 'serve' , [ 'server', 'watch' ] );
gulp.task( 'build' , [ 'scripts', 'images','bower', 'styles', 'i18n' ] )

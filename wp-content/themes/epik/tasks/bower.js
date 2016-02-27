// Include Gulp.
var gulp        = require( 'gulp' );

// Include Plugin.
var bower        = require( 'gulp-bower' );

// Run Bower Install
gulp.task( 'bower' , function() {

    // Change current working directory to theme/child theme root, ie... above our `/tasks/`
    return bower({cwd: '../'});

});
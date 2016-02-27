// Include Gulp.
var gulp        = require( 'gulp' );

// Include Plugins.
var sass        = require( 'gulp-sass' ),
    sourcemaps  = require( 'gulp-sourcemaps' ),
    plumber     = require( 'gulp-plumber' ),
    notify      = require( 'gulp-notify' ),
    postcss     = require( 'gulp-postcss' ),
    mqpacker    = require( 'css-mqpacker' ),
    pxtorem     = require( 'postcss-pxtorem' ),
    autoprefix  = require( 'autoprefixer'),
    browserSync = require( 'browser-sync' ).create();

var config      = require( '../tasks/config' ).styles;

// Styles tasks.
gulp.task( 'styles' , function() {

    var postProcessors = [
        mqpacker({
            sort: true
        }),
        autoprefix({
            browsers: ['last 2 versions']
        }),
        pxtorem({
            root_value: 16,
            replace: false,
            media_query: true
        })
    ];

    return gulp
    .src( config.src )
        .pipe(plumber({
            handleError: function (err) {
                console.log(err);
                this.emit('end');
            }
        }))
        .pipe( sourcemaps.init() )
            .pipe( sass({outputStyle: config.output }) )
            .pipe( postcss( postProcessors ) )
        .pipe( sourcemaps.write( '.' ) )
        .pipe( gulp.dest( config.dest ) )
        .pipe( notify( { message: config.message } ) )
        .pipe( browserSync.stream() );

});
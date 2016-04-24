/**
 * Gulpfile setup
 *
 * @package    SportPort Active
 * @author     Developing Designs - Joe Dooley
 * @link       https://www.developingdesigns.com
 * @copyright  Joe Dooley, Developing Designs
 * @license    GPL-2.0+
 */

var gulp         = require('gulp'),
    browserSync  = require('browser-sync'),
    connect      = require('gulp-connect'),
    autoprefixer = require('gulp-autoprefixer'),
    minifycss    = require('gulp-uglifycss'),
    filter       = require('gulp-filter'),
    uglify       = require('gulp-uglify'),
    imagemin     = require('gulp-imagemin'),
    pngquant     = require('imagemin-pngquant'),
	newer        = require('gulp-newer'),
    rename       = require('gulp-rename'),
    concat       = require('gulp-concat'),
    notify       = require('gulp-notify'),
    header       = require('gulp-header'),
    sass         = require('gulp-sass'),
    plugins      = require('gulp-load-plugins')({camelize: true}),
    ignore       = require('gulp-ignore'),
    plumber      = require('gulp-plumber'),
    sourcemaps   = require('gulp-sourcemaps'),
    wpPot        = require('gulp-wp-pot'),
    sort         = require('gulp-sort'),
    zip          = require('gulp-zip'),
    babel        = require('gulp-babel'),
    reload       = browserSync.reload;



// For sexy error notifications.
var plumberErrorHandler = {
	errorHandler: notify.onError({
		title: 'Gulp',
		message: 'Error: <%= error.message %>'
	})
};


// Directory globs.
var root          = './',
    source        = './assets/',
    dist          = './dist/',
    zipped        = './zipped/',
    scss          = 'sass/**/*.scss',
    js_custom     = 'js/custom/*.js',
	js_vendor     = 'js/vendors/*.js',
    php           = './**/*.php',
    raw           = './images/raw/**/*.{ png, jpg, gif, svg }';



/**
 * Our WordPress block for adding to the head of style.css
 *
 * @link    https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/join
 */
var styleHeader = [
	'/* # Genesis Child Theme',
	'Theme Name: Epik',
	'Theme URI: http://appfinite.com/themes/epik/',
	'Description: Epik is a mobile responsive and HTML5 theme built for the Genesis Framework.',
	'Author: Appfinite',
	'Author URI: http://www.appfinite.com/',
	'Version: 1.0.0',
	'Text Domain: epik',
	'Template: genesis',
	'Tags: mobile-first, responsive, genesis, bourbon, neat, bitters, refills, gulp, i18n, accessible, woocommerce, acf',
	'*/',
	''
].join('\n');



/**
 * Task: `styles` - WITHOUT BROWSERSYNC.
 */
gulp.task('styles', function () {
	return gulp.src( source + 'sass/style.scss' )
		.pipe( plumber(plumberErrorHandler ) )
		.pipe( sourcemaps.init() )
		.pipe( sass( { outputStyle: 'compressed' } ) )
		.pipe( header( styleHeader ) )
		.pipe( sourcemaps.write( './' ) )
		.pipe( gulp.dest( root ) )
		.pipe( reload( { stream: true } ) );
		// .pipe( notify( { message: 'TASK: "styles" Completed!', onLast: true } ) );
});




/**
 * gulp js
 *
 * Runs each js file through Babel and than concats them into a minified .dist/all.js.
 * We are not including any files in the /single directories.
 */
gulp.task('js', function () {
	return gulp.src([
		source + js_custom,
		source + js_vendor
	])
		.pipe(plumber(plumberErrorHandler))
		.pipe(sourcemaps.init())
		.pipe(babel())
		//.pipe(uglify())
		.pipe(concat('all.js'))
		.pipe(sourcemaps.write('./'))
		.pipe(gulp.dest(dist + 'js/'));
});


/**
 * Scripts: Custom
 *
 * Look at src/js and concatenate those files, send them to assets/js where we then minimize the concatenated file.
 */

//gulp.task('scriptsJs', function () {
//	return gulp.src(['./assets/js/src/custom/*.js', '!./assets/js/src/custom/single/*.js'])
//		.pipe(concat('custom.js'))
//		.pipe(gulp.dest('./assets/js/dist'))
//		.pipe(rename({
//			basename: "custom",
//			suffix: '.min'
//		}))
//		.pipe(uglify())
//		.pipe(gulp.dest('./assets/js/dist/'))
//		.pipe(notify({message: 'Custom scripts task complete', onLast: true}));
//});


/**
 * Scripts: Vendors
 *
 * Look at src/js and concatenate those files, send them to assets/js where we then minimize the concatenated file.
 */
//gulp.task('vendorsJs', function () {
//	return gulp.src(['./assets/js/src/vendors/*.js', bower + '**/*.js', '!./assets/js/src/vendors/single/*.js'])
//		.pipe(concat('vendors.js'))
//		.pipe(gulp.dest('./assets/js/dist'))
//		.pipe(rename({
//			basename: "vendors",
//			suffix  : '.min'
//		}))
//		.pipe(uglify())
//		.pipe(gulp.dest('./assets/js/dist/'))
//		.pipe(notify({message: 'Vendor scripts task complete', onLast: true}));
//});



/**
 * Task: `images`.
 *
 * Minifies PNG, JPEG, GIF and SVG images.
 *
 * This task does the following:
 *    1. Gets the source of images raw folder
 *    2. Minifies PNG, JPEG, GIF and SVG images
 *    3. Generates and saves the optimized images
 *
 * This task will run only once, if you want to run it
 * again, do it with the command `gulp images`.
 */
gulp.task( 'images', function() {
	return gulp.src('./images/raw/**/*.+(png|jpg|gif|svg)' )
		.pipe( imagemin( {
			progressive      : true,
			svgoPlugins      : [ { removeViewBox: false } ],
			use              : [ pngquant() ]
		} ) )
		.pipe( gulp.dest( './images' ) )
		.pipe( notify( { message: 'TASK: "images" Completed!', onLast: true } ) );
});



/**
 * Gulp Task Localization
 */
gulp.task( 'pot', function() {
	return gulp.src( php )
		.pipe( sort() )
		.pipe( wpPot( {
			domain: 'CHILD_THEME_TEXTDOMAIN',
			destFile: 'sportport-active.pot',
			package: 'Epik',
			bugReport: 'https://www.developingdesigns.com/',
			lastTranslator: 'Joe Dooley <wordpress@developingdesigns.com>',
			team: 'Developing Designs <wordpress@developingdesigns.com>'
		} ) )
		.pipe( gulp.dest( './languages/' ) );

} );


/**
 * Browser-Sync Proxy Server + watching scss/php/js files
 */
gulp.task( 'serve', [ 'vendorsJs', 'scriptsJs' ], function() {
	browserSync( {
		proxy: {
			target: 'sportport.dev',
			logLevel: 'debug'

			/*ws: true,
			 middleware: [
			 webpackDevMiddleware( bundler, {
			 // IMPORTANT: dev middleware can't access config, so we should provide publicPath by ourselves.
			 publicPath: webpackConfig.output.publicPath,
			 // Pretty colored output.
			 stats: { colors: true }
			 // For other settings see http://webpack.github.io/docs/webpack-dev-middleware.html.
			 }),
			 // Bundler should be the same as above.
			 webpackHotMiddleware( bundler )
			 ]*/
		}
		/**
		 * Browsersync named tunnel.
		 *
		 * Uncomment the below line to serve the site
		 * at http://sportport.localtunnel.me.
		 */
		// tunnel: 'sportport',
	} );
	gulp.watch( source + scss, [ 'styles' ] );
	gulp.watch( [ source + js ], [ 'vendorsJs', 'scriptsJs' ] );
	gulp.watch( [ php ], ['pot']).on('change', reload);
});

// Default task
gulp.task( 'default', [ 'serve', 'images' ] );


// Our dist task for packaging a clean theme all zipped up.
// Called from the commandline with `gulp dist`.
gulp.task( 'dist', function() {
	return gulp.src([
			root + '**',
			'!' + root + 'bower_components{,/**}',
			'!' + root + 'node_modules{,/**}',
			'!' + root + 'zipped{,/**}',
			'!' + root + 'src{,/**}',
			'!' + root + '**/*.map'
		],
		{ dot: false }
	)
		.pipe( zip( 'sportport.zip') )
		.pipe( gulp.dest( zipped ) );
});


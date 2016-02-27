/**
 *
 * Gulpfile setup
 *
 * @since 1.0.0
 * @authors Joe Dooley
 * @package Thin Places Tour Theme
 *
 */

// Project configuration
var project      = 'thin-places-tour', // Project name, used for build zip.
    //url       = http://thinplacestour.dev/, // Local Development URL for BrowserSync. Change as-needed.
    bower        = './bower_components/', // Not truly using this yet, more or less playing right now. TO-DO Place in Dev
    build        = 'dist/', // Files that you want to package into a zip go here
    buildInclude = [
	    // include common file types
	    '**/*.php',
	    '**/*.html',
	    '**/*.css',
	    '**/*.js',
	    '**/*.svg',
	    '**/*.ttf',
	    '**/*.otf',
	    '**/*.eot',
	    '**/*.woff',
	    '**/*.woff2',

	    // include specific files and folders
	    'screenshot.png',

	    // exclude files and folders
	    '!node_modules/**/*',
	    '!bower_components/**/*',
	    '!style.css.map',
	    '!assets/js/custom/*',
	    '!assets/sass/general/*'

    ];


var imagesSRC         = './assets/images/raw/**/*.{png,jpg,gif,svg}'; // Source folder of images which should be optimized.
var imagesDestination = './assets/images/'; // Destination folder of optimized images. Must be different from the imagesSRC folder.
var projecturl        = 'thinplacestour.dev'; // Project URL. Could be something like localhost:8888.


var styleSRC         = './assets/sass/style.scss'; // Path to main .scss file.
var styleDestination = './'; // Path to place the compiled CSS file.

// Watch files paths.
var styleWatchFiles    = './assets/sass/**/*.scss'; // Path to all *.scss files inside css folder and inside them.
var vendorJSWatchFiles = './assets/js/vendors/*.js'; // Path to all vendors JS files.
var customJSWatchFiles = './assets/js/custom/*.js'; // Path to all custom JS files.

// Browsers you care about for autoprefixing.
// Browserlist https://github.com/ai/browserslist
const AUTOPREFIXER_BROWSERS = [
	'last 2 version',
	'> 1%',
	'ie >= 9',
	'ie_mob >= 10',
	'ff >= 30',
	'chrome >= 34',
	'safari >= 7',
	'opera >= 23',
	'ios >= 7',
	'android >= 4',
	'bb >= 10'
];


var gulp         = require('gulp'),
    //browserSync     = require('browser-sync'),
    //reload          = browserSync.reload,
    connect      = require('gulp-connect'),
    //gulpLoadPlugins = require('gulp-load-plugins'),
    autoprefixer = require('gulp-autoprefixer'),
    minifycss    = require('gulp-uglifycss'),
    filter       = require('gulp-filter'),
    uglify       = require('gulp-uglify'),
    imagemin     = require('gulp-imagemin'),
    newer        = require('gulp-newer'),
    rename       = require('gulp-rename'),
    concat       = require('gulp-concat'),
    notify       = require('gulp-notify'),
    cmq          = require('gulp-combine-media-queries'),
    //runSequence     = require('gulp-run-sequence'),
    sass         = require('gulp-sass'),
    plugins      = require('gulp-load-plugins')({camelize: true}),
    ignore       = require('gulp-ignore'), // Helps with ignoring files and directories in our run tasks
    rimraf       = require('gulp-rimraf'), // Helps with removing files and directories in our run tasks
    zip          = require('gulp-zip'), // Using to zip up our packaged theme into a tasty zip file that can be installed in WordPress!
    plumber      = require('gulp-plumber'),
    cache        = require('gulp-cache'),
    sourcemaps   = require('gulp-sourcemaps');

/**
 * Browser Sync
 *
 * Asynchronous browser syncing of assets across multiple devices!! Watches for changes to js, image and php files
 * Although, I think this is redundant, since we have a watch task that does this already.
 */
/*gulp.task('browser-sync', function() {
 var files = [
 '**//*.php
 '**//*.{png,jpg,gif,svg}'
 ];
 browserSync.init(files, {

 // Read here http://www.browsersync.io/docs/options/
 proxy: url,

 // port: 8080,

 // Tunnel the Browsersync server through a random Public URL
 // tunnel: true,

 // Attempt to use the URL "http://my-private-site.localtunnel.me"
 // tunnel: "ppress",

 // Inject CSS changes
 injectChanges: true

 });
 });*/


/**
 * Task: `styles`.
 *
 * Compiles Sass, Autoprefixes it and Minifies CSS.
 *
 * This task does the following:
 *    1. Gets the source scss file
 *    2. Compiles Sass to CSS
 *    3. Writes Sourcemaps for it
 *    4. Autoprefixes it and generates style.css
 *    5. Renames the CSS file with suffix .min.css
 *    6. Minifies the CSS file and generates style.min.css
 *    7. Injects CSS or reloads the browser via browserSync
 */
gulp.task('styles', function () {
	gulp.src(styleSRC)
		.pipe(sourcemaps.init())
		.pipe(sass({
			errLogToConsole: true,
			// outputStyle: 'compact',
			// outputStyle: 'compressed',
			// outputStyle: 'nested',
			outputStyle    : 'expanded',
			precision      : 10
		}))
		.pipe(sourcemaps.write({includeContent: false}))
		.pipe(sourcemaps.init({loadMaps: true}))
		.pipe(autoprefixer(AUTOPREFIXER_BROWSERS))

		.pipe(sourcemaps.write(styleDestination))
		.pipe(gulp.dest(styleDestination))


		.pipe(rename({suffix: '.min'}))
		.pipe(minifycss({
			maxLineLen: 10
		}))
		.pipe(gulp.dest(styleDestination))
		//.pipe( browserSync.stream() )
		.pipe(notify({message: 'TASK: "styles" Completed!', onLast: true}))
});


/**
 * Scripts: Vendors
 *
 * Look at src/js and concatenate those files, send them to assets/js where we then minimize the concatenated file.
 */
gulp.task('vendorsJs', function () {
	return gulp.src(['./assets/js/vendors/*.js', bower + '**/*.js', '!./assets/js/vendors/single/*.js'])
		.pipe(concat('vendors.js'))
		.pipe(gulp.dest('./assets/js'))
		.pipe(rename({
			basename: "vendors",
			suffix  : '.min'
		}))
		.pipe(uglify())
		.pipe(gulp.dest('./assets/js/'))
		.pipe(notify({message: 'Vendor scripts task complete', onLast: true}));
});


/**
 * Scripts: Custom
 *
 * Look at src/js and concatenate those files, send them to assets/js where we then minimize the concatenated file.
 */

gulp.task('scriptsJs', function () {
	return gulp.src(['./assets/js/custom/*.js', '!./assets/js/custom/single/*.js'])
		.pipe(concat('custom.js'))
		.pipe(gulp.dest('./assets/js'))
		.pipe(rename({
			basename: "custom",
			suffix  : '.min'
		}))
		.pipe(uglify())
		.pipe(gulp.dest('./assets/js/'))
		.pipe(notify({message: 'Custom scripts task complete', onLast: true}));
});


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
gulp.task('images', function () {
	gulp.src(imagesSRC)
		.pipe(imagemin({
			progressive      : true,
			optimizationLevel: 3, // 0-7 low-high
			interlaced       : true,
			svgoPlugins      : [{removeViewBox: false}]
		}))
		.pipe(gulp.dest(imagesDestination))
		.pipe(notify({message: 'TASK: "images" Completed!', onLast: true}));
});


/**
 * Clean gulp cache
 */
gulp.task('clear', function () {
	cache.clearAll();
});


/**
 * Clean tasks for zip
 *
 * Being a little overzealous, but we're cleaning out the build folder, codekit-cache directory and annoying DS_Store files and Also
 * clearing out unoptimized image files in zip as those will have been moved and optimized
 */

gulp.task('cleanup', function () {
	return gulp.src(['bower_components', 'assets/**/.sass-cache', 'assets/**/.DS_Store'], {read: false}) // much faster
		.pipe(ignore('node_modules/**')) //Example of a directory to ignore
		.pipe(rimraf({force: true}))
	// .pipe(notify({ message: 'Clean task complete', onLast: true }));
});
gulp.task('cleanupFinal', function () {
	return gulp.src(['bower_components', 'assets/**/.sass-cache', 'assets/**/.DS_Store'], {read: false}) // much faster
		.pipe(ignore('node_modules/**')) //Example of a directory to ignore
		.pipe(rimraf({force: true}))
	// .pipe(notify({ message: 'Clean task complete', onLast: true }));
});


/**
 * Build task that moves essential theme files for production-ready sites
 *
 * buildFiles copies all the files in buildInclude to build folder - check variable values at the top
 * buildImages copies all the images from img folder in assets while ignoring images inside raw folder if any
 */

gulp.task('buildFiles', function () {
	return gulp.src(buildInclude)
		.pipe(gulp.dest(build))
		.pipe(notify({message: 'Copy from buildFiles complete', onLast: true}));
});


/**
 * Images
 *
 * Look at assets/images, optimize the images and send them to the appropriate place
 */
gulp.task('buildImages', function () {
	return gulp.src(['assets/images', '!assets/images/raw', '!assets/images/svg/sprite-src'])
		.pipe(gulp.dest(build + 'assets/images'))
		.pipe(plugins.notify({message: 'Images copied to buildTheme folder', onLast: true}));
});


/**
 * Zipping build directory for distribution
 *
 * Taking the build folder, which has been cleaned, containing optimized files and zipping it up to send out as an installable theme
 */
gulp.task('buildZip', function () {
// return   gulp.src([build+'/**/', './.jshintrc','./.bowerrc','./.gitignore' ])
	return gulp.src(build + '/**/')
		.pipe(zip(project + '.zip'))
		.pipe(gulp.dest('./'))
		.pipe(notify({message: 'Zip task complete', onLast: true}));
});


// ==== TASKS ==== //
/**
 * Gulp Default Task
 *
 * Compiles styles, fires-up browser sync, watches js and php files. Note browser sync task watches php files
 *
 */


// Package Distributable Theme
gulp.task('build', function (cb) {
	gulp.watch('styles', 'cleanup', 'vendogulp stylesrsJs', 'scriptsJs', 'buildFiles', 'buildImages', 'buildZip', 'cleanupFinal', cb);
});


// Watch Task
gulp.task('default', ['styles', 'vendorsJs', 'scriptsJs', 'images'], function () {
	gulp.watch('./assets/images/raw/**/*', ['images']);
	gulp.watch('./assets/sass/*.scss', ['styles']);
	gulp.watch('./assets/js/**/*.js', ['scriptsJs']);
});






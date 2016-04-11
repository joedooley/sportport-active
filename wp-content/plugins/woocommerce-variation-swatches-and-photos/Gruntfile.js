module.exports = function (grunt) {
	'use strict';

	grunt.initConfig({
		
		makepot: {
			options: {
				type: 'wp-plugin',
				domainPath: 'i18n/languages',
				potHeaders: {
					'report-msgid-bugs-to': '',
					'language-team': 'LANGUAGE <EMAIL@ADDRESS>'
				}
			},
			dist: {
				options: {
					potFilename: 'wc_swatches_and_photos.pot',
					exclude: [
						'apigen/.*',
						'tests/.*',
						'tmp/.*'
					]
				}
			}
		},
	});

	// Load NPM tasks to be used here
	grunt.loadNpmTasks('grunt-wp-i18n');

	// Register tasks
	grunt.registerTask('default', [
		'makepot'
	]);	

};


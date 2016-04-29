/* global YoastSEO: true, wpseoVideoL10n */
(function() {

	'use strict';

	/**
	 * Adds eventListener on page load to load the videoSEO.
	 */
	if ( wpseoVideoL10n.has_video === '1' ) {
		if ( typeof YoastSEO !== 'undefined' && typeof YoastSEO.app !== 'undefined' ) {
			new YoastVideoSEOplugin();
		}
		else {
			jQuery( window ).on(
				'YoastSEO:ready',
				function() {
					new YoastVideoSEOplugin();
				}
			);
		}
	}

	/**
	 * Adds the plugin for videoSEO to the YoastSEO Analyzer.
	 */
	function YoastVideoSEOplugin() {
		YoastSEO.app.registerPlugin( 'YoastVideoSEO', { 'status': 'ready' } );

		YoastSEO.app.registerTest( 'videoTitle', this.videoTitle, videoTitleScore, 'YoastVideoSEO' );

		YoastSEO.app.registerTest( 'videoBodyLength', this.videoBodyLength, videoBodyLengthScore, 'YoastVideoSEO' );
	}

	/**
	 * Tests if the word video appears in the title, returns number of matches
	 * @returns int
	 */
	YoastVideoSEOplugin.prototype.videoTitle = function() {
		var videoRegex = new RegExp( wpseoVideoL10n.video, 'ig' );
		var matches = YoastSEO.app.rawData.title.match( videoRegex );
		var result = 0;
		if ( matches !== null ){
			result = matches.length;
		}
		return result;
	};

	/**
	 * score for the video title
	 * @type {{scoreArray: *[]}}
	 */
	var videoTitleScore = {
		scoreArray: [
			{
				max: 0,
				score: 6,
				text: wpseoVideoL10n.video_title_ok
			},
			{
				min: 1,
				score: 9,
				text: wpseoVideoL10n.video_title_good
			}
		]
	};

	/**
	 * returns the wordcount for of the text
	 * @returns int
	 */
	YoastVideoSEOplugin.prototype.videoBodyLength = function(){
		var wordCount = YoastSEO.app.pageAnalyzer.wordCount();

		wordCount = wordCount[0].result;

		return wordCount;
	};

	/**
	 * score for the video body length
	 * @type {{scoreArray: *[]}}
	 */
	var videoBodyLengthScore = {
		scoreArray: [
			{
				max: 150,
				score: 6,
				text: wpseoVideoL10n.video_body_short
			},
			{
				min: 150,
				max: 400,
				score: 9,
				text: wpseoVideoL10n.video_body_good
			},
			{
				min: 400,
				score: 6,
				text: wpseoVideoL10n.video_body_long
			}
		],
		replaceArray: [
			{
				name: 'url',
				position: '%1$s',
				value: wpseoVideoL10n.video_body_long_url
			},
			{	name: 'endTag',
				position: '%2$s',
				value: '</a>'
			}
		]
	};
}());

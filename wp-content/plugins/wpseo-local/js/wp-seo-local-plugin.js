/* global YoastSEO */
/* global wpseoLocalL10n */
(function() {
	'use strict';

	/**
	 * Adds the plugin for videoSEO to the YoastSEO Analyzer.
	 */
	var YoastlocalSEOplugin = function(){
		YoastSEO.app.registerPlugin( 'YoastLocalSEO', { 'status': 'ready' });

		YoastSEO.app.registerTest( 'localTitle', this.localTitle, localTitleScore, 'YoastLocalSEO' );

		YoastSEO.app.registerTest( 'localUrl', this.localUrl, localUrlScore, 'YoastLocalSEO' );

		this.addCallback();
	};

	/**
	 * Tests if the location appears in the title.
	 * @returns int
	 */
	YoastlocalSEOplugin.prototype.localTitle = function() {
		if( wpseoLocalL10n.location !== '' ) {
			var business_city = new RegExp( wpseoLocalL10n.location, 'ig');
			var matches = YoastSEO.app.rawData.pageTitle.match( business_city );
			var result = 0;
			if (matches !== null) {
				result = matches.length;
			}
			return result;
		}
	};

	/**
	 * Score for the location title.
	 * @type {{scoreArray: *[]}}
	 */
	var localTitleScore = {
		scoreArray: [
			{
				max: 0,
				score: 4,
				text: wpseoLocalL10n.title_no_location
			},
			{
				min: 1,
				score: 9,
				text: wpseoLocalL10n.title_location
			}
		]
	};

	/**
	 * Tests if the location appears in the url.
	 * @returns int
	 */
	YoastlocalSEOplugin.prototype.localUrl = function(){
		if( wpseoLocalL10n.location !== '' ) {
			var business_city = new RegExp( wpseoLocalL10n.location, 'ig' );
			var matches = YoastSEO.app.rawData.url.match( business_city );
			var result = 0;
			if (matches !== null) {
				result = matches.length;
			}
			return result;
		}
	};

	/**
	 * Score for the location url.
	 * @type {{scoreArray: *[]}}
	 */
	var localUrlScore = {
		scoreArray: [
			{
				max: 0,
				score: 4,
				text: wpseoLocalL10n.url_no_location
			},
			{
				min: 1,
				score: 9,
				text: wpseoLocalL10n.url_location
			}
		]
	};

	/**
	 * Adds callback for the wpseo_business_city field so it is updated
	 */
	YoastlocalSEOplugin.prototype.addCallback = function() {
		var elem = document.getElementById( 'wpseo_business_city' );
		if( elem !== null){
			elem.addEventListener( 'change', YoastSEO.app.analyzeTimer.bind ( YoastSEO.app ) );
		}
	};

	/**
	 * Adds eventListener on page load to load the videoSEO.
	 */
	if ( typeof YoastSEO !== 'undefined' && typeof YoastSEO.app !== 'undefined' ) {
		new YoastlocalSEOplugin();
	}
	else {
		jQuery( window ).on(
			'YoastSEO:ready',
			function() {
				new YoastlocalSEOplugin();
			}
		);
	}

}());

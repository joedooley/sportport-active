/* global YoastSEO */
/* global wpseoLocalL10n */
(function() {
	'use strict';

	var AssessmentResult = require( 'yoastseo/js/values/AssessmentResult' );

	/**
	 * Adds the plugin for videoSEO to the YoastSEO Analyzer.
	 */
	var YoastlocalSEOplugin = function(){
		YoastSEO.app.registerPlugin( 'YoastLocalSEO', { 'status': 'ready' });

		YoastSEO.app.registerAssessment( 'localTitle', { getResult:  this.localTitle.bind( this ) }, 'YoastLocalSEO' );

		YoastSEO.app.registerAssessment( 'localUrl', { getResult:  this.localUrl.bind( this ) }, 'YoastLocalSEO' );

		this.addCallback();
	};

	/**
	 *
	 * @param {object} paper The paper to run this assessment on
	 * @param {object} researcher The researcher used for the assessment
	 * @param {object} i18n The i18n-object used for parsing translations
	 * @returns {object} an assessmentresult with the score and formatted text.
	 */
	YoastlocalSEOplugin.prototype.localTitle = function( paper, researcher, i18n ) {
		var assessmentResult = new AssessmentResult();
		if( wpseoLocalL10n.location !== '' ) {
			var business_city = new RegExp( wpseoLocalL10n.location, 'ig');
			var matches = paper.getTitle().match( business_city ) || 0;

			var result = this.localTitleScore( matches );

			assessmentResult.setScore( result.score );
			assessmentResult.setText( result.text );
		}
		return assessmentResult;
	};

	/**
	 *
	 * @param matches
	 * @returns {{score: number, text: *}}
	 */
	YoastlocalSEOplugin.prototype.localTitleScore = function( matches ){
		if ( matches.length > 0 ) {
			return {
				score: 9,
				text: wpseoLocalL10n.title_location
			}
		}
		return {
			score: 4,
			text: wpseoLocalL10n.title_no_location
		}
	};

	/**
	 * Runs an assessment for scoring the location in the URL.
	 * @param {object} paper The paper to run this assessment on
	 * @param {object} researcher The researcher used for the assessment
	 * @param {object} i18n The i18n-object used for parsing translations
	 * @returns {object} an assessmentresult with the score and formatted text.
	 */
	YoastlocalSEOplugin.prototype.localUrl = function( paper, researcher, i18n ) {
		var assessmentResult = new AssessmentResult();
		if( wpseoLocalL10n.location !== '' ) {
			var business_city = new RegExp( wpseoLocalL10n.location, 'ig' );
			var matches = paper.getUrl().match( business_city ) || 0;
			var result = this.scoreLocalUrl( matches );
			assessmentResult.setScore( result.score );
			assessmentResult.setText( result.text );
		}
		return assessmentResult;
	};

	/**
	 * Scores the url based on the matches of the location.
	 * @param {array} matches The matches of the location in the url
	 * @returns {{score: number, text: *}}
	 */
	YoastlocalSEOplugin.prototype.scoreLocalUrl = function( matches ) {
		if ( matches.length > 0 ) {
			return{
				score: 9,
				text: wpseoLocalL10n.url_location
			}
		}
		return{
			score: 4,
			text: wpseoLocalL10n.url_no_location
		}
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

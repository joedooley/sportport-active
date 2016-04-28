(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
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

},{"yoastseo/js/values/AssessmentResult":2}],2:[function(require,module,exports){
var isUndefined = require( "lodash/isUndefined" );
var isNumber = require( "lodash/isNumber" );

/**
 * Construct the AssessmentResult value object.
 * @constructor
 */
var AssessmentResult = function() {
	this._hasScore = false;
	this.score = 0;
	this.text = "";
};

/**
 * Check if a score is available.
 * @returns {boolean} Whether or not a score is available.
 */
AssessmentResult.prototype.hasScore = function() {
	return this._hasScore;
};

/**
 * Get the available score
 * @returns {number} The score associated with the AssessmentResult.
 */
AssessmentResult.prototype.getScore = function() {
	return this.score;
};

/**
 * Set the score for the assessment.
 * @param {number} score The score to be used for the score property
 * @returns {void}
 */
AssessmentResult.prototype.setScore = function( score ) {
	if ( isNumber( score ) ) {
		this.score = score;
		this._hasScore = true;
	}
};

/**
 * Check if a text is available.
 * @returns {boolean} Whether or not a text is available.
 */
AssessmentResult.prototype.hasText = function() {
	return this.text !== "";
};

/**
 * Get the available text
 * @returns {string} The text associated with the AssessmentResult.
 */
AssessmentResult.prototype.getText = function() {
	return this.text;
};

/**
 * Set the text for the assessment.
 * @param {string} text The text to be used for the text property
 * @returns {void}
 */
AssessmentResult.prototype.setText = function( text ) {
	if ( isUndefined( text ) ) {
		text = "";
	}

	this.text = text;
};

module.exports = AssessmentResult;

},{"lodash/isNumber":3,"lodash/isUndefined":5}],3:[function(require,module,exports){
var isObjectLike = require('./isObjectLike');

/** `Object#toString` result references. */
var numberTag = '[object Number]';

/** Used for built-in method references. */
var objectProto = Object.prototype;

/**
 * Used to resolve the
 * [`toStringTag`](http://ecma-international.org/ecma-262/6.0/#sec-object.prototype.tostring)
 * of values.
 */
var objectToString = objectProto.toString;

/**
 * Checks if `value` is classified as a `Number` primitive or object.
 *
 * **Note:** To exclude `Infinity`, `-Infinity`, and `NaN`, which are
 * classified as numbers, use the `_.isFinite` method.
 *
 * @static
 * @memberOf _
 * @since 0.1.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is correctly classified,
 *  else `false`.
 * @example
 *
 * _.isNumber(3);
 * // => true
 *
 * _.isNumber(Number.MIN_VALUE);
 * // => true
 *
 * _.isNumber(Infinity);
 * // => true
 *
 * _.isNumber('3');
 * // => false
 */
function isNumber(value) {
  return typeof value == 'number' ||
    (isObjectLike(value) && objectToString.call(value) == numberTag);
}

module.exports = isNumber;

},{"./isObjectLike":4}],4:[function(require,module,exports){
/**
 * Checks if `value` is object-like. A value is object-like if it's not `null`
 * and has a `typeof` result of "object".
 *
 * @static
 * @memberOf _
 * @since 4.0.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is object-like, else `false`.
 * @example
 *
 * _.isObjectLike({});
 * // => true
 *
 * _.isObjectLike([1, 2, 3]);
 * // => true
 *
 * _.isObjectLike(_.noop);
 * // => false
 *
 * _.isObjectLike(null);
 * // => false
 */
function isObjectLike(value) {
  return !!value && typeof value == 'object';
}

module.exports = isObjectLike;

},{}],5:[function(require,module,exports){
/**
 * Checks if `value` is `undefined`.
 *
 * @static
 * @since 0.1.0
 * @memberOf _
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is `undefined`, else `false`.
 * @example
 *
 * _.isUndefined(void 0);
 * // => true
 *
 * _.isUndefined(null);
 * // => false
 */
function isUndefined(value) {
  return value === undefined;
}

module.exports = isUndefined;

},{}]},{},[1]);

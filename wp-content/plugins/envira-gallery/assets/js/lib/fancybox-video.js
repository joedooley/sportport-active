/*!
 * Self hosted video helper for envirabox
 * version: 1.0.0
 * @requires envirabox v2.0 or later
 *
 * Usage:
 *     $(".envirabox").envirabox({
 *         helpers : {
 *             video: {
 *                 autoplay: 	0,
 *                 playpause: 	0,
 *                 progress: 	0,
 *                 current: 	0,
 *                 duration: 	0,
 *                 volume: 	    0
 *             }
 *         }
 *     });
 *
 *  Supports:
 *
 *      Video
 *          http://url.com/video.mp4
 *          http://url.com/video.3gp
 *          http://url.com/video.flv
 *          http://url.com/video.ogv
 *          http://url.com/video.webm
 */
;(function ($) {
	"use strict";

	//Shortcut for envirabox object
	var F = $.envirabox,
		format = function( url, rez, params ) {
			params = params || '';

			if ( $.type( params ) === "object" ) {
				params = $.param(params, true);
			}

			return url;
		};

	//Add helper object
	F.helpers.video = {
		defaults : {
			autoplay: 	0,
			playpause: 	0,
			progress: 	0,
			current: 	0,
			duration: 	0,
			volume: 	0
		},

		beforeLoad : function(opts, obj) {
			// Check if this Lightbox object's href is a video
			var result = (/\.(mp4|flv|ogv|webm|MP4|FLV|OGV|WEBM)$/i).test( obj.href );
			if ( result ) {
				// Yes, it's a video
				// Get content type
				var content_type = '';
				switch ( obj.href.split('.').pop() ) {
					case 'mp4':
						content_type = 'video/mp4';
						break;
					case 'ogv':
						content_type = 'video/ogg';
						break;
					case 'ogg':
						content_type = 'application/ogg';
						break;
					case 'webm':
						content_type = 'video/webm';
						break;
				}

				// Set content as HTML
				obj.content = '<video class="envira-video" width="90%" height="90%" preload="metadata"><source type="' + content_type + '" src="' + obj.href + '" /></video>';
				obj.type = 'html';
			}
		},

		afterShow: function(opts, obj) {
			// Check if this Lightbox object's href is a video
			var result = (/\.(mp4|flv|ogv|webm|MP4|FLV|OGV|WEBM)$/i).test( obj.href );
			if ( result ) {
				// Build features for MediaElementPlayer
				var features = [];
				if (opts.playpause === 1) {
					features.push('playpause');
				}
				if (opts.progress === 1) {
					features.push('progress');
				}
				if (opts.current === 1) {
					features.push('current');
				}
				if (opts.duration === 1) {
					features.push('duration');
				}
				if (opts.volume === 1) {
					features.push('volume');
				}

				// Init MediaElementPlayer
				$( '.envira-video' ).mediaelementplayer({
					features: features,
					success: function( mediaElement, domObject ) {
						if (opts.autoplay === 1) {
							mediaElement.addEventListener('canplay', function() {
								// Player is ready
				                mediaElement.play();
				            }, false);
						}
					}
				});

				// Trigger envirabox resize
				setTimeout(function() {
					$(window).trigger('resize');	
				}, 500);
			}
		}

	};

}(jQuery));
// (function (document, $, undefined) {
//
// 	'use strict';
//
// 	function gallerySlider() {
//
// 		var thumbnails = document.querySelector('.thumbnails');
//
// 		if (thumbnails)
// 			$(thumbnails).slick({
// 				arrows        : false,
// 				dots          : true,
// 				infinite      : true,
// 				slidesToShow  : 3,
// 				slidesToScroll: 3,
// 				centerMode    : true,
// 				variableWidth : true
// 			});
//
// 	}
//
//
// 	function imageSwap() {
//
// 		// Override default behavior
// 		$('.woocommerce-main-image').on('click', function (event) {
// 			event.preventDefault();
// 		});
//
// 		// Find the individual thumbnail images
// 		var thumblink = $('.thumbnails .zoom');
//
// 		// Add our active class to the first thumb which will already be displayed
// 		//on page load.
// 		thumblink.first().addClass('active');
//
// 		thumblink.on('click', function (event) {
//
// 			// Override default behavior on click.
// 			event.preventDefault();
//
// 			// We'll generate all our attributes for the new main
// 			// image from the thumbnail.
// 			var thumb = $(this).find('img');
//
// 			// The new main image url is formed from the thumbnail src by removing
// 			// the dimensions appended to the file name.
// 			var photo_fullsize = thumb.attr('src').replace('-300x300', '');
//
// 			// srcset attributes are associated with thumbnail img. We'll need to also change them.
// 			var photo_srcset = thumb.attr('srcset');
//
// 			// Retrieve alt attribute for use in main image.
// 			var alt = thumb.attr('alt');
//
// 			// If the selected thumb already has the .active class do nothing.
// 			if ($(this).hasClass('active')) {
// 				return false;
// 			} else {
//
// 				// Remove .active class from previously selected thumb.
// 				thumblink.removeClass('active');
//
// 				// Add .active class to new thumb.
// 				$(this).addClass('active');
//
// 				// Fadeout main image and replace various attributes with those defined above. Once the image is loaded we'll make it visible.
// 				$('.woocommerce-main-image img').css('opacity', '0').attr('src', photo_fullsize).attr('srcset', photo_srcset).attr('alt', alt).load(function () {
// 					$(this).animate({opacity: 1});
// 				});
// 				return false;
// 			}
// 		});
// 	}
//
//
// 	/**
// 	 * The main app object.
// 	 *
// 	 * @namespace
// 	 */
// 	var slickSlider = {
//
// 		init: function() {
//
// 			gallerySlider();
// 			//imageSwap();
//
// 		}
//
// 	};
//
//
// 	slickSlider.init();
//
//
// })(document, jQuery);
//

(function (document, $, undefined) {

	'use strict';


	var BackStretchImage = BackStretchImg;

	$('.hero').backstretch(BackStretchImage.hero);
	$('.featured-image-left').backstretch(BackStretchImage.featuredLeft);
	$('.featured-image-right').backstretch(BackStretchImage.featuredRight);



})(document, jQuery);


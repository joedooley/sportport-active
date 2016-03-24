(function (document, $, undefined) {

	'use strict';



	var BackStretchImage = BackStretchImg;

	$('.hero').backstretch(BackStretchImage.hero, { duration: 3000, fade: 750 } );
	$('.featured-image-left').backstretch(BackStretchImage.featuredLeft, { duration: 3000, fade:750 } );
	$('.featured-image-right').backstretch(BackStretchImage.featuredRight, {duration: 3000, fade: 750});


})(document, jQuery);


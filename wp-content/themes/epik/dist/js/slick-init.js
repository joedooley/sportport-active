(function (document, $, undefined) {

	'use strict';


	$('.thumbnails').slick({
		arrows: false,
		dots: true,
		infinite      : true,
		slidesToShow  : 3,
		slidesToScroll: 3,
		centerMode   : true,
		variableWidth: true
	});


})(document, jQuery);


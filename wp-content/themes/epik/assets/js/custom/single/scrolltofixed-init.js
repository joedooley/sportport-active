jQuery(function ($) {

	//$(window).scroll(function () {
	//	var scroll = $(window).scrollTop();
	//
	//	if (scroll >= 1500) {
	//		$(".single-product .images").removeClass("js-fixed");
	//	} else {
	//		$(".single-product .images").addClass("js-fixed");
	//	}
	//
	//});

	function positionProduct(e) {

		var winHeight    = window.innerHeight;
		var headerHeight = $('.site-header').outerHeight();
		var footerTop    = $('.above-footer-widgets').position().top;
		var headerTop    = $('.site-header .title-area .site-title img').position().top;
		var paddingTop   = parseInt($('.images').css('padding-top'));
		var beforeHeader = $('.before-header').outerHeight();

		var scrollHeight = headerHeight + beforeHeader + beforeHeader;

		var leftHeight = winHeight - headerHeight - beforeHeader;

		var currScroll = $(window).scrollTop();

		var imageHeight = leftHeight - paddingTop;


		if (window.matchMedia("(min-width: 768px)").matches) {

			//Set Media Query
			//if (Modernizr.mq('only screen and (min-width: 769px)')) {

			//set left height
			$('.images').height(leftHeight);

			//set image height
			$('.product-img-box').height(imageHeight);


			//check if fixed position or not
			if (footerTop < currScroll + winHeight - scrollHeight) {
				$('.images').css({
					'position': 'absolute',
					'top': footerTop - leftHeight - scrollHeight - 15
				});
			} else {
				$('.images').css({
					'position': 'fixed',
					'top': scrollHeight > currScroll ? scrollHeight - currScroll : 0
				});
			}

		} else {


			//Remove styling for mobile
			$('.images').attr('style', '');
			$('.product-img-box').attr('style', '');


		}
	}


		$(window).on('resize', positionProduct);

		$(window).load(function () {
			setTimeout(positionProduct, 200);
		});

		$(window).on('scroll', function (e) {
			positionProduct(e);
		});


});




jQuery(function( $ ){

	// Enable parallax and fade effects on homepage sections
	$(window).scroll(function(){

		scrolltop = $(window).scrollTop()
		scrollwindow = scrolltop + $(window).height();

		$(".hf1").css("backgroundPosition", "50% " + -(scrolltop/6) + "px");

		if ( $(".hf3").length ) {
		
			sectionfiveoffset = $(".hf3").offset().top;		  

			if( scrollwindow > sectionfiveoffset ) {

				// Enable parallax effect
				backgroundscroll = scrollwindow - sectionfiveoffset;
				$(".hf3").css("backgroundPosition", "50% " + -(backgroundscroll/6) + "px");

			}
		
		}

		if ( $(".hf4").length ) {
		
			sectionfiveoffset = $(".hf4").offset().top;		  

			if( scrollwindow > sectionfiveoffset ) {

				// Enable parallax effect
				backgroundscroll = scrollwindow - sectionfiveoffset;
				$(".hf4").css("backgroundPosition", "50% " + -(backgroundscroll/6) + "px");

			}
		
		}
		
		if ( $(".hf5").length ) {
		
			sectionfiveoffset = $(".hf5").offset().top;		  

			if( scrollwindow > sectionfiveoffset ) {

				// Enable parallax effect
				backgroundscroll = scrollwindow - sectionfiveoffset;
				$(".hf5").css("backgroundPosition", "50% " + -(backgroundscroll/6) + "px");

			}
		
		}
		
		if ( $(".hf6").length ) {
		
			sectionfiveoffset = $(".hf6").offset().top;		  

			if( scrollwindow > sectionfiveoffset ) {

				// Enable parallax effect
				backgroundscroll = scrollwindow - sectionfiveoffset;
				$(".hf6").css("backgroundPosition", "50% " + -(backgroundscroll/6) + "px");

			}
		
		}
		
		if ( $(".hf7").length ) {
		
			sectionfiveoffset = $(".hf7").offset().top;		  

			if( scrollwindow > sectionfiveoffset ) {

				// Enable parallax effect
				backgroundscroll = scrollwindow - sectionfiveoffset;
				$(".hf7").css("backgroundPosition", "50% " + -(backgroundscroll/6) + "px");

			}
		
		}
		
		if ( $(".hf8").length ) {
		
			sectionfiveoffset = $(".hf8").offset().top;		  

			if( scrollwindow > sectionfiveoffset ) {

				// Enable parallax effect
				backgroundscroll = scrollwindow - sectionfiveoffset;
				$(".hf8").css("backgroundPosition", "50% " + -(backgroundscroll/6) + "px");

			}
		
		}
		
		if ( $(".hf9").length ) {
		
			sectionfiveoffset = $(".hf9").offset().top;		  

			if( scrollwindow > sectionfiveoffset ) {

				// Enable parallax effect
				backgroundscroll = scrollwindow - sectionfiveoffset;
				$(".hf9").css("backgroundPosition", "50% " + -(backgroundscroll/6) + "px");

			}
		
		}
		
		if ( $(".hf14").length ) {
		
			sectionfiveoffset = $(".hf14").offset().top;		  

			if( scrollwindow > sectionfiveoffset ) {

				// Enable parallax effect
				backgroundscroll = scrollwindow - sectionfiveoffset;
				$(".hf14").css("backgroundPosition", "50% " + -(backgroundscroll/6) + "px");

			}
		
		}

	});

});
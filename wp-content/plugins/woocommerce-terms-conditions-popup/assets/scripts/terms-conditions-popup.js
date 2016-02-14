jQuery(document).ready(function( $ ) {

	// only fire after the checkout ajax has finished loading
	$( 'body' ).bind( 'updated_checkout', function() {

		// cache jquery selector
		var $target = $( "#payment .terms a" );

		// get the width & height of the thickbox
		width  = wcTermsConditionsPopupGetDimensions( $( window ).width(), 600 );
		height = wcTermsConditionsPopupGetDimensions( $( window ).height(), 550 );

		// change link href
		var href = "#TB_inline?width=" + width + "&height=" + height + "&inlineId=wc_terms_conditions_popup-popup";
		if ( 'yes' == WC_MTC_Data.force_agree ) {
			// force the user to agree by placing an agree button at the bottom of the page.
			href = href + "&modal=true";
		}
		$target.attr( "href", href );

		// change class
		$target.attr( 'class', 'thickbox' );

		// change popup title
		$target.attr( 'title', WC_MTC_Data.title );

	});

	// if a user clicks on the Agree button in the popup then check the checkbox for them
	$( '#wc_terms_conditions_popup-agree' ).on( "click", function() {
		$( '#terms' ).prop( 'checked', true );
	});

	// if a user clicks on the Decline button in the popup then uncheck the checkbox for them
	$( '#wc_terms_conditions_popup-decline' ).on( "click", function() {
		$( '#terms' ).prop( 'checked', false );
	});


});

// add some padding to the base dimension, check the maximum size, and then return
function wcTermsConditionsPopupGetDimensions( baseDimension, maxDimension ) {

	// add some padding
	dim = baseDimension * .8;

	// set a max size
	if ( dim > maxDimension ) {
		dim = maxDimension;
	}

	return dim;
}

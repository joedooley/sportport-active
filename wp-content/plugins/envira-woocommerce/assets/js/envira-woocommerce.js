jQuery( document ).ready( function( $ ) {

	// Reload Isotope when variable product option changed
	$( '.variations_form' ).on( 'woocommerce_variation_has_changed', function( e ) {
		
		var gallery_id = $( this ).closest( 'div.enviratope' ).attr( 'id' );
		setTimeout(function() {
			$( '#' + gallery_id ).enviratope( 'layout' );
		}, 500);
		
	} );

} );
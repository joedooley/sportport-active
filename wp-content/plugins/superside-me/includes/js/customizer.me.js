/**
 * Contains handlers to make Customizer preview reload changes asynchronously.
 */
( function( $ ) {
	var setting = 'supersideme';
	// menu buttons
	wp.customize( setting + '[navigation]', function( value ) {
		value.bind( function( to ) {
			$( '.slide-nav-link' ).text( to );
		} );
	} );
	wp.customize( setting + '[close]', function( value ) {
		value.bind( function( to ) {
			$( '.menu-close' ).text( to );
		} );
	} );
	// background
	wp.customize( setting + '[background]', function( value ) {
		value.bind( function( to ) {
			$( '.sidr, .slide-nav-link' ).css( {
				'background-color': to
			} );
			$( '<style type="text/css">.search-me:before{color: ' + to + ' }</style>' ).appendTo( 'head' );
		} );
	} );
	// links
	wp.customize( setting + '[link_color]', function( value ) {
		value.bind( function( to ) {
			$( '.sidr, .slide-nav-link, .sidr h3, .sidr h4, .sidr .widget, .sidr p, .sidr a, .sidr button' ).css( {
				'color': to
			} );
			$( '<style type="text/css">.sidr .sub-menu-toggle:before{color: ' + to + ' }</style>' ).appendTo( 'head' );
		} );
	} );
	// search
	wp.customize( setting + '[search]', function( value ) {
		value.bind( function( to ) {
			var style = false === to ? 'none' : 'block';
			$( '.search-me' ).attr( 'style', 'display:' + style + ';' );
		} );
	} );
} )( jQuery );

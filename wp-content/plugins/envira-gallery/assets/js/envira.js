/**
* envira.js is a placeholder, which CodeKit attaches the following JS files to, before compiling as min/envira-min.js:
* - lib/touchswipe.js
* - lib/mousewheel.js
* - lib/imagesloaded.js
* - lib/isotope.js
* - lib/fancybox.js
* - lib/fancybox-buttons.js
* - lib/fancybox-media.js
* - lib/fancybox-thumbs.js
*
* To load more JS resources:
* - Add them to the lib subfolder
* - Add the to the imports directive of this file in CodeKit
*/

/**
* If a lightbox caption's link is an anchor, close the lightbox
*/
jQuery( document ).ready( function( $ ) {

	$( 'body' ).on( 'click', 'div.envirabox-title a[href*=#]:not([href=#])', function( e ) {

		if ( location.pathname.replace( /^\//, '' ) == this.pathname.replace( /^\//, '' ) && location.hostname == this.hostname ) {
      		$.envirabox.close();
      		return false;
      	}

	} );

} );
/**
 * Contains handlers to make Customizer preview reload changes asynchronously.
 */
;(function ( $ ) {
	'use strict';

	if ( 'undefined' === typeof wp ) {
		return;
	}

	var customize    = wp.customize,
		setting      = 'supersideme',
	    menuButton   = '.slide-nav-link',
	    searchButton = '.supersideme .ssme-search';
	// menu buttons
	customize( setting + '[navigation]', function ( value ) {
		value.bind( function ( to ) {
			var spanClass = to.length ? 'label' : 'screen-reader-text';
			$( menuButton ).children( 'span' ).text( to ).removeClass().addClass( spanClass );
			_changeFlex();
		} );
	} );
	customize( setting + '[close]', function ( value ) {
		value.bind( function ( to ) {
			$( '.menu-close' ).text( to );
		} );
	} );
	// background
	customize( setting + '[background]', function ( value ) {
		value.bind( function ( to ) {
			$( '<style type="text/css">.sidr, .slide-nav-link{background-color: ' + to + ';}</style>' ).appendTo( 'head' );
		} );
	} );
	// links
	customize( setting + '[link_color]', function ( value ) {
		value.bind( function ( to ) {
			$( '<style type="text/css">.sidr, .slide-nav-link, .sidr h3, .sidr h4, .sidr .widget, .sidr p, .sidr a, .sidr button {color: ' + to + ';}</style>' ).appendTo( 'head' );
		} );
	} );
	// search
	customize( setting + '[search]', function ( value ) {
		value.bind( function ( to ) {
			var style = false === to ? 'none' : 'block';
			$( '.sidr .search-me' ).css( 'display', style );
		} );
	} );
	customize( setting + '[search_button]', function ( value ) {
		value.bind( function ( to ) {
			var style = false === to ? 'none' : 'block';
			$( searchButton ).css( 'display', style );
			_changeFlex();
		} );
	} );
	customize( setting + '[search_button_text]', function ( value ) {
		value.bind( function ( to ) {
			var spanClass = to.length ? 'label' : 'screen-reader-text';
			$( searchButton ).children( 'span' ).text( to ).removeClass().addClass( spanClass );
			_changeFlex();
		} );
	} );

	/**
	 * Get the value of whatever setting.
	 * @param currentSetting
	 * @returns {*}
	 * @private
	 */
	function _getSetting( currentSetting ) {
		return customize.instance( setting + '[' + currentSetting + ']' ).get();
	}

	/**
	 * Check and update the flex-grow values for search/menu buttons.
	 * @uses _getSetting
	 * @private
	 */
	function _checkButtonsFlex() {
		var autoWidth  = Boolean( parseInt( _getSetting( 'shrink' ), 10 ) ),
		    menuText   = _getSetting( 'navigation' ),
		    searchText = _getSetting( 'search_button_text' ),
			flexGrow   = {
				menu: 0,
				search: 0
			};
		if ( autoWidth ) {
			return flexGrow;
		}
		if ( ! menuText && ! searchText ) {
			flexGrow.menu = 1;
			flexGrow.search = 1;
		} else {
			if ( menuText ) {
				flexGrow.menu = 1;
			}
			if ( searchText ) {
				flexGrow.search = 1;
			}
		}
		return flexGrow;
	}

	function _changeFlex() {
		var flexGrow = _checkButtonsFlex();
		$( menuButton ).css( 'flex-grow', flexGrow.menu );
		$( searchButton ).css( 'flex-grow', flexGrow.search );
	}

})( jQuery );

/**
 * SuperSide Me main js engine
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2017 Robin Cornett
 * @license   GPL-2.0+
 */
;(function ( document, $, undefined ) {
	'use strict';

	var ssme                = {},
	    sidrEnabled         = false,
	    supersidemeDefaults = {
			button: 'slide-menu',
			panel: 'side-menu'
	    },
	    cssClasses          = {
		    sidr: 'sidr',
		    menuButton: 'slide-nav-link',
		    submenuToggle: 'sub-menu-toggle',
		    menuOpen: 'menu-open',
		    searchButton: 'ssme-search',
		    buttonWrap: 'ssme-buttons',
		    screenReader: 'screen-reader-text',
		    body: 'supersideme'
	    },
	    cssSelectors        = {
		    sidr: '.' + cssClasses.sidr,
		    panel: '#' + supersidemeDefaults.panel,
		    menuButton: '.' + cssClasses.menuButton,
		    submenuToggle: '.' + cssClasses.submenuToggle,
		    button: '#' + supersidemeDefaults.button,
		    searchButton: '.' + cssClasses.searchButton,
		    buttonWrap: '.' + cssClasses.buttonWrap,
		    searchInput: '.' + cssClasses.buttonWrap + ' .search-me'
		};

	ssme.init = function () {
		_supersidemeHandler( supersidemeDefaults.button );
	};

	/********************
	 * Private Functions
	 ********************/

	/**
	 * function to handle everything
	 *
	 */
	function _supersidemeHandler( button ) {

		_doMenuButton( button );

		if ( ssme.params.search.button || _isCustomizer() ) {
			_doSearchButton();
		}

		if ( cssSelectors.button.length === 0 ) {
			return;
		}

		$( cssSelectors.button ).on( 'click.supersideme', function () {
			_openSesame();
		} );

		if ( $( ssme.params.second.button !== 'undefined' ) ) {
			$( ssme.params.second.button ).on( 'click.second-ssme', function() {
				_openSecond( ssme.params.second );
			} );
		}

		// Close the menu if the window is resized
		$( window ).on( 'resize.supersideme', _doResize ).triggerHandler( 'resize.supersideme' );
	}

	/**
	 * build the main menu button
	 * @return string slide-nav-link button
	 */
	function _doMenuButton( button ) {

		var buttonText  = ssme.params.navarialabel,
			buttonClass = cssClasses.screenReader,
			_location   = $( ssme.params.location ).length > 0 ? ssme.params.location : 'body';
		if ( ssme.params.navigation ) {
			buttonText  = ssme.params.navigation;
			buttonClass = 'label';
		}
		if ( _isCustomizer() ) {
			buttonText = parent.wp.customize.instance( 'supersideme[navigation]' ).get();
		}
		var mainmenuButton = $( '<button />', {
			role: 'button',
			'aria-pressed': false,
			'aria-expanded': false,
			'aria-controls': supersidemeDefaults.panel,
			id: button,
			class: cssClasses.menuButton
		} ).append( $( '<span />', {
			class: buttonClass,
			text: buttonText
		} ) );
		$( _location )[ssme.params.function]( mainmenuButton );
		$( 'body' ).addClass( cssClasses.body );
	}

	/**
	 * Add the search button with the menu button.
	 * @private
	 */
	function _doSearchButton() {
		if ( ! ssme.params.search.input ) {
			return;
		}

		var buttonText     = ssme.params.search.button_aria,
		    buttonClass    = cssClasses.screenReader,
			buttonPosition = 'left' === ssme.params.side ? 'after' : 'before';
		if ( ssme.params.search.button_text ) {
			buttonText  = ssme.params.search.button_text;
			buttonClass = 'label';
		}
		if ( _isCustomizer() ) {
			buttonText = parent.wp.customize.instance( 'supersideme[search_button_text]' ).get();
		}
		var searchButton = $( '<button />', {
			role: 'button',
			'aria-pressed': false,
			id: cssClasses.searchButton,
			class: cssClasses.searchButton
		} ).append( $( '<span />', {
			class: buttonClass,
			text: buttonText
		} ) );

		if ( ! ssme.params.location || 'body' === ssme.params.location || ! $( ssme.params.location ).length ) {
			$( cssSelectors.button ).wrap( '<div />' );
		}
		$( cssSelectors.button ).parent().addClass( cssClasses.buttonWrap );
		$( cssSelectors.button )[ buttonPosition ]( searchButton );

		var _container    = $( cssSelectors.buttonWrap ),
			_location     = 'after',
			_position     = 'last-of-type',
			_searchButton = $( cssSelectors.searchButton ),
			_order        = '';
		if ( '0px' === _container.css( 'bottom' ) && $.inArray( _container.css( 'position' ), ['absolute', 'fixed'] ) > -1 ) {
			_location = 'before';
			_position = 'first-of-type';
			_order    = -5;
		}
		$( cssSelectors.buttonWrap + ' > button:' + _position )[ _location ]( ssme.params.search.input );
		$( cssSelectors.searchInput ).css( 'display', 'none' ).css( 'order', _order );

		if ( ! ssme.params.search.button ) {
			_searchButton.css( 'display', 'none' );
		}

		_searchButton.on( 'click.search', function () {
			$( cssSelectors.searchInput ).slideToggle( 200 );
			_toggleAria( _searchButton, 'aria-pressed' );
		} );
	}

	/**
	 * Open the side panel
	 */
	function _openSesame() {
		_engage();
		sidrEnabled = true;
		$.sidr( 'open', supersidemeDefaults.panel );
	}

	/**
	 * Close the side panel
	 */
	function _closeSesame() {
		$.sidr( 'close', supersidemeDefaults.panel );
		$( cssSelectors.sidr + ' ' + cssSelectors.submenuToggle ).removeClass( cssClasses.menuOpen ).attr( 'aria-expanded', false ).next( '.sub-menu' ).slideUp( 'fast' );
		return false;
	}

	/**
	 * Main resizing function.
	 */
	var _doResize = _debounce( function () {
		var _button = supersidemeDefaults.button;
		_addBodyClass( _button );
		if ( ssme.params.swipe ) {
			_enableSwipe( _button, 'body' );
		}

		ssme.skipLinks = typeof supersidemeSkipLinks === 'undefined' ? '' : supersidemeSkipLinks;
		if ( typeof ssme.skipLinks !== 'undefined' ) {
			_changeSkipLinks( _button );
		}
		if ( _isDisplayNone( _button ) ) {
			$( cssSelectors.searchInput ).hide();
			_closeSesame();
		}
	}, 250 );

	/**
	 * change body class based on main menu button visibility
	 * @param {id} button main menu button
	 */
	function _addBodyClass( button ) {
		var _body = $( 'body' );
		if ( _isDisplayNone( button ) ) {
			_body.removeClass( cssClasses.body );
			return;
		}
		_body.addClass( cssClasses.body );
	}

	/**
	 * To cover users without updated CSS--check if sidr is set to display:none and fix.
	 * @private
	 */
	function _checkSidrClass() {
		if ( supersidemeDefaults.panel.length === 0 ) {
			return;
		}
		if ( _isDisplayNone( supersidemeDefaults.panel ) ) {
			var element = document.getElementById( supersidemeDefaults.panel );
			element.style.setProperty( 'display', 'block' );
		}
	}

	/**
	 * enable swiping functionality
	 * @param  {id} button main menu button
	 * @param  {element} body   body element
	 * @return {null}         return early if swiping is not enabled
	 */
	function _enableSwipe( button, body ) {
		if ( _isDisplayNone( button ) ) {
			$( body ).swipe( 'disable' );
			return;
		}
		$( body ).swipe( 'enable' );
		_touchSwipe( $( body ) );
	}

	/**
	 * Changes skip links, if they exist
	 * @param  {string} button main menu button
	 */
	function _changeSkipLinks( button ) {
		var _startLink = ssme.skipLinks.startLink,
			_endLink   = button,
			_hideLinks = $( ssme.skipLinks.ulClass + ' a[href*="#' + ssme.skipLinks.contains + '"]' ).not( 'a[href*="' + ssme.skipLinks.unique + '"]' );
		if ( _isDisplayNone( button ) ) {
			_startLink = button;
			_endLink   = ssme.skipLinks.startLink;
			$( _hideLinks ).removeAttr( 'style' );
		} else {
			$( _hideLinks ).hide();
		}
		var _link   = $( ssme.skipLinks.ulClass + ' a[href*="#' + _startLink + '"]' ),
			_target = $( _link ).attr( 'href' );
		if ( ! _target ) {
			return;
		}
		_target = _target.replace( _startLink, _endLink );
		$( _link ).attr( 'href', _target );
	}

	/**
	 * define touchSwipe defaults and execute
	 * @param  {element} body what to swipe on
	 */
	function _touchSwipe( body ) {
		var left  = _openSesame,
			right = _closeSesame;
		if ( 'left' === ssme.params.side ) {
			left  = _closeSesame;
			right = _openSesame;
		}

		body.swipe( {
			allowPageScroll: 'vertical',
			threshold: 120,
			swipeLeft: left,
			swipeRight: right,
			preventDefaultEvents: false
		} );
	}

	/**
	 * start up the sidr panel when main menu button is clicked
	 * @return full sidr panel
	 */
	function _engage() {
		var panelSource = null !== ssme.params.source && null !== document.getElementById( ssme.params.source ) ? '#' + ssme.params.source : function () {
				_fillPanel();
			},
			args = {
				button: cssSelectors.menuButton,
				source: panelSource,
				panel: supersidemeDefaults.panel,
				side: ssme.params.side
			},
			submenuButton = $( supersidemeDefaults.panel + ' ' + cssSelectors.submenuToggle );

		_goSidr( $( args.button ), args );

		if ( submenuButton.length === 0 ) {
			_dosubmenuButtons();
			_orphanedParents();
			$( cssSelectors.sidr + ' ' + cssSelectors.submenuToggle ).on( 'click.supersideme-submenu', _submenuToggle );
		}
		_addCloseButton( args.panel );
		$.sidr( 'open', args.panel );

		return false;
	}

	/**
	 * Set up second menu panel.
	 * @param args
	 * @returns {boolean}
	 * @private
	 */
	function _openSecond( args ) {
		var _button = $( args.button );

		_goSidr( _button, args );
		_addCloseButton( args.panel );
		$.sidr( 'open', args.panel );

		return false;
	}

	/**
	 * Instantiate the sidr function itself.
	 * @param button
	 * @param args
	 * @private
	 */
	function _goSidr( button, args ) {
		button.sidr( {
			name: args.panel,
			side: args.side,
			source: args.source,
			renaming: false,
			displace: ssme.params.displace,
			speed: parseInt( ssme.params.speed, 10 ),
			onOpen: function () {
				button.toggleClass( cssClasses.menuOpen );
				_toggleAria( button, 'aria-pressed' );
				_toggleAria( button, 'aria-expanded' );
				_a11y( button );
			},
			onClose: function () {
				button.removeClass( cssClasses.menuOpen );
				_toggleAria( button, 'aria-pressed' );
				_toggleAria( button, 'aria-expanded' );
				sidrEnabled = false;
			}
		} );
	}

	/**
	 * add keyboard navigation to the panel
	 * @param  button main menu button
	 *
	 */
	function _a11y( button ) {
		var navEl     = $( cssSelectors.sidr ),
		    items     = navEl.children(),
		    firstItem = items.first(),
		    lastItem  = items.last();

		/* Thanks to Rob Neu for the following code,
		 all pulled from the Compass theme. */
		// Add some attributes to the menu container.
		navEl.attr( { tabindex: '0' } ).focus();
		// When focus is on the menu container.
		navEl.on( 'keydown.sidrNav', function ( e ) {
			// If it's not the tab key then return.
			if ( 9 !== e.keyCode ) {
				return;
			}
			// When tabbing forwards and tabbing out of the last link.
			if ( lastItem[ 0 ] === e.target && !e.shiftKey ) {
				button.focus();
				return false;
				// When tabbing backwards and tabbing out of the first link OR the menu container.
			}
			if ( ( firstItem[ 0 ] === e.target || navEl[ 0 ] === e.target ) && e.shiftKey ) {
				button.focus();
				return false;
			}
		} );
		// When focus is on the toggle button.
		button.on( 'keydown.sidrNav', function ( e ) {
			// If it's not the tab key then return.
			if ( 9 !== e.keyCode ) {
				return;
			}
			// when tabbing forwards
			if ( button[ 0 ] === e.target && !e.shiftKey ) {
				firstItem.focus();
				return false;
			}
		} );
	}

	/**
	 * add all the things to the menu panel: navigation, widgets, submenu buttons
	 *
	 */
	function _fillPanel() {
		var container = ssme.params.html5 ? 'nav' : 'div',
			args      = ssme.params.html5 ? {
					'class': 'side-navigation',
					'role': 'navigation',
					'itemscope': 'itemscope',
					'itemtype': 'http://schema.org/SiteNavigationElement'
				} : {
					'class': 'side-navigation'
				},
			navigationMenu = $( '<' + container + ' />', args ).append( $( '<ul />', {
				'class': 'side-nav'
			} ) ),
		    sidrInner      = $( '<div />', {
			    'class': 'sidr-inner'
		    } ),
		    _selectors     = {
			    widget: '.supersideme.widget-area',
			    inner: cssSelectors.panel + ' ' + cssSelectors.sidr + '-inner'
		    };

		// Add navigation menus
		if ( $( _selectors.inner ).length !== 0 ) {
			return;
		}
		$( cssSelectors.sidr ).prepend( sidrInner );
		if ( ssme.params.search.panel ) {
			$( _selectors.inner ).prepend( ssme.params.search.input );
			if ( '0' === ssme.params.search_on ) {
				$( '.search-me' ).css( 'display', 'none' );
			}
		}
		if ( ssme.params.menus ) {
			$( _selectors.inner ).append( navigationMenu );
			$( '.side-nav' ).prepend( ssme.params.menus );
		}
		_checkSidrClass();
		if ( $( _selectors.widget ).length === 0 ) {
			return;
		}
		$( _selectors.widget ).appendTo( _selectors.inner ).attr( 'style', 'display:block;' );
	}

	/**
	 * add submenu buttons
	 * @return submenu toggle buttons adds submenu toggles to menu items with children
	 * @since 1.7.0
	 */
	function _dosubmenuButtons() {
		var submenuButton = $( '<button />', {
			'role': 'button',
			'aria-pressed': false,
			'aria-label': ssme.params.subarialabel,
			'class': cssClasses.submenuToggle
		} ).append( $( '<span />', {
			'class': cssClasses.screenReader,
			text: ssme.params.submenu
		} ) );

		if ( $( cssSelectors.sidr + ' ' + cssSelectors.submenuToggle ).length !== 0 ) {
			return;
		}
		$( cssSelectors.sidr + ' .sub-menu' ).before( submenuButton );
		_setElementHeight( cssSelectors.sidr + ' .menu-item-has-children > a', cssSelectors.sidr + ' ' + cssSelectors.submenuToggle );
	}

	/**
	 * for parent menu items which don't actually link to anything of their own, make them into virtual buttons
	 * @since 1.6.0
	 */
	function _orphanedParents() {
		var _orphan = $( cssSelectors.sidr + ' .menu-item-has-children > a[href="#"]' );

		_orphan
			.addClass( cssClasses.submenuToggle )
			.attr( 'role', 'button' )
			.attr( 'aria-pressed', false )
			.next( cssSelectors.submenuToggle ).remove();
	}

	/**
	 * submenu toggle button behavior. when one opens, others close
	 *
	 */
	function _submenuToggle() {
		var _this   = $( this ),
			_others = _this.closest( '.menu-item' ).siblings();
		_toggleAria( _this, 'aria-pressed' );
		_this.toggleClass( cssClasses.menuOpen );
		_this.next( '.sub-menu' ).slideToggle( 'fast' );

		_others.find( cssSelectors.submenuToggle ).removeClass( cssClasses.menuOpen ).attr( 'aria-pressed', false );
		_others.find( '.sub-menu' ).slideUp( 'fast' );
	}

	/**
	 * Add a close button to the Sidr panel.
	 * @param panel
	 * @private
	 */
	function _addCloseButton( panel ) {
		var id = panel.indexOf( '#' ) <= 0 ? '#' + panel : panel;
		if ( $( id + ' .menu-close' ).length === 0 ) {
			$( id + ' ' + cssSelectors.sidr + '-inner' ).prepend( ssme.params.close );
		}
		$( ssme.params.closeevent ).on( 'click', function () {
			$( this ).attr( 'aria-pressed', false );
			$.sidr( 'close', panel );
			$( cssSelectors.sidr + ' ' + cssSelectors.submenuToggle ).removeClass( cssClasses.menuOpen ).attr( 'aria-expanded', false ).next( '.sub-menu' ).slideUp( 'fast' );
		} );
	}

	/**
	 * generic function to get the display value of an ID element
	 * @param  {id} $id id of an element
	 * @return {string}     CSS property value of the element
	 */
	function _isDisplayNone( $id ) {
		var element = document.getElementById( $id ),
			style   = window.getComputedStyle( element );
		return 'none' === style.getPropertyValue( 'display' );
	}

	/**
	 * Toggle aria attributes
	 * @param $this
	 * @param attribute
	 * @private
	 */
	function _toggleAria( $this, attribute ) {
		$this.attr( attribute, function ( index, value ) {
			return 'false' === value;
		} );
	}

	/**
	 * check whether we are in the customizer window or not
	 * @returns {boolean}
	 * @private
	 */
	function _isCustomizer() {
		return Boolean( ssme.params.search_on );
	}

	/**
	 * Delay action after resize
	 * @param func
	 * @param wait
	 * @param immediate
	 * @returns {Function}
	 * @private
	 */
	function _debounce( func, wait, immediate ) {
		var timeout;
		return function() {
			var context = this, args = arguments;
			var later   = function () {
				timeout = null;
				if ( ! immediate ) {
					func.apply( context, args );
				}
			};
			var callNow = immediate && ! timeout;
			clearTimeout( timeout );
			timeout = setTimeout( later, wait );
			if ( callNow ) {
				func.apply( context, args );
			}
		};
	}

	/**
	 * Set the height of one element based on the height of another.
	 * @param $selector
	 * @param $before
	 * @private
	 */
	function _setElementHeight( $selector, $before ) {
		$( $before ).css( 'height', $( $selector ).outerHeight() );
	}

	$( document ).ready( function () {
		ssme.params = typeof SuperSideMeVar === 'undefined' ? '' : SuperSideMeVar;

		if ( typeof ssme.params !== 'undefined' ) {
			ssme.init();
		}
	} );

})( document, jQuery );

/**
 * SuperSide Me main js engine
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2016 Robin Cornett
 * @license   GPL-2.0+
 */
(function ( document, $, undefined ) {
	'use strict';

	var plugin              = {},
	    sidrEnabled         = false,
	    supersidemeDefaults = {
			button: 'slide-menu',
			panel: 'side-menu'
	    },
	    cssClasses          = {
		    sidr: 'sidr',
		    submenuToggle: 'sub-menu-toggle',
		    menuOpen: 'menu-open'
	    },
	    cssSelectors        = {
		    sidr: '.' + cssClasses.sidr,
		    submenuToggle: '.' + cssClasses.submenuToggle,
		    button: '#' + supersidemeDefaults.button
		};

	plugin.init = function () {
		_supersidemeHandler( supersidemeDefaults.button );
	};

	/********************
	 * Private Functions
	 ********************/

	/**
	 * function to handle everything
	 * @return {panel} Sidr panel
	 */
	function _supersidemeHandler( button ) {

		_mainmenuButton( button );

		if ( cssSelectors.button.length === 0 ) {
			return;
		}

		if ( _isCustomizer() ) {
			_engage();
		}

		$( cssSelectors.button ).on( 'click.supersideme', function () {
			_openSesame();
			_closeSesame();
		} );

		// Close the menu if the window is resized
		$( window ).on( 'resize.supersideme', _doResize ).triggerHandler( 'resize.supersideme' );
	}

	/**
	 * build the main menu button
	 * @return {<button>} slide-nav-link button
	 */
	function _mainmenuButton( button ) {

		var buttonText = plugin.params.navigation;
		if ( _isCustomizer() ) {
			buttonText = parent.wp.customize.instance( 'supersideme[navigation]' ).get();
		}
		var mainmenuButton = $( '<button />', {
			role : 'button',
			'aria-pressed' : false,
			'aria-expanded' : false,
			'aria-controls' : $( supersidemeDefaults.panel ).attr( 'id' ),
			id : button,
			'class' : 'slide-nav-link'
		} ).append( buttonText );

		if ( plugin.params.navigation.length === 0 ) {
			mainmenuButton.attr( 'aria-label', plugin.params.navarialabel );
		}
		$( plugin.params.location ).prepend( mainmenuButton );
	}

	/**
	 * Open the side panel
	 */
	function _openSesame() {
		if ( ! sidrEnabled ) {
			_engage();
			sidrEnabled = true;
			$.sidr( 'open', supersidemeDefaults.panel );
			_checkSidrClass();
		}
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
	 * actions to perform on load/window resize
	 */
	function _doResize() {
		_addBodyClass( supersidemeDefaults.button );
		if ( plugin.params.swipe ) {
			_enableSwipe( supersidemeDefaults.button, 'body' );
		}

		plugin.skipLinks = typeof supersidemeSkipLinks === 'undefined' ? '' : supersidemeSkipLinks;
		if ( typeof plugin.skipLinks !== 'undefined' ) {
			_changeSkipLinks( supersidemeDefaults.button );
		}
	}

	/**
	 * change body class based on main menu button visibility
	 * @param {id} button main menu button
	 */
	function _addBodyClass( button ) {
		if ( 'none' === _getDisplayValue( button ) ) {
			$( 'body' ).removeClass( 'supersideme' );
			_closeSesame();
			return;
		}
		$( 'body' ).addClass( 'supersideme' );
	}

	/**
	 * To cover users without updated CSS--check if sidr is set to display:none and fix.
	 * @private
	 */
	function _checkSidrClass() {
		if ( 'none' === _getDisplayValue( 'side-menu' ) ) {
			var element = document.getElementById( 'side-menu' );
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
		if ( 'none' === _getDisplayValue( button ) ) {
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
		var _startLink = plugin.skipLinks.startLink,
			_endLink   = button,
			_hideLinks = $( plugin.skipLinks.ulClass + ' a[href*="#' + plugin.skipLinks.contains + '"]' ).not( 'a[href*="' + plugin.skipLinks.unique + '"]' );
		if ( 'none' === _getDisplayValue( button ) ) {
			_startLink = button;
			_endLink   = plugin.skipLinks.startLink;
			$( _hideLinks ).removeAttr( 'style' );
		} else {
			$( _hideLinks ).hide();
		}
		var _link   = $( plugin.skipLinks.ulClass + ' a[href*="#' + _startLink + '"]' ),
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
		$.fn.swipe.defaults.allowPageScroll = 'vertical';
		$.fn.swipe.defaults.threshold       = 120;
		$.fn.swipe.defaults.swipeLeft       = 'left' === plugin.params.side ? _closeSesame : _openSesame;
		$.fn.swipe.defaults.swipeRight      = 'left' === plugin.params.side ? _openSesame : _closeSesame;
		body.swipe( {} );
	}

	/**
	 * start up the sidr panel when main menu button is clicked
	 * @param  mainmenuButton menu button
	 * @return full sidr panel
	 */
	function _engage() {
		var mainmenuButton = $( '.slide-nav-link' ),
		    submenuButton  = $( cssSelectors.sidr + ' ' + cssSelectors.submenuToggle ),
		    panelSource    = function () {
			    _fillPanel();
		    };
		if ( null !== plugin.params.source && null !== document.getElementById( plugin.params.source ) ) {
			panelSource = '#' + plugin.params.source;
		}

		mainmenuButton.sidr( {
			name: supersidemeDefaults.panel,
			side: plugin.params.side,
			source: panelSource,
			renaming: false,
			displace: plugin.params.displace,
			speed: parseInt( plugin.params.speed, 10 ),
			onOpen: function () {
				mainmenuButton
					.attr( 'aria-pressed', true )
					.attr( 'aria-expanded', true )
					.toggleClass( cssClasses.menuOpen );
				_a11y( mainmenuButton );
			},
			onClose: function () {
				mainmenuButton
					.attr( 'aria-pressed', false )
					.attr( 'aria-expanded', false )
					.removeClass( cssClasses.menuOpen );
				sidrEnabled = false;
			}
		} );

		if ( $( '.menu-close' ).length === 0 ) {
			$( cssSelectors.sidr + '-inner' ).prepend( plugin.params.close );
		}

		if ( submenuButton.length === 0 ) {
			_dosubmenuButtons();
			_orphanedParents();
			$( cssSelectors.sidr + ' ' + cssSelectors.submenuToggle ).on( 'click.supersideme-submenu', _submenuToggle );
		}

		// Close the navigation if close link is clicked
		$( plugin.params.closeevent ).on( 'click', function () {
			var closeButton = $( this );
			closeButton.attr( 'aria-pressed', 'false' );
			_closeSesame();
		} );

		return false;

	}

	/**
	 * add keyboard navigation to the panel
	 * @param  mainmenuButton main menu button
	 *
	 */
	function _a11y( mainmenuButton ) {
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
				mainmenuButton.focus();
				return false;
				// When tabbing backwards and tabbing out of the first link OR the menu container.
			}
			if ( ( firstItem[ 0 ] === e.target || navEl[ 0 ] === e.target ) && e.shiftKey ) {
				mainmenuButton.focus();
				return false;
			}
		} );
		// When focus is on the toggle button.
		mainmenuButton.on( 'keydown.sidrNav', function ( e ) {
			// If it's not the tab key then return.
			if ( 9 !== e.keyCode ) {
				return;
			}
			// when tabbing forwards
			if ( mainmenuButton[ 0 ] === e.target && !e.shiftKey ) {
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
		var navigationMenu = $( '<nav />', {
			    'class': 'side-navigation',
			    'role': 'navigation',
			    'itemscope': 'itemscope',
			    'itemtype': 'http://schema.org/SiteNavigationElement'
		    } ).append( $( '<ul />', {
			    'class': 'side-nav'
		    } ) ),
		    sidrInner      = $( '<div />', {
			    'class': 'sidr-inner'
		    } ),
		    _selectors     = {
			    widget: '.supersideme.widget-area',
			    inner: cssSelectors.sidr + '-inner'
		    };

		// Add navigation menus
		if ( $( _selectors.inner ).length !== 0 ) {
			return;
		}
		$( cssSelectors.sidr ).prepend( sidrInner );
		if ( plugin.params.search ) {
			$( _selectors.inner ).prepend( plugin.params.search );
			if ( '0' === plugin.params.search_on ) {
				$( '.search-me' ).attr( 'style', 'display:none;' );
			}
		}
		if ( plugin.params.menus ) {
			$( _selectors.inner ).append( navigationMenu );
			$( '.side-nav' ).prepend( plugin.params.menus );
		}
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
			'aria-label': plugin.params.subarialabel,
			'class': cssClasses.submenuToggle
		} ).append( $( '<span />', {
			'class': 'screen-reader-text',
			text: plugin.params.submenu
		} ) );

		if ( $( cssSelectors.sidr + ' ' + cssSelectors.submenuToggle ).length !== 0 ) {
			return;
		}
		$( cssSelectors.sidr + ' .sub-menu' ).before( submenuButton );
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
			.attr( 'aria-label', plugin.params.subarialabel );

		_orphan.next( cssSelectors.submenuToggle ).remove();
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
	 * generic function to get the display value of an ID element
	 * @param  {id} $id id of an element
	 * @return {string}     CSS property value of the element
	 */
	function _getDisplayValue( $id ) {
		var element = document.getElementById( $id ),
			style   = window.getComputedStyle( element );
		return style.getPropertyValue( 'display' );
	}

	/**
	 * Toggle aria attributes
	 * @param  {button} $this     passed through
	 * @param  {aria-xx} attribute aria attribute to toggle
	 * @return {bool}           from _ariaReturn
	 */
	function _toggleAria( $this, attribute ) {
		$this.attr( attribute, function ( index, value ) {
			return _ariaReturn( value );
		} );
	}

	/**
	 * update aria-xx value of an attribute
	 * @param  {aria-xx} value passed from function
	 * @return {bool}
	 */
	function _ariaReturn( value ) {
		return 'false' === value ? true : false;
	}

	/**
	 * check whether we are in the customizer window or not
	 * @returns {boolean}
	 * @private
	 */
	function _isCustomizer() {
		return Boolean( plugin.params.search_on );
	}

	$( document ).ready( function () {
		plugin.params = typeof SuperSideMeVar === 'undefined' ? '' : SuperSideMeVar;

		if ( typeof plugin.params !== 'undefined' ) {
			plugin.init();
		}
	} );

})( document, jQuery );

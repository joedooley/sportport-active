'use strict';

(function ($, window, document, undefined) {

	'use strict';

	$('div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)').append('<div class="inc button buttons_added">+</div><div class="dec button buttons_added">-</div>');

	$('.buttons_added').on('click', function () {

		var $button = $(this);
		var oldValue = $button.parent().find("input").val();

		if ($button.text() == "+") {
			var newVal = parseFloat(oldValue) + 1;
		} else {
			// Don't allow decrementing below zero
			if (oldValue > 0) {
				var newVal = parseFloat(oldValue) - 1;
			} else {
				newVal = 0;
			}
		}

		$button.parent().find("input").val(newVal);
	});

	// Use to add AJAX

	//var id = $button.attr("id");
	//$.ajax({
	//	type: "POST",
	//	url: "dosomething.php?id=" + id + "&newvalue=" + newVal,
	//	success: function () {
	//		$button.parent().find("input").val(newVal);
	//	}
	//});
})(jQuery, window, document);
'use strict';

(function (document, $, undefined) {

	$('body').addClass('js');

	'use strict';

	var digital = {},
	    mainMenuButtonClass = 'menu-toggle',
	    subMenuButtonClass = 'sub-menu-toggle';

	digital.init = function () {
		var toggleButtons = {
			menu: $('<button />', {
				'class': mainMenuButtonClass,
				'aria-expanded': false,
				'aria-pressed': false,
				'role': 'button'
			}).append(digital.params.mainMenu),
			submenu: $('<button />', {
				'class': subMenuButtonClass,
				'aria-expanded': false,
				'aria-pressed': false,
				'role': 'button'
			}).append($('<span />', {
				'class': 'screen-reader-text',
				text: digital.params.subMenu
			}))
		};
		$('.nav-primary').before(toggleButtons.menu); // add the main nav buttons
		$('nav .sub-menu').before(toggleButtons.submenu); // add the submenu nav buttons
		$('.' + mainMenuButtonClass).each(_addClassID);
		$(window).on('resize.digital', _doResize).triggerHandler('resize.digital');
		$('.' + mainMenuButtonClass).on('click.digital-mainbutton', _mainmenuToggle);
		$('.' + subMenuButtonClass).on('click.digital-subbutton', _submenuToggle);
	};

	// add nav class and ID to related button
	function _addClassID() {
		var $this = $(this),
		    nav = $this.next('nav'),
		    id = 'class';
		$this.addClass($(nav).attr('class'));
		if ($(nav).attr('id')) {
			id = 'id';
		}
		$this.attr('id', 'mobile-' + $(nav).attr(id));
	}

	// Change Skiplinks and Superfish
	function _doResize() {
		var buttons = $('button[id^=mobile-]').attr('id');
		if (typeof buttons === 'undefined') {
			return;
		}
		_superfishToggle(buttons);
		_changeSkipLink(buttons);
		_maybeClose(buttons);
	}

	/**
  * action to happen when the main menu button is clicked
  */
	function _mainmenuToggle() {
		var $this = $(this);
		_toggleAria($this, 'aria-pressed');
		_toggleAria($this, 'aria-expanded');
		$this.toggleClass('activated');
		$('nav.nav-primary').slideToggle('fast'); //changed to .nav-primary since we're not toggling .nav-secondary
	}

	/**
  * action for submenu toggles
  */
	function _submenuToggle() {

		var $this = $(this),
		    others = $this.closest('.menu-item').siblings();
		_toggleAria($this, 'aria-pressed');
		_toggleAria($this, 'aria-expanded');
		$this.toggleClass('activated');
		$this.next('.sub-menu').slideToggle('fast');

		others.find('.' + subMenuButtonClass).removeClass('activated').attr('aria-pressed', 'false');
		others.find('.sub-menu').slideUp('fast');
	}

	/**
  * activate/deactivate superfish
  */
	function _superfishToggle(buttons) {
		if (typeof $('.js-superfish').superfish !== 'function') {
			return;
		}
		if ('none' === _getDisplayValue(buttons)) {
			$('.js-superfish').superfish({
				'delay': 100,
				'animation': { 'opacity': 'show', 'height': 'show' },
				'dropShadows': false
			});
		} else {
			$('.js-superfish').superfish('destroy');
		}
	}

	/**
  * modify skip links to match mobile buttons
  */
	function _changeSkipLink(buttons) {
		var startLink = 'genesis-nav',
		    endLink = 'mobile-genesis-nav';
		if ('none' === _getDisplayValue(buttons)) {
			startLink = 'mobile-genesis-nav';
			endLink = 'genesis-nav';
		}
		$('.genesis-skip-link a[href^="#' + startLink + '"]').each(function () {
			var link = $(this).attr('href');
			link = link.replace(startLink, endLink);
			$(this).attr('href', link);
		});
	}

	function _maybeClose(buttons) {
		if ('none' !== _getDisplayValue(buttons)) {
			return;
		}
		$('.menu-toggle, .sub-menu-toggle').removeClass('activated').attr('aria-expanded', false).attr('aria-pressed', false);
		$('nav, .sub-menu').attr('style', '');
	}

	/**
  * generic function to get the display value of an element
  * @param  {id} $id ID to check
  * @return {string}     CSS value of display property
  */
	function _getDisplayValue($id) {
		var element = document.getElementById($id),
		    style = window.getComputedStyle(element);
		return style.getPropertyValue('display');
	}

	/**
  * Toggle aria attributes
  * @param  {button} $this     passed through
  * @param  {aria-xx} attribute aria attribute to toggle
  * @return {bool}           from _ariaReturn
  */
	function _toggleAria($this, attribute) {
		$this.attr(attribute, function (index, value) {
			return 'false' === value;
		});
	}

	$(document).ready(function () {

		digital.params = typeof DigitalL10n === 'undefined' ? '' : DigitalL10n;

		if (typeof digital.params !== 'undefined') {
			digital.init();
		}
	});
})(document, jQuery);
//import {VanillaModal} from 'vanilla-modal';
//
//
//const modal = new VanillaModal({
//
//	// Default API Options
//	modal: '.modal',
//	modalInner: '.modal-inner',
//	modalContent: '.modal-content',
//	open: '[rel="modal:open"]',
//	close: '[rel="modal:close"]',
//	page: 'body',
//	loadClass: 'vanilla-modal',
//	class: 'modal-visible',
//	clickOutside: false,
//	closeKeys: [27],
//	transitions: true,
//	//onBeforeOpen: null,
//	//onBeforeClose: null,
//	//onOpen: null,
//	//onClose: null,
//
//	onBeforeOpen: function (e) {
//		console.log('onBeforeOpen hook', e, this);
//	},
//	onOpen: function (e) {
//		console.log('onOpen hook', e, this);
//	},
//	onBeforeClose: function (e) {
//		console.log('onBeforeClose hook', e, this);
//	},
//	onClose: function (e) {
//		console.log('onClose hook', e, this);
//	}
//});
"use strict";
'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol ? "symbol" : typeof obj; };

(function (global, factory) {

	'use strict';

	if (typeof define === "function" && define.amd) {
		define(['exports'], factory);
	} else if (typeof exports !== "undefined") {
		factory(exports);
	} else {
		var mod = {
			exports: {}
		};
		factory(mod.exports);
		global.vanillaModal = mod.exports;
	}
})(undefined, function (exports) {
	Object.defineProperty(exports, "__esModule", {
		value: true
	});

	function _classCallCheck(instance, Constructor) {
		if (!(instance instanceof Constructor)) {
			throw new TypeError("Cannot call a class as a function");
		}
	}

	var _createClass = function () {
		function defineProperties(target, props) {
			for (var i = 0; i < props.length; i++) {
				var descriptor = props[i];
				descriptor.enumerable = descriptor.enumerable || false;
				descriptor.configurable = true;
				if ("value" in descriptor) descriptor.writable = true;
				Object.defineProperty(target, descriptor.key, descriptor);
			}
		}

		return function (Constructor, protoProps, staticProps) {
			if (protoProps) defineProperties(Constructor.prototype, protoProps);
			if (staticProps) defineProperties(Constructor, staticProps);
			return Constructor;
		};
	}();

	var VanillaModal = exports.VanillaModal = function () {

		/**
   * @param {Object} [userSettings]
   */

		function VanillaModal(userSettings) {
			_classCallCheck(this, VanillaModal);

			this.$$ = {
				modal: '.modal',
				modalInner: '.modal-inner',
				modalContent: '.modal-content',
				open: '[rel="modal:open"]',
				close: '[rel="modal:close"]',
				page: 'body',
				class: 'modal-visible',
				loadClass: 'vanilla-modal',
				clickOutside: true,
				closeKeys: [27],
				transitions: true,
				transitionEnd: null,
				onBeforeOpen: null,
				onBeforeClose: null,
				onOpen: null,
				onClose: null
			};

			this._applyUserSettings(userSettings);
			this.error = false;
			this.isOpen = false;
			this.current = null;
			this.open = this._open.bind(this);
			this.close = this._close.bind(this);
			this.$$.transitionEnd = this._transitionEndVendorSniff();
			this.$ = this._setupDomNodes();

			if (!this.error) {
				this._addLoadedCssClass();
				this._events().add();
			} else {
				console.error('Please fix errors before proceeding.');
			}
		}

		/**
   * @param {Object} userSettings
   */

		_createClass(VanillaModal, [{
			key: '_applyUserSettings',
			value: function _applyUserSettings(userSettings) {
				if ((typeof userSettings === 'undefined' ? 'undefined' : _typeof(userSettings)) === 'object') {
					for (var i in userSettings) {
						if (userSettings.hasOwnProperty(i)) {
							this.$$[i] = userSettings[i];
						}
					}
				}
			}
		}, {
			key: '_transitionEndVendorSniff',
			value: function _transitionEndVendorSniff() {
				if (this.$$.transitions === false) return;
				var el = document.createElement('div');
				var transitions = {
					'transition': 'transitionend',
					'OTransition': 'otransitionend',
					'MozTransition': 'transitionend',
					'WebkitTransition': 'webkitTransitionEnd'
				};
				for (var i in transitions) {
					if (transitions.hasOwnProperty(i) && el.style[i] !== undefined) {
						return transitions[i];
					}
				}
			}

			/**
    * @param {String} selector
    * @param {Node} parent
    */

		}, {
			key: '_getNode',
			value: function _getNode(selector, parent) {
				var targetNode = parent || document;
				var node = targetNode.querySelector(selector);
				if (!node) {
					this.error = true;
					return console.error(selector + ' not found in document.');
				}
				return node;
			}
		}, {
			key: '_setupDomNodes',
			value: function _setupDomNodes() {
				var $ = {};
				$.modal = this._getNode(this.$$.modal);
				$.page = this._getNode(this.$$.page);
				$.modalInner = this._getNode(this.$$.modalInner, this.modal);
				$.modalContent = this._getNode(this.$$.modalContent, this.modal);
				return $;
			}
		}, {
			key: '_addLoadedCssClass',
			value: function _addLoadedCssClass() {
				this._addClass(this.$.page, this.$$.loadClass);
			}

			/**
    * @param {Node} el
    * @param {String} className
    */

		}, {
			key: '_addClass',
			value: function _addClass(el, className) {
				if (el instanceof HTMLElement === false) return;
				var cssClasses = el.className.split(' ');
				if (cssClasses.indexOf(className) === -1) {
					cssClasses.push(className);
				}
				el.className = cssClasses.join(' ');
			}

			/**
    * @param {Node} el
    * @param {String} className
    */

		}, {
			key: '_removeClass',
			value: function _removeClass(el, className) {
				if (el instanceof HTMLElement === false) return;
				var cssClasses = el.className.split(' ');
				if (cssClasses.indexOf(className) > -1) {
					cssClasses.splice(cssClasses.indexOf(className), 1);
				}
				el.className = cssClasses.join(' ');
			}
		}, {
			key: '_setOpenId',
			value: function _setOpenId() {
				var id = this.current.id || 'anonymous';
				this.$.page.setAttribute('data-current-modal', id);
			}
		}, {
			key: '_removeOpenId',
			value: function _removeOpenId() {
				this.$.page.removeAttribute('data-current-modal');
			}

			/**
    * @param {mixed} e
    */

		}, {
			key: '_getElementContext',
			value: function _getElementContext(e) {
				if (e && typeof e.hash === 'string') {
					return document.querySelector(e.hash);
				} else if (typeof e === 'string') {
					return document.querySelector(e);
				} else {
					return console.error('No selector supplied to open()');
				}
			}

			/**
    * @param {Event} e
    */

		}, {
			key: '_open',
			value: function _open(matches, e) {
				this._releaseNode();
				this.current = this._getElementContext(matches);
				if (this.current instanceof HTMLElement === false) return console.error('VanillaModal target must exist on page.');
				if (typeof this.$$.onBeforeOpen === 'function') this.$$.onBeforeOpen.call(this, e);
				this._captureNode();
				this._addClass(this.$.page, this.$$.class);
				this._setOpenId();
				this.isOpen = true;
				if (typeof this.$$.onOpen === 'function') this.$$.onOpen.call(this, e);
			}
		}, {
			key: '_detectTransition',
			value: function _detectTransition() {
				var css = window.getComputedStyle(this.$.modal, null);
				var transitionDuration = ['transitionDuration', 'oTransitionDuration', 'MozTransitionDuration', 'webkitTransitionDuration'];
				var hasTransition = transitionDuration.filter(function (i) {
					if (typeof css[i] === 'string' && parseFloat(css[i]) > 0) {
						return true;
					}
				});
				return hasTransition.length ? true : false;
			}

			/**
    * @param {Event} e
    */

		}, {
			key: '_close',
			value: function _close(e) {
				if (this.isOpen === true) {
					this.isOpen = false;
					if (typeof this.$$.onBeforeClose === 'function') this.$$.onBeforeClose.call(this, e);
					this._removeClass(this.$.page, this.$$.class);
					var transitions = this._detectTransition();
					if (this.$$.transitions && this.$$.transitionEnd && transitions) {
						this._closeModalWithTransition(e);
					} else {
						this._closeModal(e);
					}
				}
			}
		}, {
			key: '_closeModal',
			value: function _closeModal(e) {
				this._removeOpenId(this.$.page);
				this._releaseNode();
				this.isOpen = false;
				this.current = null;
				if (typeof this.$$.onClose === 'function') this.$$.onClose.call(this, e);
			}
		}, {
			key: '_closeModalWithTransition',
			value: function _closeModalWithTransition(e) {
				var _closeTransitionHandler = function () {
					this.$.modal.removeEventListener(this.$$.transitionEnd, _closeTransitionHandler);
					this._closeModal(e);
				}.bind(this);
				this.$.modal.addEventListener(this.$$.transitionEnd, _closeTransitionHandler);
			}
		}, {
			key: '_captureNode',
			value: function _captureNode() {
				if (this.current) {
					while (this.current.childNodes.length > 0) {
						this.$.modalContent.appendChild(this.current.childNodes[0]);
					}
				}
			}
		}, {
			key: '_releaseNode',
			value: function _releaseNode() {
				if (this.current) {
					while (this.$.modalContent.childNodes.length > 0) {
						this.current.appendChild(this.$.modalContent.childNodes[0]);
					}
				}
			}

			/**
    * @param {Event} e
    */

		}, {
			key: '_closeKeyHandler',
			value: function _closeKeyHandler(e) {
				if (Object.prototype.toString.call(this.$$.closeKeys) !== '[object Array]' || this.$$.closeKeys.length === 0) return;
				if (this.$$.closeKeys.indexOf(e.which) > -1 && this.isOpen === true) {
					e.preventDefault();
					this.close(e);
				}
			}

			/**
    * @param {Event} e
    */

		}, {
			key: '_outsideClickHandler',
			value: function _outsideClickHandler(e) {
				if (this.$$.clickOutside !== true) return;
				var node = e.target;
				while (node && node != document.body) {
					if (node === this.$.modalInner) return;
					node = node.parentNode;
				}
				this.close(e);
			}

			/**
    * @param {Event} e
    * @param {String} selector
    */

		}, {
			key: '_matches',
			value: function _matches(e, selector) {
				var el = e.target;
				var matches = (el.document || el.ownerDocument).querySelectorAll(selector);
				for (var i = 0; i < matches.length; i++) {
					var child = el;
					while (child && child !== document.body) {
						if (child === matches[i]) return child;
						child = child.parentNode;
					}
				}
				return null;
			}

			/**
    * @param {Event} e
    */

		}, {
			key: '_delegateOpen',
			value: function _delegateOpen(e) {
				var matches = this._matches(e, this.$$.open);
				if (matches) {
					e.preventDefault();
					e.delegateTarget = matches;
					return this.open(matches, e);
				}
			}

			/**
    * @param {Event} e
    */

		}, {
			key: '_delegateClose',
			value: function _delegateClose(e) {
				if (this._matches(e, this.$$.close)) {
					e.preventDefault();
					return this.close(e);
				}
			}

			/**
    * @private {Function} add
    */

		}, {
			key: '_events',
			value: function _events() {

				var _closeKeyHandler = this._closeKeyHandler.bind(this);
				var _outsideClickHandler = this._outsideClickHandler.bind(this);
				var _delegateOpen = this._delegateOpen.bind(this);
				var _delegateClose = this._delegateClose.bind(this);

				var add = function add() {
					this.$.modal.addEventListener('click', _outsideClickHandler, false);
					document.addEventListener('keydown', _closeKeyHandler, false);
					document.addEventListener('click', _delegateOpen, false);
					document.addEventListener('click', _delegateClose, false);
				};

				this.destroy = function () {
					this.close();
					this.$.modal.removeEventListener('click', _outsideClickHandler);
					document.removeEventListener('keydown', _closeKeyHandler);
					document.removeEventListener('click', _delegateOpen);
					document.removeEventListener('click', _delegateClose);
				};

				return {
					add: add.bind(this)
				};
			}
		}]);

		return VanillaModal;
	}();
});
"use strict";

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol ? "symbol" : typeof obj; };

/*! modernizr 3.2.0 (Custom Build) | MIT *
 * http://modernizr.com/download/?-flexbox !*/
!function (e, n, t) {
	function r(e, n) {
		return (typeof e === "undefined" ? "undefined" : _typeof(e)) === n;
	}

	function o() {
		var e, n, t, o, s, i, a;
		for (var l in C) {
			if (C.hasOwnProperty(l)) {
				if (e = [], n = C[l], n.name && (e.push(n.name.toLowerCase()), n.options && n.options.aliases && n.options.aliases.length)) for (t = 0; t < n.options.aliases.length; t++) {
					e.push(n.options.aliases[t].toLowerCase());
				}for (o = r(n.fn, "function") ? n.fn() : n.fn, s = 0; s < e.length; s++) {
					i = e[s], a = i.split("."), 1 === a.length ? Modernizr[a[0]] = o : (!Modernizr[a[0]] || Modernizr[a[0]] instanceof Boolean || (Modernizr[a[0]] = new Boolean(Modernizr[a[0]])), Modernizr[a[0]][a[1]] = o), g.push((o ? "" : "no-") + a.join("-"));
				}
			}
		}
	}

	function s(e) {
		var n = x.className,
		    t = Modernizr._config.classPrefix || "";
		if (_ && (n = n.baseVal), Modernizr._config.enableJSClass) {
			var r = new RegExp("(^|\\s)" + t + "no-js(\\s|$)");
			n = n.replace(r, "$1" + t + "js$2");
		}
		Modernizr._config.enableClasses && (n += " " + t + e.join(" " + t), _ ? x.className.baseVal = n : x.className = n);
	}

	function i() {
		return "function" != typeof n.createElement ? n.createElement(arguments[0]) : _ ? n.createElementNS.call(n, "http://www.w3.org/2000/svg", arguments[0]) : n.createElement.apply(n, arguments);
	}

	function a(e, n) {
		return !! ~("" + e).indexOf(n);
	}

	function l(e) {
		return e.replace(/([a-z])-([a-z])/g, function (e, n, t) {
			return n + t.toUpperCase();
		}).replace(/^-/, "");
	}

	function f(e, n) {
		return function () {
			return e.apply(n, arguments);
		};
	}

	function u(e, n, t) {
		var o;
		for (var s in e) {
			if (e[s] in n) return t === !1 ? e[s] : (o = n[e[s]], r(o, "function") ? f(o, t || n) : o);
		}return !1;
	}

	function d(e) {
		return e.replace(/([A-Z])/g, function (e, n) {
			return "-" + n.toLowerCase();
		}).replace(/^ms-/, "-ms-");
	}

	function p() {
		var e = n.body;
		return e || (e = i(_ ? "svg" : "body"), e.fake = !0), e;
	}

	function c(e, t, r, o) {
		var s,
		    a,
		    l,
		    f,
		    u = "modernizr",
		    d = i("div"),
		    c = p();
		if (parseInt(r, 10)) for (; r--;) {
			l = i("div"), l.id = o ? o[r] : u + (r + 1), d.appendChild(l);
		}return s = i("style"), s.type = "text/css", s.id = "s" + u, (c.fake ? c : d).appendChild(s), c.appendChild(d), s.styleSheet ? s.styleSheet.cssText = e : s.appendChild(n.createTextNode(e)), d.id = u, c.fake && (c.style.background = "", c.style.overflow = "hidden", f = x.style.overflow, x.style.overflow = "hidden", x.appendChild(c)), a = t(d, e), c.fake ? (c.parentNode.removeChild(c), x.style.overflow = f, x.offsetHeight) : d.parentNode.removeChild(d), !!a;
	}

	function m(n, r) {
		var o = n.length;
		if ("CSS" in e && "supports" in e.CSS) {
			for (; o--;) {
				if (e.CSS.supports(d(n[o]), r)) return !0;
			}return !1;
		}
		if ("CSSSupportsRule" in e) {
			for (var s = []; o--;) {
				s.push("(" + d(n[o]) + ":" + r + ")");
			}return s = s.join(" or "), c("@supports (" + s + ") { #modernizr { position: absolute; } }", function (e) {
				return "absolute" == getComputedStyle(e, null).position;
			});
		}
		return t;
	}

	function h(e, n, o, s) {
		function f() {
			d && (delete z.style, delete z.modElem);
		}

		if (s = r(s, "undefined") ? !1 : s, !r(o, "undefined")) {
			var u = m(e, o);
			if (!r(u, "undefined")) return u;
		}
		for (var d, p, c, h, v, y = ["modernizr", "tspan"]; !z.style;) {
			d = !0, z.modElem = i(y.shift()), z.style = z.modElem.style;
		}for (c = e.length, p = 0; c > p; p++) {
			if (h = e[p], v = z.style[h], a(h, "-") && (h = l(h)), z.style[h] !== t) {
				if (s || r(o, "undefined")) return f(), "pfx" == n ? h : !0;
				try {
					z.style[h] = o;
				} catch (g) {}
				if (z.style[h] != v) return f(), "pfx" == n ? h : !0;
			}
		}return f(), !1;
	}

	function v(e, n, t, o, s) {
		var i = e.charAt(0).toUpperCase() + e.slice(1),
		    a = (e + " " + E.join(i + " ") + i).split(" ");
		return r(n, "string") || r(n, "undefined") ? h(a, n, o, s) : (a = (e + " " + b.join(i + " ") + i).split(" "), u(a, n, t));
	}

	function y(e, n, r) {
		return v(e, t, t, n, r);
	}

	var g = [],
	    C = [],
	    w = {
		_version: "3.2.0", _config: { classPrefix: "", enableClasses: !0, enableJSClass: !0, usePrefixes: !0 }, _q: [], on: function on(e, n) {
			var t = this;
			setTimeout(function () {
				n(t[e]);
			}, 0);
		}, addTest: function addTest(e, n, t) {
			C.push({ name: e, fn: n, options: t });
		}, addAsyncTest: function addAsyncTest(e) {
			C.push({ name: null, fn: e });
		}
	},
	    Modernizr = function Modernizr() {};
	Modernizr.prototype = w, Modernizr = new Modernizr();
	var x = n.documentElement,
	    _ = "svg" === x.nodeName.toLowerCase(),
	    S = "Moz O ms Webkit",
	    b = w._config.usePrefixes ? S.toLowerCase().split(" ") : [];
	w._domPrefixes = b;
	var E = w._config.usePrefixes ? S.split(" ") : [];
	w._cssomPrefixes = E;
	var P = { elem: i("modernizr") };
	Modernizr._q.push(function () {
		delete P.elem;
	});
	var z = { style: P.elem.style };
	Modernizr._q.unshift(function () {
		delete z.style;
	}), w.testAllProps = v, w.testAllProps = y, Modernizr.addTest("flexbox", y("flexBasis", "1px", !0)), o(), s(g), delete w.addTest, delete w.addAsyncTest;
	for (var N = 0; N < Modernizr._q.length; N++) {
		Modernizr._q[N]();
	}e.Modernizr = Modernizr;
}(window, document);
//# sourceMappingURL=all.js.map

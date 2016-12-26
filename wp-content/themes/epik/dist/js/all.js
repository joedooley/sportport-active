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

		var npButton = document.getElementById('mobile-nav-primary');

		function headerToggleMinHeight() {
			var header = document.querySelector('.site-header');
			header.classList.toggle('no-minh');
		}

		npButton.addEventListener('click', headerToggleMinHeight, false);

	});



})(document, jQuery);
"use strict";
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
		return !!~("" + e).indexOf(n);
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

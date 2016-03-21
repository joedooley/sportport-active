/*! Backstretch - v2.0.4 - 2013-06-19
 * http://srobbin.com/jquery-plugins/backstretch/
 * Copyright (c) 2013 Scott Robbin; Licensed MIT */
(function (a, d, p) {
	a.fn.backstretch          = function (c, b) {
		(c === p || 0 === c.length) && a.error("No images were supplied for Backstretch");
		0 === a(d).scrollTop() && d.scrollTo(0, 0);
		return this.each(function () {
			var d = a(this), g = d.data("backstretch");
			if (g) {
				if ("string" == typeof c && "function" == typeof g[c]) {
					g[c](b);
					return
				}
				b = a.extend(g.options, b);
				g.destroy(!0)
			}
			g = new q(this, c, b);
			d.data("backstretch", g)
		})
	};
	a.backstretch             = function (c, b) {
		return a("body").backstretch(c, b).data("backstretch")
	};
	a.expr[":"].backstretch   = function (c) {
		return a(c).data("backstretch") !== p
	};
	a.fn.backstretch.defaults = {centeredX: !0, centeredY: !0, duration: 5E3, fade: 0};
	var r                     = {
		left: 0,
		top: 0,
		overflow: "hidden",
		margin: 0,
		padding: 0,
		height: "100%",
		width: "100%",
		zIndex: -999999
	}, s                      = {
		position: "absolute",
		display: "none",
		margin: 0,
		padding: 0,
		border: "none",
		width: "auto",
		height: "auto",
		maxHeight: "none",
		maxWidth: "none",
		zIndex: -999999
	}, q                      = function (c, b, e) {
		this.options = a.extend({}, a.fn.backstretch.defaults, e || {});
		this.images  = a.isArray(b) ? b : [b];
		a.each(this.images, function () {
			a("<img />")[0].src = this
		});
		this.isBody     = c === document.body;
		this.$container = a(c);
		this.$root      = this.isBody ? l ? a(d) : a(document) : this.$container;
		c               = this.$container.children(".backstretch").first();
		this.$wrap      = c.length ? c : a('<div class="backstretch"></div>').css(r).appendTo(this.$container);
		this.isBody || (c = this.$container.css("position"), b = this.$container.css("zIndex"), this.$container.css({
			position: "static" === c ? "relative" : c,
			zIndex: "auto" === b ? 0 : b,
			background: "none"
		}), this.$wrap.css({zIndex: -999998}));
		this.$wrap.css({position: this.isBody && l ? "fixed" : "absolute"});
		this.index = 0;
		this.show(this.index);
		a(d).on("resize.backstretch", a.proxy(this.resize, this)).on("orientationchange.backstretch", a.proxy(function () {
			this.isBody && 0 === d.pageYOffset && (d.scrollTo(0, 1), this.resize())
		}, this))
	};
	q.prototype               = {
		resize: function () {
			try {
				var a = {
					left: 0,
					top: 0
				}, b  = this.isBody ? this.$root.width() : this.$root.innerWidth(), e = b, g = this.isBody ? d.innerHeight ? d.innerHeight : this.$root.height() : this.$root.innerHeight(), j = e / this.$img.data("ratio"), f;
				j >= g ? (f = (j - g) / 2, this.options.centeredY && (a.top = "-" + f + "px")) : (j = g, e = j * this.$img.data("ratio"), f = (e - b) / 2, this.options.centeredX && (a.left = "-" + f + "px"));
				this.$wrap.css({width: b, height: g}).find("img:not(.deleteable)").css({width: e, height: j}).css(a)
			} catch (h) {
			}
			return this
		}, show: function (c) {
			if (!(Math.abs(c) > this.images.length - 1)) {
				var b = this, e = b.$wrap.find("img").addClass("deleteable"), d = {relatedTarget: b.$container[0]};
				b.$container.trigger(a.Event("backstretch.before", d), [b, c]);
				this.index = c;
				clearInterval(b.interval);
				b.$img = a("<img />").css(s).bind("load", function (f) {
					var h = this.width || a(f.target).width();
					f     = this.height || a(f.target).height();
					a(this).data("ratio", h / f);
					a(this).fadeIn(b.options.speed || b.options.fade, function () {
						e.remove();
						b.paused || b.cycle();
						a(["after", "show"]).each(function () {
							b.$container.trigger(a.Event("backstretch." + this, d), [b, c])
						})
					});
					b.resize()
				}).appendTo(b.$wrap);
				b.$img.attr("src", b.images[c]);
				return b
			}
		}, next: function () {
			return this.show(this.index < this.images.length - 1 ? this.index + 1 : 0)
		}, prev: function () {
			return this.show(0 === this.index ? this.images.length - 1 : this.index - 1)
		}, pause: function () {
			this.paused = !0;
			return this
		}, resume: function () {
			this.paused = !1;
			this.next();
			return this
		}, cycle: function () {
			1 < this.images.length && (clearInterval(this.interval), this.interval = setInterval(a.proxy(function () {
				this.paused || this.next()
			}, this), this.options.duration));
			return this
		}, destroy: function (c) {
			a(d).off("resize.backstretch orientationchange.backstretch");
			clearInterval(this.interval);
			c || this.$wrap.remove();
			this.$container.removeData("backstretch")
		}
	};
	var l, f                  = navigator.userAgent, m = navigator.platform, e = f.match(/AppleWebKit\/([0-9]+)/), e = !!e && e[1], h = f.match(/Fennec\/([0-9]+)/), h = !!h && h[1], n = f.match(/Opera Mobi\/([0-9]+)/), t = !!n && n[1], k = f.match(/MSIE ([0-9]+)/), k = !!k && k[1];
	l                         = !((-1 < m.indexOf("iPhone") || -1 < m.indexOf("iPad") || -1 < m.indexOf("iPod")) && e && 534 > e || d.operamini && "[object OperaMini]" === {}.toString.call(d.operamini) || n && 7458 > t || -1 < f.indexOf("Android") && e && 533 > e || h && 6 > h || "palmGetResource" in d && e && 534 > e || -1 < f.indexOf("MeeGo") && -1 < f.indexOf("NokiaBrowser/8.5.0") || k && 6 >= k)
})(jQuery, window);

/*! modernizr 3.2.0 (Custom Build) | MIT *
 * http://modernizr.com/download/?-flexbox !*/
!function (e, n, t) {
	function r(e, n) {
		return typeof e === n
	}

	function o() {
		var e, n, t, o, s, i, a;
		for (var l in C)if (C.hasOwnProperty(l)) {
			if (e = [], n = C[l], n.name && (e.push(n.name.toLowerCase()), n.options && n.options.aliases && n.options.aliases.length))for (t = 0; t < n.options.aliases.length; t++)e.push(n.options.aliases[t].toLowerCase());
			for (o = r(n.fn, "function") ? n.fn() : n.fn, s = 0; s < e.length; s++)i = e[s], a = i.split("."), 1 === a.length ? Modernizr[a[0]] = o : (!Modernizr[a[0]] || Modernizr[a[0]] instanceof Boolean || (Modernizr[a[0]] = new Boolean(Modernizr[a[0]])), Modernizr[a[0]][a[1]] = o), g.push((o ? "" : "no-") + a.join("-"))
		}
	}

	function s(e) {
		var n = x.className, t = Modernizr._config.classPrefix || "";
		if (_ && (n = n.baseVal), Modernizr._config.enableJSClass) {
			var r = new RegExp("(^|\\s)" + t + "no-js(\\s|$)");
			n     = n.replace(r, "$1" + t + "js$2")
		}
		Modernizr._config.enableClasses && (n += " " + t + e.join(" " + t), _ ? x.className.baseVal = n : x.className = n)
	}

	function i() {
		return "function" != typeof n.createElement ? n.createElement(arguments[0]) : _ ? n.createElementNS.call(n, "http://www.w3.org/2000/svg", arguments[0]) : n.createElement.apply(n, arguments)
	}

	function a(e, n) {
		return !!~("" + e).indexOf(n)
	}

	function l(e) {
		return e.replace(/([a-z])-([a-z])/g, function (e, n, t) {
			return n + t.toUpperCase()
		}).replace(/^-/, "")
	}

	function f(e, n) {
		return function () {
			return e.apply(n, arguments)
		}
	}

	function u(e, n, t) {
		var o;
		for (var s in e)if (e[s] in n)return t === !1 ? e[s] : (o = n[e[s]], r(o, "function") ? f(o, t || n) : o);
		return !1
	}

	function d(e) {
		return e.replace(/([A-Z])/g, function (e, n) {
			return "-" + n.toLowerCase()
		}).replace(/^ms-/, "-ms-")
	}

	function p() {
		var e = n.body;
		return e || (e = i(_ ? "svg" : "body"), e.fake = !0), e
	}

	function c(e, t, r, o) {
		var s, a, l, f, u = "modernizr", d = i("div"), c = p();
		if (parseInt(r, 10))for (; r--;)l = i("div"), l.id = o ? o[r] : u + (r + 1), d.appendChild(l);
		return s = i("style"), s.type = "text/css", s.id = "s" + u, (c.fake ? c : d).appendChild(s), c.appendChild(d), s.styleSheet ? s.styleSheet.cssText = e : s.appendChild(n.createTextNode(e)), d.id = u, c.fake && (c.style.background = "", c.style.overflow = "hidden", f = x.style.overflow, x.style.overflow = "hidden", x.appendChild(c)), a = t(d, e), c.fake ? (c.parentNode.removeChild(c), x.style.overflow = f, x.offsetHeight) : d.parentNode.removeChild(d), !!a
	}

	function m(n, r) {
		var o = n.length;
		if ("CSS" in e && "supports" in e.CSS) {
			for (; o--;)if (e.CSS.supports(d(n[o]), r))return !0;
			return !1
		}
		if ("CSSSupportsRule" in e) {
			for (var s = []; o--;)s.push("(" + d(n[o]) + ":" + r + ")");
			return s = s.join(" or "), c("@supports (" + s + ") { #modernizr { position: absolute; } }", function (e) {
				return "absolute" == getComputedStyle(e, null).position
			})
		}
		return t
	}

	function h(e, n, o, s) {
		function f() {
			d && (delete z.style, delete z.modElem)
		}

		if (s = r(s, "undefined") ? !1 : s, !r(o, "undefined")) {
			var u = m(e, o);
			if (!r(u, "undefined"))return u
		}
		for (var d, p, c, h, v, y = ["modernizr", "tspan"]; !z.style;)d = !0, z.modElem = i(y.shift()), z.style = z.modElem.style;
		for (c = e.length, p = 0; c > p; p++)if (h = e[p], v = z.style[h], a(h, "-") && (h = l(h)), z.style[h] !== t) {
			if (s || r(o, "undefined"))return f(), "pfx" == n ? h : !0;
			try {
				z.style[h] = o
			} catch (g) {
			}
			if (z.style[h] != v)return f(), "pfx" == n ? h : !0
		}
		return f(), !1
	}

	function v(e, n, t, o, s) {
		var i = e.charAt(0).toUpperCase() + e.slice(1), a = (e + " " + E.join(i + " ") + i).split(" ");
		return r(n, "string") || r(n, "undefined") ? h(a, n, o, s) : (a = (e + " " + b.join(i + " ") + i).split(" "), u(a, n, t))
	}

	function y(e, n, r) {
		return v(e, t, t, n, r)
	}

	var g        = [], C = [], w = {
		_version       : "3.2.0", _config: {classPrefix: "", enableClasses: !0, enableJSClass: !0, usePrefixes: !0}, _q: [], on: function (e, n) {
			var t = this;
			setTimeout(function () {
				n(t[e])
			}, 0)
		}, addTest     : function (e, n, t) {
			C.push({name: e, fn: n, options: t})
		}, addAsyncTest: function (e) {
			C.push({name: null, fn: e})
		}
	}, Modernizr = function () {
	};
	Modernizr.prototype = w, Modernizr = new Modernizr;
	var x            = n.documentElement, _ = "svg" === x.nodeName.toLowerCase(), S = "Moz O ms Webkit", b = w._config.usePrefixes ? S.toLowerCase().split(" ") : [];
	w._domPrefixes   = b;
	var E            = w._config.usePrefixes ? S.split(" ") : [];
	w._cssomPrefixes = E;
	var P            = {elem: i("modernizr")};
	Modernizr._q.push(function () {
		delete P.elem
	});
	var z = {style: P.elem.style};
	Modernizr._q.unshift(function () {
		delete z.style
	}), w.testAllProps = v, w.testAllProps = y, Modernizr.addTest("flexbox", y("flexBasis", "1px", !0)), o(), s(g), delete w.addTest, delete w.addAsyncTest;
	for (var N = 0; N < Modernizr._q.length; N++)Modernizr._q[N]();
	e.Modernizr = Modernizr
}(window, document);

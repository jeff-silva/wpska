/*! head.core - v1.0.2 */
(function(n,t){"use strict";function r(n){a[a.length]=n}function k(n){var t=new RegExp(" ?\\b"+n+"\\b");c.className=c.className.replace(t,"")}function p(n,t){for(var i=0,r=n.length;i<r;i++)t.call(n,n[i],i)}function tt(){var t,e,f,o;c.className=c.className.replace(/ (w-|eq-|gt-|gte-|lt-|lte-|portrait|no-portrait|landscape|no-landscape)\d+/g,"");t=n.innerWidth||c.clientWidth;e=n.outerWidth||n.screen.width;u.screen.innerWidth=t;u.screen.outerWidth=e;r("w-"+t);p(i.screens,function(n){t>n?(i.screensCss.gt&&r("gt-"+n),i.screensCss.gte&&r("gte-"+n)):t<n?(i.screensCss.lt&&r("lt-"+n),i.screensCss.lte&&r("lte-"+n)):t===n&&(i.screensCss.lte&&r("lte-"+n),i.screensCss.eq&&r("e-q"+n),i.screensCss.gte&&r("gte-"+n))});f=n.innerHeight||c.clientHeight;o=n.outerHeight||n.screen.height;u.screen.innerHeight=f;u.screen.outerHeight=o;u.feature("portrait",f>t);u.feature("landscape",f<t)}function it(){n.clearTimeout(b);b=n.setTimeout(tt,50)}var y=n.document,rt=n.navigator,ut=n.location,c=y.documentElement,a=[],i={screens:[240,320,480,640,768,800,1024,1280,1440,1680,1920],screensCss:{gt:!0,gte:!1,lt:!0,lte:!1,eq:!1},browsers:[{ie:{min:6,max:11}}],browserCss:{gt:!0,gte:!1,lt:!0,lte:!1,eq:!0},html5:!0,page:"-page",section:"-section",head:"head"},v,u,s,w,o,h,l,d,f,g,nt,e,b;if(n.head_conf)for(v in n.head_conf)n.head_conf[v]!==t&&(i[v]=n.head_conf[v]);u=n[i.head]=function(){u.ready.apply(null,arguments)};u.feature=function(n,t,i){return n?(Object.prototype.toString.call(t)==="[object Function]"&&(t=t.call()),r((t?"":"no-")+n),u[n]=!!t,i||(k("no-"+n),k(n),u.feature()),u):(c.className+=" "+a.join(" "),a=[],u)};u.feature("js",!0);s=rt.userAgent.toLowerCase();w=/mobile|android|kindle|silk|midp|phone|(windows .+arm|touch)/.test(s);u.feature("mobile",w,!0);u.feature("desktop",!w,!0);s=/(chrome|firefox)[ \/]([\w.]+)/.exec(s)||/(iphone|ipad|ipod)(?:.*version)?[ \/]([\w.]+)/.exec(s)||/(android)(?:.*version)?[ \/]([\w.]+)/.exec(s)||/(webkit|opera)(?:.*version)?[ \/]([\w.]+)/.exec(s)||/(msie) ([\w.]+)/.exec(s)||/(trident).+rv:(\w.)+/.exec(s)||[];o=s[1];h=parseFloat(s[2]);switch(o){case"msie":case"trident":o="ie";h=y.documentMode||h;break;case"firefox":o="ff";break;case"ipod":case"ipad":case"iphone":o="ios";break;case"webkit":o="safari"}for(u.browser={name:o,version:h},u.browser[o]=!0,l=0,d=i.browsers.length;l<d;l++)for(f in i.browsers[l])if(o===f)for(r(f),g=i.browsers[l][f].min,nt=i.browsers[l][f].max,e=g;e<=nt;e++)h>e?(i.browserCss.gt&&r("gt-"+f+e),i.browserCss.gte&&r("gte-"+f+e)):h<e?(i.browserCss.lt&&r("lt-"+f+e),i.browserCss.lte&&r("lte-"+f+e)):h===e&&(i.browserCss.lte&&r("lte-"+f+e),i.browserCss.eq&&r("eq-"+f+e),i.browserCss.gte&&r("gte-"+f+e));else r("no-"+f);r(o);r(o+parseInt(h,10));i.html5&&o==="ie"&&h<9&&p("abbr|article|aside|audio|canvas|details|figcaption|figure|footer|header|hgroup|main|mark|meter|nav|output|progress|section|summary|time|video".split("|"),function(n){y.createElement(n)});p(ut.pathname.split("/"),function(n,u){if(this.length>2&&this[u+1]!==t)u&&r(this.slice(u,u+1).join("-").toLowerCase()+i.section);else{var f=n||"index",e=f.indexOf(".");e>0&&(f=f.substring(0,e));c.id=f.toLowerCase()+i.page;u||r("root"+i.section)}});u.screen={height:n.screen.height,width:n.screen.width};tt();b=0;n.addEventListener?n.addEventListener("resize",it,!1):n.attachEvent("onresize",it)})(window);
/*! head.css3 - v1.0.0 */
(function(n,t){"use strict";function a(n){for(var r in n)if(i[n[r]]!==t)return!0;return!1}function r(n){var t=n.charAt(0).toUpperCase()+n.substr(1),i=(n+" "+c.join(t+" ")+t).split(" ");return!!a(i)}var h=n.document,o=h.createElement("i"),i=o.style,s=" -o- -moz- -ms- -webkit- -khtml- ".split(" "),c="Webkit Moz O ms Khtml".split(" "),l=n.head_conf&&n.head_conf.head||"head",u=n[l],f={gradient:function(){var n="background-image:";return i.cssText=(n+s.join("gradient(linear,left top,right bottom,from(#9f9),to(#fff));"+n)+s.join("linear-gradient(left top,#eee,#fff);"+n)).slice(0,-n.length),!!i.backgroundImage},rgba:function(){return i.cssText="background-color:rgba(0,0,0,0.5)",!!i.backgroundColor},opacity:function(){return o.style.opacity===""},textshadow:function(){return i.textShadow===""},multiplebgs:function(){i.cssText="background:url(https://),url(https://),red url(https://)";var n=(i.background||"").match(/url/g);return Object.prototype.toString.call(n)==="[object Array]"&&n.length===3},boxshadow:function(){return r("boxShadow")},borderimage:function(){return r("borderImage")},borderradius:function(){return r("borderRadius")},cssreflections:function(){return r("boxReflect")},csstransforms:function(){return r("transform")},csstransitions:function(){return r("transition")},touch:function(){return"ontouchstart"in n},retina:function(){return n.devicePixelRatio>1},fontface:function(){var t=u.browser.name,n=u.browser.version;switch(t){case"ie":return n>=9;case"chrome":return n>=13;case"ff":return n>=6;case"ios":return n>=5;case"android":return!1;case"webkit":return n>=5.1;case"opera":return n>=10;default:return!1}}};for(var e in f)f[e]&&u.feature(e,f[e].call(),!0);u.feature()})(window);
/*! head.load - v1.0.3 */
(function(n,t){"use strict";function w(){}function u(n,t){if(n){typeof n=="object"&&(n=[].slice.call(n));for(var i=0,r=n.length;i<r;i++)t.call(n,n[i],i)}}function it(n,i){var r=Object.prototype.toString.call(i).slice(8,-1);return i!==t&&i!==null&&r===n}function s(n){return it("Function",n)}function a(n){return it("Array",n)}function et(n){var i=n.split("/"),t=i[i.length-1],r=t.indexOf("?");return r!==-1?t.substring(0,r):t}function f(n){(n=n||w,n._done)||(n(),n._done=1)}function ot(n,t,r,u){var f=typeof n=="object"?n:{test:n,success:!t?!1:a(t)?t:[t],failure:!r?!1:a(r)?r:[r],callback:u||w},e=!!f.test;return e&&!!f.success?(f.success.push(f.callback),i.load.apply(null,f.success)):e||!f.failure?u():(f.failure.push(f.callback),i.load.apply(null,f.failure)),i}function v(n){var t={},i,r;if(typeof n=="object")for(i in n)!n[i]||(t={name:i,url:n[i]});else t={name:et(n),url:n};return(r=c[t.name],r&&r.url===t.url)?r:(c[t.name]=t,t)}function y(n){n=n||c;for(var t in n)if(n.hasOwnProperty(t)&&n[t].state!==l)return!1;return!0}function st(n){n.state=ft;u(n.onpreload,function(n){n.call()})}function ht(n){n.state===t&&(n.state=nt,n.onpreload=[],rt({url:n.url,type:"cache"},function(){st(n)}))}function ct(){var n=arguments,t=n[n.length-1],r=[].slice.call(n,1),f=r[0];return(s(t)||(t=null),a(n[0]))?(n[0].push(t),i.load.apply(null,n[0]),i):(f?(u(r,function(n){s(n)||!n||ht(v(n))}),b(v(n[0]),s(f)?f:function(){i.load.apply(null,r)})):b(v(n[0])),i)}function lt(){var n=arguments,t=n[n.length-1],r={};return(s(t)||(t=null),a(n[0]))?(n[0].push(t),i.load.apply(null,n[0]),i):(u(n,function(n){n!==t&&(n=v(n),r[n.name]=n)}),u(n,function(n){n!==t&&(n=v(n),b(n,function(){y(r)&&f(t)}))}),i)}function b(n,t){if(t=t||w,n.state===l){t();return}if(n.state===tt){i.ready(n.name,t);return}if(n.state===nt){n.onpreload.push(function(){b(n,t)});return}n.state=tt;rt(n,function(){n.state=l;t();u(h[n.name],function(n){f(n)});o&&y()&&u(h.ALL,function(n){f(n)})})}function at(n){n=n||"";var t=n.split("?")[0].split(".");return t[t.length-1].toLowerCase()}function rt(t,i){function e(t){t=t||n.event;u.onload=u.onreadystatechange=u.onerror=null;i()}function o(f){f=f||n.event;(f.type==="load"||/loaded|complete/.test(u.readyState)&&(!r.documentMode||r.documentMode<9))&&(n.clearTimeout(t.errorTimeout),n.clearTimeout(t.cssTimeout),u.onload=u.onreadystatechange=u.onerror=null,i())}function s(){if(t.state!==l&&t.cssRetries<=20){for(var i=0,f=r.styleSheets.length;i<f;i++)if(r.styleSheets[i].href===u.href){o({type:"load"});return}t.cssRetries++;t.cssTimeout=n.setTimeout(s,250)}}var u,h,f;i=i||w;h=at(t.url);h==="css"?(u=r.createElement("link"),u.type="text/"+(t.type||"css"),u.rel="stylesheet",u.href=t.url,t.cssRetries=0,t.cssTimeout=n.setTimeout(s,500)):(u=r.createElement("script"),u.type="text/"+(t.type||"javascript"),u.src=t.url);u.onload=u.onreadystatechange=o;u.onerror=e;u.async=!1;u.defer=!1;t.errorTimeout=n.setTimeout(function(){e({type:"timeout"})},7e3);f=r.head||r.getElementsByTagName("head")[0];f.insertBefore(u,f.lastChild)}function vt(){for(var t,u=r.getElementsByTagName("script"),n=0,f=u.length;n<f;n++)if(t=u[n].getAttribute("data-headjs-load"),!!t){i.load(t);return}}function yt(n,t){var v,p,e;return n===r?(o?f(t):d.push(t),i):(s(n)&&(t=n,n="ALL"),a(n))?(v={},u(n,function(n){v[n]=c[n];i.ready(n,function(){y(v)&&f(t)})}),i):typeof n!="string"||!s(t)?i:(p=c[n],p&&p.state===l||n==="ALL"&&y()&&o)?(f(t),i):(e=h[n],e?e.push(t):e=h[n]=[t],i)}function e(){if(!r.body){n.clearTimeout(i.readyTimeout);i.readyTimeout=n.setTimeout(e,50);return}o||(o=!0,vt(),u(d,function(n){f(n)}))}function k(){r.addEventListener?(r.removeEventListener("DOMContentLoaded",k,!1),e()):r.readyState==="complete"&&(r.detachEvent("onreadystatechange",k),e())}var r=n.document,d=[],h={},c={},ut="async"in r.createElement("script")||"MozAppearance"in r.documentElement.style||n.opera,o,g=n.head_conf&&n.head_conf.head||"head",i=n[g]=n[g]||function(){i.ready.apply(null,arguments)},nt=1,ft=2,tt=3,l=4,p;if(r.readyState==="complete")e();else if(r.addEventListener)r.addEventListener("DOMContentLoaded",k,!1),n.addEventListener("load",e,!1);else{r.attachEvent("onreadystatechange",k);n.attachEvent("onload",e);p=!1;try{p=!n.frameElement&&r.documentElement}catch(wt){}p&&p.doScroll&&function pt(){if(!o){try{p.doScroll("left")}catch(t){n.clearTimeout(i.readyTimeout);i.readyTimeout=n.setTimeout(pt,50);return}e()}}()}i.load=i.js=ut?lt:ct;i.test=ot;i.ready=yt;i.ready(r,function(){y()&&u(h.ALL,function(n){f(n)});i.feature&&i.feature("domloaded",!0)})})(window);


window.wpska = window.wpska||(function() {

	var scripts = document.getElementsByTagName('script');
	var index = scripts.length - 1;
	var __dir = (scripts[index].src||"").replace('wpska.js', '');

	this.tests = [
		{test:(window.jQuery||false), files:['https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js']},
		{test:((window.jQuery||false) && (window.jQuery.fn||false) && (window.jQuery.fn.modal||false)), files:['https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css']},
		{test:(window.Vue||false), files:['https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.16/vue.min.js', 'https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.6.0/Sortable.min.js', 'https://cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/15.0.0/vuedraggable.min.js']},
		{test:false, files:['https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', (__dir+'/wpska.css')]},
	];

	this.files = [];
	for(var i in this.tests) {
		if (!this.tests[i].test) {
			for(var ii in this.tests[i].files) {
				this.files.push(this.tests[i].files[ii]);
			}
		}
	}

	// Init all
	this.init = function() {
	
		// jQuery basic plugins
		(function($) {
			$.fn.wpskaParams = function(attr, defs) {
				var params = $(this).attr(attr)||"";
				if (typeof window[params]=="function") { params = window[params](); }
				else if (typeof params != "object") {try { eval('params='+params); } catch(e) { params={}; }}
				defs = (typeof defs=="object")? defs: {};
				for(var i in defs) { if (typeof params[i]=="undefined") params[i]=defs[i]; }
				return params;
			};

			$.fn.wpskaLoad = function(call, files) {
				if (this.length>0) {
					call = typeof call=="function"? call: function() {};
					head.load(files, call);
				}
			};
		})(jQuery);


		/* Vue2 methods helper */
		window.Vue2 = function(opts) {
			opts = (typeof opts=="object")? opts: {};
			opts.methods = opts.methods||{};

			opts.methods._id = function() {
				var d = new Date();
				return [d.getYear(), d.getMonth(), d.getDate(), d.getHours(), d.getMinutes(), d.getSeconds(), d.getMilliseconds()].join('');
			};

			opts.methods._default = function(item, defs) {
				item = (typeof item=="object")? item: {};
				defs = (typeof defs=="object")? defs: {};
				item._id = item._id||this._id();
				for(var i in defs) {if (typeof item[i]=="undefined") item[i]=defs[i]; }
				for(var i in item) { item[i] = item[i].replace(/\{id\}/, item._id); }
				return item;
			};

			opts.methods._add = function(parent, keyname, item, params) {
				params = (typeof params=="object")? params: {};
				params.unique = (typeof params.unique=="undefined")? false: params.unique;
				params.prepend = (typeof params.prepend=="undefined")? false: params.prepend;

				item = (typeof item=="object")? item: {};
				item._id = this._id();

				parent = parent||this;
				parent[keyname] = (typeof parent[keyname]=="object")? parent[keyname]: [];

				if (params.unique) {
					for(var i in parent[keyname]) {
						if (parent[keyname][i]._id==item._id) {
							return false;
						}
					}
				}

				params.prepend? parent[keyname].unshift(item): parent[keyname].push(item);
				Vue.set(parent, keyname, parent[keyname]);
			};

			opts.methods._append = function(parent, keyname, item, params) {
				params = (typeof params=="object")? params: {};
				params.prepend = false;
				this._add(parent, keyname, item, params);
			};

			opts.methods._prepend = function(parent, keyname, item, params) {
				params = (typeof params=="object")? params: {};
				params.prepend = true;
				this._add(parent, keyname, item, params);
			};

			opts.methods._remove = function(items, item, confirmStr) {
				if ((confirmStr||false) && !confirm(confirmStr)) return false;
				var index = items.indexOf(item);
				return items.splice(index, 1);
			};

			opts.methods._exists = function(parent, item) {
				parent = parent||this;
				for(var i in parent) {
					if (parent[i]._id==item._id) {
						return true;
					}
				}
				return false;
			};

			opts.methods._toggle = function(parent, keyname, item, params) {
				parent = parent||this;
				this._exists((parent[keyname]||[]), item)?
				this._remove(parent, keyname, item):
				this._add(parent, keyname, item, params);
			};

			return new Vue(opts);
		}


		// data-slick
		$("[data-slick]").wpskaLoad(function() {
			$("[data-slick]").each(function() {
				var params = $(this).wpskaParams("data-slick", {});
				$(this).slick(params);
			});
		}, [
			"https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js",
			"https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css",
		]);


		// data-flatpickr
		$("[data-flatpickr]").wpskaLoad(function() {

			var Portuguese = {
				weekdays: {
					shorthand: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"],
					longhand: ["Domingo", "Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "Sábado",],
				},
				months: {
					shorthand: ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"],
					longhand: ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"],
				},
				rangeSeparator: " até ",
			};


			$("[data-flatpickr]").each(function() {
				var params = $(this).wpskaParams("data-flatpickr", {
					dateFormat: 'Y-m-d H:i:S',
					altInput: true,
					altFormat: 'd/m/Y - H:i',
					altInputClass: 'form-control',
					enableTime: true,
					time_24hr: true,
					locale: Portuguese,
				});
				var pickr = flatpickr(this, params);
				// $(this).flatpickr(params);
				this.flatpickr = function() { return pickr; };
			});
		}, [
			"https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.4.3/flatpickr.min.css",
			"https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.4.3/flatpickr.min.js",
		]);


		// .wpska-select
		$(".wpska-select").each(function() {
			var $parent = $(this);
			var $input = $parent.find("input[type=text]");
			var $list = $parent.find(".list-group");
			var $listItems = $list.find(">.list-group-item");
			var $checks = $parent.find("input[type=checkbox], input[type=radio]");

			$list.hide();

			$input.on("focus", function() {
				$listItems.show();
				$list.slideDown(200);
			});

			$input.on("keyup", function(ev) {
				var search = this.value.toLowerCase();
				if (search) {
					$listItems.hide().each(function() {
						var text = $(this).text().toLowerCase();
						if (text.indexOf(search) >= 0) $(this).show();
					});
				}
				else {
					$listItems.show();
				}
			});

			$checks.on("change", function() {
				var values = [];
				$checks.each(function() {
					if (this.checked && this.value) {
						var value = this.placeholder || $(this).parent().find(">div").text().trim();
						values.push(value);
					}
				});
				$input.val(values.join(', '));
			});

			// $checks.on("click", function(ev) {
			// 	if (this.type=="radio") {
			// 		this.checked = !!this.checked;
			// 	}
			// });

			$checks.trigger("change");
		});

		$(document).click(function(ev) {
			var $not = $(ev.target).closest(".wpska-select").find("> .list-group");
			$(".wpska-select > .list-group").not($not).slideUp(200);
		});



		// loading
		$(".wpska-loading").not("body").fadeOut(200);
		$(window).on('beforeunload', function() {
			$("body").addClass("wpska-loading");
			$(".wpska-loading").not("body").fadeIn(200);
		});

		// Bootsratch load
		var bootswatch = $("body").attr("data-bootswatch")||"";
		if (bootswatch) head.load('https://cdnjs.cloudflare.com/ajax/libs/bootswatch/3.3.7/'+bootswatch+'/bootstrap.min.css');

		// Automatic init
		for(var i in window) {
			if (i.substring(0, 9)=="wpskaInit") {
				window[i].call(this);
			}
		}
	};

	head.load(this.files, this.init);

	// $("[data-vue]").wpskaLoad(["/assets/load-vue.js"]);
	// $("[data-slick]").wpskaLoad(["/assets/load-slick.js"]);
	// $("[data-codemirror]").wpskaLoad(["/assets/load-codemirror.js"]);
	// $("[data-flatpickr]").wpskaLoad(["/assets/load-flatpickr.js"]);
	// $("[data-firebase]").wpskaLoad(["/assets/load-firebase.js"]);
	// $("[data-mask]").wpskaLoad(["/assets/load-mask.js"]);
	// $("wpska-test").wpskaLoad(["/assets/vuel.js", "/components/wpska-test/index.php"]);

	return this;
})();

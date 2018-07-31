function Vuel(tagname, paramsDefault) {
	var currentScript = document.currentScript? document.currentScript.ownerDocument: false;
	var _template = currentScript? (currentScript.getElementById(tagname)? currentScript.getElementById(tagname).innerHTML: ""): "";
	_template = _template || (document.getElementById(tagname)? document.getElementById(tagname).innerHTML: "");
	_template = '<div>'+_template+'<div style="display:none;"><textarea :name="name" class="vuel-value"></textarea><pre style="background:#b6b9f9;">{{ $data }}</pre></div></div>';

	var proto = Object.create(HTMLElement.prototype);
	proto.createdCallback = function() {};
	proto.attachedCallback = function() {
		var wrapper=this, $=jQuery, dataset={};
		$(wrapper).data("vuel-content", this.innerHTML);

		// content
		// _template = _template.replace(/\<content\>\<\/content\>/g, this.innerHTML);
		this.innerHTML = _template;

		
		var app = {el:wrapper.children[0]};
		app.data = function() {
			var appData = JSON.parse(JSON.stringify((paramsDefault.data||{})));
			appData.name = appData.name||"";
			appData.value = appData.value||"";
			appData.hook = appData.hook||"";
			for(var i in appData) {
				var attr = wrapper.getAttribute(i)||"";
				if (attr) {
					if (i!="name" && i!="hook") {
						try { eval('attr='+attr); } catch(e) {};
					}
					appData[i] = attr;
				}
			}

			return appData;
		};


		app.methods = (typeof paramsDefault.methods=="object")? paramsDefault.methods: {};

		app.watch = {
			value: {
				handler: function() {
					var vm=this;
					// $(vm.$el).parents().each(function() {
					// 	if (this.__vue__) {
					// 		var vm = this.__vue__;
					// 		console.log(vm._data);
					// 		return false;
					// 	}
					// });

					eval(this.hook);
					var stringify = JSON.stringify(this.value);
					$(wrapper).attr("value", stringify);
					$(wrapper).find(".vuel-value").val(stringify);
					$(wrapper).find(".vuel-value").trigger("change");
					$(wrapper).trigger("change");
				},
				deep: true,
			},
		};

		app.mounted = (typeof paramsDefault.mounted=="function")? paramsDefault.mounted: function() {};
		var vm = new Vue(app);
		$(wrapper).data("vue", vm);

		// Default value
		$(wrapper).find(".vuel-value").val( $(wrapper).attr("value") );

		// methods
		proto.vuel = function() {
			var vuel = {};

			vuel.value = function(value) {
				return this.value;
			};

			vuel.vue = function() {
				return $(wrapper).data("vue");
			};

			vuel.content = function() {
				return $(wrapper).data("vuel-content");
			};

			return vuel;
		};


		// method: vuelValue
		proto.vuelValue = function(value) {
			var $=jQuery;

			value = value||null;
			if (value) {
				try { eval('value='+value); } catch(e) {}
				Vue.set(wrapper.dataset.vm, "value", value);
				return value;
			}

			value = $(wrapper.dataset.vm.$el).find(".vuel-value").val();
			try { eval('value='+value); } catch(e) {}
			return value;
		};

		for(var i in app.methods) {
			proto[i] = app.methods[i];
		}
	};

	return document.registerElement(tagname, {prototype: proto});
};
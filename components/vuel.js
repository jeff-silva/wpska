function Vuel(tagname, paramsDefault) {
	var currentScript = document.currentScript? document.currentScript.ownerDocument: false;
	var _template = currentScript? (currentScript.getElementById(tagname)? currentScript.getElementById(tagname).innerHTML: ""): "";
	_template = _template || (document.getElementById(tagname)? document.getElementById(tagname).innerHTML: "");
	_template = '<div>'+_template+'<div style="display:none;"><textarea :name="name" class="vuel-value">{{ value }}</textarea><pre style="background:#b6b9f9;">{{ $data }}</pre></div></div>';

	var proto = Object.create(HTMLElement.prototype);
	proto.createdCallback = function() {};
	proto.attachedCallback = function() {
		var wrapper=this, $=jQuery, dataset={};
		dataset['vuel-content'] = this.innerHTML;

		// content
		// _template = _template.replace(/\<content\>\<\/content\>/g, this.innerHTML);
		this.innerHTML = _template;

		dataset['vuel-vm'] = {el:this.children[0]};

		dataset['vuel-vm']['data'] = function() {
			var $data = paramsDefault.data||{};

			if (typeof paramsDefault.data=="function") {
				console.log(this);
				$data = paramsDefault.data.call(this);
			}

			if (typeof $data != "object") {
				try { eval('$data='+$data); } catch(e) {}
			}

			$data.name = $data.name||"";
			$data.value = $data.value||"";

			for(var i in $data) {
				if (i=="name" || i=="value") continue;
				var attr = wrapper.getAttribute(i);
				if (attr) {
					if (i!="name") {
						if ("{\\"==attr.substring(0, 2)) { attr = attr.replace(/\\/g, ""); }
						try { eval('attr='+attr); } catch(e) {};
					}
					$data[i] = attr;
				}
			}

			return $data;
		};


		dataset['vuel-vm']['methods'] = (typeof paramsDefault.methods=="object")? paramsDefault.methods: {};

		// Se merge==true, mistura valores em vez de setar
		dataset['vuel-vm']['methods']['_value'] = function(value, merge) {
			var $ = jQuery;

			// if this==htmlElement
			if (this instanceof HTMLElement) {
				var vm = $(this).data("vuel-vm");
				var wrapper = this;
			}
			else {
				var vm = this;
				var wrapper = this.$el;
			}


			if (typeof value != "undefined") {

				if (typeof value=="string") {
					if ("{\\"==value.substring(0, 2)) { value = value.replace(/\\/g, ""); }
					try { eval('value='+value); } catch(e) {};
					if (typeof value=="object" && merge==true) {
						for(var i in vm.value) { value[i] = vm.value[i]; }
					}
				}

				var strValue = (typeof value=="object"? JSON.stringify(value): value);
				$(wrapper.parentElement).attr("value", strValue);
				$(wrapper.parentElement).find(".wpska-value").val(strValue);
				Vue.set(vm, "value", value);
			}

			return vm.value;
		};

		// Attach methods to this and this.vue wrapper
		for(var i in dataset['vuel-vm']['methods']) {
			this[i] = dataset['vuel-vm']['methods'][i];
			this.children[0][i] = dataset['vuel-vm']['methods'][i];
		}

		/* dataset['vuel-vm']['watch'] = {
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

					var stringify = JSON.stringify(this.value);
					$(wrapper).attr("value", stringify);
					$(wrapper).find(".vuel-value").val(stringify);
					$(wrapper).find(".vuel-value").trigger("change");
					$(wrapper).trigger("change");
				},
				deep: true,
			},
		}; */

		var mounted = (typeof paramsDefault.mounted=="function")? paramsDefault.mounted: function() {};
		dataset['vuel-vm']['mounted'] = function() {
			var $ = jQuery;
			var vm = this;
			var wrapper = vm.$el.parentElement;
			var value = $(wrapper).attr("value");
			vm._value(value);
			mounted.call(this);
		};
		dataset['vuel-vm'] = new Vue(dataset['vuel-vm']);

		// Default value
		// $(wrapper).find(".vuel-value").val( $(wrapper).attr("value") );

		for(var i in dataset) {
			$(this).data(i, dataset[i]);
			$(this.children[0]).data(i, dataset[i]);
		}
	};

	return document.registerElement(tagname, {prototype: proto});
};

function _vm(e) {
	return (e instanceof Element)? $(e).data("vuel-vm"): e;
}
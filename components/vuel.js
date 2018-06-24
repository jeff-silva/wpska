function Vuel(tagname, paramsDefault) {
	var currentScript = document.currentScript? document.currentScript.ownerDocument: false;
	var _template = currentScript? (currentScript.getElementById(tagname)? currentScript.getElementById(tagname).innerHTML: ""): "";
	_template = _template || (document.getElementById(tagname)? document.getElementById(tagname).innerHTML: "");
	_template = '<div>'+_template+'<textarea :name="name" class="vuel-value" style="display:none;">{{ value }}</textarea></div>';

	var proto = Object.create(HTMLElement.prototype);
	proto.createdCallback = function() {};
	proto.attachedCallback = function() {
		var wrapper = this;
		_template = _template.replace(/\<content\>\<\/content\>/g, this.innerHTML);

		var Component = {
			template: _template,
			data: function() {
				var ret = JSON.parse(JSON.stringify((paramsDefault.data||{})));
				for(var i in ret) {
					var attr = wrapper.getAttribute(i)||"";
					if (attr) {
						try { eval('attr='+attr); } catch(e) {};
						ret[i] = attr;
					}
				}
				ret.name = ret.name || "";
				ret.value = ret.value || "";
				return ret;
			},
			methods: (paramsDefault.methods||{}),
			mounted: (paramsDefault.mounted||function() {}),
		};
		
		this.innerHTML = '<vue-component></vue-component>';
		var app = new Vue({
			el: this.children[0],
			components: {
				"vue-component": Component,
			},
		});

		proto.getValue = function() {
			var value = $(".vuel-value").val();
			try { eval('value='+value); } catch(e) {}
			return value;
		};
	};

	return document.registerElement(tagname, {prototype: proto});
};
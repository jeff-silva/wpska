function Vuel(tagname, paramsDefault) {
	var currentScript = document.currentScript? document.currentScript.ownerDocument: false;
	var _template = currentScript? (currentScript.getElementById(tagname)? currentScript.getElementById(tagname).innerHTML: ""): "";
	_template = _template || (document.getElementById(tagname)? document.getElementById(tagname).innerHTML: "");
	_template = '<div>'+_template+'<textarea :name="name" class="vuel-value" style="display:none;" @change="_componentChange();">{{ value }}</textarea></div>';

	var proto = Object.create(HTMLElement.prototype);
	proto.createdCallback = function() {};
	proto.attachedCallback = function() {
		var wrapper = this;
		_template = _template.replace(/\<content\>\<\/content\>/g, this.innerHTML);

		var Component = {template:_template};
		Component.data = function() {
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
		};

		Component.methods = (typeof paramsDefault.methods=="object")? paramsDefault.methods: {};
		Component.methods._componentChange = function() {
			alert('Aaa');
			console.log(this.$el);
		};
		Component.mounted = (typeof paramsDefault.mounted=="object")? paramsDefault.mounted: function() {};
		
		this.innerHTML = '<vue-component></vue-component>';
		var app = new Vue({
			el: this.children[0],
			components: {"vue-component": Component},
		});

		proto.vuelValue = function(value) {
			value = value||null;
			if (value) {
				try { eval('value='+value); } catch(e) {}
				app.value = value;
				return value;
			}

			value = $(".vuel-value").val();
			try { eval('value='+value); } catch(e) {}
			return value;
		};
	};

	return document.registerElement(tagname, {prototype: proto});
};
var _loadVue = function() {
	$("[data-vue]").each(function() {
		
		var datavue = $(this).wpskaParams("data-vue", {
			data: {},
			methods: {},
		});

		datavue.el = this;
		datavue.data = datavue.data||{};
		datavue.data.loading = false;
		datavue.data.data = {};

		datavue.methods = datavue.methods||{};

		datavue.methods._sync = function() {
			this.loading = true;
			ref.set(this.data);
		};

		datavue.methods._id = function() {
			var d = new Date();
			return [
				d.getYear(),
				d.getMonth(),
				d.getDate(),
				d.getHours(),
				d.getMinutes(),
				d.getSeconds(),
				d.getMilliseconds()
			].join('');
		};

		datavue.methods._default = function(item, defs) {
			item = (typeof item=="object")? item: {};
			defs = (typeof defs=="object")? defs: {};
			item._id = item._id||this._id();
			for(var i in defs) {if (typeof item[i]=="undefined") item[i]=defs[i]; }
			return item;
		};

		datavue.methods._add = function(parent, keyname, item) {
			parent = parent||this;
			item = (typeof item=="object")? item: {};
			item._id = this._id();
			var items = (typeof parent[keyname]=="object")? parent[keyname]: [];
			items.push(item);
			Vue.set(parent, keyname, items);
		};

		datavue.methods._remove = function(parent, keyname, item, confirmStr) {
			parent = parent||this;
			if ((confirmStr||false) && !confirm(confirmStr)) return false;
			var items = parent[keyname]||[];
			var index = items.indexOf(item);
			items.splice(index, 1);
			Vue.set(parent, keyname, items);
		};

		datavue.methods._array = function(parent, keyname) {
			if (typeof parent[keyname]!="object") return [];
			return parent[keyname];
		};
		
		new Vue(datavue);
	});
};

$.wpskaLoad([
	"https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.2/vue.min.js",
	"https://cdn.jsdelivr.net/npm/sortablejs@1.7.0/Sortable.min.js",
	"https://cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.16.0/vuedraggable.min.js",
], _loadVue);

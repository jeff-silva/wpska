var files = [
	"https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.2/vue.min.js",
	"https://cdn.jsdelivr.net/npm/sortablejs@1.7.0/Sortable.min.js",
	"https://cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.16.0/vuedraggable.min.js",
];

head.load(files, function() {
	$("[data-vue]").each(function() {
		var $this = $(this);

		var params = $(this).params("data-vue", {
			el: this,
			data: {},
			methods: {},
		});

		// _default
		params.methods._default = function(item, def) {
			item = (typeof item=="object")? item: {};
			def = (typeof def=="object")? def: {};
			if (typeof item._id=="undefined") {
				var d = new Date();
				item._id = [
					d.getYear(),
					d.getMonth(),
					d.getDate(),
					d.getHours(),
					d.getMinutes(),
					d.getSeconds(),
					d.getMilliseconds()
				].join('');
			}
			for(var i in def) {
				if (typeof item[i]=="undefined") {
					item[i] = def[i];
				}
			}
			for(var i in item) {
				item[i] = item[i].replace('{$_id}', item._id);
			}
			return item;
		};

		// _add
		params.methods._add = function(items, item, prepend) {
			(prepend||false)? items.unshift(item): items.push(item);
		};

		// _remove
		params.methods._remove = function(items, item, prepend) {
			params = (typeof params=="object")? params: {};
			params.confirm = params.confirm||false;
			params.closest = params.closest||false;
			params.ev = params.ev||false;

			var _remove = function() {
				setTimeout(function() {
					var index = items.indexOf(item);
					items.splice(index, 1);
				}, 150);
			};

			if (params.confirm) {
				if (! confirm(params.confirm)) {
					return null;
				}
			}

			// if (params.closest && params.ev) {
			// 	$(params.ev.target).closest(params.closest).addClass("v-transition").attr("v-transition");
			// }

			_remove();
		};

		if (typeof params.init=="function") {
			params = params.init.call(this, params);
		}
		
		this.datavue = new Vue(params);
		console.log(this.datavue, this);
	});
});

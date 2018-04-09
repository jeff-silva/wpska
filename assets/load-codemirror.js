$.wpskaLoad([
	"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/codemirror.min.css",
	"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/codemirror.min.js",
	"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/theme/ambiance.min.css",
	"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/mode/xml/xml.min.js",
	"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/mode/javascript/javascript.min.js",
	"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/mode/css/css.min.js",
	"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/mode/htmlmixed/htmlmixed.min.js",
	"https://cdn.jsdelivr.net/npm/emmet-codemirror@1.2.5/dist/emmet.js",
], function() {
	$("[data-codemirror]").each(function() {
		var target = this;
		var opts = $(this).wpskaParams("data-codemirror", {
			lineNumbers: true,
			selectionPointer: true,
			htmlMode: true,
			mode: "text/html",
			theme: "ambiance",
			tabMode: "indent",
		});

		var editor = CodeMirror.fromTextArea(target, opts);
		emmetCodeMirror(editor, {
			'Ctrl-E': 'emmet.expand_abbreviation_with_tab',
			// 'Cmd-Alt-B': 'emmet.balance_outward',
		});
		target.codemirror = function() { return editor; };
	});
});
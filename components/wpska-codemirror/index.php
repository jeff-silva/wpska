<?php include __DIR__ . '/../../wpska.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/theme/ambiance.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/emmet-codemirror@1.2.5/dist/emmet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.16/vue.min.js"></script>
<script src="../vuel.js"></script>

<template id="wpska-codemirror">
	<textarea :name="name" class="wpska-codemirror-textarea"></textarea>
</template>

<script>
Vuel("wpska-codemirror", {
	data: {
		settings: {
			lineNumbers: true,
			selectionPointer: true,
			htmlMode: true,
			mode: "text/html",
			theme: "ambiance",
			tabMode: "indent",
		},
	},
	methods: {
		// 
	},
	mounted: function() {
		var app=this, $=jQuery;

		$(app.$el).find("textarea.wpska-codemirror-textarea").each(function() {
			$(this).val( $(app.$el.parentElement).data("vuel-content") );
			
			var editor = CodeMirror.fromTextArea(this, app.settings);
			emmetCodeMirror(editor, {
				'Ctrl-E': 'emmet.expand_abbreviation_with_tab',
				// 'Cmd-Alt-B': 'emmet.balance_outward',
			});
		});
	},
});
</script>

<!--

$("[data-codemirror]").wpskaLoad(function() {
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
}, [
	"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/codemirror.min.css",
	"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/codemirror.min.js",
	"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/theme/ambiance.min.css",
	"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/mode/xml/xml.min.js",
	"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/mode/javascript/javascript.min.js",
	"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/mode/css/css.min.js",
	"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/mode/htmlmixed/htmlmixed.min.js",
	"https://cdn.jsdelivr.net/npm/emmet-codemirror@1.2.5/dist/emmet.js",
]);

-->
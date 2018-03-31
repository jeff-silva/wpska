$("[data-codemirror]").each(function() {
	var opts = $(this).attr("data-codemirror")||"{}";
	try { eval('opts='+opts); } catch(e) { opts={}; }
	opts.lineNumbers = (typeof opts.lineNumbers=="undefined")? true: opts.lineNumbers;
	opts.mode = opts.mode||"htmlmixed";
	opts.theme = opts.theme||"ambiance";

	var files = [
		"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/codemirror.min.css",
		"https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/codemirror.min.js",
		("https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/theme/"+opts.theme+".min.css"),
		("https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.36.0/mode/"+opts.mode+"/"+opts.mode+".min.js"),
	];

	var target = this;
	head.load(files, function() {
		var editor = CodeMirror.fromTextArea(target, opts);
	});
});
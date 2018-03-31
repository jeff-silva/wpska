function _load(files) {
	var _testloaded = "";
	var scripts = document.getElementsByTagName("script");
	for(var i in scripts) { _testloaded += (scripts[i].src||""); }
	var links = document.getElementsByTagName("link");
	for(var i in links) { _testloaded += (links[i].href||""); }
	for(var i in files) {
		var regex = new RegExp(files[i][0]);
		if (regex.test(_testloaded)) continue;
		var url = files[i][1];
		if (/\.js/.test(url)) {document.write('<scri'+'pt src="'+url+'"><\/scri'+'pt>');}
		else if (/\.css/.test(url)) {document.write('<link rel="stylesheet" href="'+url+'" />');}
	}
}

_load([
	["jquery.min.js", "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"],
	["bootstrap.min.js", "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"],
	["bootstrap.min.css", "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css"],
	["font-awesome.min.css", "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"],
	["vue.min.js", "https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.16/vue.min.js"],
	["wpska.css", "https://wpska.herokuapp.com/wpska.css"],
]);

function _load(files) {this.files = files;this.testLoad = function(url) {for(var i in this.files) {var freg = new RegExp(this.files[i][0]);var furl = new RegExp(this.files[i][1]);var ftest = freg.test(url);if (ftest) { this.files.splice(i, 1); }}};var scripts = document.getElementsByTagName("script");for(var i in scripts) { var url = scripts[i].src||false; this.testLoad(url); }var links = document.getElementsByTagName("link");for(var i in links) { var url = links[i].href||false; this.testLoad(url); }for(var i in this.files) {var url = this.files[i][1];if (/\.js/.test(url)) {var script = document.createElement("script");script.src = url;document.head.appendChild(script);}else if (/\.css/.test(url)) {var link = document.createElement("link");script.rel = "stylesheet";script.href = url;document.head.appendChild(link);}}}
_load([
	["jquery.min.js", "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"],
	["bootstrap.min.js", "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"],
	["bootstrap.min.css", "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css"],
	["font-awesome.min.css", "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"],
	["vue.min.js", "https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.16/vue.min.js"],
]);

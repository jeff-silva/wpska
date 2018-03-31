var files = [
	"https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js",
	"https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css",
	"https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css",
];

head.load(files, function() {
	$("[data-slick]").each(function() {
		var slick = $(this).attr("data-slick")||"{}";
		try { eval('slick='+slick); } catch(e) { slick={}; }
		$(this).slick(slick);
	});
});
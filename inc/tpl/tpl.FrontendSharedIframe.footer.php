<?php
echo Ajax::wrapJS('
function resizeMap() {
	var $m = $(".map"), w = $(window).innerHeight(), d = $("body").outerHeight();
	$m.height(w - d + $m.height() - 20);
}

$(document).ready(function(){
	$(window).resize(function() { resizeMap(); });
	resizeMap();
});
');
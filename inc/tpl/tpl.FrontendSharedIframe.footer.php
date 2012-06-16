<div id="copy2" class="bottom">
	<a class="tab singleTab b" href="http://www.runalyze.de/" title="Runalyze" target="_blank">&copy; Runalyze v<?php echo RUNALYZE_VERSION; ?></a>
</div>

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
?>
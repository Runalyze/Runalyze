$("input[name=distance], input[name=s]").change(function() {
	jUpdatePace();
	jUpdateKmh();
<?php if (CONF_COMPUTE_KCAL): ?>
	jUpdateKcal();
<?php endif; ?>
});
$("#sportid").change(function(){
	jUpdateSportValues();
<?php if (CONF_COMPUTE_KCAL): ?>
	jUpdateKcal();
<?php endif; ?>
});

$("input[name=distance], input[name=s]").change(function() {
	jUpdatePace();
	jUpdateKmh();
<?php if (CONF_COMPUTE_KCAL): ?>
	jUpdateKcal();
<?php endif; ?>
});

jUpdateSportValues();
jUpdatePace();

if ($("input[name=kcal]").val() == 0)
	jUpdateKcal();
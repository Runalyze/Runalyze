$("#sportid").change(function(){
	jUpdateSportValues();
<?php if (Configuration::ActivityForm()->computeCalories()): ?>
	jUpdateKcal();
<?php endif; ?>
});

$("input[name=distance], input[name=s]").change(function() {
	jUpdatePace();
	jUpdateKmh();
<?php if (Configuration::ActivityForm()->computeCalories()): ?>
	jUpdateKcal();
<?php endif; ?>
});

jUpdateSportValues();
jUpdatePace();

if ($("input[name=kcal]").val() == 0)
	jUpdateKcal();
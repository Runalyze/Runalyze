 $("#sportid").change(function(){
    jUpdateSportValues();
<?php if (\Runalyze\Configuration::ActivityForm()->computeCalories()): ?>
    jUpdateKcal();
<?php endif; ?>
});

$("#time_day").change(function(){
    jUpdateAvailableEquipment();
    jUpdateWeather();
});

$("input[name=distance], input[name=s]").change(function() {
    jUpdatePace();
    jUpdateKmh();
<?php if (\Runalyze\Configuration::ActivityForm()->computeCalories()): ?>
    jUpdateKcal();
<?php endif; ?>
});

$(function(){
    jUpdateAvailableEquipment();
    jUpdateSportValues();
    jUpdatePace();

    if ($("input[name=kcal]").val() == 0)
        jUpdateKcal();
});

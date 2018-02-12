$(function(){
    Runalyze.ActivityForm.init($('#training'), {
        calculateCalories: <?php echo (\Runalyze\Configuration::ActivityForm()->computeCalories()) ? 'true' : 'false'; ?>,
        loadWeather: <?php echo ($this->dataObject->Weather()->isEmpty() && \Runalyze\Configuration::ActivityForm()->loadWeather()) ? 'true' : 'false'; ?>
    });
});

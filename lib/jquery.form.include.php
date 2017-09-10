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

    var $e = $("#fieldset-weather");

    $e.find(".weatherdata-button-load").click(function() {
        $e.find(".weatherdata-loading-text").removeClass("hide");
        $e.find(".weatherdata-none-text").addClass("hide");

        var args = [];

        var date = parseDate($("#time_day").val(), 'dd.mm.yyyy');
        args.push("date=" + date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate() + "T" + ($("#time_daytime").val() == "" ? "00:00" : $("#time_daytime").val()));

        var lat = $("#training input[name=arr_lat]").val(),
            lng = $("#training input[name=arr_lon]").val();

        if (lat.length && lng.length) {
            args.push("latlng=" + lat.split("|").find(function(v){return v != 0.0;}) + "," + lng.split("|").find(function(v){return v != 0.0;}));
        }

        $.get("_internal/service/weather?"+args.join("&"), function(data) {
            if (!data.empty) {
                $e.find("#weatherid option[value="+data.weatherid+"]").attr("selected", true);
                $e.find("#temperature").val(Math.round(data.temperature));
                $e.find("#wind_speed").val(Math.round(data.wind_speed));
                $e.find("#wind_deg").val(data.wind_deg);
                $e.find("#humidity").val(data.humidity);
                $e.find("#pressure").val(data.pressure);
                $e.find(".weatherdata-source").html("via "+data.source.name);
                $("#weather_source").val(data.source.id);

                $e.find(".weatherdata-none-text, .weatherdata-loading-text").addClass("hide");
                $e.find(".w50, .weatherdata-source").removeClass("hide");
            } else {
                $e.find(".weatherdata-none-text").removeClass("hide");
                $e.find(".w50, .weatherdata-loading-text, .weatherdata-source").addClass("hide");
            }
        }).fail(function(){
            $e.find(".weatherdata-none-text").removeClass("hide");
            $e.find(".w50, .weatherdata-loading-text, .weatherdata-source").addClass("hide");
        });
    });
    $e.find(".weatherdata-button-edit").click(function() {
        $e.find(".w50, .weatherdata-source, .weatherdata-button-remove").removeClass("hide");
        $e.find(".weatherdata-none-text, .weatherdata-button-edit").addClass("hide");

    });
    $e.find(".weatherdata-button-remove").click(function() {
        $e.find(".w50, .weatherdata-source, .weatherdata-button-remove").addClass("hide");
        $e.find(".weatherdata-none-text, .weatherdata-button-edit").removeClass("hide");

        $("#weatherid option[value=1]").attr("selected", true);
        $e.find("input").val("");
        $e.find(".weatherdata-source").html("");
        $("#weather_source").val("");
    });

 <?php if ($this->dataObject->Weather()->isEmpty()): ?>
     <?php if ($this->submitMode == StandardFormular::$SUBMIT_MODE_CREATE && \Runalyze\Configuration::ActivityForm()->loadWeather()): ?>
         $e.find(".weatherdata-button-load").trigger('click');
     <?php else: ?>
         $e.find(".weatherdata-button-remove").trigger('click');
     <?php endif; ?>
 <?php endif; ?>
});

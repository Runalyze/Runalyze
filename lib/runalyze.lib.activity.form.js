Runalyze.ActivityForm = (function($, Common){

	// Public

	var self = {};

	// Private

	var form = null;
    var splits = null;
    var weather = null;
    var defaultSplit = '';
    var caloriesPerHour = 0;
    var activityId = NaN;

    var options = {
        calculateCalories: true,
        canLoadWeather: true,
        loadWeather: true
    };

	// Private Methods

    function isNewActivity() {
        return isNaN(activityId);
    }

    function isWeatherEmpty() {
        var isConditionUnknown = '1' === weather.find('#weatherid').val();
        var inputsAreEmpty = weather.find('input').length === weather.find('input').filter(function(){return $.trim($(this).val()).length === 0;}).length;

        return isConditionUnknown && inputsAreEmpty;
    }

    function initialUpdates() {
        updateAvailableEquipment();
        updateVisibilitiesForSport(form.find('#sportid').find(':selected:first'));
        updatePace();

        if (!(parseInt(form.find('input[name=kcal]').val()) > 0)) {
            updateCalories();
        }

        if (isWeatherEmpty()) {
            if (isNewActivity() && options.canLoadWeather && options.loadWeather) {
                tryToLoadWeatherData();
            } else {
                hideAndUnsetWeatherInputs();
            }
        }
    }

    function initHandlers() {
        initChangeSportHandler();
        initChangeDateHandler();
        initChangeDistanceOrDurationHandler();
        initSplitsHandlers();
        initWeatherHandlers();
    }

    function initChangeSportHandler() {
        form.find('#sportid').change(function() {
            updateVisibilitiesForSport($(this).find(':selected:first'));

            if (options.calculateCalories) {
                updateCalories();
            }
        });
    }

    function initChangeDateHandler() {
        form.find('#time_day').change(function(){
            updateAvailableEquipment();

            if (options.loadWeather && form.find('input[name=weather_source]').val().length > 0) {
                tryToLoadWeatherData(false);
            }
        });
    }

    function initChangeDistanceOrDurationHandler() {
        form.find('input[name=distance], input[name=s]').change(function() {
            updatePace();

            if (options.calculateCalories) {
                updateCalories();
            }
        });
    }

    function initSplitsHandlers() {
        splits.find('.add-split').click(function(){
            splits.find('ol.splits').append(defaultSplit);
        });

        splits.find('.round-splits').click(function(){
            splits.find('input[name="splits[km][]"]').each(function(e){
                $(this).val((Math.round(10 * $(this).val()) / 10).toFixed(2));
            });
        });

        splits.find('.sum-splits').click(function(){
            var dist = 0, time = 0;

            splits.find('input[name="splits[km][]"]').each(function(e){
                dist += stringToDistance($(this).val());
            });
            splits.find('input[name="splits[time][]"]').each(function(e){
                time += Common.stringToSeconds($(this).val());
            });

            form.find('#s').val(Common.secondsToString(time));
            form.find('#distance').val(dist.toFixed(2));
        });

        splits.find(".active-splits").click(function(){
            splits.find('select[name="splits[active][]"]').val('1');
        });

        splits.find(".rest-splits").click(function(){
            splits.find('select[name="splits[active][]"]').val('0');
        });

        var evenSplits = function(val) {
            splits.find('select[name="splits[active][]"]:even').val(val);
        };

        var oddSplits = function(val) {
            splits.find('select[name="splits[active][]"]:odd').val(val);
        };

        splits.find(".alternate-splits-rest").click(function(){
            evenSplits(0);
            oddSplits(1);
        });

        splits.find(".alternate-splits-active").click(function(){
            evenSplits(1);
            oddSplits(0);
        });
    }

    function initWeatherHandlers() {
        weather.find('.weatherdata-button-load').click(function(){
            tryToLoadWeatherData();
        });

        weather.find('.weatherdata-button-edit').click(function() {
            weather.find('.w50, .weatherdata-source, .weatherdata-button-remove').removeClass('hide');
            weather.find('.weatherdata-none-text, .weatherdata-button-edit').addClass('hide');
        });

        weather.find('.weatherdata-button-remove').click(function(){
            hideAndUnsetWeatherInputs();
        });
    }

    function updateVisibilitiesForSport(elem) {
        var sportId = elem.val(),
            elemType = form.find('#typeid'),
            kcal = elem.attr('data-kcal'),
            run = elem.attr('data-running'),
            out = elem.attr('data-outside'),
            dis = elem.attr('data-distances'),
            pow = elem.attr('data-power');

        if (kcal > 0)
            caloriesPerHour = kcal;

        form.find('.only-running').toggle(typeof run !== 'undefined' && run !== false);
        form.find('.only-not-running').toggle(typeof run === 'undefined' || run === false);
        form.find('.only-outside').toggle(typeof out !== 'undefined' && out !== false);
        form.find('.only-distances').toggle(typeof dis !== 'undefined' && dis !== false);
        form.find('.only-power').toggle(typeof pow !== 'undefined' && pow !== false);

        elemType.find('option:not([data-sport=all])').attr('disabled', true).hide();
        elemType.find('option[data-sport=' + sportId + ']').attr('disabled', false).show();
        $('.only-specific-sports:not(.only-sport-' + sportId + ')').attr('disabled', true).hide();
        $('.only-specific-sports.only-sport-' + sportId).attr('disabled', false).show();

        if (elemType.find('option:selected').attr('disabled')) {
            elemType.find('option:selected').attr('selected', false);
            elemType.find('option[data-sport=all]').attr('selected', false);
        }

        elemType.parent().toggle(elemType.find('option[value!=0]:not(:disabled)').length > 0);

        updateDefaultType(elemType, elem.attr('data-default-typeid'));
    }

    function updateDefaultType(elemType, defaultId) {
        if (isNaN(form.find('input[name=id]').val())) {
            elemType.find('option:selected').attr('selected', false);
            elemType.find('option[value=' + defaultId + ']').prop('selected', true);
        }
    }

    function updateAvailableEquipment() {
        var date = Common.parseDate(form.find('#time_day').val(), 'dd.mm.yyyy').getTime();

        form.find('form .depends-on-date option, form .depends-on-date input').each(function(){
            var available = date == 0 || (
                    !($(this).data('start') && Common.parseDate($(this).data('start')) > date) &&
                    !($(this).data('end') && Common.parseDate($(this).data('end')) < date)
                );

            $(this).attr('disabled', !available).toggle(available);
            $(this).attr('hidden', !available).toggle(available);

            if (!$(this).is('option')) {
                $(this).parent().toggle(available);
            }

            if (!available) {
                if ($(this).is('option')) {
                    $(this).prop('selected', false);
                } else {
                    $(this).prop('checked', false);
                }
            }
        });
    }

    function updateCalories() {
        form.find('input[name=kcal]').val(Math.round(Number(caloriesPerHour) * getTimeInHours()));
    }

    function updatePace() {
        var d = getDistance(),
            s = getTimeInSeconds(),
            elem = form.find('input[name=pace]');

        if (0 == d || 0 == s) {
            elem.val('-:--');
        } else {
            var pace = s / 60 / d,
                min  = Math.floor(pace),
                sec  = Math.round((pace - min) * 60);

            if (60 == sec) {
                sec = 0;
                min += 1;
            }

            elem.val(min + ':' + (!(sec > 9) ? '0' : '') + sec);
        }
    }

    function updateWeatherDataFrom(data) {
        if (data && !data.empty) {
            var locationDetails = [];

            if (data.location) {
                if (data.location.name) {
                    locationDetails.push(data.location.name);
                } else if (data.location.lat && data.location.lng) {
                    locationDetails.push(Math.abs(data.location.lat).toFixed(2) + ' °' + (data.location.lat >= 0 ? 'N' : 'S') + ', ' + Math.abs(data.location.lng).toFixed(2) + ' °' + (data.location.lng >= 0 ? 'W' : 'E'));
                }

                if (data.location.date && !isNaN(Date.parse(data.location.date))) {
                    locationDetails.push((new Date(data.location.date)).toLocaleString());
                }
            }

            weather.find('#weatherid').val('' + data.weatherid);
            weather.find('#temperature').val(Math.round(data.temperature));
            weather.find('#wind_speed').val(Math.round(data.wind_speed));
            weather.find('#wind_deg').val(data.wind_deg);
            weather.find('#humidity').val(data.humidity);
            weather.find('#pressure').val(data.pressure);
            weather.find('.weatherdata-source').html('via ' + data.source.name + (locationDetails.length > 0 ? ' (' + locationDetails.join(', ') + ')' : ''));
            form.find('input[name=weather_source]').val(data.source.id);
        }
    }

    function tryToLoadWeatherData(hideIfRequestFails) {
        hideIfRequestFails = hideIfRequestFails === "undefined" ? true : hideIfRequestFails;

        weather.find('.weatherdata-loading-text').removeClass('hide');
        weather.find('.weatherdata-none-text').addClass('hide');

        var args = [];

        var date = Common.parseDate($("#time_day").val(), 'dd.mm.yyyy');
        args.push('date=' + date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate() + 'T' + (form.find('#time_daytime').val() == '' ? '00:00' : form.find('#time_daytime').val()));

        var lat = form.find('input[name=arr_lat]').val(),
            lng = form.find('input[name=arr_lon]').val(),
            startCoords = form.find('input[name=start-coordinates]').val();

        if (lat && lat.length && lng && lng.length) {
            args.push('latlng=' + lat.split('|').find(function(v){return v != 0.0;}) + ',' + lng.split('|').find(function(v){return v != 0.0;}));
        } else if (startCoords && startCoords.length) {
            args.push('latlng=' + startCoords);
        }

        $.get('_internal/service/weather?'+args.join('&'), function(data) {
            if (!data.empty) {
                updateWeatherDataFrom(data);

                weather.find('.weatherdata-none-text, .weatherdata-button-edit, .weatherdata-loading-text').addClass('hide');
                weather.find('.w50, .weatherdata-source, .weatherdata-button-remove').removeClass('hide');
            } else {
                weather.find('.weatherdata-loading-text').addClass('hide');

                if (hideIfRequestFails) {
                    weather.find('.weatherdata-none-text, .weatherdata-button-edit').removeClass('hide');
                    weather.find('.w50, .weatherdata-source, .weatherdata-button-remove').addClass('hide');
                }
            }
        }).fail(function(){
            weather.find('.weatherdata-loading-text').addClass('hide');

            if (hideIfRequestFails) {
                weather.find('.weatherdata-none-text, .weatherdata-button-edit').removeClass('hide');
                weather.find('.w50, .weatherdata-source, .weatherdata-button-remove').addClass('hide');
            }
        });
    }

    function hideAndUnsetWeatherInputs() {
        weather.find('.w50, .weatherdata-source, .weatherdata-button-remove').addClass('hide');
        weather.find('.weatherdata-none-text, .weatherdata-button-edit').removeClass('hide');

        weather.find('#weatherid').val('1');
        weather.find('input').val('');
        weather.find(".weatherdata-source").html('');
        form.find('input[name=weather_source]').val('');
    }

    function getDistance() {
        return stringToDistance(form.find('input[name=distance]').val());
    }

    function getTimeInHours() {
        return (getTimeInSeconds() / 3600);
    }

    function getTimeInSeconds() {
        return Common.stringToSeconds(form.find('input[name=s]').val());
    }

    function stringToDistance(string) {
        return Number(string.replace(',', '.')) * form.find('input[name="distance-to-km-factor"]').val();
    }

	// Public Methods

    self.setOptions = function(opt) {
        options = $.extend({}, options, opt);
    };

	self.init = function(elem, opt) {
	    form = elem;
        splits = elem.find('#formularSplitsContainer');
        weather = elem.find('#fieldset-weather');
        defaultSplit = splits.find('#defaultInputSplit').val();
        activityId = parseInt(form.find('input[name=id]').val());

        self.setOptions(opt);

        options.canLoadWeather = weather.find('.weatherdata-button-load').length > 0;

        initHandlers();
        initialUpdates();
	};

	return self;
})(jQuery, Runalyze.Common);

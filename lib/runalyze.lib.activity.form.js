Runalyze.ActivityForm = (function($, Common, Formatter){

	// Public

	var self = {};

	// Private

	var form = null;
    var splits = null;
    var weather = null;
    var equipment = null;
    var defaultSplit = '';
    var caloriesPerHour = 0;
    var currentPaceUnit = 0;

    var options = {
        baseId: 'activity',
        isNew: true,
        calculateCalories: true,
        canLoadWeather: true,
        loadWeather: true
    };

	// Private Methods
    function isNewActivity() {
        return options.isNew;
    }

    function isWeatherEmpty() {
        var isConditionUnknown = '1' === findId('weatherid', weather).val();
        var inputsAreEmpty = weather.find('input').length === weather.find('input').filter(function(){return $.trim($(this).val()).length === 0;}).length;

        return isConditionUnknown && inputsAreEmpty;
    }

    function find(selector, baseElement) {
        return (baseElement || form).find(selector);
    }

    function findInput(name, baseElement) {
        return find('input[name="' + options.baseId + '[' + name + ']"]', baseElement);
    }

    function findId(name, baseElement) {
        return $('#' + options.baseId + '_' + name);
    }

    function initialUpdates() {
        var emptyType = findId('type').find('option[value=""]');
        emptyType.text('---- ' + emptyType.text());

        initLayoutForEquipment();
        updateAvailableEquipmentWithRespectToDate();
        updateVisibilitiesForSport(findId('sport').find(':selected:first'));

        updatePace();

        if (!(parseInt(findInput('kcal').val()) > 0)) {
            updateCalories();
        }

        if (isWeatherEmpty()) {
            if (isNewActivity() && options.canLoadWeather && options.loadWeather) {
                tryToLoadWeatherData();
            } else {
                hideAndUnsetWeatherInputs();
            }
        } else {
            var sources = ['', 'openweathermap.org', '', 'Powered by Dark Sky'];
            var source = sources[findId('weatherSource').val()];

            if (source) {
                weather.find('.weatherdata-source').html('via ' + source);
            }
        }

        hideSelectedDataSeriesAfterSubmit();
    }

    function initHandlers() {
        initChangeSportHandler();
        initChangeDateHandler();
        initChangeDistanceOrDurationHandler();
        initSplitsHandlers();
        initSingleSplitHandlers();
        initWeatherHandlers();
    }

    function initChangeSportHandler() {
        findId('sport').change(function() {
            updateVisibilitiesForSport($(this).find(':selected:first'));

            if (options.calculateCalories) {
                updateCalories();
            }

            updatePace();
        });
    }

    function initChangeDateHandler() {
        findId('time_date').change(function(){
            updateAvailableEquipmentWithRespectToDate();

            if (options.loadWeather && findInput('weatherSource').val().length > 0) {
                tryToLoadWeatherData(false);
            }
        });
    }

    function initChangeDistanceOrDurationHandler() {
        form.find('#' + options.baseId + '_distance, #' + options.baseId + '_s').change(function() {
            updatePace();

            if (options.calculateCalories) {
                updateCalories();
            }
        });
    }

    function initSplitsHandlers() {
        splits.find('.add-split').click(function(){
            splits.find('.splits').append(defaultSplit);
            var newSplit = splits.find('.splits li:last');
            newSplit.find('.split-distance input').val('1.00');
            newSplit.find('.split-duration input').val('6:00');
            newSplit.find('.split-intensity select').val('1');

            reIndexSplits();
            initSingleSplitHandlers();
        });

        splits.find('.round-splits').click(function(){
            splits.find('.splits .split-distance input').each(function(e){
                $(this).val((Math.round(10 * $(this).val()) / 10).toFixed(2));
            });
        });

        splits.find('.sum-splits').click(function(){
            var dist = 0, time = 0;

            splits.find('.splits .split-distance input').each(function(e){
                dist += stringToDistance($(this).val());
            });
            splits.find('.splits .split-duration input').each(function(e){
                time += Common.stringToSeconds($(this).val());
            });

            findId('s').val(Common.secondsToString(time));
            findId('distance').val(dist.toFixed(2));
        });

        splits.find(".active-splits").click(function(){
            splits.find('.splits .split-intensity select').val('1');
        });

        splits.find(".rest-splits").click(function(){
            splits.find('.splits .split-intensity select').val('0');
        });

        var evenSplits = function(val) {
            splits.find('.splits .split-intensity:even select').val(val);
        };

        var oddSplits = function(val) {
            splits.find('.splits .split-intensity:odd select').val(val);
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

    function initSingleSplitHandlers() {
        splits.find(".split-copy").unbind('click').click(function(){
            var $p = $(this).parent();
            $p.clone().insertAfter($p);

            reIndexSplits();
            initSingleSplitHandlers();
        });

        splits.find(".split-remove").unbind('click').click(function(){
            $(this).parent().remove();

            reIndexSplits();
            initSingleSplitHandlers();
        });
    }

    function reIndexSplits() {
        var nameByIndex = function(field, index) {
            return options.baseId + '[splits][' + index + '][' + field + ']';
        };
        var idByIndex = function(field, index) {
            return nameByIndex(field, index).replace(/\[/g, '_').replace(/\]/g, '');
        };

        var index = 0;

        splits.find("li").each(function(){
            $(this).find('.split-distance input').attr('id', idByIndex('distance', index)).attr('name', nameByIndex('distance', index));
            $(this).find('.split-distance label').attr('for', idByIndex('distance', index));
            $(this).find('.split-duration input').attr('id', idByIndex('duration', index)).attr('name', nameByIndex('duration', index));
            $(this).find('.split-intensity select').attr('id', idByIndex('isActive', index)).attr('name', nameByIndex('isActive', index));

            ++index;
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
            elemType = findId('type'),
            kcal = parseInt(elem.data('energy')) || false,
            internal = parseInt(elem.data('internal')) || false,
            equipmentTypes = elem.data('equipment-types');

        if (kcal > 0)
            caloriesPerHour = kcal;

        currentPaceUnit = elem.data('speed');

        form.find('.only-running').toggle(internal === 1);
        form.find('.only-not-running').toggle(internal !== 1);
        form.find('.only-outside').toggle(elem.data('outside') === 1);
        form.find('.only-distances').toggle(elem.data('distances') === 1);

        elemType.find('option:not([value=""])').attr('disabled', true).hide();
        elemType.find('option[data-sport=' + sportId + ']').attr('disabled', false).show();
        $('.only-specific-sports:not(.only-sport-' + sportId + ')').attr('disabled', true).hide();
        $('.only-specific-sports.only-sport-' + sportId).attr('disabled', false).show();

        if (elemType.find('option:selected').attr('disabled')) {
            elemType.find('option:selected').attr('selected', false);
            elemType.find('option[data-sport=all]').attr('selected', false);
        }

        elemType.parent().toggle(elemType.find('option[value!=0]:not(:disabled)').length > 0);

        updateDefaultType(elemType, elem.attr('data-activity-type'));

        if (equipmentTypes.length) {
            equipment.find('#' + options.baseId + '_equipment > div').hide();

            equipmentTypes.forEach(function (id) {
                equipment.find('#' + options.baseId + '_equipment_' + id).parent().show();
            });

            equipment.show();
        } else {
            equipment.hide();
        }
    }

    function updateDefaultType(elemType, defaultId) {
        if (isNaN(findId('id').val())) {
            elemType.find('option:selected').attr('selected', false);
            elemType.find('option[value="' + defaultId + '"]').prop('selected', true);
        }
    }

    function initLayoutForEquipment() {
        equipment.find('.depends-on-date > label').hide();
        equipment.find('.w100').addClass('with50erLabel');
        equipment.find('select').addClass('full-size');
        equipment.find('.w100 > div').each(function() {
            $(this).addClass('full-size left');
            var id = $(this).attr('id');
            $(this).find('label').each(function(i) {
                $(this).addClass(id + '-' + i);
            });
            $(this).find('input').each(function(i) {
                $(this).addClass(id + '-' + i);
                equipment.find('.' + id + '-' + i).wrapAll('<div class="inline small w25" />');
            });
        });
    }

    function updateAvailableEquipmentWithRespectToDate() {
        var date = Common.parseDate(findId('time_date').val(), 'yyyy-mm-dd').getTime();

        form.find('.depends-on-date option, .depends-on-date input').each(function(){
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
        findId('kcal').val(Math.round(Number(caloriesPerHour) * getTimeInHours()));
    }

    function updatePace() {
        var d = getDistance(),
            s = getTimeInSeconds(),
            elem = findId('pace');

        var paceObject = Formatter.getPaceObject(d > 0.0 ? s / d : 0.0, currentPaceUnit);

        elem.val(paceObject.string);
        elem.next().text(paceObject.shortAppendix);
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

            findId('weatherid', weather).val('' + data.weatherid);
            findId('temperature', weather).val(Math.round(data.temperature));
            findId('wind_speed', weather).val(Math.round(data.wind_speed));
            findId('wind_deg', weather).val(data.wind_deg);
            findId('humidity', weather).val(data.humidity);
            findId('pressure', weather).val(data.pressure);
            weather.find('.weatherdata-source').html('via ' + data.source.name + (locationDetails.length > 0 ? ' (' + locationDetails.join(', ') + ')' : ''));
            findId('weatherSource', weather).val(data.source.id);
        }
    }

    function tryToLoadWeatherData(hideIfRequestFails) {
        hideIfRequestFails = hideIfRequestFails || true;

        weather.find('.weatherdata-loading-text').removeClass('hide');
        weather.find('.weatherdata-none-text').addClass('hide');

        var args = [];

        var date = Common.parseDate(findId('time_date').val(), 'yyyy-mm-dd');
        args.push('date=' + date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate() + 'T' + (findId('time_time').val() == '' ? '00:00' : findId('time_time').val()));

        var startCoords = findId('start-coordinates').val();

        if (startCoords && startCoords.length) {
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

        findId('weatherid', weather).val('1');
        weather.find('input').val('');
        weather.find(".weatherdata-source").html('');
    }

    function hideSelectedDataSeriesAfterSubmit() {
        $("#fieldset-remove-data-series").find("input:checked").each(function(){
            $(this).parent().hide();
        });
    }

    function getDistance() {
        return stringToDistance(findId('distance').val());
    }

    function getTimeInHours() {
        return (getTimeInSeconds() / 3600);
    }

    function getTimeInSeconds() {
        return Common.stringToSeconds(findId('s').val());
    }

    function stringToDistance(string) {
        return Number(string.replace(',', '.')) * form.find('input[name=distance-to-km-factor]').val();
    }

	// Public Methods

    self.setOptions = function(opt) {
        options = $.extend({}, options, opt);
    };

	self.init = function(elem, opt) {
	    form = elem;
        splits = elem.find('#fieldset-splits');
        weather = elem.find('#fieldset-weather');
        equipment = elem.find('#fieldset-equipment');
        defaultSplit = '<li>' + splits.find('.split-prototype').html() + '</li>';
        splits.find('.split-prototype').remove();

        self.setOptions(opt);

        options.canLoadWeather = weather.find('.weatherdata-button-load').length > 0;

        initHandlers();
        initialUpdates();
	};

	return self;
})(jQuery, Runalyze.Common, Runalyze.Formatter);

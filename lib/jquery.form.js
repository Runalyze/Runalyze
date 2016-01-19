var kcalPerHour = 0;
var splits = $("#formularSplitsContainer");
var defaultSplit = $("#defaultInputSplit").val();

function parseDate(input, format) {
	format = format || 'yyyy-mm-dd';
	var parts = input.match(/(\d+)/g), i = 0, fmt = {};

	format.replace(/(yyyy|dd|mm)/g, function(part) { fmt[part] = i++; });

	return new Date(parts[fmt['yyyy']], parts[fmt['mm']]-1, parts[fmt['dd']]);
}

function jUpdateSportValues() {
	var $s = $("#sportid :selected"),
		kcal = $s.attr('data-kcal'),
		run = $s.attr('data-running'),
		out = $s.attr('data-outside'),
		dis = $s.attr('data-distances'),
		pow = $s.attr('data-power');

	if (kcal > 0)
		kcalPerHour = kcal;

	$("form .only-running").toggle( typeof run !== "undefined" && run !== false );
	$("form .only-not-running").toggle( typeof run === "undefined" || run === false );
	$("form .only-outside").toggle( typeof out !== "undefined" && out !== false );
	$("form .only-distances").toggle( typeof dis !== "undefined" && dis !== false );
	$("form .only-power").toggle( typeof pow !== "undefined" && pow !== false );

	$("#typeid option:not([data-sport='all'])").attr('disabled', true).hide();
	$("#typeid option[data-sport='"+$s.val()+"']").attr('disabled', false).show();
	$(".only-specific-sports:not(.only-sport-"+$s.val()+")").attr('disabled', true).hide();
	$(".only-specific-sports.only-sport-"+$s.val()).attr('disabled', false).show();

	if ($("#typeid option:selected").attr('disabled')) {
		$("#typeid option:selected").attr('selected', false);
		$("#typeid option[data-sport='all']").attr('selected', true);
	}

	if ($("#typeid option[value!=0]:not(:disabled)").length) {
		$("#typeid").parent().show();
	} else {
		$("#typeid").parent().hide();
	}
}

function jUpdateAvailableEquipment() {
	var date = parseDate($("#time_day").val(), 'dd.mm.yyyy').getTime();

	$("form .depends-on-date option, form .depends-on-date input").each(function(){
		var available = date == 0 || (
			!($(this).data('start') && parseDate($(this).data('start')) > date) &&
			!($(this).data('end') && parseDate($(this).data('end')) < date)
		);

		$(this).attr('disabled', !available).toggle(available);

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

function jUpdatePace() {
	var d = getDistance(),
		s = getTimeInSeconds();

	if (d == 0 || s == 0) {
		$("input[name=pace]").val("-:--");
		return;
	}

	var pace = s / 60 / d,
		min  = Math.floor(pace),
		sec  = Math.round( (pace - min) * 60);

	if (sec == 60) {
		sec = 0;
		min += 1;
	}

	if (!(sec > 9))
		$("input[name=pace]").val(min + ":0" + sec);
	else
		$("input[name=pace]").val(min + ":" + sec);
}

function jUpdateKmh() {
	if (getTimeInHours() == 0)
		return;

	var kmh  = getDistance() / getTimeInHours(),
		full = Math.floor(kmh),
		dec  = Math.round( (kmh - full) * 100 );

	if (!(dec > 9))
		$("input[name=kmh]").val(full + ",0" + dec);
	else
		$("input[name=kmh]").val(full + "," + dec);
}

function jUpdateKcal() {
	$("input[name=kcal]").val( Math.round(Number(kcalPerHour) * getTimeInHours()) );
}

function getDistance() {
	return stringToDistance($("input[name=distance]").val());
}

function getTimeInHours() {
	return (getTimeInSeconds() / 3600);
}

function getTimeInSeconds() {
	return stringToSeconds($("input[name=s]").val());
}

function stringToDistance(string) {
	return Number(string.replace(',', '.')) * $("input[name='distance-to-km-factor']").val();
}

function stringToSeconds(string) {
	var h  = 0, m = 0, s = 0, ms = 0,
		milisec = string.split(","),
		time    = milisec[0].split(":");
	s = Number(time[0])*3600 + Number(time[1])*60 + Number(time[2]);

	if (milisec.length > 1) {
		if (milisec[1].length == 1)
			ms = Number(milisec[1])/10;
		else
			ms = Number(milisec[1])/100;
	}

	if (time.length == 1)
		s = Number(time[0]);
	else if (time.length == 2) {
		m = Number(time[0]);
		s = Number(time[1]);
	} else {
		h = Number(time[0]);
		m = Number(time[1]);
		s = Number(time[2]);
	}

	return h*3600 + m*60 + s + ms/100;
}

function secondsToString(s) {
	var date = new Date(null);
	date.setSeconds(s - 60*60);

	return date.toTimeString().substr(0, 8);
}

function sumSplitsToTotal() {
	var dist = 0, time = 0;

	splits.find("input[name='splits[km][]']").each(function(e){
		dist += stringToDistance($(this).val());
	});
	splits.find("input[name='splits[time][]']").each(function(e){
		time += stringToSeconds($(this).val());
	});

	$("#s").val( secondsToString(time) );
	$("#distance").val( dist.toFixed(2) );
}

function allSplitsRest() {
	splits.find("select[name='splits[active][]']").val("0");
}

function allSplitsActive() {
	splits.find("select[name='splits[active][]']").val("1");
}

function evenSplits(theValue) {
	splits.find("select[name='splits[active][]']:even").val(theValue);
}

function oddSplits(theValue) {
	splits.find("select[name='splits[active][]']:odd").val(theValue);
}

function roundSplits() {
	splits.find("input[name='splits[km][]']").each(function(e){$(this).val((Math.round(10*$(this).val())/10).toFixed(2));});
}

function addSplit() {
	splits.find("ol.splits").append(defaultSplit);
}

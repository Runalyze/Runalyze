var kcalPerHour = 0;

function jUpdateSportValues() {
	var $s = $("#sportid :selected"),
		kcal = $s.attr('data-kcal'),
		run = $s.attr('data-running'),
		out = $s.attr('data-outside'),
		typ = $s.attr('data-types'),
		dis = $s.attr('data-distances');

	if (kcal > 0)
		kcalPerHour = kcal;
		//$("input[name=kcalPerHour]").val(kcal);

	$("form .only-running").toggle( typeof run !== "undefined" && run !== false );
	$("form .only-outside").toggle( typeof out !== "undefined" && out !== false );
	$("form .only-types").toggle( typeof typ !== "undefined" && typ !== false );
	$("form .only-distances").toggle( typeof dis !== "undefined" && dis !== false );
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
	//$("input[name=kcal]").val( Math.round(Number($("input[name=kcalPerHour]").val()) * getTimeInHours()) );
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
	return Number(string.replace(',', '.'));
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

	$("input[name='splits[km][]']").each(function(e){
		dist += stringToDistance($(this).val());
	});
	$("input[name='splits[time][]']").each(function(e){
		time += stringToSeconds($(this).val());
	});

	$("#s").val( secondsToString(time) );
	$("#distance").val( dist.toFixed(2) );
}

function allSplitsRest() {
	$("select[name='splits[active][]'] option[value='0']").attr('selected',true);
}

function allSplitsActive() {
	$("select[name='splits[active][]'] option[value='1']").attr('selected',true);
}
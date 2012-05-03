function jUpdatePace() {
	if (getDistance() == 0)
		return;

	var pace = getTimeInSeconds() / 60 / getDistance(),
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
	$("input[name=kcal]").val( Math.round(Number($("input[name=kcalPerHour]").val()) * getTimeInHours()) );
}

function getDistance() {
	return Number($("input[name=distance]").val().replace(',', '.'));
}

function getTimeInHours() {
	return (getTimeInSeconds() / 3600);
}

function getTimeInSeconds() {
	var h  = 0, m = 0, s = 0, ms = 0,
		milisec = $("input[name=s]").val().split(","),
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
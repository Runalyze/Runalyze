function show(id) {
	var div = document.getElementById(id);
	div.style.display = 'block';
}   

function unshow(id) {
	var div = document.getElementById(id);
	div.style.display = 'none';
}   

function kps(kalorien) {
	document.getElementById('kalorien_stunde').value = kalorien;
}

function kalorienberechnung() {
	var kps     = document.getElementById('kalorien_stunde').value;
	var dauer   = document.getElementById('dauer').value;
	var ms      = dauer.split(",");
	var zeiten  = ms[0].split(":");
	var stunden = Number(zeiten[0]) + Number(1/60 * zeiten[1]) + Number(1/3600 * zeiten[2]);
	document.getElementById('kalorien').value = Math.round(kps*stunden);
}

function paceberechnung() {
	var dist = document.getElementById('dist').value;
	var dauer = document.getElementById('dauer').value;
	var ms     = dauer.split(",");
	var zeiten = ms[0].split(":");
	var minuten = Number(zeiten[0] * 60) + Number(zeiten[1]) + Number(1/60 * zeiten[2]);
	if (ms.length > 1)
		minuten += Number(1/6000 * Number(ms[1]));
	var pace = minuten / dist;
	var pace_minuten = Math.floor(pace);
	var pace_sekunden = Math.round((pace - pace_minuten) * 60);
	if (pace_sekunden == 60) {
		pace_sekunden = 0;
		pace_minuten += 1;
	}
	if (!(pace_sekunden > 9)) pace_sekunden = "0" + pace_sekunden;
	document.getElementById('pace').value = pace_minuten + ":" + pace_sekunden;
}

function kmhberechnung() {
	var dist = document.getElementById('dist').value;
	var dauer = document.getElementById('dauer').value;
	var ms     = dauer.split(",");
	var zeiten = ms[0].split(":");
	var stunden = Number(zeiten[0]) + Number(1/60 * zeiten[1]) + Number(1/3600 * zeiten[2]);
	if (ms.length > 1)
		stunden += Number(1/360000 * Number(ms[1]));
	var kmh = dist / stunden;
	var kmh_ganze = Math.floor(kmh);
	var kmh_nachkomma = Math.round((kmh - kmh_ganze) * 100);
	if (!(kmh_nachkomma > 9)) kmh_nachkomma = "0" + kmh_nachkomma;
	document.getElementById('kmh').value = kmh_ganze + "," + kmh_nachkomma;
}

function check_wunschgewicht() {
	var textbox = document.config.wunschgewicht;
	if (document.config.use_wunschgewicht.checked == true)
		textbox.disabled = false;
	else
		textbox.disabled = true;
}
<?php header('Content-type: text/html; charset=ISO-8859-1'); ?>
<img
	id="close" src="img/cross.png" onClick="ajax_close()" />
<h1>Kalenderauswahl</h1>
<?php
include_once('../config/functions.php');
connect();

for ($j = $config['startjahr']; $j <= date("Y"); $j++) {
	echo('<a href="#" onClick="ajax_close(); daten(\''.mktime(0,0,0,1,1,$j).'\',\''.mktime(0,0,0,1,1,$j).'\',\''.mktime(23,59,50,12,31,$j).'\');" style="margin:0 20px;">'.$j.'</a>'.NL);

	echo('<select style="margin-right:20px;">'.NL);
	for ($m = 1; $m <= 12; $m++) {
		echo('<option onClick="ajax_close(); daten(\''.mktime(0,0,0,$m,1,$j).'\',\''.mktime(0,0,0,$m,1,$j).'\',\''.mktime(23,59,50,$m+1,0,$j).'\');">'.monat($m).'</option>'.NL);
	}
	echo('</select>'.NL);
	echo('<select style="margin-right:20px;">'.NL);
	for ($w = 1; $w <= 52; $w++) {
		$heute = mktime(0,0,0,1,1+($w-1)*7,$j);
		$ws = wochenstart($heute);
		$we = wochenende($heute);
		echo('<option onClick="ajax_close(); daten(\''.$heute.'\',\''.$ws.'\',\''.$we.'\');">'.$w.'. Woche: '.date("d.m.Y",$ws).' - '.date("d.m.Y",$we).'</option>'.NL);
	}
	echo('</select>'.NL);
	echo('<br /><br />'.NL);
}
?>
<?php
close();
?>
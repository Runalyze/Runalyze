<?php
// Standard inclusions
include("../pChart/pData.class");
include("../pChart/pChart.class");
include("../pChart/pCache.class");
include('../../config/functions.php');
connect();

// TODO Draw Scale anpassen

// Dataset
$temp = array();
$time = array();
$y = !is_numeric($_GET['y']) ? date("Y") : $_GET['y'];

if ($_GET['all'] == "all") {
	$info = 'Durchschnittstemperaturen Gesamt';
	for ($m = 0; $m <= 11; $m++)
	$time[] = monat($m+1,true);

	$y_dat_db = mysql_query('SELECT YEAR(FROM_UNIXTIME(`time`)) as `year` FROM `ltb_training` WHERE !ISNULL(`temperatur`) GROUP BY `year` ORDER BY `year` ASC');
	while ($y_dat = mysql_fetch_assoc($y_dat_db)) {
		for ($m = 0; $m <= 11; $m++)
		$temp[$y_dat['year']][] = '';

		$dat_db = mysql_query('SELECT MONTH(FROM_UNIXTIME(`time`)) as `month`, AVG(`temperatur`) as `temp` FROM `ltb_training` WHERE YEAR(FROM_UNIXTIME(`time`)) = "'.$y_dat['year'].'" AND !ISNULL(`temperatur`) GROUP BY `month` ORDER BY `month` ASC');
		while ($dat = mysql_fetch_assoc($dat_db))
		$temp[$y_dat['year']][$dat['month']-1] = $dat['temp'];
	}
}

elseif ($_GET['m'] == "m") {
	$info = 'Durchschnittstemperatur '.$y;
	for ($m = 0; $m <= 11; $m++) {
		$temp[] = '';
		$time[] = monat($m+1,true);
	}

	$dat_db = mysql_query('SELECT MONTH(FROM_UNIXTIME(`time`)) as `month`, AVG(`temperatur`) as `temp` FROM `ltb_training` WHERE YEAR(FROM_UNIXTIME(`time`)) = "'.$y.'" AND !ISNULL(`temperatur`) GROUP BY `month` ORDER BY `month` ASC');
	while ($dat = mysql_fetch_assoc($dat_db))
	$temp[$dat['month']-1] = $dat['temp'];
}

else {
	$info = 'Temperaturverlauf '.$y;
	for ($t = 1; $t <= 183; $t++) {
		$temp[] = '';
		$time[] = monat(ceil($t/16)+1,true);
	}

	$dat_db = mysql_query('SELECT ROUND(DAYOFYEAR(FROM_UNIXTIME(`time`))/2) as `day`, AVG(`temperatur`) as `temp` FROM `ltb_training` WHERE YEAR(FROM_UNIXTIME(`time`)) = "'.$y.'" AND !ISNULL(`temperatur`) GROUP BY `day` ORDER BY `day` ASC');
	while ($dat = mysql_fetch_assoc($dat_db))
	$temp[$dat['day']] = $dat['temp'];
}

close();

// Dataset definition
$DataSet = new pData;
if ($_GET['all'] == "all") {
	foreach ($temp as $year => $y_temp) {
		$DataSet->AddPoint($temp[$year],$year);
		$DataSet->AddSerie($year);
		$DataSet->SetSerieName($year,$year);
	}
}
else {
	$DataSet->AddPoint($temp,"Temperatur");
	$DataSet->AddSerie("Temperatur");
}
$DataSet->AddPoint($time,"Zeit");
$DataSet->SetAbsciseLabelSerie("Zeit");
$DataSet->SetYAxisUnit("°C");

// Cache definition
$Cache = new pCache();
$Cache->GetFromCache("Wetter-".$y."-".$_GET['m'],$DataSet->GetData());

// Initialise the graph
$Bild = new pChart(780,240);
$Bild->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);
$Bild->setFontProperties("../pChart/Fonts/tahoma.ttf",8);
$Bild->setGraphArea(60,10,770,195);
$Bild->drawGraphArea(213,217,221);
$Bild->drawGraphAreaGradient(162,183,202,50);

// Graph
$Bild->setFixedScale(-20,40,6);
$Bild->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_ADDALL,213,217,221,TRUE,0,2,FALSE,($_GET['m'] == 'm' ? 1 : 16));
$Bild->drawGrid(4,FALSE,184,201,217,200,TRUE,FALSE);
$Bild->drawTreshold(0,136,0,0,FALSE,FALSE,0);
$Bild->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());
if ($_GET['m'] == 'm')
$Bild->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),2);

// Finish the graph
if ($_GET['all'] == 'all')
$Bild->drawLegend(65,15,$DataSet->GetDataDescription(),255,255,255);
$Bild->drawTextBox(0,220,780,240,$info,0,255,255,255,ALIGN_CENTER,TRUE,0,0,0,30);
$Bild->AddBorder(1);
$Cache->WriteToCache("Wetter-".$y."-".$_GET['m'],$DataSet->GetData(),$Bild);
$Bild->Stroke();
?>
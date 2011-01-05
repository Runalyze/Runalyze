<?php
// Standard inclusions
include("../pChart/pData.class");
include("../pChart/pChart.class");
include("../pChart/pCache.class");
include('../../config/functions.php');
connect();

// Dataset
$dauer = array();
$tag = array("Mo", "Di", "Mi", "Do", "Fr", "Sa", "So");
$sport_db = mysql_query('SELECT `id`, `name` FROM `ltb_sports` ORDER BY `id` ASC');
while ($sport = mysql_fetch_assoc($sport_db)) {
	$tage_db = mysql_query('SELECT SUM(1) as `num`, SUM(`dauer`) as `trainingsdauer`, (DAYOFWEEK(FROM_UNIXTIME(`time`))-1) as `day` FROM `ltb_training` WHERE `sportid`="'.$sport['id'].'" GROUP BY `day` ORDER BY ((`day`+6)%7) ASC');
	while ($tage = mysql_fetch_assoc($tage_db))
	$dauer[$sport['name']][wochentag($tage['day'],true)] = $tage['trainingsdauer']/60/60;
}

close();

// Dataset definition
$DataSet = new pData;
$DataSet->AddPoint($tag,"Wochentag");
foreach ($dauer as $name => $array) {
	$DataSet->AddPoint($dauer[$name],$name);
	$DataSet->AddSerie($name);
}
$DataSet->SetAbsciseLabelSerie("Wochentag");

// Cache definition
$Cache = new pCache();
$Cache->GetFromCache("Trainingstage",$DataSet->GetData());

// Initialise the graph
$Bild = new pChart(368,188);
$Bild->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);
$Bild->setFontProperties("../pChart/Fonts/tahoma.ttf",8);
$Bild->setGraphArea(30,7,360,147);
$Bild->drawGraphArea(213,217,221);
$Bild->drawGraphAreaGradient(162,183,202,50);

// 1st Graph
$Bild->setFixedScale($min,$max,$div);
$Bild->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_ADDALLSTART0,213,217,221,TRUE,0,0,TRUE);
$Bild->setColorPalette(0,255,149,0);
$Bild->drawGrid(4,FALSE,213,217,221,255,TRUE,FALSE);
$Bild->drawStackedBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),80);
$Bild->drawTextBox(0,168,368,188,"Trainingstage (in h)",0,255,255,255,ALIGN_CENTER,TRUE,0,0,0,30);

// Finish the graph
$Bild->AddBorder(1);
$Cache->WriteToCache("Trainingstage",$DataSet->GetData(),$Bild);
$Bild->Stroke();
?>
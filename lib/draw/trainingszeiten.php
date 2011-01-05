<?php
// Standard inclusions
include("../pChart/pData.class");
include("../pChart/pChart.class");
include("../pChart/pCache.class");
include('../../config/functions.php');
connect();

// Dataset
$dauer = array();
$zeit = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);
$sport_db = mysql_query('SELECT `id`, `name` FROM `ltb_sports` ORDER BY `id` ASC');
while ($sport = mysql_fetch_assoc($sport_db)) {
	$dauer[$sport['name']] = array("","","","","","","","","","","","","","","","","","","","","","","","");
	$zeiten_db = mysql_query('SELECT SUM(1) as `num`, SUM(`dauer`) as `trainingsdauer`, HOUR(FROM_UNIXTIME(`time`)) as `stunde` FROM `ltb_training` WHERE `sportid`="'.$sport['id'].'" AND (HOUR(FROM_UNIXTIME(`time`))!=0 OR MINUTE(FROM_UNIXTIME(`time`))!=0) GROUP BY `stunde` ORDER BY `stunde` ASC');
	while ($zeiten = mysql_fetch_assoc($zeiten_db))
	$dauer[$sport['name']][$zeiten['stunde']] = $zeiten['trainingsdauer']/60/60;
}

close();

// Dataset definition
$DataSet = new pData;
$DataSet->AddPoint($zeit,"Tageszeit");
foreach ($dauer as $name => $array) {
	$DataSet->AddPoint($dauer[$name],$name);
	$DataSet->AddSerie($name);
}
$DataSet->SetAbsciseLabelSerie("Tageszeit");

// Cache definition
$Cache = new pCache();
$Cache->GetFromCache("Zeiten",$DataSet->GetData());

// Initialise the graph
$Bild = new pChart(368,188);
$Bild->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);
$Bild->setFontProperties("../pChart/Fonts/tahoma.ttf",8);
$Bild->setGraphArea(30,7,360,147);
$Bild->drawGraphArea(213,217,221);
$Bild->drawGraphAreaGradient(162,183,202,50);

// Graph
$Bild->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_ADDALLSTART0,213,217,221,TRUE,0,0,TRUE);
$Bild->setColorPalette(0,255,149,0);
$Bild->drawGrid(4,FALSE,213,217,221,255,TRUE,FALSE);
$Bild->drawStackedBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),80);
$Bild->drawLegend(275,7,$DataSet->GetDataDescription(),255,255,255,-1,-1,-1,0,0,0,FALSE);
$Bild->drawTextBox(0,168,368,188,"Trainingszeiten (in h)",0,255,255,255,ALIGN_CENTER,TRUE,0,0,0,30);

// Finish the graph
$Bild->AddBorder(1);
$Cache->WriteToCache("Zeiten",$DataSet->GetData(),$Bild);
$Bild->Stroke();
?>
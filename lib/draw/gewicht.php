<?php
// Standard inclusions
include("../pChart/pData.class");
include("../pChart/pChart.class");
include("../pChart/pCache.class");
include('../../config/functions.php');
connect();

// Dataset
$gewicht = array();
$puls = array();
$db = mysql_query('SELECT * FROM `ltb_user` ORDER BY `time` DESC LIMIT 30');
while($dat = mysql_fetch_array($db)) {
	$gewicht[] = $dat['gewicht'];
	$puls[] = $dat['puls_ruhe'];
}

$kg_min = floor(min($gewicht));
$kg_max = ceil(max($gewicht));
$bpm_min = min($puls) - 2;
$bpm_max = max($puls);

if ($kg_min >= $config['wunschgewicht'])
	$kg_min = floor($config['wunschgewicht'] - 0.5);

$kg_max = ($kg_max - $kg_min > 5) ? $kg_min+10 : $kg_min+5;
$bpm_max = $bpm_max + 5 - ($bpm_max-$bpm_min)%5;

for ($i = 20; $i < 30; $i++)
	unset($gewicht[$i], $puls[$i]);

close();

// Dataset definition
$DataSet = new pData;
$DataSet->AddPoint(array_reverse($puls),"Ruhepuls");
$DataSet->AddSerie("Ruhepuls");
$DataSet->SetYAxisUnit("");

// Cache definition
$DataSet->AddSerie("Ruhepuls");
$Cache = new pCache();
$Cache->GetFromCache("Gewicht",$DataSet->GetData());
$DataSet->RemoveSerie("Gewicht");

// Initialise the graph
$Bild = new pChart(320,148);
$Bild->setFontProperties("../pChart/Fonts/tahoma.ttf",7);
$Bild->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);
$Bild->setGraphArea(22,10,290,140);
$Bild->drawGraphArea(213,217,221);
$Bild->drawGraphAreaGradient(162,183,202,50);

if ($config['use_ruhepuls'] == 1) {
	// 1st Graph: Puls
	$Bild->setFixedScale($bpm_min, $bpm_max, 5);
	$Bild->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,213,217,221,TRUE,0,0,FALSE,100);
	$Bild->setColorPalette(0,136,0,0);
	$Bild->drawGrid(4,FALSE,184,201,217,255);
	$Bild->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());
	$Bild->drawLegend(27,10,$DataSet->GetDataDescription(),255,255,255,-1,-1,-1,0,0,0,FALSE);
}

if ($config['use_gewicht'] == 1) {
	// 2nd Graph: Gewicht
	$DataSet->RemoveSerie("Ruhepuls");
	$DataSet->AddPoint(array_reverse($gewicht),"Gewicht");
	$DataSet->AddSerie("Gewicht");
	$DataSet->SetYAxisUnit("kg");
	$Bild->setFixedScale($kg_min, $kg_max, 5);
	$Bild->drawRightScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,213,217,221,TRUE,0,0,FALSE,100);
	if ($config['use_ruhepuls'] != 1)
	$Bild->drawGrid(4,FALSE,184,201,217,255);
	if ($config['wunschgewicht'] != 0)
	$Bild->drawTreshold($config['wunschgewicht'],0,136,0,0);
	$Bild->setColorPalette(0,0,136,0);
	$Bild->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());
	$Bild->drawLegend(211,10,$DataSet->GetDataDescription(),255,255,255,-1,-1,-1,0,0,0,FALSE);
}

// Finish the graph
$Bild->AddBorder(1);
$Cache->WriteToCache("Gewicht",$DataSet->GetData(),$Bild);
$Bild->Stroke();
?>
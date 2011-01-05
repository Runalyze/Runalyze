<?php
// Standard inclusions   
include("../pChart/pData.class");
include("../pChart/pChart.class");
include("../pChart/pCache.class");
include('../../config/functions.php');
connect();

// Get data from database
$distanz = array();
$zeit = array();
$dat_db = mysql_query('SELECT `id`, `arr_dist`, `arr_alt`, `sportid`, `time`, `typid` FROM `ltb_training` WHERE `id`='.$_GET['id'].' LIMIT 1');
$dat = mysql_fetch_assoc($dat_db);
$arr_alt = explode('|', $dat['arr_alt']);
$arr_dist = explode('|', $dat['arr_dist']);

if (sizeof($arr_alt) <= 1 || sizeof($arr_dist) <= 1)
	die('No altitude/distance in database.');

foreach($arr_alt as $i => $hm)
	if ($hm == 0 || $i%6 != 1) {
		unset($arr_alt[$i]);
		unset($arr_dist[$i]);
	}
$min = floor(min($arr_alt)/20)*20;
$max = ceil(max($arr_alt)/20)*20;
$div = ($max-$min)/20;
if ($div == 1) {
	$min -= 20;
	$max += 20;
	$div = 3;
}
if ($div > 10)
	$div = 10;

$max_dist = max($arr_dist);
if ($max_dist <= 5.5)
	$each = 0.5;
elseif ($max_dist <= 11)
	$each = 1;
elseif ($max_dist <= 20)
	$each = 2;
else
	$each = 5;
$skip = round(sizeof($arr_dist)/($max_dist/$each));
foreach($arr_dist as $i => $dist)
	$arr_dist[$i] = round($dist*2)/2;

$info_left = ($dat['sportid'] == 1 ? typ($dat['typid']) : sport($dat['sportid'])).', '.date("d.m.Y", $dat['time']);
$info_right = 'Höhenprofil';

close();

// Dataset definition 
$DataSet = new pData;
$DataSet->AddPoint($arr_dist,"Distanz");
$DataSet->AddPoint($arr_alt,"Hoehenmeter");
$DataSet->AddSerie("Hoehenmeter");
$DataSet->SetAbsciseLabelSerie("Distanz");
$DataSet->SetXAxisUnit(" km");
$DataSet->SetYAxisUnit(" hm");

// Cache definition   
$Cache = new pCache();
#$Cache->GetFromCache("Hoehenmeter-".$_GET['id'],$DataSet->GetData());
  
// Initialise the chart  
$Bild = new pChart(480,190);
$Bild->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);
$Bild->setFontProperties("../pChart/Fonts/tahoma.ttf",8);
$Bild->setGraphArea(50,10,470,145);
$Bild->drawGraphArea(213,217,221);
$Bild->drawGraphAreaGradient(162,183,202,50);
$Bild->setFixedScale($min,$max,$div);
$Bild->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,213,217,221,TRUE,0,2,TRUE,($skip == 0 ? 1 : $skip));
$Bild->drawGrid(4,TRUE,220,220,220,20,TRUE,FALSE);
$Bild->setColorPalette(0,227,212,187);
$Bild->drawFilledCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription(),.1,90);

// Finish the graph
$Bild->drawTextBox(0,170,239,190,$info_left,0,255,255,255,ALIGN_LEFT,TRUE,0,0,0,30);
$Bild->drawTextBox(240,170,480,190,$info_right,0,255,255,255,ALIGN_RIGHT,TRUE,0,0,0,30);
$Bild->AddBorder(1);
$Cache->WriteToCache("Hoehenmeter-".$_GET['id'],$DataSet->GetData(),$Bild);
$Bild->Stroke();
?>
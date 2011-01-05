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
$dat_db = mysql_query('SELECT `id`, `arr_dist`, `arr_pace`, `sportid`, `time`, `typid` FROM `ltb_training` WHERE `id`='.$_GET['id'].' LIMIT 1');
$dat = mysql_fetch_assoc($dat_db);
$arr_pace = explode('|', $dat['arr_pace']);
$arr_dist = explode('|', $dat['arr_dist']);

if (sizeof($arr_pace) <= 1 || sizeof($arr_dist) <= 1)
die('No pace/distance in database.');

$arr_pace_n = array();
$arr_dist_n = array();
for ($i = 0; $i < sizeof($arr_pace); $i++)
if ($i%5 == 0)
if ($arr_pace[$i] > 60 && $arr_pace[$i-1] > 60 && $arr_pace[$i-2] > 60 && $arr_pace[$i-3] > 60 && $arr_pace[$i-4] > 60 &&
$arr_pace[$i] < 900 && $arr_pace[$i-1] < 900 && $arr_pace[$i-2] < 900 && $arr_pace[$i-3] < 900 && $arr_pace[$i-4] < 900) {
	$arr_pace_n[] = ($arr_pace[$i]+$arr_pace[$i-1]+$arr_pace[$i-2]+$arr_pace[$i-3]+$arr_pace[$i-4])/5;
	$arr_dist_n[] = $arr_dist[$i];
}
$arr_pace = $arr_pace_n;
$arr_dist = $arr_dist_n;

$min = floor(min($arr_pace)/60)*60;
$max = ceil(max($arr_pace)/60)*60;
if ($min < 120) $min = 120;
if ($max > 480) $max = 480;
$div = round(($max-$min)/60);
if ($div <= 3) $div *= 2;

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
// TODO May cause a wrong axis: 1.5 | 2 | 3 | ... if a fet steps are deleted
$arr_dist[$i] = round($dist*2)/2;

$info_left = ($dat['sportid'] == 1 ? typ($dat['typid']) : sport($dat['sportid'])).', '.date("d.m.Y", $dat['time']);
$info_right = 'Geschwindigkeit';

close();

// Dataset definition
$DataSet = new pData;
$DataSet->AddPoint($arr_dist,"Distanz");
$DataSet->AddPoint($arr_pace,"Pace");
$DataSet->AddSerie("Pace");
$DataSet->SetAbsciseLabelSerie("Distanz");
$DataSet->SetXAxisUnit(" km");
$DataSet->SetYAxisFormat("time");

// Cache definition
$Cache = new pCache();
$Cache->GetFromCache("Pace-".$_GET['id'],$DataSet->GetData());

// Initialise the chart
$Bild = new pChart(480,190);
$Bild->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);
$Bild->setFontProperties("../pChart/Fonts/tahoma.ttf",8);
$Bild->setGraphArea(60,10,470,145);
$Bild->drawGraphArea(213,217,221);
$Bild->drawGraphAreaGradient(162,183,202,50);
$Bild->setFixedScale($min,$max,$div);
$Bild->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,213,217,221,TRUE,0,2,TRUE,($skip == 0 ? 1 : $skip));
$Bild->drawGrid(4,TRUE,220,220,220,20,TRUE,FALSE);
$Bild->setColorPalette(0,0,0,136);
$Bild->drawCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription());

// Finish the graph
$Bild->drawTextBox(0,170,239,190,$info_left,0,255,255,255,ALIGN_LEFT,TRUE,0,0,0,30);
$Bild->drawTextBox(240,170,480,190,$info_right,0,255,255,255,ALIGN_RIGHT,TRUE,0,0,0,30);
$Bild->AddBorder(1);
$Cache->WriteToCache("Pace-".$_GET['id'],$DataSet->GetData(),$Bild);
$Bild->Stroke();
?>
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
$dat_db = mysql_query('SELECT `id`, `arr_dist`, `arr_heart`, `sportid`, `time`, `typid` FROM `ltb_training` WHERE `id`='.$_GET['id'].' LIMIT 1');
$dat = mysql_fetch_assoc($dat_db);
$arr_heart = explode('|', $dat['arr_heart']);
$arr_dist = explode('|', $dat['arr_dist']);

if (sizeof($arr_heart) <= 1 || sizeof($arr_dist) <= 1)
die('No heartrate/distance in database.');

foreach($arr_heart as $i => $puls)
$arr_heart[$i] = round(100*$puls/$global['HFmax']);

$avg = round(array_sum($arr_heart)/sizeof($arr_heart));
$min = 50;
$max = 100;
$div = 5;

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
$info_right = 'Herzfrequenz';

close();

// Dataset definition
$DataSet = new pData;
$DataSet->AddPoint($arr_dist,"Distanz");
$DataSet->AddPoint($arr_heart,"Puls");
$DataSet->AddSerie("Puls");
$DataSet->SetAbsciseLabelSerie("Distanz");
$DataSet->SetXAxisUnit(" km");
$DataSet->SetYAxisUnit(" %");

// Cache definition
$Cache = new pCache();
$Cache->GetFromCache("Puls-".$_GET['id'],$DataSet->GetData());

// Initialise the chart
$Bild = new pChart(480,190);
$Bild->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);
$Bild->setFontProperties("../pChart/Fonts/tahoma.ttf",8);
$Bild->setGraphArea(42,10,450,145);
$Bild->drawGraphArea(213,217,221);
$Bild->drawGraphAreaGradient(162,183,202,50);
$Bild->setFixedScale($min,$max,$div);
$Bild->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,213,217,221,TRUE,0,2,TRUE,($skip == 0 ? 1 : $skip));
$Bild->drawGrid(4,TRUE,220,220,220,20,TRUE,FALSE);
$Bild->setColorPalette(0,136,0,0);
$Bild->drawTreshold($avg,208,130,132,TRUE,TRUE,2,$avg.' %');
$Bild->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());

// Finish the graph
$Bild->drawTextBox(0,170,239,190,$info_left,0,255,255,255,ALIGN_LEFT,TRUE,0,0,0,30);
$Bild->drawTextBox(240,170,480,190,$info_right,0,255,255,255,ALIGN_RIGHT,TRUE,0,0,0,30);
$Bild->AddBorder(1);
$Cache->WriteToCache("Puls-".$_GET['id'],$DataSet->GetData(),$Bild);
$Bild->Stroke();
?>
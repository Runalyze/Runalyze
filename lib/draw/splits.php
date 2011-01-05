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
$dat_db = mysql_query('SELECT `id`, `bemerkung`, `splits`, `sportid`, `time`, `typid` FROM `ltb_training` WHERE `id`='.$_GET['id'].' LIMIT 1');
$dat = mysql_fetch_assoc($dat_db);
$splits = explode("-", str_replace("\r\n", "-", $dat['splits']));
for ($i = 0; $i < count($splits); $i++) {
	$split = explode("|", $splits[$i]);
	$zeit_dat = explode(":", $split[1]);
	$distanz[] = $split[0];
	$zeit[] = round(($zeit_dat[0]*60 + $zeit_dat[1])/$split[0]);
}

$min = floor(min($zeit)/20)*20-20;
$max = ceil(max($zeit)/20)*20;
$div = ($max-$min)/20;
if ($div <= 3) $div *= 2;
$skip = floor(count($distanz)*2 / 11);

$info_left = ($dat['sportid'] == 1 ? typ($dat['typid']) : sport($dat['sportid'])).', '.date("d.m.Y", $dat['time']);
$info_right = $dat['bemerkung'];
$tempo_soll = explode("in ", $dat['bemerkung']);
$tempo_soll = explode(",", $tempo_soll[1]);
$tempo_soll = explode(":", $tempo_soll[0]);
$treshold_soll = sizeof($tempo_soll) == 2 ? 60*$tempo_soll[0]+$tempo_soll[1] : 0;

close();

// Dataset definition
$DataSet = new pData;
$DataSet->AddPoint($distanz,"Distanz");
$DataSet->AddPoint($zeit,"Zeit");
$DataSet->AddSerie("Zeit");
$DataSet->SetAbsciseLabelSerie("Distanz");
$DataSet->SetXAxisUnit(" km");
$DataSet->SetYAxisFormat("time");

// Cache definition
$Cache = new pCache();
$Cache->GetFromCache("Splits-".$_GET['id'],$DataSet->GetData());

// Initialise the chart
$Test = new pChart(480,190);
$Test->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);
$Test->setFontProperties("../pChart/Fonts/tahoma.ttf",8);
$Test->setGraphArea(60,10,470,145);
$Test->drawGraphArea(213,217,221);
$Test->drawGraphAreaGradient(162,183,202,50);
$Test->setFixedScale($min,$max,$div);
$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,213,217,221,TRUE,0,2,TRUE,($skip == 0 ? 1 : $skip));
$Test->drawGrid(4,FALSE,220,220,220,255,TRUE,FALSE);

// Treshold
for ($i = ceil($min/60); $i <= floor($max/60); $i++)
$Test->drawTreshold($i*60,136,0,0,FALSE,FALSE,0);
if ($treshold_soll != 0)
$Test->drawTreshold($treshold_soll,0,136,0,0,FALSE,FALSE,0);

$Test->setColorPalette(0,255,149,0);
$Test->drawStackedBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),80);

// Finish the graph
$Test->drawTextBox(0,170,239,190,$info_left,0,255,255,255,ALIGN_LEFT,TRUE,0,0,0,30);
$Test->drawTextBox(240,170,480,190,$info_right,0,255,255,255,ALIGN_RIGHT,TRUE,0,0,0,30);
$Test->AddBorder(1);
$Cache->WriteToCache("Splits-".$_GET['id'],$DataSet->GetData(),$Test);
$Test->Stroke();
?>
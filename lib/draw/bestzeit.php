<?php
// Standard inclusions
include("../pChart/pData.class");
include("../pChart/pChart.class");
include("../pChart/pCache.class");
include('../../config/functions.php');
connect();

// Dataset
$dauer = array();
$time = array();
$km = !is_numeric($_GET['km']) ? 10 : $_GET['km'];

$info = 'Bestzeitverlauf auf '.km($km,(round($km) != $km ? 1 : 0));
$dat_db = mysql_query('SELECT `time`, `dauer` FROM `ltb_training` WHERE `typid`='.$global['wettkampf_typid'].' AND `distanz`="'.$km.'" ORDER BY `time` ASC');
while ($dat = mysql_fetch_assoc($dat_db)) {
	$dauer[] = $dat['dauer'];
	$time[] = date("j.n.y", $dat['time']);
}

close();

// Dataset definition
$DataSet = new pData;
$DataSet->AddPoint($dauer,"Bestzeit");
$DataSet->AddPoint($time,"Zeit");
$DataSet->AddSerie("Bestzeit");
$DataSet->SetAbsciseLabelSerie("Zeit");
$DataSet->SetYAxisFormat("time");

// Cache definition
$Cache = new pCache();
$Cache->GetFromCache("Bestzeit-".$km,$DataSet->GetData());

// Initialise the chart
$Bild = new pChart(480,190);
$Bild->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);
$Bild->setFontProperties("../pChart/Fonts/tahoma.ttf",8);
$Bild->setGraphArea(18,10,423,145);
$Bild->drawGraphArea(213,217,221);
$Bild->drawGraphAreaGradient(162,183,202,50);

// Graph
$Bild->drawRightScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_ADDALL,213,217,221,TRUE,0,2,FALSE,(sizeof($time) > 10 ? 2 : 1));
$Bild->drawGrid(4,FALSE,184,201,217,255,TRUE,FALSE);

// Treshold
switch ($km) {
	case 3: $treshold = 12*60; break;
	case 5: $treshold = 20*60; break;
	case 10: $treshold = 40*60; break;
	case 21.1: $treshold = 90*60; break;
	case 42.2: $treshold = 180*60; break;
	default: $treshold = 0;
}

if ($treshold != 0)
$Bild->drawTreshold($treshold,136,0,0,FALSE,FALSE,0);

$bestzeit = min($dauer);
foreach ($dauer as $key => $value)
if ($value == $bestzeit)
$bestzeit_time = $time[$key];
$Bild->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Bestzeit",$bestzeit_time,"PB ".dauer($bestzeit));

#$Test->setColorPalette(0,255,149,0);
$Bild->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());
$Bild->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),2);

// Finish the graph
$Bild->drawTextBox(0,170,480,190,$info,0,255,255,255,ALIGN_CENTER,TRUE,0,0,0,30);
$Bild->AddBorder(1);
$Cache->WriteToCache("Bestzeit-".$km,$DataSet->GetData(),$Bild);
$Bild->Stroke();
?>
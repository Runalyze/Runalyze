<?php
// Standard inclusions
include("../pChart/pData.class");
include("../pChart/pChart.class");
include("../pChart/pCache.class");
include('../../config/functions.php');
connect();

// Dataset
$fett = array();
$wasser = array();
$muskeln = array();
$db = mysql_query('SELECT * FROM `ltb_user` WHERE `fett` > 0 ORDER BY `time` DESC LIMIT 20');
while($dat = mysql_fetch_array($db)) {
	$fett[] = $dat['fett'];
	$wasser[] = $dat['wasser'];
	$muskeln[] = $dat['muskeln'];
}

close();

// Dataset definition
$DataSet = new pData;
$DataSet->AddPoint(array_reverse($fett),"Fett");
$DataSet->AddSerie("Fett");
$DataSet->AddPoint(array_reverse($wasser),"Wasser");
$DataSet->AddSerie("Wasser");
$DataSet->AddPoint(array_reverse($muskeln),"Muskeln");
$DataSet->AddSerie("Muskeln");
$DataSet->SetYAxisUnit("%");
$DataSet->SetAbsciseLabelSerie();

// Cache definition
$Cache = new pCache();
$Cache->GetFromCache("Fett",$DataSet->GetData());

// Initialise the graph
$Bild = new pChart(320,148);
$Bild->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);
$Bild->setFontProperties("../pChart/Fonts/tahoma.ttf",7);
$Bild->setGraphArea(33,10,298,143);
$Bild->drawGraphArea(213,217,221);
$Bild->drawGraphAreaGradient(162,183,202,50);

// Wasser
$DataSet->RemoveSerie("Fett");
$DataSet->RemoveSerie("Muskeln");
$Bild->setFixedScale(floor(min(array_merge($wasser,$muskeln))/5)*5,ceil(max(array_merge($wasser,$muskeln))/5)*5,5);
$Bild->drawRightScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,213,217,221,TRUE,0,0,FALSE,100);
$Bild->setColorPalette(0,0,136,0);
$Bild->drawGrid(4,FALSE,184,201,217,255);
$Bild->setColorPalette(0,100,100,255);
$Bild->drawFilledLineGraph($DataSet->GetData(),$DataSet->GetDataDescription(),70,TRUE);
$Bild->drawLegend(150,1,$DataSet->GetDataDescription(),255,255,255);

// Fett
$DataSet->RemoveSerie("Wasser");
$DataSet->AddSerie("Fett");
$Bild->setFixedScale(floor(min($fett)/5)*5,ceil(max($fett)/5)*5,5);
$Bild->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,213,217,221,TRUE,0,0,FALSE,100);
$Bild->setColorPalette(0,255,50,50);
$Bild->drawFilledLineGraph($DataSet->GetData(),$DataSet->GetDataDescription(),70,TRUE);
$Bild->drawLegend(37,1,$DataSet->GetDataDescription(),255,255,255);

// Muskeln
$DataSet->RemoveSerie("Fett");
$DataSet->AddSerie("Muskeln");
$Bild->setFixedScale(floor(min(array_merge($wasser,$muskeln))/5)*5,ceil(max(array_merge($wasser,$muskeln))/5)*5,5);
$Bild->drawRightScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,213,217,221,TRUE,0,0,FALSE,100);
$Bild->setColorPalette(0,0,255,0);
$Bild->drawFilledLineGraph($DataSet->GetData(),$DataSet->GetDataDescription(),70,TRUE);

// Legende
$Bild->drawLegend(230,1,$DataSet->GetDataDescription(),255,255,255);

// Finish the graph
$Bild->AddBorder(1);
$Cache->WriteToCache("Gewicht",$DataSet->GetData(),$Bild);
$Bild->Stroke();
?>
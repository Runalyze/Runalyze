<?php
// Standard inclusions
include("../pChart/pData.class");
include("../pChart/pChart.class");
include("../pChart/pCache.class");
include('../../config/functions.php');
connect();

// Dataset
$jahr = $_GET['jahr'];
$distanz = array();
$wettkampf = array();
$wochen = array();
for ($w = 1; $w <= 52; $w++) {
	$wochen[] = $w;
	$heute = mktime(0,0,0,1,1+($w-1)*7,$jahr);
	$ws = wochenstart($heute);
	$we = wochenende($heute);
	$db = mysql_query('SELECT SUM(`distanz`) as `km` FROM `ltb_training` WHERE `sportid`=1 AND `typid`!='.$global['wettkampf_typid'].' AND `time` BETWEEN '.($ws-10).' AND '.($we-10).' GROUP BY `sportid` LIMIT 1');
	$dat = mysql_fetch_assoc($db);
	$distanz[] = $dat['km'];
	$db = mysql_query('SELECT SUM(`distanz`) as `km` FROM `ltb_training` WHERE `sportid`=1 AND `typid`='.$global['wettkampf_typid'].' AND `time` BETWEEN '.($ws-10).' AND '.($we-10).' GROUP BY `sportid` LIMIT 1');
	$dat = mysql_fetch_assoc($db);
	$wettkampf[] = $dat['km'];
}

$trimp_week = trimp(0,7*CTL());
$pace_month = mysql_fetch_assoc(mysql_query('SELECT AVG(`dauer`/60/`distanz`) as `avg` FROM `ltb_training` WHERE `time` > '.(time()-30*day).' AND `sportid`=1 LIMIT 1'));
$treshold = 5*round($trimp_week / $pace_month['avg'] / 5);

close();

// Dataset definition
$DataSet = new pData;
$DataSet->AddPoint($distanz,"Distanz");
$DataSet->AddPoint($wochen,"Wochen");
$DataSet->AddPoint($wettkampf,"Wettkampf");
$DataSet->AddSerie("Wettkampf");
$DataSet->AddSerie("Distanz");
$DataSet->SetAbsciseLabelSerie("Wochen");
$DataSet->SetYAxisUnit(" km");

// Cache definition
$Cache = new pCache();
$Cache->GetFromCache("Wochenkilometer-".$_GET['jahr'],$DataSet->GetData());

// Initialise the graph
$Bild = new pChart(800,500);
$Bild->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);
$Bild->setFontProperties("../pChart/Fonts/tahoma.ttf",8);
$Bild->setGraphArea(50,10,790,455);
$Bild->drawGraphArea(213,217,221);
$Bild->drawGraphAreaGradient(162,183,202,50);

// Graph
$Bild->setFixedScale(0,100,10);
$Bild->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,213,217,221,TRUE,0,2,TRUE);
$Bild->drawGrid(4,FALSE,184,201,217,200,TRUE,FALSE);
$Bild->drawTreshold($treshold,136,0,0,FALSE,FALSE,0);
$Bild->setColorPalette(0,255,25,0);
$Bild->setColorPalette(1,255,149,0);
$Bild->drawStackedBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),80);

// Finish the graph
$Bild->drawTextBox(0,480,800,500,"Wochenkilometer $jahr",0,255,255,255,ALIGN_CENTER,TRUE,0,0,0,30);
$Bild->AddBorder(1);
$Cache->WriteToCache("Wochenkilometer-".$_GET['jahr'],$DataSet->GetData(),$Bild);
$Bild->Stroke();
?>
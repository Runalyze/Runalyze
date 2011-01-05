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
$monate = array();
for ($m = 1; $m <= 12; $m++) {
	$monate[] = monat($m,true);
	$start = mktime(0,0,0,$m,1,$jahr);
	$ende = mktime(23,59,59,$m+1,0,$jahr);
	$db = mysql_query('SELECT SUM(`distanz`) as `km` FROM `ltb_training` WHERE `sportid`=1 AND `typid`!='.$global['wettkampf_typid'].' AND `time` BETWEEN '.($start-10).' AND '.($ende-10).' GROUP BY `sportid` LIMIT 1');
	$dat = mysql_fetch_assoc($db);
	$distanz[] = $dat['km'];
	$db = mysql_query('SELECT SUM(`distanz`) as `km` FROM `ltb_training` WHERE `sportid`=1 AND `typid`='.$global['wettkampf_typid'].' AND `time` BETWEEN '.($start-10).' AND '.($ende-10).' GROUP BY `sportid` LIMIT 1');
	$dat = mysql_fetch_assoc($db);
	$wettkampf[] = $dat['km'];
}

$trimp_month = trimp(0,365*CTL()/12);
$pace_month = mysql_fetch_assoc(mysql_query('SELECT AVG(`dauer`/60/`distanz`) as `avg` FROM `ltb_training` WHERE `time` > '.(time()-30*day).' AND `sportid`=1 LIMIT 1'));
$treshold = 10*round($trimp_month / $pace_month['avg'] / 10);

close();

// Dataset definition
$DataSet = new pData;
$DataSet->AddPoint($distanz,"Distanz");
$DataSet->AddPoint($monate,"Monate");
$DataSet->AddPoint($wettkampf,"Wettkampf");
$DataSet->AddSerie("Wettkampf");
$DataSet->AddSerie("Distanz");
$DataSet->SetAbsciseLabelSerie("Monate");
$DataSet->SetYAxisUnit(" km");

// Cache definition
$Cache = new pCache();
$Cache->GetFromCache("Monatskilometer-".$_GET['jahr'],$DataSet->GetData());

// Initialise the graph
$Bild = new pChart(800,500);
$Bild->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);
$Bild->setFontProperties("../pChart/Fonts/tahoma.ttf",8);
$Bild->setGraphArea(50,10,790,455);
$Bild->drawGraphArea(213,217,221);
$Bild->drawGraphAreaGradient(162,183,202,50);

// Graph
// TODO automatisch berechnete Skalierung
$Bild->setFixedScale(0,400,20);
$Bild->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,213,217,221,TRUE,0,2,TRUE);
$Bild->drawGrid(4,FALSE,184,201,217,200,TRUE,FALSE);
$Bild->drawTreshold($treshold,136,0,0,FALSE,FALSE,0);
$Bild->setColorPalette(0,255,25,0);
$Bild->setColorPalette(1,255,149,0);
$Bild->drawStackedBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),80);

// Finish the graph
$Bild->drawTextBox(0,480,800,500,"Monatskilometer $jahr",0,255,255,255,ALIGN_CENTER,TRUE,0,0,0,30);
$Bild->AddBorder(1);
#$Cache->WriteToCache("Monatskilometer-".$_GET['jahr'],$DataSet->GetData(),$Bild);
$Bild->Stroke();
?>
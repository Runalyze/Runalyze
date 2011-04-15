<?php
// Standard inclusions
include("../pChart/pData.class");
include("../pChart/pChart.class");
include("../pChart/pCache.class");
include('../../config/functions.php');
connect();

// Dataset
$jahr = $_GET['jahr'];
$muedigkeit = array();
$form = array();
$VDOT_add = false;
$VDOT_wert = array();
$VDOT = array();
$tage = array();
$monate = array();
for ($t = 1; $t <= 365; $t++) {
	$heute = mktime(23,59,0,1,$t,$jahr);
	$tage[] = $t;
	$monate[] = monat(ceil($t/31)+1,true);
	$morgen = $heute + day;
	if ($heute < (time()+day))
		$muedigkeit[] = round(100*atl($heute)/$config['max_atl']);
	if ($heute < (time()+day))
		$form[] = round(100*ctl($heute)/$config['max_ctl']);

	$VDOT_wert_x = 0;
	$VDOT_db = mysql_query('SELECT `id` FROM `ltb_training` WHERE `sportid`=1 AND `puls`!=0 AND `time` BETWEEN '.$heute.' AND '.$morgen.' LIMIT 3');
	if (mysql_num_rows($VDOT_db) > 0) {
		while ($VDOT_dat = mysql_fetch_assoc($VDOT_db))
			$VDOT_wert_x += jd_VDOT_bereinigt($VDOT_dat['id']);
		$VDOT_wert[] = $VDOT_wert_x / mysql_num_rows($VDOT_db);
	}

	$VDOT_aktuell = 0;
	for ($i = 1; $i <= 10; $i++)
	if (isset($VDOT_wert[sizeof($VDOT_wert)-$i])) $VDOT_aktuell += $VDOT_wert[sizeof($VDOT_wert)-$i];
	if ($VDOT_add && sizeof($VDOT_wert)!=0) {
		$teiler = sizeof($VDOT_wert)>10 || sizeof($VDOT_wert)==0 ? 10 : sizeof($VDOT_wert);
		for ($x = 1; $x <= $t; $x++) $VDOT[] = round(($VDOT_aktuell/$teiler),2);
		$VDOT_add = false;
	}
	elseif (sizeof($VDOT_wert)==0) $VDOT_add = true;
	else {
		$teiler = sizeof($VDOT_wert)>10 || sizeof($VDOT_wert)==0 ? 10 : sizeof($VDOT_wert);
		$VDOT[] = round(($VDOT_aktuell/$teiler),2);
	}
}
close();

if (empty($muedigkeit) || empty($form) || empty($VDOT))
	die('Array are empty. No data found.');

// Dataset definition
$DataSet = new pData;
$DataSet->AddPoint($muedigkeit, "Muedigkeit");
$DataSet->AddPoint($form, "Form");
$DataSet->AddPoint($VDOT, "VDOT");
$DataSet->AddPoint($tage, "Tage");
$DataSet->AddPoint($monate, "Monate");
$DataSet->AddSerie("Form");
$DataSet->SetAbsciseLabelSerie("Monate");
$DataSet->SetYAxisUnit(" %");

// Cache definition
$Cache = new pCache();
$Cache->GetFromCache("Mued-".$_GET['jahr'], $DataSet->GetData());

// Initialise the graph
$Bild = new pChart(798, 448);
$Bild->drawGraphAreaGradient(132, 153, 172, 50, TARGET_BACKGROUND);
$Bild->setFontProperties("../pChart/Fonts/tahoma.ttf",8);
$Bild->setGraphArea(45, 10, 770, 405);
$Bild->drawGraphArea(213, 217, 221);
$Bild->drawGraphAreaGradient(162, 183, 202, 50);
$Bild->drawTextBox(0, 428, 798, 448, "Formkurve ".$_GET['jahr'], 0, 255, 255, 255, ALIGN_CENTER, TRUE, 0, 0, 0, 30);

// 1st Graph
$Bild->setFixedScale(0, 100, 10);
$Bild->drawScale($DataSet->GetData(), $DataSet->GetDataDescription(), SCALE_NORMAL, 213, 217, 221, TRUE, 0, 2, FALSE, 31);
$Bild->setColorPalette(0, 0, 136, 0);
$Bild->drawGrid(11, FALSE, 184, 201, 217, 255, TRUE, FALSE);
$Bild->drawFilledLineGraph($DataSet->GetData(), $DataSet->GetDataDescription(), 50, TRUE);
$Bild->setFontProperties("../pChart/Fonts/tahoma.ttf", 8);
$Bild->drawLegend(20, 428, $DataSet->GetDataDescription(), 255, 255, 255, -1, -1, -1, 255, 255, 255, FALSE);

// 2nd Graph
$DataSet->RemoveSerie("Form");
$DataSet->AddSerie("Muedigkeit");
$Bild->setColorPalette(0, 230, 40, 15);
$Bild->drawLineGraph($DataSet->GetData(), $DataSet->GetDataDescription());
$Bild->drawLegend(100, 428, $DataSet->GetDataDescription(), 255, 255, 255, -1, -1, -1, 255, 255, 255, FALSE);

// 3rd Graph
$DataSet->RemoveSerie("Muedigkeit");
$DataSet->AddSerie("VDOT");
$DataSet->SetYAxisUnit("");
$Bild->clearScale();
$Bild->setFixedScale(floor(min($VDOT)/10)*10, ceil(max($VDOT)/10)*10, 10);
$Bild->drawRightScale($DataSet->GetData(), $DataSet->GetDataDescription(), SCALE_NORMAL, 213, 217, 221, TRUE, 0, 2, FALSE, 31);
$Bild->setColorPalette(0, 0, 0, 0);
$Bild->drawLineGraph($DataSet->GetData(), $DataSet->GetDataDescription());
$Bild->drawLegend(700, 428, $DataSet->GetDataDescription(), 255, 255, 255, -1, -1, -1, 255, 255, 255, FALSE);

// Finish the graph
$Bild->AddBorder(1);
$Cache->WriteToCache("Mued-".$_GET['jahr'], $DataSet->GetData(), $Bild);
$Bild->Stroke();
?>
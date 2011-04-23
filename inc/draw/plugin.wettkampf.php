<?php
/**
 * Draw personal bests for a given distance
 * Call:   inc/draw/plugin.wettkampf.php?id=
 */
require_once '../class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);

$Draw = new Draw();

$distance    = !is_numeric($_GET['km']) ? 10 : $_GET['km'];
$Dates       = array();
$Results     = array();
$titleCenter = 'Bestzeiten &#252;ber '.Helper::Km($distance, 1, ($distance <= 3));
$titleError  = '';
$timeFormat  = 'i:s';

$titleCenter = str_replace('&nbsp;', ' ', $titleCenter);

$competitions = Mysql::getInstance()->fetchAsArray('SELECT `time`, `dauer` FROM `ltb_training` WHERE `typid`='.WK_TYPID.' AND `distanz`="'.$distance.'" ORDER BY `time` ASC');
if (!empty($competitions)) {
	foreach ($competitions as $competition) {
		$Dates[]   = $competition['time'];
		$Results[] = $competition['dauer'] + 23*3600; // Attention: timestamp(0) => 1:00:00
	}

	if (max($Results) > 24*3600)
		$timeFormat = 'G:i:s';

} else {
	$titleError = 'F&#252;r diese Distanz sind keine Wettk&#228;mpfe vorhanden.';
}

$ScaleFormat    = array(
	"Factors" => array(30),
	"XMargin" => 0,
	"XLabelsRotation" => 0);
$SerieColor     = array(
	"R" => 0, "G" => 0, "B" => 138);
$LabelFont      = array(
	"R" => 0, "G" => 0, "B" => 0);
$LabelSettings  = array(
	"VerticalMargin" => 2,
	"HorizontalMargin" => 2,
	"NoTitle" => TRUE,
	"DrawPoint" => FALSE,
	"DrawSerieColor" => FALSE);

if (!empty($Results)) {
	$Draw->pData->addPoints($Dates, 'Datum');
	$Draw->pData->addPoints($Results, 'Ergebnis');
	$Draw->pData->setSerieOnAxis('Datum', 0);
	$Draw->pData->setSerieOnAxis('Ergebnis', 1);
	$Draw->pData->setAxisXY(0, AXIS_X);
	$Draw->pData->setAxisXY(1, AXIS_Y);
	$Draw->pData->setAxisPosition(0, AXIS_POSITION_BOTTOM);
	$Draw->pData->setAxisDisplay(0, AXIS_FORMAT_TIME, 'M y');
	$Draw->pData->setAxisDisplay(1, AXIS_FORMAT_TIME, $timeFormat);
	
	$Draw->pData->setScatterSerie('Datum', 'Ergebnis', 0);
	$Draw->pData->setScatterSerieColor(0, $SerieColor);
	$Draw->pData->setScatterSerieTicks(0, 4);
}

$Draw->startImage();
$Draw->drawCenterTitle($titleCenter);

if (!empty($Results)) {
	$Scatter = new pScatter($Draw->pImage, $Draw->pData);
	$Scatter->drawScatterScale($ScaleFormat);
	$Scatter->drawScatterSplineChart();
	$Scatter->drawScatterPlotChart();
	$Draw->pImage->setFontProperties($LabelFont);
	$Scatter->writeScatterLabel(0, array_keys($Results, min($Results)), $LabelSettings);
}

if ($titleError != '')
	$Draw->drawCenteredTitle($titleError);

$Draw->finish();
?>
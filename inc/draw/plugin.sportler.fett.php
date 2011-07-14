<?php
/**
 * Draw analyse of 'fett', 'muskeln' and 'water' for the user
 * Call:   inc/draw/plugin.sportler.fett.php
 */
require_once '../class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);

$Draw = new Draw(320, 148);
$Draw->padding['left']   = 20;
$Draw->padding['right']  = 35;
$Draw->padding['bottom'] = 7;

// TODO: Set as config-var?
$data_num   = 20;
$titleError = '';
$Adiposes   = array();
$Water      = array();
$Muscles    = array();

$data = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'user` ORDER BY `time` DESC LIMIT '.$data_num);

if (!empty($data)) {
	foreach ($data as $dat) {
		$Adiposes[] = $dat['fett'];
		$Water[]    = $dat['wasser'];
		$Muscles[]  = $dat['muskeln'];
	}

	$Adiposes = array_reverse($Adiposes);
	$Water    = array_reverse($Water);
	$Muscles  = array_reverse($Muscles);
} else {
	$titleError = 'Es sind keine Daten vorhanden.';
}


$ScaleFormat    = array("XMargin" => 0, "DrawYLines" => array(1), "TickAlpha" => 50);
$LegendFormat   = array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL);


if (CONFIG_USE_KOERPERFETT) {
	$WaterFormat = array("R" => 50, "G" => 50, "B" => 255);
	$Draw->pData->addPoints($Water, 'Wasser');
	$Draw->pData->setSerieOnAxis('Wasser', 1);
	$Draw->pData->setPalette('Wasser', $WaterFormat);

	$AdiposeFormat = array("R" => 255, "G" => 50, "B" => 50);
	$Draw->pData->addPoints($Adiposes, 'Fett');
	$Draw->pData->setSerieOnAxis('Fett', 0);
	$Draw->pData->setPalette('Fett', $AdiposeFormat);

	$MuscleFormat = array("R" => 50, "G" => 255, "B" => 50);
	$Draw->pData->addPoints($Muscles, 'Muskeln');
	$Draw->pData->setSerieOnAxis('Muskeln', 1);
	$Draw->pData->setPalette('Muskeln', $MuscleFormat);

	$Draw->pData->setAxisPosition(1, AXIS_POSITION_RIGHT);
	$Draw->pData->setAxisUnit(1, ' %');
	$Draw->pData->setAxisUnit(0, ' %');
} else {
	$titleError = 'Es werden keine K&#246;rperfettdaten protokolliert.';
}

$xAxis = array();
for ($i = 0; $i < $data_num; $i++)
	$xAxis[] = '';
$Draw->pData->addPoints($xAxis, 'Labels');
$Draw->pData->setAbscissa('Labels');

$Draw->startImage();
$Draw->drawScale($ScaleFormat);
$Draw->drawAreaChart();

$Draw->pImage->drawLegend(130, 15, $LegendFormat);

if ($titleError != '')
	$Draw->drawCenteredTitle($titleError);

$Draw->finish();
?>
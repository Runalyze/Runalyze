<?php
/**
 * Draw pace for a given training
 * Call:   inc/draw/training.pace.php?id=
 */
require_once '../class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);

$Draw = new Draw();

// Hint for usage: Pace data is used for all 50m
$skip = 0.1;

$titleLeft    = '';
$titleRight   = 'Geschwindigkeit';
$titleError   = '';

if (is_numeric($_GET['id'])) {
	$Training = new Training($_GET['id']);

	$titleLeft = $Training->getTitle().', '.$Training->getDate();

	if ($Training->hasPaceData()) {
		$Paces     = array();
		$Paces_num = array();
		$Distances = array();
		$Paces_raw     = explode('|', $Training->get('arr_pace'));
		$Distances_raw = explode('|', $Training->get('arr_dist'));
		$Distance      = max($Distances_raw);

		for ($i = 0; $i < $Distance; $i += $skip) {
			$Distances[] = $i; //floor($i);
			$Paces[] = VOID;
			$Paces_num[] = 0;
		}

		for ($i = 0, $n = count($Distances_raw); $i < $n; $i++) {
			$position = floor($Distances_raw[$i] / $skip);

			if ($Paces_raw[$i] > 60) {
				$Paces[$position] += $Paces_raw[$i];
				$Paces_num[$position]++;
			}
		}

		for ($i = 0, $n = count($Paces); $i < $n; $i++) {
			if ($Paces_num[$i] != 0)
				$Paces[$i] = round($Paces[$i] / $Paces_num[$i]);
			elseif ($i == 0)
				$Paces[$i] = $Paces[$i+1];
			elseif ($i == count($Paces)-1)
				$Paces[$i] = $Paces[$i-1];
			else
				$Paces[$i] = ($Paces[$i-1] + $Paces[$i+1]) / 2;
		}

	} else {
		$titleError = 'Keine Daten vorhanden.';
	}
} else {
	$titleError = 'Es wurde kein Training ausgew&#228;hlt.';
}

if ($Distance >= 15)
	$skip /= ceil(($Distance-5)/10);

$Draw->setCaching(false);

if ($titleError == '') {
	$avg = array_sum($Paces)/count($Paces);
	$sig = sqrt(Helper::getVariance($Paces));
	$min = 60*floor(($avg-1.5*$sig)/60);
	$max = 60*ceil(($avg+1.5*$sig)/60);

	$ScaleFormat    = array(
		"Factors" => array(30, 60),
		"Mode" => SCALE_MODE_MANUAL,
		"ManualScale" => array(0 => array(
			"Min" => $min,
			"Max" => $max)),
		"LabelSkip" => (1/$skip - 1),
		"XMargin" => 0);
} else {
	$ScaleFormat = array();
}
$SplineFormat   = array("R" => 0, "G" => 0, "B" => 136); 

$Draw->pData->addPoints($Distances, 'Distanz');
$Draw->pData->addPoints($Paces, 'Pace');
$Draw->pData->setXAxisUnit(' km');
$Draw->pData->setAxisDisplay(0, AXIS_FORMAT_TIME, 'i:s');
$Draw->pData->setAbscissa('Distanz');
$Draw->pData->setPalette('Pace', $SplineFormat);

$Draw->startImage();
$Draw->drawScale($ScaleFormat);
$Draw->drawSplineChart();
$Draw->drawLeftTitle($titleLeft);
$Draw->drawRightTitle($titleRight);

if ($titleError != '')
	$Draw->drawCenteredTitle($titleError);

$Draw->finish();
?>
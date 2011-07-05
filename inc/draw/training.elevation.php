<?php
/**
 * Draw elevation for a given training
 * Call:   inc/draw/training.elevation.php?id=
 */
require_once '../class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);

$Draw = new Draw();

// Hint for usage: Elevation data is used for all 50m
$skip = 0.1;

$titleLeft    = '';
$titleRight   = 'H&#246;henprofil';
$titleError   = '';

if (is_numeric($_GET['id'])) {
	$Training = new Training($_GET['id']);

	$titleLeft = $Training->getTitle().', '.$Training->getDate();

	if ($Training->hasElevationData()) {
		$Elevations     = array();
		$Elevations_num = array();
		$Distances      = array();
		$Elevation_raw  = explode('|', $Training->get('arr_alt'));
		$Distances_raw  = explode('|', $Training->get('arr_dist'));
		$Distance      = max($Distances_raw);

		for ($i = 0; $i < $Distance; $i += $skip) {
			$Distances[] = $i; //floor($i);
			$Elevations[] = VOID;
			$Elevations_num[] = 0;
		}

		for ($i = 0, $n = count($Distances_raw); $i < $n; $i++) {
			$position = floor($Distances_raw[$i] / $skip);

			$Elevations[$position] += $Elevation_raw[$i];
			$Elevations_num[$position]++;
		}

		for ($i = 0, $n = count($Elevations); $i < $n; $i++) {
			if ($Elevations_num[$i] != 0)
				$Elevations[$i] = round($Elevations[$i] / $Elevations_num[$i]);
			elseif ($i == 0)
				$Elevations[$i] = $Elevations[$i+1];
			elseif ($i == count($Elevations)-1)
				$Elevations[$i] = $Elevations[$i-1];
			else
				$Elevations[$i] = ($Elevations[$i-1] + $Elevations[$i+1]) / 2;
		}

	} else {
		$titleError = 'Keine Daten vorhanden.';
	}
} else {
	$titleError = 'Es wurde kein Training ausgew&#228;hlt.';
}


if ($Distance >= 15)
	$skip /= ceil(($Distance-5)/10);

$ScaleFormat    = array(
	"Factors" => array(10),
	"LabelSkip" => (1/$skip - 1),
	"XMargin" => 0);
$SplineFormat   = array("R" => 227, "G" => 217, "B" => 187, "Alpha" => 165);
$BoundsSettings = array(
	"MaxDisplayR" => 255, "MaxDisplayG" => 255, "MaxDisplayB" => 255,
	"MinDisplayR" => 255, "MinDisplayG" => 255, "MinDisplayB" => 255,
	"MaxLabelPos" => BOUND_LABEL_POS_TOP,
	"MinLabelPos" => BOUND_LABEL_POS_BOTTOM);


$Draw->pData->addPoints($Distances, 'Distanz');
$Draw->pData->addPoints($Elevations, 'Hoehe');
$Draw->pData->setXAxisUnit(' km');
$Draw->pData->setAxisUnit(0, ' hm');
$Draw->pData->setAbscissa('Distanz');
$Draw->pData->setPalette('Hoehe', $SplineFormat);

$Draw->startImage();
$Draw->drawScale($ScaleFormat);
$Draw->drawFilledSplineChart();
$Draw->drawLeftTitle($titleLeft);
$Draw->drawRightTitle($titleRight);
$Draw->pImage->writeBounds(BOUND_BOTH, $BoundsSettings);

if ($titleError != '')
	$Draw->drawCenteredTitle($titleError);

$Draw->finish();
?>
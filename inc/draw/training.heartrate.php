<?php
/**
 * Draw heart-rate for a given training
 * Call:   inc/draw/training.heartrate.php?id=
 */
require_once '../class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);

$Draw = new Draw();

// Hint for usage: Heart-rate data is used for all 50m
$skip = 0.1;

$titleLeft    = '';
$titleRight   = 'Herzfrequenz';
$titleError   = '';

if (is_numeric($_GET['id'])) {
	$Training = new Training($_GET['id']);

	$titleLeft = $Training->getTitle().', '.$Training->getDate();

	if ($Training->hasPulseData()) {
		$Paces     = array();
		$Paces_num = array();
		$Distances = array();
		$Distance      = $Training->get('distanz');
		$HR_raw        = explode('|', $Training->get('arr_heart'));
		$Distances_raw = explode('|', $Training->get('arr_dist'));

		for ($i = 0; $i < $Distance; $i += $skip) {
			$Distances[] = $i; //floor($i);
			$HR[] = VOID;
			$HR_num[] = 0;
		}

		for ($i = 0, $n = count($Distances_raw); $i < $n; $i++) {
			$position = floor($Distances_raw[$i] / $skip);

			if ($HR_raw[$i] > 60) {
				$HR[$position] += round(100 * $HR_raw[$i] / HF_MAX);
				$HR_num[$position]++;
			}
		}

		for ($i = 0, $n = count($HR); $i < $n; $i++) {
			if ($HR_num[$i] != 0)
				$HR[$i] = round($HR[$i] / $HR_num[$i]);
			elseif ($i == 0)
				$HR[$i] = $HR[$i+1];
			elseif ($i == count($HR)-1)
				$HR[$i] = $HR[$i-1];
			else
				$HR[$i] = ($HR[$i-1] + $HR[$i+1]) / 2;
		}

	} else {
		$titleError = 'Keine Daten vorhanden.';
	}
} else {
	$titleError = 'Es wurde kein Training ausgew&#228;hlt.';
}

$averageHR      = round(array_sum($HR) / count($HR));
$ScaleFormat    = array(
	"Factors" => array(10),
	"Mode" => SCALE_MODE_MANUAL,
	"ManualScale" => array(0 => array(
		"Min" => 50,
		"Max" => 100)),
	"LabelSkip" => (1/$skip - 1),
	"XMargin" => 0);
$TresholdFormat = array(
	"WriteCaption" => TRUE, "Caption" => "&#216; ".$averageHR." &#37;",
	"CaptionAlign" => CAPTION_RIGHT_BOTTOM,
	"R" => 180, "G" => 0, "B" => 0, "Alpha" => 50);
$SplineFormat   = array(
	"R" => 136, "G" => 0, "B" => 0);

$Draw->pData->addPoints($Distances, 'Distanz');
$Draw->pData->addPoints($HR, 'Herzfrequenz');
$Draw->pData->setXAxisUnit(' km');
$Draw->pData->setAxisUnit(0, ' %');
$Draw->pData->setAbscissa('Distanz');
$Draw->pData->setPalette('Herzfrequenz', $SplineFormat);

$Draw->startImage();
$Draw->drawScale($ScaleFormat);
$Draw->drawSplineChart();
$Draw->drawLeftTitle($titleLeft);
$Draw->drawRightTitle($titleRight);

$Draw->pImage->drawThreshold($averageHR, $TresholdFormat);

if ($titleError != '')
	$Draw->drawCenteredTitle($titleError);

$Draw->finish();
?>
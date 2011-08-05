<?php
/**
 * Draw splits for a given training
 * Call:   inc/draw/training.splits.php?id=
 */
require_once '../class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);

$Draw = new Draw();

$titleLeft    = '';
$titleRight   = '';
$titleError   = '';
$demandedPace = 0;
$achievedPace = 0;
$ScaleFormat  = array();

if (is_numeric($_GET['id'])) {
	$Training = new Training($_GET['id']);

	$titleLeft = $Training->getTitle().', '.$Training->getDate();
	$titleRight = $Training->get('comment');

	if ($Training->hasSplitsData()) {
		$demandedPace = Helper::DescriptionToDemandedPace($Training->get('comment'));
		$achievedPace = array_sum($Training->getSplitsPacesArray()) / count($Training->getSplitsPacesArray());

		if (count($Training->getSplitsPacesArray()) > 10)
			$ScaleFormat["LabelSkip"] = 1;
	} else {
		$titleError = 'Keine Zwischenzeiten vorhanden.';
	}
} else {
	$titleLeft = 'Zwischenzeiten';
	$titleError = 'Es wurde kein Training ausgew&#228;hlt.';
}

$Draw->pData->addPoints($Training->getSplitsDistancesArray(), 'Distanz');
$Draw->pData->addPoints($Training->getSplitsPacesArray(), 'Zeit');
$Draw->pData->setXAxisUnit(' km');
$Draw->pData->setAxisDisplay(0, AXIS_FORMAT_TIME, 'i:s');
$Draw->pData->setAbscissa('Distanz');

$Draw->startImage();
$Draw->drawScale($ScaleFormat);
$Draw->drawBarChart();
$Draw->drawLeftTitle($titleLeft);
$Draw->drawRightTitle($titleRight);

if ($demandedPace > 0)
	$Draw->pImage->drawThreshold($demandedPace, array("WriteCaption" => TRUE, "Caption" => "Vorgabe", "R" => 180, "G" => 0, "B" => 0, "Alpha" => 50, "Ticks" => 0));
if ($achievedPace > 0)
	$Draw->pImage->drawThreshold($achievedPace, array("WriteCaption" => TRUE, "Caption" => "Schnitt", "R" => 0, "G" => 180, "B" => 0, "Alpha" => 50, "CaptionAlign" => CAPTION_RIGHT_BOTTOM));

if ($titleError != '')
	$Draw->drawCenteredTitle($titleError);

$Draw->finish();
?>
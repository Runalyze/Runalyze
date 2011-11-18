<?php
/**
 * Draw splits for a given training
 * Call:   include Plot.Training.splits.php
 */

if (is_numeric($_GET['id'])) {
	$Training = new Training($_GET['id']);

	if ($Training->hasSplitsData()) {
		$demandedPace = Helper::DescriptionToDemandedPace($Training->get('comment'));
		$achievedPace = array_sum($Training->getSplitsPacesArray()) / count($Training->getSplitsPacesArray());
	}
}

$Labels = $Training->getSplitsDistancesArray();
$Data   = $Training->getSplitsPacesArray();

foreach ($Data as $key => $val) {
	$Labels[$key] = array($key, $Labels[$key].' km');
	$Data[$key] = $val*1000;
}

$Plot = new Plot("splits_".$_GET['id'], 480, 190);
$Plot->Data[] = array('label' => 'Zwischenzeiten', 'data' => $Data);

$Plot->setMarginForGrid(5);
$Plot->setYAxisTimeFormat('%M:%S');
$Plot->setXLabels($Labels);
$Plot->showBars(true);

if ($demandedPace > 0)
	$Plot->addTreshold("y", $demandedPace*1000, 'rgb(180,0,0)');
if ($achievedPace > 0)
	$Plot->addTreshold("y", $achievedPace*1000, 'rgb(0,180,0)');

$Plot->outputJavaScript();
?>
<?php
/**
 * Draw splits for a given training
 * Call:   include Plot.Training.splits.php
 */

$Plot = new Plot("splits_".$_GET['id'], 480, 190);

if (!is_numeric($_GET['id']))
	$Plot->raiseError('Es ist kein Training angegeben.');

$Training     = new Training($_GET['id']);
$demandedPace = 0;
$achievedPace = 0;
$Labels       = array();
$Data         = array();

if ($Training->hasSplits()) {
	$Labels  = $Training->Splits()->distancesAsArray();
	$Data    = $Training->Splits()->pacesAsArray();
	$num     = count($Data);
	$unit    = ($num >= 20) ? '' : ' km';

	$demandedPace = Helper::DescriptionToDemandedPace($Training->get('comment'));
	$achievedPace = array_sum($Data) / $num;

	foreach ($Data as $key => $val) {
		$Labels[$key] = array($key, $Labels[$key].$unit);
		$Data[$key]   = $val*1000;
	}
} else {
	$RawData = $Training->GpsData()->getRoundsAsFilledArray();
	$num     = count($RawData);

	foreach ($RawData as $key => $val) {
		$km = $key + 1;
		if ($num < 20) {
			$label = ($km%2 == 0 && $km > 0) ? $km.'&nbsp;km' : '';
		} else {
			$label = ($km%5 == 0 && $km > 0) ? $km.'&nbsp;km' : '';
		}

		$Labels[$key] = array($key, $label);
		$Data[$key]   = $val['s']*1000/$val['km'];
	}
}

$min = min($Data); $min = floor($min/30000)*30000;
$max = max($Data); $max = ceil($max/30000)*30000;

$Plot = new Plot("splits_".$_GET['id'], 480, 190);
$Plot->Data[] = array('label' => 'Zwischenzeiten', 'data' => $Data);

$Plot->setYAxisTimeFormat('%M:%S');
$Plot->setXLabels($Labels);
$Plot->showBars(true);

$Plot->setYLimits(1, $min, $max, false);
$Plot->setYTicks(1, null);

$Plot->hideLegend();
$Plot->setTitle('Zwischenzeiten', 'right');
$Plot->setTitle($Training->getPlotTitle(), 'left');

$Plot->enableTracking();

if ($demandedPace > 0) {
	$Plot->addThreshold("y", $demandedPace*1000, 'rgb(180,0,0)');
	//$Plot->addAnnotation(count($Data)-1, $demandedPace*1000, 'Soll: '.Helper::Time($demandedPace), -10, -7);
}
if ($achievedPace > 0) {
	$Plot->addThreshold("y", $achievedPace*1000, 'rgb(0,180,0)');
	$Plot->addAnnotation(0, $achievedPace*1000, '&oslash; '.Helper::Time(round($achievedPace)), -20, -7);
}

$Plot->outputJavaScript();
?>
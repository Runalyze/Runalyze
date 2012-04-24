<?php
/**
 * Draw pace for a given training
 * Call:   include Plot.Training.pace.php
 */

$Plot = new Plot("pace_".$_GET['id'], 480, 190);

if (!is_numeric($_GET['id']))
	$Plot->raiseError('Es ist kein Training angegeben.');

$Training = new Training($_GET['id']);
$Data = $Training->GpsData()->getPlotDataForPace();
if ($Training->Sport()->usesKmh()) {
	$Data = Plot::correctValuesFromPaceToKmh($Data);
	$correctYAxis = false;
} else {
	$Data = Plot::correctValuesForTime($Data);

	$min = min($Data);
	$max = max($Data);
	
	if ($max >= 10*60*1000) {
		$minL = $min;
		$maxL = 10*60*1000;
		$correctYAxis = true;
	} else {
		$correctYAxis = false;
	}
}

$Plot->Data[] = array('label' => 'Pace', 'color' => 'rgb(0,0,136)', 'data' => $Data);

if ($Training->Sport()->usesKmh())
	$Plot->addYUnit(1, 'km/h');
else
	$Plot->setYAxisTimeFormat('%M:%S');

if ($correctYAxis) {
	$Plot->setYLimits(1, $minL, $maxL, true);
	$Plot->setYTicks(1, null);
}

$Plot->setXUnit('km');
$Plot->enableTracking();

$Plot->hideLegend();
$Plot->setTitle('Pace', 'right');
$Plot->setTitle($Training->getPlotTitle(), 'left');

$Plot->outputJavaScript();
?>
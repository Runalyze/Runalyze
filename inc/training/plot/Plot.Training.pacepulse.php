<?php
/**
 * Draw heartrate,pace for a given training
 * Call:   include Plot.Training.pacepulse.php
 */

$Plot = new Plot("pulse_".$_GET['id'], 480, 190);

if (!is_numeric($_GET['id']))
	$Plot->raiseError('Es ist kein Training angegeben.');

$Training = new Training($_GET['id']);
//Collecting Puls Data
$puData = $Training->GpsData()->getPlotDataForHeartrateInPercent();
$average = round(array_sum($puData)/count($puData));

//Collecting Pace Data
$paData = $Training->GpsData()->getPlotDataForPace();
if ($Training->Sport()->usesKmh()) {
	$paData = Plot::correctValuesFromPaceToKmh($paData);
	$correctYAxis = false;
} else {
	$paData = Plot::correctValuesForTime($paData);
	
	$pmin = min($paData);
	$pmax = max($paData);
	
	if ($pmax >= 10*60*1000) {
		$minPL = $pmin;
		$maxPL = 10*60*1000;
		$correctYAxis = true;
	} else {
		$correctYAxis = false;
	}
}
//
/////////////////


//Configuration for Pace Axis
$Plot->Data[] = array('label' => 'Pace', 'color' => 'rgb(0,0,136)', 'data' => $paData);
$Plot->addYAxis(1, 'left');
if ($Training->Sport()->usesKmh())
$Plot->addYUnit(1, 'km/h');
else
$Plot->setYAxisTimeFormat('%M:%S', 1);

if ($correctYAxis) {
	$Plot->setYLimits(1, $minPL, $maxPL, true);
	$Plot->setYTicks(1, null);
}

//Configuration for Pulse Axis
$Plot->Data[] = array('label' => 'Herzfrequenz', 'color' => 'rgb(136,0,0)', 'data' => $puData, 'yaxis' => 2);
$Plot->addYAxis(2, 'right');
$Plot->addYUnit(2, '%');
$Plot->setYTicks(2, 5, 0);
$Plot->setYLimits(2, 50, 100);
$Plot->enableTracking();
if ($Training->GpsData()->plotUsesTimeOnXAxis()) {
	$Plot->setXAxisAsTime();
	$Plot->setXAxisTimeFormat("%h:%M:%S",2);
	$Plot->Options['xaxis']['ticks'] = 5;
} else
$Plot->setXUnit('km');



$Plot->hideLegend();
$Plot->setTitle('Pulse und Pace', 'right');
$Plot->setTitle($Training->getPlotTitle(), 'left');

$Plot->addThreshold("y2", $average, 'rgba(0,0,0,0.5)');
$Plot->addAnnotation(2, $average, '&oslash; '.$average.' &#37;');

//Marked Area for Pulse
$Plot->addMarkingArea("y2", 100, 90, 'rgba(255,100,100,0.3)');
$Plot->addMarkingArea("y2",  90, 80, 'rgba(255,100,100,0.2)');
$Plot->addMarkingArea("y2",  80, 70, 'rgba(255,100,100,0.1)');


$Plot->outputJavaScript();
?>

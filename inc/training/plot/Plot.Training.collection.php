<?php
/**
 * Draw Collection of pace, heartrate, elevation for a given training
 * Call:   include Plot.Training.collection.php
 * @author Michael Pohl <michael@mipapo.de>
 * @version 1.0
 */

$Plot = new Plot("pace_".$_GET['id'], 480, 190);

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

//Collecting Elevation Data
$eData = $Training->GpsData()->getPlotDataForElevation();
$emin = min($eData); $min_x = array_keys($eData, $emin);
$emax = max($eData); $max_x = array_keys($eData, $emax);
if ($max - $min <= 50) {
	$minEL = $emin - 20;
	$maxEL = $emax + 20;
} else {
	$minEL = $emin;
	$maxEL = $emax;
}


//Configuration for Elevation Axis
$Plot->Data[] = array('label' => 'H&ouml;he', 'color' => 'rgba(227,217,187,1)', 'data' => $eData);
$Plot->addYAxis(1, 'right');
$Plot->addYUnit(1, 'm');
$Plot->setYLimits(1, $minEL, $maxEL, true);
$Plot->setLinesFilled();

//Configuration for Pulse Axis
$Plot->Data[] = array('label' => 'Herzfrequenz', 'color' => 'rgb(136,0,0)', 'data' => $puData, 'yaxis' => 2);
$Plot->addYAxis(2, 'left');
$Plot->addYUnit(2, '%');
$Plot->setYTicks(2, 5, 1);
$Plot->setYLimits(2, 50, 100);
$Plot->enableTracking();
if ($Training->GpsData()->plotUsesTimeOnXAxis()) {
	$Plot->setXAxisAsTime();
	$Plot->setXAxisTimeFormat("%h:%M:%S",2);
	$Plot->Options['xaxis']['ticks'] = 5;
} else
$Plot->setXUnit('km');

//Configuration for Pace Axis
$Plot->Data[] = array('label' => 'Pace', 'color' => 'rgb(0,0,136)', 'data' => $paData, 'yaxis' => 3);
$Plot->addYAxis(3, 'left');
if ($Training->Sport()->usesKmh())
$Plot->addYUnit(3, 'km/h');
else
$Plot->setYAxisTimeFormat('%M:%S', 3);

if ($correctYAxis) {
	$Plot->setYLimits(3, $minPL, $maxPL, true);
	$Plot->setYTicks(3, null);
}

$Plot->hideLegend();
$Plot->setTitle('Zusammenfassung', 'right');
$Plot->setTitle($Training->getPlotTitle(), 'left');

$Plot->addThreshold("y2", $average, 'rgba(0,0,0,0.5)');
$Plot->addAnnotation(0, $average, '&oslash; '.$average.' &#37;');

//Marked Area for Pulse
$Plot->addMarkingArea("y2", 100, 90, 'rgba(255,100,100,0.3)');
$Plot->addMarkingArea("y2",  90, 80, 'rgba(255,100,100,0.2)');
$Plot->addMarkingArea("y2",  80, 70, 'rgba(255,100,100,0.1)');

//Min/Max Elevation
$Plot->addAnnotation($min_x[0], $emin, $emin.'m');
$Plot->addAnnotation($max_x[0], $emax, $emax.'m');

$Plot->outputJavaScript();
?>
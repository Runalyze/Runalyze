<?php
/**
 * Draw heartrate for a given training
 * Call:   include Plot.Training.pulse.php
 */

$Plot = new Plot("pulse_".$_GET['id'], 480, 190);

if (!is_numeric($_GET['id']))
	$Plot->raiseError('Es ist kein Training angegeben.');

$Training = new Training($_GET['id']);
$Data = $Training->GpsData()->getPlotDataForHeartrateInPercent();

$average = round(array_sum($Data)/count($Data));

$Plot->Data[] = array('label' => 'Herzfrequenz', 'color' => 'rgb(136,0,0)', 'data' => $Data);

if ($Training->GpsData()->plotUsesTimeOnXAxis()) {
	$Plot->setXAxisAsTime();
	$Plot->setXAxisTimeFormat("%h:%M:%S");
	$Plot->Options['xaxis']['ticks'] = 5;
} else
	$Plot->setXUnit('km');

$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, '%');
$Plot->setYTicks(1, 5, 0);
$Plot->setYLimits(1, 50, 100);
$Plot->enableTracking();

$Plot->hideLegend();
$Plot->setTitle('Herzfrequenz', 'right');
$Plot->setTitle($Training->getPlotTitle(), 'left');

$Plot->addThreshold("y1", $average, 'rgba(0,0,0,0.5)');
$Plot->addAnnotation(0, $average, '&oslash; '.$average.' &#37;');

$Plot->addMarkingArea("y1", 100, 90, 'rgba(255,100,100,0.3)');
$Plot->addMarkingArea("y1",  90, 80, 'rgba(255,100,100,0.2)');
$Plot->addMarkingArea("y1",  80, 70, 'rgba(255,100,100,0.1)');

$Plot->outputJavaScript();
?>
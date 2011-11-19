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
$Data = Plot::correctValuesForTime($Data);

$Plot->Data[] = array('label' => 'Pace', 'color' => 'rgb(0,0,136)', 'data' => $Data);

$Plot->setYAxisTimeFormat('%M:%S');
$Plot->setXUnit('km');
$Plot->enableTracking();

$Plot->hideLegend();
$Plot->setTitle('Pace', 'right');
$Plot->setTitle($Training->getPlotTitle(), 'left');

$Plot->outputJavaScript();
?>
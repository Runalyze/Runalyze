<?php
/**
 * Draw elevation for a given training
 * Call:   include Plot.Training.elevation.php
 */

$Plot = new Plot("elevation_".$_GET['id'], 480, 190);

if (!is_numeric($_GET['id']))
	$Plot->raiseError('Es ist kein Training angegeben.');

$Training = new Training($_GET['id']);
$Data = $Training->GpsData()->getPlotDataForElevation();

$min = min($Data); $min_x = array_keys($Data, $min);
$max = max($Data); $max_x = array_keys($Data, $max);

if ($max - $min <= 50) {
	$minL = $min - 20;
	$maxL = $max + 20;
} else {
	$minL = $min;
	$maxL = $max;
}


$Plot->Data[] = array('label' => 'H&ouml;he', 'color' => 'rgba(227,217,187,1)', 'data' => $Data);

$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, 'm');
$Plot->setYLimits(1, $minL, $maxL, true);
$Plot->setXUnit('km');
$Plot->setLinesFilled();
$Plot->enableTracking();
$Plot->enableSelection();

$Plot->hideLegend();
$Plot->setTitle('H&ouml;henprofil', 'right');
$Plot->setTitle($Training->getPlotTitle(), 'left');

$Plot->addAnnotation($min_x[0], $min, $min.'m');
$Plot->addAnnotation($max_x[0], $max, $max.'m');

$Plot->outputJavaScript();
?>
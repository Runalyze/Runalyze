<?php
/**
 * Draw heartrate for a given training
 * Call:   include Plot.Training.pulse.php
 */

$Plot = new Plot("pulse_".$_GET['id'], 480, 190);

if (!is_numeric($_GET['id']))
	$Plot->raiseError('Es ist kein Training angegeben.');

$InPercent = (CONF_PULS_MODE == 'hfmax');
$Training  = new Training($_GET['id']);
$Data      = $Training->GpsData()->getPlotDataForHeartrate($InPercent);
$HFmax     = ($InPercent) ? 100 : HF_MAX;

$average = round(array_sum($Data)/count($Data));

$Plot->Data[] = array('label' => 'Herzfrequenz', 'color' => 'rgb(136,0,0)', 'data' => $Data);

if ($Training->GpsData()->plotUsesTimeOnXAxis()) {
	$Plot->setXAxisAsTime();
	$Plot->setXAxisTimeFormat("%h:%M:%S");
	$Plot->Options['xaxis']['ticks'] = 5;
} else
	$Plot->setXUnit('km');

$Plot->addYAxis(1, 'left');

if ($InPercent) {
	$Plot->addYUnit(1, '%');
	$Plot->setYTicks(1, 5, 0);
	$Plot->setYLimits(1, 50, 100);
} else {
	$Plot->addYUnit(1, 'bpm');
	$Plot->setYTicks(1, 10, 0);
	$Plot->setYLimits(1, 10*floor(0.5*$HFmax/10), 10*ceil($HFmax/10));
}

$Plot->enableTracking();
$Plot->hideLegend();
$Plot->setTitle('Herzfrequenz', 'right');
$Plot->setTitle($Training->getPlotTitle(), 'left');

$Plot->addThreshold("y1", $average, 'rgba(0,0,0,0.5)');
$Plot->addAnnotation(0, $average, '&oslash; '.$average.' '.($InPercent ? '&#37;' : 'bpm'));

$Plot->addMarkingArea("y1", 10*ceil($HFmax/10)*1,   10*ceil($HFmax/10)*0.9, 'rgba(255,100,100,0.3)');
$Plot->addMarkingArea("y1", 10*ceil($HFmax/10)*0.9, 10*ceil($HFmax/10)*0.8, 'rgba(255,100,100,0.2)');
$Plot->addMarkingArea("y1", 10*ceil($HFmax/10)*0.8, 10*ceil($HFmax/10)*0.7, 'rgba(255,100,100,0.1)');

$Plot->outputJavaScript();
?>
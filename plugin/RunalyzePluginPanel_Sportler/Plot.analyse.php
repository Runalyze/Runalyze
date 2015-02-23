<?php
/**
 * Draw weight and rest-heartrate for the user
 * Call:   include 'Plot.gewicht.php'
 * @package Runalyze\Plugins\Panels
 */

$Factory = new PluginFactory();
$Plugin = $Factory->newInstance('RunalyzePluginPanel_Sportler');

if ($Plugin->Configuration()->value('plot_timerange') > 0) {
	$QueryEnd = 'WHERE `time` > '.(time() - DAY_IN_S * (int)$Plugin->Configuration()->value('plot_timerange')).' ORDER BY `time` DESC';
} else {
	$QueryEnd = 'ORDER BY `time` DESC LIMIT '.((int)$Plugin->Configuration()->value('plot_points'));
}

$Data     = array_reverse( DB::getInstance()->query('SELECT fat,water,muscles,time FROM `'.PREFIX.'user` '.$QueryEnd)->fetchAll() );
$Adiposes = array();
$Water    = array();
$Muscles  = array();

if (count($Data) == 1) {
	$Data[1] = $Data[0];
}

if (!empty($Data)) {
	foreach ($Data as $D) {
		$Adiposes[$D['time'].'000'] = $D['fat'];
		$Water[$D['time'].'000']    = $D['water'];
		$Muscles[$D['time'].'000']  = $D['muscles'];
	}
} 

$Labels = array_keys($Water);
foreach ($Labels as $i => &$value) {
	if ($i != 0 && $i != count($Labels)-1) {
		$value = '';
	}
}

$Plot = new Plot("sportler_analyse", 320, 150);

$Plot->Data[] = array('label' => __('Fat').'', 'color' => '#800', 'data' => $Adiposes);
$Plot->Data[] = array('label' => __('Water'), 'color' => '#008', 'data' => $Water, 'yaxis' => 2);
$Plot->Data[] = array('label' => __('Muscles'), 'color' => '#080', 'data' => $Muscles, 'yaxis' => 2);

$Plot->setMarginForGrid(5);

//$Plot->hideXLabels();
$Plot->setXLabels($Labels);
$Plot->setXAxisTimeFormat('%m/%y');
$Plot->setXAxisMaxToToday();
$Plot->Options['xaxis']['labelWidth'] = 50;
//$Plot->Options['xaxis']['tickLength'] = 3;
$Plot->Options['yaxis']['autoscaleMargin'] = 0.1;
$Plot->Options['series']['curvedLines']['fit'] = true;

$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, '%', 0);
$Plot->setYTicks(1, 1, 0);
$Plot->addYAxis(2, 'right');
$Plot->addYUnit(2, '%', 0);
$Plot->setYTicks(1, 1, 0);

if (empty($Data)) {
	$Plot->raiseError( __('No data available.') );
} elseif (min(min($Adiposes), min($Water), min($Muscles)) == 0 || count($Adiposes) <= 1) {
	$Plot->setZeroPointsToNull();
	$Plot->lineWithPoints();
	$Plot->Options['series']['curvedLines']['apply'] = false;
}

$Plot->outputJavaScript( true );
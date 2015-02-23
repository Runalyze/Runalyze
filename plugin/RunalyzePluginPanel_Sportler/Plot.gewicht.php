<?php
/**
 * Draw weight and rest-heartrate for the user
 * Call:   include 'Plot.gewicht.php'
 * @package Runalyze\Plugins\Panels
 */

$Factory = new PluginFactory();
$Plugin = $Factory->newInstance('RunalyzePluginPanel_Sportler');
$Wunschgewicht = $Plugin->Configuration()->value('wunschgewicht');

if ($Plugin->Configuration()->value('plot_timerange') > 0)
	$QueryEnd = 'WHERE `time` > '.(time() - DAY_IN_S * (int)$Plugin->Configuration()->value('plot_timerange')).' ORDER BY `time` DESC';
else
	$QueryEnd = 'ORDER BY `time` DESC LIMIT '.((int)$Plugin->Configuration()->value('plot_points'));

$Data     = array_reverse( DB::getInstance()->query('SELECT weight,pulse_rest,time FROM `'.PREFIX.'user` '.$QueryEnd)->fetchAll() );
$Weights  = array();
$HRrests  = array();

if (count($Data) == 1)
	$Data[1] = $Data[0];

if (!empty($Data)) {
	foreach ($Data as $D) {
		$Weights[$D['time'].'000'] = (double)$D['weight'];
		$HRrests[$D['time'].'000'] = (int)$D['pulse_rest'];
	}
}

$Labels = array_keys($Weights);
foreach ($Labels as $i => &$value)
	if ($i != 0 && $i != count($Labels)-1)
		$value = '';

$Plot = new Plot("sportler_weights", 320, 150);
if ($Plugin->Configuration()->value('use_weight'))
	$Plot->Data[] = array('label' => __('Weight'), 'color' => '#008', 'data' => $Weights);
if ($Plugin->Configuration()->value('use_pulse'))
	$Plot->Data[] = array('label' => __('Resting HR'), 'color' => '#800', 'data' => $HRrests, 'yaxis' => 2);

$Plot->setMarginForGrid(5);

$Plot->setXLabels($Labels);
$Plot->setXAxisTimeFormat('%m/%y');
$Plot->setXAxisMaxToToday();
$Plot->Options['xaxis']['labelWidth'] = 50;
//$Plot->Options['xaxis']['tickLength'] = 3;
$Plot->Options['series']['curvedLines']['fit'] = true;

$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, 'kg', 1);
$Plot->setYTicks(1, 2, 0);
$Plot->addYAxis(2, 'right', false);
$Plot->addYUnit(2, 'bpm', 0);
$Plot->setYTicks(2, 1, 0);

if ($Wunschgewicht > 1) {
	$Plot->addThreshold('y1', $Wunschgewicht);
	$Plot->addMarkingArea('y1', $Wunschgewicht, 0);
}

if (empty($Data)) {
	$Plot->raiseError( __('No data available.') );
} elseif (min(min($Weights), min($HRrests)) == 0 || count($Weights) <= 1) {
	$Plot->setZeroPointsToNull();
	$Plot->lineWithPoints();
	$Plot->Options['series']['curvedLines']['apply'] = false;
}

$Plot->outputJavaScript( true );
<?php
/**
 * Draw personal bests for a given distance
 * Call:   include Plot.Bestzeit.php
 */

use Runalyze\Configuration;
use Runalyze\Activity\Distance;

if (!($this instanceof RunalyzePluginStat_Wettkampf)) {
	die('Not allowed.');
}

$distance    = !is_numeric($_GET['km']) ? 10 : (float)$_GET['km'];
$Dates       = array();
$Results     = array();
$label       = str_replace('&nbsp;', ' ', sprintf( __('Result over %s'), (new Distance($distance))->stringAuto(true, 1) ) );
$trend       = str_replace('&nbsp;', ' ', sprintf( __('Trend over %s'), (new Distance($distance))->stringAuto(true, 1) ) );
$titleCenter = str_replace('&nbsp;', ' ', sprintf( __('Result overs %s'), (new Distance($distance))->stringAuto(true, 1) ) );
$timeFormat  = '%M:%S';

$competitions = $this->RaceContainer->races($distance);

if (!empty($competitions)) {
	foreach ($competitions as $competition) {
		if (!$this->isFunCompetition($competition['id'])) {
			$Dates[]   = $competition['time'];
			$Results[$competition['time'].'000'] = ($competition['s']*1000); // Attention: timestamp(0) => 1:00:00
		}
	}

	if (!empty($Results) && max($Results) > 3600*1000)
		$timeFormat = '%H:%M:%S';
}

$Plot = new Plot("bestzeit".$distance*1000, 480, 190);
$Plot->Data[] = array('label' => $label, 'data' => $Results);

$Plot->setMarginForGrid(5);
$Plot->setXAxisAsTime();

if (count($Results) == 1)
	$Plot->setXAxisTimeFormat('%d.%m.%y');

$Plot->addYAxis(1, 'left');
$Plot->setYAxisAsTime(1);
$Plot->setYAxisTimeFormat($timeFormat, 1);

$Plot->smoothing(false);
$Plot->lineWithPoints();
$Plot->setTitle($titleCenter);

$Plot->outputJavaScript();
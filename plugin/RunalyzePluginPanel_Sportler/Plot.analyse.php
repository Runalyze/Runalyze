<?php
/**
 * Draw weight and rest-heartrate for the user
 * Call:   include 'Plot.gewicht.php'
 */

// TODO: Config: num=20
$data_num = 20;
$Data     = Mysql::getInstance()->fetchAsArray('SELECT fat,water,muscles,time FROM `'.PREFIX.'user` ORDER BY `time` DESC LIMIT '.$data_num);

$Adiposes   = array();
$Water      = array();
$Muscles    = array();

if (count($Data) == 1)
	$Data[1] = $Data[0];

if (!empty($Data)) {
	foreach ($Data as $D) {
		$Adiposes[$D['time'].'000'] = $D['fat'];
		$Water[$D['time'].'000']    = $D['water'];
		$Muscles[$D['time'].'000']  = $D['muscles'];
	}
} 

$Labels = array_keys($Water);
foreach ($Labels as $i => &$value)
	if ($i != 0 && $i != count($Labels)-1)
		$value = '';

$Plot = new Plot("sportler_analyse", 320, 148);

$Plot->Data[] = array('label' => 'Fett (links)&nbsp;&nbsp;&nbsp;&nbsp;', 'color' => '#FF3232', 'data' => $Adiposes);
$Plot->Data[] = array('label' => 'Wasser', 'color' => '#3232FF', 'data' => $Water, 'yaxis' => 2);
$Plot->Data[] = array('label' => 'Muskeln', 'color' => '#21FF21', 'data' => $Muscles, 'yaxis' => 2);

$Plot->setMarginForGrid(5);

//$Plot->hideXLabels();
$Plot->setXLabels($Labels);
$Plot->setXAxisTimeFormat('%m/%y');
$Plot->setXAxisMaxToToday();
$Plot->Options['xaxis']['labelWidth'] = 50;
$Plot->Options['xaxis']['tickLength'] = 3;
$Plot->Options['yaxis']['autoscaleMargin'] = 0.1;

$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, '%');
$Plot->setYTicks(1, 1, 0);
$Plot->addYAxis(2, 'right');
$Plot->addYUnit(2, '%');
$Plot->setYTicks(1, 1, 0);

if(empty($Data)) 
	$Plot->raiseError('Es wurden keine Daten über den Sportler hinterlegt');

$Plot->outputJavaScript( true );
?>
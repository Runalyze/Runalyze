<?php
/**
 * Draw weight and rest-heartrate for the user
 * Call:   include 'Plot.gewicht.php'
 */

// TODO: Config: num=20
$data_num = 20;
$Data     = Mysql::getInstance()->fetchAsArray('SELECT fat,water,muscles FROM `'.PREFIX.'user` ORDER BY `time` DESC LIMIT '.$data_num);

$Adiposes   = array();
$Water      = array();
$Muscles    = array();

if (!empty($Data)) {
	foreach ($Data as $D) {
		$Adiposes[] = $D['fat'];
		$Water[]    = $D['water'];
		$Muscles[]  = $D['muscles'];
	}

	$Adiposes = array_reverse($Adiposes);
	$Water    = array_reverse($Water);
	$Muscles  = array_reverse($Muscles);
}


$Plot = new Plot("sportler_analyse", 320, 148);
$Plot->Data[] = array('label' => 'Fett (links)&nbsp;&nbsp;&nbsp;&nbsp;', 'color' => '#FF3232', 'data' => $Adiposes);
$Plot->Data[] = array('label' => 'Wasser', 'color' => '#3232FF', 'data' => $Water, 'yaxis' => 2);
$Plot->Data[] = array('label' => 'Muskeln', 'color' => '#21FF21', 'data' => $Muscles, 'yaxis' => 2);

$Plot->setMarginForGrid(5);
$Plot->setLinesFilled(array(0,1));
$Plot->hideXLabels();
$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, '%');
$Plot->setYTicks(1, 1, 0);
$Plot->addYAxis(2, 'right');
$Plot->addYUnit(2, '%');
$Plot->setYTicks(1, 1, 0);

$Plot->outputJavaScript();
?>
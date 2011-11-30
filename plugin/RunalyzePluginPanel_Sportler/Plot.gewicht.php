<?php
/**
 * Draw weight and rest-heartrate for the user
 * Call:   include 'Plot.gewicht.php'
 */

// TODO: Config: num=20
$data_num = 20;
$Data     = Mysql::getInstance()->fetchAsArray('SELECT weight,pulse_rest FROM `'.PREFIX.'user` ORDER BY `time` DESC LIMIT '.$data_num);
$Weights  = array();
$HRrests  = array();

if (!empty($Data)) {
	foreach ($Data as $D) {
		$Weights[] = (double)$D['weight'];
		$HRrests[] = (int)$D['pulse_rest'];
	}

	$Weights = array_reverse($Weights);
	$HRrests = array_reverse($HRrests);
} 


$Plugin = Plugin::getInstanceFor('RunalyzePluginPanel_Sportler');
$Plugin_conf = $Plugin->get('config');
$Wunschgewicht = $Plugin_conf['wunschgewicht']['var'];


$Plot = new Plot("sportler_weights", 320, 148);
$Plot->Data[] = array('label' => 'Ruhepuls', 'color' => '#800', 'data' => $HRrests);
$Plot->Data[] = array('label' => 'Gewicht', 'color' => '#008', 'data' => $Weights, 'yaxis' => 2);

$Plot->setMarginForGrid(5);
$Plot->hideXLabels();
$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, 'bpm');
$Plot->setYTicks(1, 1, 0);
$Plot->addYAxis(2, 'right');
$Plot->addYUnit(2, 'kg');
$Plot->setYTicks(2, 2, 0);

if ($Wunschgewicht > 1) {
	$Plot->addThreshold('y2', $Wunschgewicht);
	$Plot->addMarkingArea('y2', $Wunschgewicht, 0);
}

if(empty($Data)) 
	$Plot->raiseError('Es wurden keine Daten über den Sportler hinterlegt');

$Plot->outputJavaScript();
?>
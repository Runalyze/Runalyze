<?php
/**
 * Draw weight and rest-heartrate for the user
 * Call:   include 'Plot.gewicht.php'
 */

// TODO: Config: num=20
$data_num = 20;
$Data     = Mysql::getInstance()->fetchAsArray('SELECT weight,pulse_rest,time FROM `'.PREFIX.'user` ORDER BY `time` DESC LIMIT '.$data_num);
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

$Plugin = Plugin::getInstanceFor('RunalyzePluginPanel_Sportler');
$Plugin_conf = $Plugin->get('config');
$Wunschgewicht = $Plugin_conf['wunschgewicht']['var'];

$Labels = array_keys($Weights);
foreach ($Labels as $i => &$value)
	if ($i != 0 && $i != count($Labels)-1)
		$value = '';

$Plot = new Plot("sportler_weights", 320, 148);
if ($Plugin_conf['use_weight']['var'])
	$Plot->Data[] = array('label' => 'Gewicht', 'color' => '#008', 'data' => $Weights);
if ($Plugin_conf['use_pulse']['var'])
	$Plot->Data[] = array('label' => 'Ruhepuls', 'color' => '#800', 'data' => $HRrests, 'yaxis' => 2);

$Plot->setMarginForGrid(5);

$Plot->setXLabels($Labels);
$Plot->setXAxisTimeFormat('%m/%y');
$Plot->setXAxisMaxToToday();
$Plot->Options['xaxis']['labelWidth'] = 50;
$Plot->Options['xaxis']['tickLength'] = 3;

$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, 'bpm');
$Plot->setYTicks(1, 2, 0);
$Plot->addYAxis(2, 'right', false);
$Plot->addYUnit(2, 'kg');
$Plot->setYTicks(2, 1, 0);

if ($Wunschgewicht > 1) {
	$Plot->addThreshold('y1', $Wunschgewicht);
	$Plot->addMarkingArea('y1', $Wunschgewicht, 0);
}

if(empty($Data)) 
	$Plot->raiseError('Es wurden keine Daten über den Sportler hinterlegt');

$Plot->outputJavaScript( true );
?>
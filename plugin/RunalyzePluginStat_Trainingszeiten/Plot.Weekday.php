<?php
/**
 * Draw total time of training for each day of a week for the user
 * Include:   inc/draw/Plot.Weekday.php
 */

$titleCenter = 'Trainingszeit pro Wochentag [in h]';
$yAxis       = array();
$xAxis       = array();

for ($w = 1; $w <= 7; $w++)
	$xAxis[] = array($w-1, Time::Weekday($w, true));

$Sports = Mysql::getInstance()->fetchAsArray('SELECT `id`, `name` FROM `'.PREFIX.'sport` ORDER BY `id` ASC');
foreach ($Sports as $sport) {
	$id = $sport['name'];
	$yAxis[$id] = array(0, 0, 0, 0, 0, 0, 0);

	$data = Mysql::getInstance()->fetchAsArray('SELECT SUM(`s`) as `value`, (DAYOFWEEK(FROM_UNIXTIME(`time`))-1) as `day` FROM `'.PREFIX.'training` WHERE `sportid`="'.$sport['id'].'" GROUP BY `day` ORDER BY ((`day`+6)%7) ASC');

	foreach ($data as $dat)
		$yAxis[$id][($dat['day']+6)%7] = $dat['value']/3600;
}

$Plot = new Plot("weekday", 350, 190);

foreach ($yAxis as $key => $data)
	$Plot->Data[] = array('label' => $key, 'data' => $data);

$Plot->hideLegend();
$Plot->setMarginForGrid(5);
$Plot->setXLabels($xAxis);
$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, 'h');
$Plot->setYTicks(1, 1, 0);

$Plot->showBars(true);
$Plot->stacked();

$error = true;
foreach($yAxis as $t) 
	foreach($t as $e) 
		if($e != "0") 
			$error = false;

if($error === true) 
	$Plot->raiseError('Keine Trainingsdaten vorhanden.');

$Plot->outputJavaScript();
?>
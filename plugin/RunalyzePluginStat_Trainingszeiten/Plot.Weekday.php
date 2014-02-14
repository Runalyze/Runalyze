<?php
/**
 * Draw total time of training for each day of a week for the user
 * Include:   inc/draw/Plot.Weekday.php
 * @package Runalyze\Plugins\Stats
 */

$titleCenter = 'Trainingszeit pro Wochentag [in h]';
$yAxis       = array();
$xAxis       = array();

for ($w = 1; $w <= 7; $w++)
	$xAxis[] = array($w-1, Time::Weekday($w, true));

if ($this->sportid > 0) {
	$Sports = DB::getInstance()->query('SELECT `id`, `name` FROM `'.PREFIX.'sport` WHERE `id`='.(int)$this->sportid)->fetchAll();
} else {
	$Sports = DB::getInstance()->query('SELECT `id`, `name` FROM `'.PREFIX.'sport` ORDER BY `id` ASC')->fetchAll();
}

// TODO: Should be possible with one query?
foreach ($Sports as $sport) {
	$id = $sport['name'];
	$yAxis[$id] = array(0, 0, 0, 0, 0, 0, 0);

	$data = DB::getInstance()->query('
		SELECT
			SUM(`s`) as `value`,
			(DAYOFWEEK(FROM_UNIXTIME(`time`))-1) as `day`
		FROM `'.PREFIX.'training`
		WHERE
			`sportid`="'.$sport['id'].'"
			'.($this->year > 0 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.(int)$this->year : '').'
		GROUP BY `day`
		ORDER BY ((`day`+6)%7) ASC
	')->fetchAll();

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
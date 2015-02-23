<?php
/**
 * Draw total time of training for each day of a week for the user
 * Include:   inc/draw/Plot.Weekday.php
 * @package Runalyze\Plugins\Stats
 */

$titleCenter = __('Activity [in h] by weekday');
$yAxis       = array();
$xAxis       = array();

for ($w = 1; $w <= 7; $w++)
	$xAxis[] = array($w-1, Time::Weekday($w, true));

if ($this->sportid > 0) {
	$Sports = array(SportFactory::DataFor((int)$this->sportid));
} else {
	$Sports = SportFactory::AllSports();
}

$Query = DB::getInstance()->prepare(
	'SELECT
		SUM(`s`) as `value`,
		(DAYOFWEEK(FROM_UNIXTIME(`time`))-1) as `day`
	FROM `'.PREFIX.'training`
	WHERE
		`sportid`=:id
		'.($this->year > 0 ? 'AND `time` BETWEEN UNIX_TIMESTAMP(\''.(int)$this->year.'-01-01\') AND UNIX_TIMESTAMP(\''.((int)$this->year+1).'-01-01\')-1' : '').'
	GROUP BY `day`
	ORDER BY ((`day`+6)%7) ASC'
);

// TODO: Should be possible with one query?
foreach ($Sports as $sport) {
	$id = $sport['name'];
	$yAxis[$id] = array(0, 0, 0, 0, 0, 0, 0);

	$Query->execute(array(':id' => $sport['id']));
	$data = $Query->fetchAll();

	foreach ($data as $dat)
		$yAxis[$id][($dat['day']+6)%7] = $dat['value']/3600;
}

$Plot = new Plot("weekday", 350, 190);

$max = 0;
foreach ($yAxis as $key => $data) {
	$Plot->Data[] = array('label' => $key, 'data' => $data);
	$max += max($data);
}

$Plot->setLegendAsTable();
$Plot->setMarginForGrid(5);
$Plot->setXLabels($xAxis);
$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, 'h');
$Plot->setYTicks(1, 1, 0);
$Plot->setYLimits(1, 0, $max);

$Plot->showBars(true);
$Plot->stacked();

$error = true;
foreach($yAxis as $t) 
	foreach($t as $e) 
		if($e != "0") 
			$error = false;

if($error === true)
	$Plot->raiseError( __('No data available.') );

$Plot->outputJavaScript();
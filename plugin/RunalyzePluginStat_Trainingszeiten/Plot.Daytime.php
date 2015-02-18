<?php
/**
 * Draw total time of training for each hour of a day for the user
 * Include:   inc/draw/Plot.Daytime.php
 * @package Runalyze\Plugins\Stats
 */

$titleCenter = __('Activity [in h] by day time');
$xAxis       = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);
$yAxis       = array();

if ($this->sportid > 0) {
	$Sports = array(SportFactory::DataFor((int)$this->sportid));
} else {
	$Sports = SportFactory::AllSports();
}

$Query = DB::getInstance()->prepare(
	'SELECT
		SUM(1) as `num`,
		SUM(`s`) as `value`,
		HOUR(FROM_UNIXTIME(`time`)) as `h`
	FROM `'.PREFIX.'training`
	WHERE
		`sportid`=:id AND
		(HOUR(FROM_UNIXTIME(`time`))!=0 OR MINUTE(FROM_UNIXTIME(`time`))!=0)
		'.($this->year > 0 ? 'AND `time` BETWEEN UNIX_TIMESTAMP(\''.(int)$this->year.'-01-01\') AND UNIX_TIMESTAMP(\''.((int)$this->year+1).'-01-01\')-1' : '').'
	GROUP BY `h`
	ORDER BY `h` ASC'
);

foreach ($Sports as $sport) {
	$id = $sport['name'];
	$yAxis[$id] = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

	$Query->execute(array(':id' => $sport['id']));
	$data = $Query->fetchAll();

	foreach ($data as $dat)
		$yAxis[$id][$dat['h']] = $dat['value']/3600;
}

$Plot = new Plot("daytime", 350, 190);

$max = 0;
foreach ($yAxis as $key => $data) {
	$Plot->Data[] = array('label' => $key, 'data' => $data);
	$max += max($data);
}

$Plot->setLegendAsTable();
$Plot->setMarginForGrid(5);
$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, 'h');
$Plot->setYTicks(1, 1, 0);
$Plot->setYLimits(1, 0, $max);

$Plot->showBars(true);
$Plot->stacked();

$error = true;
foreach ($yAxis as $t) 
	foreach ($t as $e) 
		if ($e != "0") 
			$error = false;

if ($error === true) 
	$Plot->raiseError( __('No data available.') );


$Plot->outputJavaScript();
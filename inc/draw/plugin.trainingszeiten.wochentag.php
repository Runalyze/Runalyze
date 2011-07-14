<?php
/**
 * Draw total time of training for each day of a week for the user
 * Call:   inc/draw/plugin.trainingszeiten.wochentag.php
 */
require_once '../class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);

$Draw = new Draw(350, 190);
$Draw->padding['right']  = 5;
$Draw->padding['left']   = 20;

$titleError  = '';
$titleCenter = 'Trainingszeit pro Wochentag [in h]';
$xAxis       = array('Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So');
$yAxis       = array();

$Sports = Mysql::getInstance()->fetchAsArray('SELECT `id`, `name` FROM `'.PREFIX.'sports` ORDER BY `id` ASC');
foreach ($Sports as $sport) {
	$id = $sport['name'];
	$yAxis[$id] = array('Mo' => 0, 'Di' => 0, 'Mi' => 0, 'Do' =>0, 'Fr' => 0, 'Sa' => 0, 'So' => 0);

	$data = Mysql::getInstance()->fetchAsArray('SELECT SUM(`dauer`) as `value`, (DAYOFWEEK(FROM_UNIXTIME(`time`))-1) as `day` FROM `'.PREFIX.'training` WHERE `sportid`="'.$sport['id'].'" GROUP BY `day` ORDER BY ((`day`+6)%7) ASC');
	foreach ($data as $dat) {
		$day = Helper::Weekday($dat['day'], true);
		$yAxis[$id][$day] = $dat['value']/3600;
	}
}

if (!empty($Sports)) {
	foreach ($yAxis as $key => $data)
		$Draw->pData->addPoints($data, $key);

	$Draw->pData->addPoints($xAxis, 'Wochentag');
	$Draw->pData->setAbscissa('Wochentag');
} else {
	$titleError = 'Es sind keine Daten vorhanden.';
}

$ScaleFormat    = array("Mode" => SCALE_MODE_ADDALL_START0);

$Draw->startImage();
$Draw->drawScale($ScaleFormat);
$Draw->drawStackedBarChart();

$Draw->drawCenterTitle($titleCenter);

if ($titleError != '')
	$Draw->drawCenteredTitle($titleError);

$Draw->finish();
?>
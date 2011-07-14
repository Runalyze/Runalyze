<?php
/**
 * Draw total time of training for each hour of a day for the user
 * Call:   inc/draw/plugin.trainingszeiten.uhrzeit.php
 */
require_once '../class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);

$Draw = new Draw(350, 190);
$Draw->padding['right']  = 5;
$Draw->padding['left']   = 20;

$titleError  = '';
$titleCenter = 'Trainingszeit nach Uhrzeit [in h]';
$xAxis       = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);
$yAxis       = array();

$Sports = Mysql::getInstance()->fetchAsArray('SELECT `id`, `name` FROM `'.PREFIX.'sports` ORDER BY `id` ASC');
foreach ($Sports as $sport) {
	$id = $sport['name'];
	$yAxis[$id] = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

	$data = Mysql::getInstance()->fetchAsArray('SELECT SUM(1) as `num`, SUM(`dauer`) as `value`, HOUR(FROM_UNIXTIME(`time`)) as `h` FROM `'.PREFIX.'training` WHERE `sportid`="'.$sport['id'].'" AND (HOUR(FROM_UNIXTIME(`time`))!=0 OR MINUTE(FROM_UNIXTIME(`time`))!=0) GROUP BY `h` ORDER BY `h` ASC');
	foreach ($data as $dat)
		$yAxis[$id][$dat['h']] = $dat['value']/3600;
}

if (!empty($Sports)) {
	foreach ($yAxis as $key => $data)
		$Draw->pData->addPoints($data, $key);

	$Draw->pData->addPoints($xAxis, 'Uhrzeit');
	$Draw->pData->setAbscissa('Uhrzeit');
} else {
	$titleError = 'Es sind keine Daten vorhanden.';
}

$LegendFormat   = array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_VERTICAL);
$ScaleFormat    = array("Mode" => SCALE_MODE_ADDALL_START0);

$Draw->startImage();
$Draw->drawScale($ScaleFormat);
$Draw->drawStackedBarChart();

$Draw->drawCenterTitle($titleCenter);
$Draw->pImage->drawLegend(30, 15, $LegendFormat);

if ($titleError != '')
	$Draw->drawCenteredTitle($titleError);

$Draw->finish();
?>
<?php
/**
 * Draw weather-plot
 * Call:   include 'Plot.Year.php'
 */

$Year         = (int)$_GET['y'];
$Temperatures = array();

$Data = Mysql::getInstance()->fetchAsArray('SELECT time*1000 as time, DAYOFYEAR(FROM_UNIXTIME(`time`)) as `d`, AVG(`temperature`) as `temp` FROM `'.PREFIX.'training` WHERE !ISNULL(`temperature`) AND YEAR(FROM_UNIXTIME(`time`))='.$Year.' GROUP BY `d` ORDER BY `d` ASC');
foreach ($Data as $dat)
	$Temperatures[$dat['time']] = (int)$dat['temp'];

$Plot = new Plot("year".$Year, 780, 240);
$Plot->Data[] = array('label' => 'Temperaturen '.$Year, 'data' => $Temperatures);

$Plot->setMarginForGrid(5);
$Plot->setXAxisAsTime();
$Plot->setXAxisLimitedTo($Year);
$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, '°C');
$Plot->setYTicks(1, 5, 0);

$Plot->addThreshold('y', 0);
$Plot->addMarkingArea('y', -99, 0);
$Plot->showPoints(2);

if(empty($Data))
	$Plot->raiseError('Es sind keine Daten vorhanden.');

$Plot->outputJavaScript();
?>
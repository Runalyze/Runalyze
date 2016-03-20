<?php
/**
 * Draw weather-plot
 * Call:   include 'Plot.Year.php'
 * @package Runalyze\Plugins\Stats
 */
use Runalyze\Activity\Temperature;

$Temperature = new Temperature;
$Year         = (int)$_GET['y'];
$Temperatures = array();

$Query = '
	SELECT
		time*1000 as time,
		DAYOFYEAR(FROM_UNIXTIME(`time`)) as `d`,
		AVG(`temperature`) as `temp`
	FROM `'.PREFIX.'training`
	WHERE
		`accountid`='.SessionAccountHandler::getId().' AND
		!ISNULL(`temperature`) AND
		`time` BETWEEN UNIX_TIMESTAMP(\''.(int)$Year.'-01-01\') AND UNIX_TIMESTAMP(\''.((int)$Year+1).'-01-01\')-1
	GROUP BY `d`
	ORDER BY `d` ASC';

$Data = DB::getInstance()->query($Query)->fetchAll();

foreach ($Data as $dat)
	$Temperatures[$dat['time']] = $Temperature->format((int)$dat['temp'], false);

$Plot = new Plot("year".$Year, 780, 240);
$Plot->Data[] = array('label' => __('Temperatures').' '.$Year, 'data' => $Temperatures);

$Plot->setMarginForGrid(5);
$Plot->setXAxisAsTime();
$Plot->setXAxisLimitedTo($Year);
$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, $Temperature->unit(), 0);
$Plot->setYTicks(1, 5, 0);

$Plot->addThreshold('y', 0);
$Plot->addMarkingArea('y', -99, 0);
$Plot->showPoints(2);
$Plot->smoothing(false);

if(empty($Data))
	$Plot->raiseError( __('No data available.') );

$Plot->outputJavaScript();
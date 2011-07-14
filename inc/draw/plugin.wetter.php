<?php
/**
 * Draw weather-plot
 * Call:   inc/draw/plugin.wetter.php?y=[&m=m]
 */
require_once '../class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);

$Draw = new Draw(780, 240);

$titleCenter  = '';
$titleError   = '';
$isDrawable   = true;
$plotChart    = false;
$Year         = (int)$_GET['y'];
$Months       = array();
$Temperatures = array();
$TempPalette  = array("R" => 136, "G" => 0, "B" => 0);

if ($_GET['all'] == 'all') {
	$titleCenter = 'Durchschnittstemperaturen';
	$plotChart = true;

	for ($m = 1; $m <= 12; $m++) {
		$Months[] = Helper::Month($m, true);

		for ($y = START_YEAR, $n = date('Y'); $y <= $n; $y++)
			$Temperatures[$y] = array(VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID,VOID);
	}

	$Data = Mysql::getInstance()->fetchAsArray('SELECT YEAR(FROM_UNIXTIME(`time`)) as `y`, MONTH(FROM_UNIXTIME(`time`)) as `m`, AVG(`temperatur`) as `temp` FROM `'.PREFIX.'training` WHERE !ISNULL(`temperatur`) GROUP BY `y`, `m` ORDER BY `y` ASC, `m` ASC');
	foreach ($Data as $dat)
		$Temperatures[$dat['y']][$dat['m'] - 1] = $dat['temp'];

	for ($y = START_YEAR, $n = date('Y'); $y <= $n; $y++) {
		if (min($Temperatures[$y]) != VOID || max($Temperatures[$y]) != VOID)
			$Draw->pData->addPoints($Temperatures[$y], $y);
	}
}

elseif ($Year >= START_YEAR && $Year <= date('Y')) {
	if ($_GET['m'] == 'm') {
		$titleCenter = 'Durchschnittstemperaturen '.$Year;

		for ($m = 1; $m <= 12; $m++) {
			$Months[] = Helper::Month($m, true);
			$Temperatures[] = VOID;
		}

		$Data = Mysql::getInstance()->fetchAsArray('SELECT MONTH(FROM_UNIXTIME(`time`)) as `m`, AVG(`temperatur`) as `temp` FROM `'.PREFIX.'training` WHERE !ISNULL(`temperatur`) AND YEAR(FROM_UNIXTIME(`time`))='.$Year.' GROUP BY `m` ORDER BY `m` ASC');
		foreach ($Data as $dat)
			$Temperatures[$dat['m'] - 1] = $dat['temp'];
	}

	else {
		$titleCenter = 'Temperaturen '.$Year;
		$plotChart   = true;

		for ($d = 1; $d <= 366; $d++) {
			$Months[] = Helper::Month(ceil(12*$d/366), true);
			$Temperatures[] = VOID;
		}

		$Data = Mysql::getInstance()->fetchAsArray('SELECT DAYOFYEAR(FROM_UNIXTIME(`time`)) as `d`, AVG(`temperatur`) as `temp` FROM `'.PREFIX.'training` WHERE !ISNULL(`temperatur`) AND YEAR(FROM_UNIXTIME(`time`))='.$Year.' GROUP BY `d` ORDER BY `d` ASC');
		foreach ($Data as $dat)
			$Temperatures[$dat['d'] - 1] = $dat['temp'];
	}

	$Draw->pData->addPoints($Temperatures, 'Temperaturen');
	$Draw->pData->setPalette('Temperaturen', $TempPalette);
}

else {
	$isDrawable = false;
	$titleError = 'Das Jahr liegt au&#223;erhalb meiner M&#246;glichkeiten.';
}

$Draw->setCaching(false);
$Draw->startImage();

if ($isDrawable) {
	$ScaleFormat    = array(
		"Factors" => array(5),
		"Mode" => SCALE_MODE_MANUAL,
		"ManualScale" => array(0 => array(
			"Min" => -10,
			"Max" => 30)),
		"LabelingMethod" => LABELING_DIFFERENT,
		"XMargin" => 0);
	$TresholdFormat = array(
		"R" => 180, "G" => 0, "B" => 0, "Alpha" => 50);
	$LegendFormat   = array(
		"Style" => LEGEND_NOBORDER, "Mode" => LEGEND_VERTICAL);
	$PlotFormat     = array(
		"PlotSize" => 1);
	$SplineFormat   = array(
		"BreakVoid" => FALSE);
	$SplinePalette   = array(
		"R" => 136, "G" => 0, "B" => 0, "Alpha" => 40);

	$Draw->pData->addPoints($Months, 'Monate');
	$Draw->pData->setAxisUnit(0, '&#176;C');
	$Draw->pData->setAbscissa('Monate');

	$Draw->drawScale($ScaleFormat);
	$Draw->drawCenterTitle($titleCenter);
	if ($plotChart) {
		$Draw->pData->setPalette('Temperaturen', $SplinePalette);
		$Draw->pImage->drawSplineChart($SplineFormat);
		$Draw->pData->setPalette('Temperaturen', $TempPalette);
		$Draw->pImage->drawPlotChart($PlotFormat);
	} else {
		$Draw->pImage->drawLineChart();
	}
	$Draw->pImage->drawThreshold(0, $TresholdFormat);

	if ($_GET['all'] == 'all')
		$Draw->pImage->drawLegend(50, 20, $LegendFormat);
}

if ($titleError != '')
	$Draw->drawCenteredTitle($titleError);

$Draw->finish();
?>
<?php
/**
 * Draw kilometer per month
 * Include:   inc/draw/Plot.MonthKM.php, $_GET['y'] = 2011
 */
$Year = (int)$_GET['y'];

$titleCenter           = 'Monatskilometer '.$Year;
$Months                = array();
$Kilometers            = array();
$KilometersCompetition = array();
$possibleKM            = 0;

if ($Year >= START_YEAR && $Year <= date('Y') && START_TIME != time()) {
	for ($m = 1; $m <= 12; $m++) {
		$Months[]                = array($m-1, Helper::Month($m, true));
		$Kilometers[]            = 0;
		$KilometersCompetition[] = 0;
	}

	$Data = Mysql::getInstance()->fetchAsArray('SELECT (`typeid` = '.CONF_WK_TYPID.') as `wk`, SUM(`distance`) as `km`, MONTH(FROM_UNIXTIME(`time`)) as `m` FROM `'.PREFIX.'training` WHERE `sportid`='.CONF_RUNNINGSPORT.' AND YEAR(FROM_UNIXTIME(`time`))='.$Year.' GROUP BY (`typeid` = '.CONF_WK_TYPID.'), MONTH(FROM_UNIXTIME(`time`))');
	foreach ($Data as $dat) {
		if ($dat['wk'] == 1)
			$KilometersCompetition[$dat['m']-1] = $dat['km'];
		else
			$Kilometers[$dat['m']-1] = $dat['km'];
	}

	if (CONF_RECHENSPIELE) {
		$TrimpPerMonth = Helper::TRIMP(0, 365 * Helper::CTL() / 12);
		$AvgMonthPace  = Mysql::getInstance()->fetchSingle('SELECT AVG(`s`/60/`distance`) AS `avg` FROM `'.PREFIX.'training` WHERE `time` > '.(time()-30*DAY_IN_S).' AND `sportid`='.CONF_RUNNINGSPORT);
		$possibleKM    = 10 * round($TrimpPerMonth / $AvgMonthPace['avg'] / 10);
	}
}

$Plot = new Plot("monthKM".$Year, 800, 500);
$Plot->Data[] = array('label' => 'Wettkampf-Kilometer', 'data' => $KilometersCompetition);
$Plot->Data[] = array('label' => 'Kilometer', 'data' => $Kilometers);

$Plot->setMarginForGrid(5);
$Plot->setXLabels($Months);
$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, 'km');
$Plot->setYTicks(1, 10, 0);

$Plot->showBars(true);
$Plot->stacked();

if ($possibleKM > 0)
	$Plot->addTreshold('y', $possibleKM);

$Plot->outputJavaScript();
?>
<?php
/**
 * Draw kilometer per week
 * Include:   inc/draw/Plot.WeekKM.php, $_GET['y'] = 2011
 */
$Year = (int)$_GET['y'];

$titleCenter           = 'Wochenkilometer '.$Year;
$Weeks                 = array();
$Kilometers            = array();
$KilometersCompetition = array();
$possibleKM            = 0;

if ($Year >= START_YEAR && $Year <= date('Y') && START_TIME != time()) {	
	for ($w = 0; $w <= 53; $w++) {
		$Kilometers[]            = 0;
		$KilometersCompetition[] = 0;
	}

	$Data = Mysql::getInstance()->fetchAsArray('SELECT (`typeid` = '.CONF_WK_TYPID.') as `wk`, SUM(`distance`) as `km`, WEEK(FROM_UNIXTIME(`time`),1) as `w` FROM `'.PREFIX.'training` WHERE `sportid`='.CONF_RUNNINGSPORT.' AND YEAR(FROM_UNIXTIME(`time`))='.$Year.' GROUP BY (`typeid` = '.CONF_WK_TYPID.'), WEEK(FROM_UNIXTIME(`time`),1)');
	foreach ($Data as $dat) {
		if ($dat['wk'] == 1)
			$KilometersCompetition[$dat['w']] = $dat['km'];
		else
			$Kilometers[$dat['w']] = $dat['km'];
	}

	if (CONF_RECHENSPIELE) {
		$TrimpPerMonth = Helper::TRIMP(0, 7 * Helper::CTL());
		$AvgMonthPace  = Mysql::getInstance()->fetchSingle('SELECT AVG(`s`/60/`distance`) AS `avg` FROM `'.PREFIX.'training` WHERE `time` > '.(time()-30*DAY_IN_S).' AND `sportid`='.CONF_RUNNINGSPORT);
		$possibleKM    = 5 * round($TrimpPerMonth / $AvgMonthPace['avg'] / 5);
	}
}

$Plot = new Plot("weekKM".$Year, 800, 500);
$Plot->Data[] = array('label' => 'Wettkampf-Kilometer', 'data' => $KilometersCompetition);
$Plot->Data[] = array('label' => 'Kilometer', 'data' => $Kilometers);

$Plot->setMarginForGrid(5);
$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, 'km');
$Plot->setYTicks(1, 10, 0);

$Plot->showBars();
$Plot->stacked();

if ($possibleKM > 0) {
	$Plot->addTreshold('y', $possibleKM);
}

$Plot->outputJavaScript();
?>
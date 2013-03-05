<?php
/**
 * Draw kilometer per month
 * Include:   inc/draw/Plot.MonthKM.php, $_GET['y'] = 2011
 */
$Year = (int)$_GET['y'];

$Plot = new Plot("monthKM".$Year, 800, 500);

$titleCenter           = 'Monatskilometer '.$Year;
$Months                = array();
$Kilometers            = array();
$KilometersCompetition = array();
$possibleKM            = Running::possibleKmInOneMonth();

if ($Year >= START_YEAR && $Year <= date('Y') && START_TIME != time()) {
	for ($m = 1; $m <= 12; $m++) {
		$Months[]                = array($m-1, Time::Month($m, true));
		$Kilometers[]            = 0;
		$KilometersCompetition[] = 0;
	}

	$Data = Mysql::getInstance()->fetchAsArray('SELECT (`typeid` = '.CONF_WK_TYPID.') as `wk`, SUM(`distance`) as `km`, MONTH(FROM_UNIXTIME(`time`)) as `m` FROM `'.PREFIX.'training` WHERE `sportid`='.CONF_RUNNINGSPORT.' AND YEAR(FROM_UNIXTIME(`time`))='.$Year.' GROUP BY (`typeid` = '.CONF_WK_TYPID.'), m');
	foreach ($Data as $dat) {
		if ($dat['wk'] == 1)
			$KilometersCompetition[$dat['m']-1] = $dat['km'];
		else
			$Kilometers[$dat['m']-1] = $dat['km'];
	}
} else {
	$Plot->raiseError('F&uuml;r dieses Jahr liegen keine Daten vor.');
}

$Plot->Data[] = array('label' => 'Wettkampf-Kilometer', 'data' => $KilometersCompetition);
$Plot->Data[] = array('label' => 'Kilometer', 'data' => $Kilometers);

$Plot->setMarginForGrid(5);
$Plot->setXLabels($Months);
$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, 'km');
$Plot->setYTicks(1, 10, 0);

$Plot->showBars(true);
$Plot->stacked();

$Plot->enableTracking();

$Plot->setTitle($titleCenter);

if ($possibleKM > 0) {
	$Plot->addThreshold('y', $possibleKM);
	$Plot->addAnnotation(0, $possibleKM, 'aktuelles Leistungslevel');
}

$Plot->outputJavaScript();
?>
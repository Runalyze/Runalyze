<?php
/**
 * Draw analyse of training: ATL/CTL/VDOT
 * Call:   include Plot.form.php
 */
$ATLs          = array();
$CTLs          = array();
$VDOTs         = array();
$Trimps_raw    = array();
$VDOTs_raw     = array();
$Durations_raw = array();

$Year  = (int)$_GET['y'];

if ($Year >= START_YEAR && $Year <= date('Y') && START_TIME != time()) {
	for ($d = 1; $d <= 366; $d++) {
		$Trimps_raw[]    = 0;
		$VDOTs_raw[]     = 0;
		$Durations_raw[] = 0;
	}

	for ($i = 0; $i < CONF_CTL_DAYS; $i++)
		$Trimps_raw[]    = 0;
	for ($i = 0; $i < 30;       $i++) {
		$VDOTs_raw[]     = 0;
		$Durations_raw[] = 0;
	}

	// Here ATL/CTL/VDOT will be implemented again
	// Normal functions are too slow, calling them for each day would trigger each time a query
	// - ATL/CTL: SUM(`trimp`) for CONF_ATL_DAYS / CONF_CTL_DAYS
	// - VDOT: AVG(`vdot`) der letzten 30 Tage

	$Data = Mysql::getInstance()->fetchAsArray('
		SELECT
			YEAR(FROM_UNIXTIME(`time`)) as `y`,
			DAYOFYEAR(FROM_UNIXTIME(`time`)) as `d`,
			SUM(`trimp`) as `trimp`
		FROM `'.PREFIX.'training`
		WHERE
			YEAR(FROM_UNIXTIME(`time`))='.$Year.' OR (
				YEAR(FROM_UNIXTIME(`time`))='.($Year-1).' AND
				DAYOFYEAR(FROM_UNIXTIME(`time`)) >= '.(366-CONF_CTL_DAYS).'
			)
		GROUP BY `y`, `d`');

	foreach ($Data as $dat) {
		$index = $dat['d'] - (366 - CONF_CTL_DAYS);
		if ($dat['y'] == $Year)
			$index += 366;

		$Trimps_raw[$index] = $dat['trimp'];
	}

	$Data = Mysql::getInstance()->fetchAsArray('
		SELECT
			YEAR(FROM_UNIXTIME(`time`)) as `y`,
			DAYOFYEAR(FROM_UNIXTIME(`time`)) as `d`,
			`id`,
			SUM(`vdot`*`s`) as `vdot`,
			SUM(`s`) as `s`
		FROM `'.PREFIX.'training`
		WHERE
			`vdot`>0 AND (
				YEAR(FROM_UNIXTIME(`time`))='.$Year.' OR (
					YEAR(FROM_UNIXTIME(`time`))='.($Year-1).' AND
					DAYOFYEAR(FROM_UNIXTIME(`time`)) >= '.(366-VDOT_DAYS).'
				)
			)
		GROUP BY `y`, `d`');
	foreach ($Data as $dat) {
		$index = $dat['d'] - (366 - 30);
		if ($dat['y'] == $Year)
			$index += 366;

		$VDOTs_raw[$index]     = $dat['vdot']; // Remember: These values are already multiplied with `s`
		$Durations_raw[$index] = (double)$dat['s'];
	}

	$max = 366;

	if ($Year == date('Y'))
		$max = date('z') + 1;

	for ($d = 1; $d <= $max + 1; $d++) {
		$index = Plot::dayOfYearToJStime($Year, $d);

		$ATLs[$index]    = 100 * array_sum(array_slice($Trimps_raw, $d, CONF_ATL_DAYS)) / CONF_ATL_DAYS / Trimp::maxATL();
		$CTLs[$index]    = 100 * array_sum(array_slice($Trimps_raw, $d, CONF_CTL_DAYS)) / CONF_CTL_DAYS / Trimp::maxCTL();

		$Durations_slice = array_slice($Durations_raw, $d, VDOT_DAYS);
		$VDOT_slice      = array_slice($VDOTs_raw, $d, VDOT_DAYS);

		$VDOT_sum        = array_sum($VDOT_slice);
		$Durations_sum   = array_sum($Durations_slice);

		if (count($VDOT_slice) != 0 && $Durations_sum != 0)
			$VDOTs[$index]  = JD::correctVDOT($VDOT_sum / $Durations_sum);
	}
}


$Plot = new Plot("form".$Year, 800, 450);
$Plot->Data[] = array('label' => 'Form (CTL)', 'color' => '#008800', 'data' => $CTLs);
$Plot->Data[] = array('label' => 'M&uuml;digkeit (ATL)', 'color' => '#880000', 'data' => $ATLs);
$Plot->Data[] = array('label' => 'VDOT', 'color' => '#000000', 'data' => $VDOTs, 'yaxis' => 2);

$Plot->enableTracking();

$Plot->setMarginForGrid(5);
$Plot->setLinesFilled(array(0));
$Plot->setXAxisAsTime();
$Plot->setXAxisLimitedTo($Year);

$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, '%');
$Plot->setYTicks(1, 1);
$Plot->setYLimits(1, 0, 100);
$Plot->addYAxis(2, 'right');
$Plot->setYTicks(2, 1, 1);

$Plot->setTitle('Formkurve '.$Year);

$Plot->outputJavaScript();
?>
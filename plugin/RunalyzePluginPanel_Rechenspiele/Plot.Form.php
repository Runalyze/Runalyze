<?php
/**
 * Draw analyse of training: ATL/CTL/VDOT
 * Call:   include Plot.form.php
 */
$MaxATLPoints  = 750;
$DataFailed    = false;
$ATLs          = array();
$CTLs          = array();
$VDOTs         = array();
$Trimps_raw    = array();
$VDOTs_raw     = array();
$Durations_raw = array();

$All   = ($_GET['y'] == 'all');
$Year  = $All ? date('Y') : (int)$_GET['y'];

if ($Year >= START_YEAR && $Year <= date('Y') && START_TIME != time()) {
	$MaxDays    = !$All ? 366   : (date('Y') - START_YEAR + 1)*366;
	$StartYear  = !$All ? $Year : START_YEAR;
	$EndYear    = !$All ? $Year : date('Y');
	$AddDays    = max(CONF_ATL_DAYS, CONF_CTL_DAYS, CONF_VDOT_DAYS);
	$MinAddDays = min(CONF_ATL_DAYS, CONF_CTL_DAYS, CONF_VDOT_DAYS);

	$EmptyArray    = array_fill(0, $MaxDays + $AddDays, 0);
	$Trimps_raw    = $EmptyArray;
	$VDOTs_raw     = $EmptyArray;
	$Durations_raw = $EmptyArray;

	// Here ATL/CTL/VDOT will be implemented again
	// Normal functions are too slow, calling them for each day would trigger each time a query
	// - ATL/CTL: SUM(`trimp`) for CONF_ATL_DAYS / CONF_CTL_DAYS
	// - VDOT: AVG(`vdot`) for CONF_VDOT_DAYS

	$Data = Mysql::getInstance()->fetchAsArray('
		SELECT
			YEAR(FROM_UNIXTIME(`time`))*366+DAYOFYEAR(FROM_UNIXTIME(`time`))-'.$StartYear.'*366+'.$AddDays.' as `index`,
			YEAR(FROM_UNIXTIME(`time`)) as `y`,
			DAYOFYEAR(FROM_UNIXTIME(`time`)) as `d`,
			SUM(`trimp`) as `trimp`
		FROM `'.PREFIX.'training`
		WHERE
			(
				(
					YEAR(FROM_UNIXTIME(`time`))>='.$StartYear.' AND
					YEAR(FROM_UNIXTIME(`time`))<='.$EndYear.'
				) OR (
					YEAR(FROM_UNIXTIME(`time`))='.($StartYear-1).' AND
					DAYOFYEAR(FROM_UNIXTIME(`time`)) >= '.(366-$AddDays).'
				)
			)
		GROUP BY `y`, `d`');

	foreach ($Data as $dat)
		$Trimps_raw[$dat['index']] = $dat['trimp'];

	$Data = Mysql::getInstance()->fetchAsArray('
		SELECT
			YEAR(FROM_UNIXTIME(`time`))*366+DAYOFYEAR(FROM_UNIXTIME(`time`))-'.$StartYear.'*366+'.$AddDays.' as `index`,
			YEAR(FROM_UNIXTIME(`time`)) as `y`,
			DAYOFYEAR(FROM_UNIXTIME(`time`)) as `d`,
			SUM(`vdot`*`s`*`use_vdot`) as `vdot`,
			SUM(`s`*`use_vdot`) as `s`
		FROM `'.PREFIX.'training`
		WHERE
			`vdot`>0 AND (
				(
					YEAR(FROM_UNIXTIME(`time`))>='.$StartYear.' AND
					YEAR(FROM_UNIXTIME(`time`))<='.$EndYear.'
				) OR (
					YEAR(FROM_UNIXTIME(`time`))='.($StartYear-1).' AND
					DAYOFYEAR(FROM_UNIXTIME(`time`)) >= '.(366-$AddDays).'
				)
			)
		GROUP BY `y`, `d`');
	foreach ($Data as $dat) {
		$index = $dat['index'];

		$VDOTs_raw[$index]     = $dat['vdot']; // Remember: These values are already multiplied with `s`
		$Durations_raw[$index] = (double)$dat['s'];
	}

	// Don't ask why +2 is needed
	// - and don't ask, why ATL/CTL need +1 in array_slice
	// But this way panel and plot have the same last values
	$HighestIndex  = $index + 2;

	for ($d = $AddDays; $d <= $HighestIndex; $d++) {
		$index = Plot::dayOfYearToJStime($StartYear, $d - $AddDays);

		$ATLs[$index]    = 100 * array_sum(array_slice($Trimps_raw, $d - CONF_ATL_DAYS + 1, CONF_ATL_DAYS)) / CONF_ATL_DAYS / Trimp::maxATL();
		$CTLs[$index]    = 100 * array_sum(array_slice($Trimps_raw, $d - CONF_CTL_DAYS + 1, CONF_CTL_DAYS)) / CONF_CTL_DAYS / Trimp::maxCTL();

		$Durations_slice = array_slice($Durations_raw, $d - CONF_VDOT_DAYS, CONF_VDOT_DAYS);
		$VDOT_slice      = array_slice($VDOTs_raw, $d - CONF_VDOT_DAYS, CONF_VDOT_DAYS);

		$VDOT_sum        = array_sum($VDOT_slice);
		$Durations_sum   = array_sum($Durations_slice);

		if (count($VDOT_slice) != 0 && $Durations_sum != 0)
			$VDOTs[$index]  = JD::correctVDOT($VDOT_sum / $Durations_sum);
	}
} else {
	$DataFailed = true;
}

$Plot = new Plot("form".$_GET['y'], 800, 450);

$Plot->Data[] = array('label' => 'Form (CTL)', 'color' => '#008800', 'data' => $CTLs);
if (count($ATLs) < $MaxATLPoints)
	$Plot->Data[] = array('label' => 'M&uuml;digkeit (ATL)', 'color' => '#880000', 'data' => $ATLs);
$Plot->Data[] = array('label' => 'VDOT', 'color' => '#000000', 'data' => $VDOTs, 'yaxis' => 2);

$Plot->enableTracking();
$Plot->enableSelection('x', '', false);

$Plot->setMarginForGrid(5);
$Plot->setLinesFilled(array(0));
$Plot->setXAxisAsTime();

if (!$All)
	$Plot->setXAxisLimitedTo($Year);

$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, '%');
$Plot->setYTicks(1, 1);
$Plot->setYLimits(1, 0, 100);
$Plot->addYAxis(2, 'right');
$Plot->setYTicks(2, 1, 1);

if ($All)
	$Plot->setTitle('Formkurve Gesamt');
else
	$Plot->setTitle('Formkurve '.$Year);

if ($DataFailed)
	$Plot->raiseError('Für dieses Jahr kann ich dir keine Daten zeigen.');

$Plot->outputJavaScript();
?>
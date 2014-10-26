<?php
/**
 * Draw analyse of training: ATL/CTL/VDOT
 * Call:   include Plot.form.php
 * @package Runalyze\Plugins\Panels
 */
$MaxATLPoints   = 750;
$DataFailed     = false;
$ATLs           = array();
$CTLs           = array();
$VDOTs          = array();
$Trimps_raw     = array();
$VDOTs_raw      = array();
$Durations_raw  = array();

$All   = ($_GET['y'] == 'all');
$Year  = $All ? date('Y') : (int)$_GET['y'];

if ($Year >= START_YEAR && $Year <= date('Y') && START_TIME != time()) {
	$StartYear    = !$All ? $Year : START_YEAR;
	$EndYear      = !$All ? $Year : date('Y');
	$MaxDays      = ($EndYear - $StartYear + 1)*366;
	$AddDays      = max(Configuration::Trimp()->daysForATL(), Configuration::Trimp()->daysForCTL(), Configuration::Vdot()->days());
	$StartTime    = !$All ? mktime(1,0,0,1,1,$StartYear) : START_TIME;
	$StartDay     = date('Y-m-d', $StartTime);
	$EndTime      = !$All && $Year < date('Y') ? mktime(1,0,0,12,31,$Year) : time();
	$NumberOfDays = Time::diffInDays($StartTime, $EndTime);

	$EmptyArray    = array_fill(0, $MaxDays + $AddDays, 0);
	$Trimps_raw    = $EmptyArray;
	$VDOTs_raw     = $EmptyArray;
	$Durations_raw = $EmptyArray;

	// Here VDOT will be implemented again
	// Normal functions are too slow, calling them for each day would trigger each time a query
	// - VDOT: AVG(`vdot`) for Configuration::Vdot()->days()

	$Data = DB::getInstance()->query('
		SELECT
			DATEDIFF(FROM_UNIXTIME(`time`), "'.$StartDay.'") as `index`,
			SUM(`trimp`) as `trimp`,
			SUM('.JD::mysqlVDOTsum().'*(`sportid`='.Configuration::General()->runningSport().')) as `vdot`,
			SUM('.JD::mysqlVDOTsumTime().') as `s`
		FROM `'.PREFIX.'training`
		WHERE
			DATEDIFF(FROM_UNIXTIME(`time`), "'.$StartDay.'") BETWEEN -'.$AddDays.' AND '.$NumberOfDays.'
		GROUP BY `index`')->fetchAll();

	foreach ($Data as $dat) {
		$index = $dat['index'] + $AddDays;

		$Trimps_raw[$index] = $dat['trimp'];

		if ($dat['vdot'] != 0) {
			$VDOTs_raw[$index]     = $dat['vdot']; // Remember: These values are already multiplied with `s`
			$Durations_raw[$index] = (double)$dat['s'];
		}
	}

	$StartDayInYear = $All ? Time::diffInDays($StartTime, mktime(1,0,0,1,1,$StartYear)) + 1 : 0;
	$LowestIndex = $AddDays + 1;
	$HighestIndex = $AddDays + 1 + $NumberOfDays;

	$VDOTdays = Configuration::Vdot()->days();
	$ATLdays = Configuration::Trimp()->daysForATL();
	$CTLdays = Configuration::Trimp()->daysForCTL();

	$maxATL = Configuration::Data()->maxATL();
	$maxCTL = Configuration::Data()->maxCTL();

	$TSBModel = new Runalyze\Calculation\Performance\TSB($Trimps_raw, $CTLdays, $ATLdays);
	$TSBModel->calculate();

	for ($d = $LowestIndex; $d <= $HighestIndex; $d++) {
		$index = Plot::dayOfYearToJStime($StartYear, $d - $AddDays + $StartDayInYear);

		$ATLs[$index] = 100 * $TSBModel->fatigueAt($d - 1) / $maxATL;
		$CTLs[$index] = 100 * $TSBModel->fitnessAt($d - 1) / $maxCTL;

		$VDOT_slice      = array_slice($VDOTs_raw, $d - $VDOTdays, $VDOTdays);
		$Durations_slice = array_slice($Durations_raw, $d - $VDOTdays, $VDOTdays);
		$VDOT_sum        = array_sum($VDOT_slice);
		$Durations_sum   = array_sum($Durations_slice);

		if (count($VDOT_slice) != 0 && $Durations_sum != 0)
			$VDOTs[$index]  = JD::correctVDOT($VDOT_sum / $Durations_sum);
	}
} else {
	$DataFailed = true;
}

$Plot = new Plot("form".$_GET['y'], 800, 450);

$Plot->Data[] = array('label' => __('Fitness (CTL)'), 'color' => '#008800', 'data' => $CTLs);
if (count($ATLs) < $MaxATLPoints)
	$Plot->Data[] = array('label' => __('Fatigue (ATL)'), 'color' => '#880000', 'data' => $ATLs);
$Plot->Data[] = array('label' => __('VDOT'), 'color' => '#000000', 'data' => $VDOTs, 'yaxis' => 2);

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
	$Plot->setTitle( __('Shape for all years') );
else
	$Plot->setTitle( __('Shape').' '.$Year);

if ($DataFailed)
	$Plot->raiseError( __('No data available.') );

$Plot->outputJavaScript();
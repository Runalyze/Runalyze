<?php
/**
 * Draw analyse of training: ATL/CTL/VDOT
 * Call:   include Plot.form.php
 * @package Runalyze\Plugins\Panels
 */

use Runalyze\Configuration;
use Runalyze\Calculation\JD;
use Runalyze\Util\Time;

$MaxATLPoints   = 750;
$DataFailed     = false;
$ATLs           = array();
$CTLs           = array();
$VDOTs          = array();
$TRIMPs         = array();
$Trimps_raw     = array();
$VDOTs_raw      = array();
$Durations_raw  = array();
$VDOTsday       = array();
$maxTrimp=0;

$All      = 1*($timerange == 'all'); //0 or 1
$lastHalf = 1*($timerange == 'lasthalf');
$lastYear = 1*($timerange == 'lastyear');
$Year     = $All || $lastHalf || $lastYear ? date('Y') : (int)$timerange;

if ($Year >= START_YEAR && $Year <= date('Y') && START_TIME != time()) {
	$StartYear    = !$All ? $Year : START_YEAR;
	$StartYear    = $lastHalf ? date('Y', strtotime("today -180days")) : $StartYear;
	$StartYear    = $lastYear ? date('Y', strtotime("today -1 year")) : $StartYear;
	$EndYear      = !$All && !$lastHalf ? $Year : date('Y');
	$MaxDays      = ($EndYear - $StartYear + 1)*366;
	$MaxDays      = $lastHalf ? 366 : $MaxDays;
	$MaxDays      = $lastYear ? 396 : $MaxDays;
	$AddDays      = 3*max(Configuration::Trimp()->daysForATL(), Configuration::Trimp()->daysForCTL(), Configuration::Vdot()->days());
	$StartTime    = !$All ? mktime(0,0,0,1,1,$StartYear) : strtotime("today 00:00", START_TIME);
	$StartTime    = $lastHalf ? strtotime("today 00:00 -180days") : $StartTime;
	$StartTime    = $lastYear ? strtotime("today 00:00 -1 year") : $StartTime;
	$StartDay     = date('Y-m-d', $StartTime);
	$EndTime      = !$All && $Year < date('Y') ? mktime(23,59,0,12,31,$Year) : strtotime("today 23:59");
	$EndTime      = $lastHalf || $lastYear ? strtotime("today 23:59 +30days") : $EndTime;
	$NumberOfDays = Time::diffInDays($StartTime, $EndTime);

	$EmptyArray    = array_fill(0, $NumberOfDays + $AddDays + 2, 0);
	$Trimps_raw    = $EmptyArray;
	$VDOTs_raw     = $EmptyArray;
	$Durations_raw = $EmptyArray;


	// Here VDOT will be implemented again
	// Normal functions are too slow, calling them for each day would trigger each time a query
	// - VDOT: AVG(`vdot`) for Configuration::Vdot()->days()

	//Can't cache until we can invalidate it
	//$Data = Cache::get('calculationsPlotData'.$Year.$All.$lastHalf.$lastYear);
	//if (is_null($Data)) {
	$withElevation = Configuration::Vdot()->useElevationCorrection();

	$Data = DB::getInstance()->query('
			SELECT
				DATEDIFF(FROM_UNIXTIME(`time`), "'.$StartDay.'") as `index`,
				SUM(`trimp`) as `trimp`,
				SUM('.JD\Shape::mysqlVDOTsum($withElevation).'*(`sportid`='.Configuration::General()->runningSport().')) as `vdot`,
				SUM('.JD\Shape::mysqlVDOTsumTime($withElevation).'*(`sportid`='.Configuration::General()->runningSport().')) as `s`
			FROM `'.PREFIX.'training`
			WHERE 
				`accountid`='.\SessionAccountHandler::getId().' AND
				`time` BETWEEN UNIX_TIMESTAMP("'.$StartDay.'" + INTERVAL -'.$AddDays.' DAY) AND UNIX_TIMESTAMP("'.$StartDay.'" + INTERVAL '.$NumberOfDays.' DAY)-1
			GROUP BY `index`')->fetchAll();

	//	Cache::set('calculationsPlotData'.$Year.$All.$lastHalf.$lastYear, $Data, '300');
	//}

	foreach ($Data as $dat) {
		$index = $dat['index'] + $AddDays;

		$Trimps_raw[$index] = 1*$dat['trimp'];

		if ($dat['vdot'] != 0) {
			$VDOTs_raw[$index]     = $dat['vdot']; // Remember: These values are already multiplied with `s`
			$Durations_raw[$index] = (double)$dat['s'];
		}
	}

	$TodayIndex = Time::diffInDays($StartTime) + $AddDays;
	$StartDayInYear = $All || $lastHalf || $lastYear ? Time::diffInDays($StartTime, mktime(0,0,0,1,1,$StartYear)) + 1 + 1*($StartYear < $Year) : 1;
	$LowestIndex = $AddDays + 1;
	$HighestIndex = $AddDays + 1 + $NumberOfDays;

	$VDOTdays = Configuration::Vdot()->days();
	$ATLdays = Configuration::Trimp()->daysForATL();
	$CTLdays = Configuration::Trimp()->daysForCTL();

	if ($perfmodel == 'banister') {
		$performanceModel = new Runalyze\Calculation\Performance\Banister($Trimps_raw, $CTLdays, $ATLdays, 1, 3);
	} else {
		$performanceModel = new Runalyze\Calculation\Performance\TSB($Trimps_raw, $CTLdays, $ATLdays);
	}

	$performanceModel->calculate();

	if ($All) {
		$maxATL = $performanceModel->maxFatigue();
		$maxCTL = $performanceModel->maxFitness();

		if ($perfmodel == 'tsb' && $maxATL != Configuration::Data()->maxATL()) {
			Configuration::Data()->updateMaxATL($maxATL);
		}

		if ($perfmodel == 'tsb' && $maxCTL != Configuration::Data()->maxCTL()) {
			Configuration::Data()->updateMaxCTL($maxCTL);
		}
	} else {
		$maxATL = Configuration::Data()->maxATL();
		$maxCTL = Configuration::Data()->maxCTL();
	}

	$showInPercent = Configuration::Trimp()->showInPercent() && $perfmodel != 'banister';

	if (!$showInPercent) {
		$maxATL = 100;
		$maxCTL = 100;
	}

	for ($d = $LowestIndex; $d <= $HighestIndex; $d++) {
		$index = Plot::dayOfYearToJStime($StartYear, $d - $AddDays + $StartDayInYear);

		$ATLs[$index] = 100 * $performanceModel->fatigueAt($d) / $maxATL;
		$CTLs[$index] = 100 * $performanceModel->fitnessAt($d) / $maxCTL;
		$TSBs[$index] = 100 * $performanceModel->performanceAt($d) / $maxCTL;
		$TRIMPs[$index]    = $Trimps_raw[$d];
		if ($maxTrimp<$Trimps_raw[$d]) $maxTrimp=$Trimps_raw[$d];

		$VDOT_slice      = array_slice($VDOTs_raw, $d - $VDOTdays, $VDOTdays);
		$Durations_slice = array_slice($Durations_raw, $d - $VDOTdays, $VDOTdays);
		$VDOT_sum        = array_sum($VDOT_slice);
		$Durations_sum   = array_sum($Durations_slice);

		if (count($VDOT_slice) != 0 && $Durations_sum != 0) {
			$VDOTs[$index] = Configuration::Data()->vdotFactor() * ($VDOT_sum / $Durations_sum);
		}

		if ( $VDOTs_raw[$d]) $VDOTsday[$index]= Configuration::Data()->vdotFactor() * ($VDOTs_raw[$d]/$Durations_raw[$d]);
	}
} else {
	$DataFailed = true;
}

$Plot = new Plot("form".$timerange.$perfmodel, 800, 450);

$Plot->Data[] = array('label' => __('Fitness (CTL)'), 'color' => '#008800', 'data' => $CTLs);
//if (count($ATLs) < $MaxATLPoints)

if ($perfmodel == 'banister') {
	$Plot->Data[] = array('label' => 'TSB', 'color' => '#BBBB00', 'data' => $TSBs);
}

$Plot->Data[] = array('label' => __('Fatigue (ATL)'), 'color' => '#CC2222', 'data' => $ATLs);
$Plot->Data[] = array('label' => __('avg VDOT'), 'color' => '#000000', 'data' => $VDOTs, 'yaxis' => 2);
$Plot->Data[] = array('label' => 'TRIMP', 'color' => '#5555FF', 'data' => $TRIMPs, 'yaxis' => 3);
$Plot->Data[] = array('label' => __('day VDOT'), 'color' => '#444444', 'data' => $VDOTsday, 'yaxis' => 2);

$Plot->setMarginForGrid(5);
$Plot->setLinesFilled(array(0));
if ($perfmodel == 'banister') {
	$Plot->setLinesFilled(array(2), 0.3);
}
$Plot->setLinesFilled(array(1),0.4);
$Plot->setXAxisAsTime();

if (!$All && !$lastHalf && !$lastYear)
	$Plot->setXAxisLimitedTo($Year);

$Plot->addYAxis(1, 'left');
$Plot->setYTicks(1, 1);
if ($showInPercent) {
	$Plot->addYUnit(1, '%');
	$Plot->setYLimits(1, 0, 100);
}

$Plot->addYAxis(2, 'right');
$Plot->setYTicks(2, 1, 1);

$Plot->addYAxis(3, 'right');
$Plot->setYLimits(3, 0, $maxTrimp*2);

if ($perfmodel == 'banister') {
	$Plot->showAsBars(4,1,2);
	$Plot->showAsPoints(5);
} else {
	$Plot->showAsBars(3,1,2);
	$Plot->showAsPoints(4);
}

$Plot->smoothing(false);

if (($lastHalf || $lastYear) && !$DataFailed) {
	$Plot->addMarkingArea('x', time().'000', $index, 'rgba(255,255,255,0.3)');//'rgba(200,200,200,0.5)');
}

$Plot->setGridAboveData();

if ($All)
	$Plot->setTitle( __('Shape for all years') );
else
	$Plot->setTitle( __('Shape').' '.$Year);

if ($DataFailed)
	$Plot->raiseError( __('No data available.') );

$Plot->outputJavaScript();

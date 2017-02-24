<?php
/**
 * Draw analyse of training: ATL/CTL/VDOT
 * Call:   include Plot.form.php
 * @package Runalyze\Plugins\Panels
 */

use Runalyze\Configuration;
use Runalyze\Calculation\BasicEndurance;
use Runalyze\Calculation\JD;
use Runalyze\Sports\Performance\Model\BanisterModel;
use Runalyze\Sports\Performance\Model\TsbModel;
use Runalyze\Util\Time;
use Runalyze\Util\LocalTime;

$DataFailed     = false;
$BasicEndurance = array();
$ATLs           = array();
$CTLs           = array();
$VDOTs          = array();
$TRIMPs         = array();
$VDOTsday       = array();
$maxTrimp=0;

$All      = 1*($timerange == 'all'); //0 or 1
$lastHalf = 1*($timerange == 'lasthalf');
$lastYear = 1*($timerange == 'lastyear');
$Year     = $All || $lastHalf || $lastYear ? date('Y') : (int)$timerange;

$BasicEnduranceObj = new BasicEndurance();
$BasicEnduranceObj->readSettingsFromConfiguration();

$VDOTdays = Configuration::Vdot()->days();
$ATLdays = Configuration::Trimp()->daysForATL();
$CTLdays = Configuration::Trimp()->daysForCTL();
$BEkmDays = $BasicEnduranceObj->getDaysForWeekKm();
$BElongjogsDays = $BasicEnduranceObj->getDaysToRecognizeForLongjogs();
$MinKmOfLongJogs = $BasicEnduranceObj->getMinimalDistanceForLongjogs();

if ($Year >= START_YEAR && $Year <= date('Y') && START_TIME != time()) {
	if ($All) {
		$StartTime = strtotime('today 00:00', START_TIME);
		$EndTime = strtotime('today 23:59');
	} elseif ($lastHalf) {
		$StartTime = strtotime('today 00:00 -180days');
		$EndTime = strtotime('today 23:59 +30days');
	} elseif ($lastYear) {
		$StartTime = strtotime('today 00:00 -1 year');
		$EndTime = strtotime('today 23:59 +30days');
	} else {
		$StartTime = mktime(0, 0, 0, 1, 1, $Year);
		$EndTime = mktime(23, 59, 0, 12, 31, $Year);
	}

	$AddDays      = max(3*max($ATLdays, $CTLdays, $VDOTdays), $BEkmDays, $BElongjogsDays);
	$NumberOfDays = Time::diffInDays($StartTime, $EndTime);
	$StartYear    = date('Y', $StartTime);
	$EndYear      = date('Y', $EndTime);

	$EmptyArray    = array_fill(0, $NumberOfDays + $AddDays + 2, 0);
	$Trimps_raw    = $EmptyArray;
	$VDOTs_raw     = $EmptyArray;
	$Durations_raw = $EmptyArray;
	$Distances_raw = $EmptyArray;
	$Longjogs_raw  = array_fill(0, $NumberOfDays + $AddDays + 2, array());

	// Here VDOT will be implemented again
	// Normal functions are too slow, calling them for each day would trigger each time a query
	// - VDOT: AVG(`vdot`) for Configuration::Vdot()->days()

	$withElevation = Configuration::Vdot()->useElevationCorrection();
	$StartDay = LocalTime::fromServerTime($StartTime)->format('Y-m-d');

	// Can't cache until we can invalidate it
	$Statement = DB::getInstance()->query(
		'SELECT
			DATEDIFF(FROM_UNIXTIME(`time`), "'.$StartDay.'") as `index`,
			`trimp`,
			`distance`,
			'.JD\Shape::mysqlVDOTsum($withElevation).' as `vdot_weighted`,
			'.JD\Shape::mysqlVDOTsumTime($withElevation).' as `vdot_sum_time`,
			`sportid` = "'.Configuration::General()->runningSport().'" as `is_running`
		FROM `'.PREFIX.'training`
		WHERE
			`accountid`='.\SessionAccountHandler::getId().' AND
			`time` BETWEEN UNIX_TIMESTAMP("'.$StartDay.'" + INTERVAL -'.$AddDays.' DAY) AND '.$EndTime
	);

	while ($activity = $Statement->fetch()) {
		$index = $activity['index'] + $AddDays;

		$Trimps_raw[$index] += $activity['trimp'];

		if ($activity['is_running']) {
			$Distances_raw[$index] += $activity['distance'];

			if ($activity['distance'] > $MinKmOfLongJogs) {
				$Longjogs_raw[$index][] = $activity['distance'];
			}

			if ($activity['vdot_weighted'] != 0) {
				$VDOTs_raw[$index]     += $activity['vdot_weighted'];
				$Durations_raw[$index] += $activity['vdot_sum_time'];
			}
		}
	}

	$LowestIndex = $AddDays + 1*(!$All);
	$HighestIndex = $LowestIndex + $NumberOfDays;

	if ($perfmodel == 'banister') {
		$performanceModel = new BanisterModel($Trimps_raw, $CTLdays, $ATLdays, 1, 3);
	} else {
		$performanceModel = new TsbModel($Trimps_raw, $CTLdays, $ATLdays);
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

	$StartTime = strtotime($StartDay) + DAY_IN_S * 0.5;
	$vdotFactor = Configuration::Data()->vdotFactor();

	for ($d = $LowestIndex; $d <= $HighestIndex; $d++) {
		$index = ($StartTime + DAY_IN_S * ($d - $AddDays)) . '000';

		$ATLs[$index] = 100 * $performanceModel->fatigueAt($d) / $maxATL;
		$CTLs[$index] = 100 * $performanceModel->fitnessAt($d) / $maxCTL;
		$TSBs[$index] = 100 * $performanceModel->performanceAt($d) / $maxCTL;
		$TRIMPs[$index]    = $Trimps_raw[$d];

		if ($maxTrimp < $Trimps_raw[$d]) {
			$maxTrimp = $Trimps_raw[$d];
		}

		$VDOT_slice      = array_slice($VDOTs_raw, $d - $VDOTdays, $VDOTdays);
		$Durations_slice = array_slice($Durations_raw, $d - $VDOTdays, $VDOTdays);
		$VDOT_sum        = array_sum($VDOT_slice);
		$Durations_sum   = array_sum($Durations_slice);

		if (count($VDOT_slice) != 0 && $Durations_sum != 0) {
			$VDOTs[$index] = $vdotFactor * ($VDOT_sum / $Durations_sum);

			$BasicEnduranceObj->setEffectiveVO2max($VDOTs[$index]);
			$currentKmSum = array_sum(array_slice($Distances_raw, $d - $BEkmDays, $BEkmDays));
			$longJogPoints = 0;

			for ($i = 0; $i <= $BElongjogsDays; ++$i) {
				foreach ($Longjogs_raw[$d - $i] as $longjog) {
					$longJogPoints += 2*(1 - $i/$BElongjogsDays) * pow($longjog - $MinKmOfLongJogs, 2);
				}
			}
			$longJogPoints /= pow($BasicEnduranceObj->getTargetLongjogKmPerWeek(), 2);
			$BasicEndurance[$index] = $BasicEnduranceObj->asArray(0, ['km' => $currentKmSum, 'sum' => $longJogPoints])['percentage'];
		}

		if ($VDOTs_raw[$d]) {
			$VDOTsday[$index] = $vdotFactor * ($VDOTs_raw[$d] / $Durations_raw[$d]);
		}
	}
} else {
	$DataFailed = true;
}

$Plot = new Plot("form".$timerange.$perfmodel, 800, 450);

$Plot->Data[] = array('label' => __('Fitness (CTL)'), 'color' => '#008800', 'data' => $CTLs);

if ($perfmodel == 'banister') {
	$Plot->Data[] = array('label' => 'TSB', 'color' => '#BBBB00', 'data' => $TSBs);
}

$Plot->Data[] = array('label' => __('Fatigue (ATL)'), 'color' => '#CC2222', 'data' => $ATLs);
$Plot->Data[] = array('label' => __('avg VDOT'), 'color' => '#000000', 'data' => $VDOTs, 'yaxis' => 2);
$Plot->Data[] = array('label' => 'TRIMP', 'color' => '#5555FF', 'data' => $TRIMPs, 'yaxis' => 3);
$Plot->Data[] = array('label' => __('day VDOT'), 'color' => '#444444', 'data' => $VDOTsday, 'yaxis' => 2);
$Plot->Data[] = array('label' => __('Marathon shape'), 'color' => '#CC9322', 'data' => $BasicEndurance, 'yaxis' => 4);

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

$Plot->addYAxis(4, 'right');
$Plot->addYUnit(4, '%');

$maxBasicEndurance = empty($BasicEndurance) ? 100 : ceil(max($BasicEndurance)/100)*100;
$Plot->setYLimits(4, 0, $maxBasicEndurance);

if ($perfmodel == 'banister') {
	$Plot->showAsBars(4,1,2);
	$Plot->showAsPoints(5);
} else {
	$Plot->showAsBars(3,1,2);
	$Plot->showAsPoints(4);
}

$Plot->smoothing(false);

if (($lastHalf || $lastYear || ($Year == date('Y'))) && !$DataFailed) {
	$Plot->addMarkingArea('x', time().'000', $index, 'rgba(255,255,255,0.3)');
}

$Plot->setGridAboveData();

if ($All)
	$Plot->setTitle( __('Shape for all years') );
else
	$Plot->setTitle( __('Shape').' '.$Year);

if ($DataFailed)
	$Plot->raiseError( __('No data available.') );

$Plot->outputJavaScript();

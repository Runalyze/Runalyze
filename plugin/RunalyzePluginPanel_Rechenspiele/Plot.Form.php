<?php
/**
 * Draw analyse of training: ATL/CTL/VDOT
 * Call:   include Plot.form.php
 * @package Runalyze\Plugins\Panels
 */

use Runalyze\Configuration;
use Runalyze\Calculation\JD;

$MaxATLPoints   = 750;
$DataFailed     = false;
$ATLs           = array();
$CTLs           = array();
$VDOTs          = array();
$Trimps_raw     = array();
$VDOTs_raw      = array();
$Durations_raw  = array();
$VDOTsday       = array();
$maxTrimp=0;

$All   = 1*($_GET['y'] == 'all'); //0 or 1
$lastHalf   = 1*($_GET['y'] == 'lasthalf');
$Year  = $All || $lastHalf ? date('Y') : (int)$_GET['y'];

if ($Year >= START_YEAR && $Year <= date('Y') && START_TIME != time()) {
    $StartYear    = !$All ? $Year : START_YEAR;
    $StartYear    = $lastHalf ? date('Y', time()-60*60*24*180) : $StartYear;
    $EndYear      = !$All && !$lastHalf ? $Year : date('Y');
	$MaxDays      = ($EndYear - $StartYear + 1)*366;
    $MaxDays      = $lastHalf ? 366 : $MaxDays;
	$AddDays      = max(Configuration::Trimp()->daysForATL(), Configuration::Trimp()->daysForCTL(), Configuration::Vdot()->days());
	$StartTime    = !$All ? mktime(1,0,0,1,1,$StartYear) : START_TIME;
    $StartTime    = $lastHalf ? time()-60*60*24*180 : $StartTime;
	$StartDay     = date('Y-m-d', $StartTime);
	$EndTime      = !$All && $Year < date('Y') ? mktime(1,0,0,12,31,$Year) : time();
    $EndTime      = $lastHalf ? time()+24*60*60*30 : $EndTime;
	$NumberOfDays = Time::diffInDays($StartTime, $EndTime);

	$EmptyArray    = array_fill(0, $MaxDays + $AddDays + 1, 0);
	$Trimps_raw    = $EmptyArray;
	$VDOTs_raw     = $EmptyArray;
	$Durations_raw = $EmptyArray;


//    trigger_error('ST:'.date('Y-m-d',$StartTime), E_USER_NOTICE);
//    trigger_error('ET:'.date('Y-m-d',$EndTime), E_USER_NOTICE);
//    trigger_error('SY:'.$StartYear, E_USER_NOTICE);
//    trigger_error('EY:'.$EndYear, E_USER_NOTICE);


	// Here VDOT will be implemented again
	// Normal functions are too slow, calling them for each day would trigger each time a query
	// - VDOT: AVG(`vdot`) for Configuration::Vdot()->days()
	$Data = Cache::get('calculationsPlotData'.$Year.$All.$lastHalf);
	if (is_null($Data)) {
		$withElevation = Configuration::Vdot()->useElevationCorrection();

		$Data = DB::getInstance()->query('
			SELECT
				DATEDIFF(FROM_UNIXTIME(`time`), "'.$StartDay.'") as `index`,
				SUM(`trimp`) as `trimp`,
				SUM('.JD\Shape::mysqlVDOTsum($withElevation).'*(`sportid`='.Configuration::General()->runningSport().')) as `vdot`,
				SUM('.JD\Shape::mysqlVDOTsumTime($withElevation).'*(`sportid`='.Configuration::General()->runningSport().')) as `s`
			FROM `'.PREFIX.'training`
			WHERE
				DATEDIFF(FROM_UNIXTIME(`time`), "'.$StartDay.'") BETWEEN -'.$AddDays.' AND '.$NumberOfDays.'
			GROUP BY `index`')->fetchAll();

		Cache::set('calculationsPlotData'.$Year.$All.$lastHalf, $Data, '300');
	}

	foreach ($Data as $dat) {
		$index = $dat['index'] + $AddDays;

		$Trimps_raw[$index] = $dat['trimp'];

		if ($dat['vdot'] != 0) {
			$VDOTs_raw[$index]     = $dat['vdot']; // Remember: These values are already multiplied with `s`
			$Durations_raw[$index] = (double)$dat['s'];
		}
	}

	$StartDayInYear = $All || $lastHalf ? Time::diffInDays($StartTime, mktime(1,0,0,1,1,$StartYear)) + 1 : 0;
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

$Plot = new Plot("form".$_GET['y'], 800, 450);

$Plot->Data[] = array('label' => __('Fitness (CTL)'), 'color' => '#008800', 'data' => $CTLs);
//if (count($ATLs) < $MaxATLPoints)
$Plot->Data[] = array('label' => __('Fatigue (ATL)'), 'color' => '#CC2222', 'data' => $ATLs);
$Plot->Data[] = array('label' => __('avg VDOT'), 'color' => '#000000', 'data' => $VDOTs, 'yaxis' => 2);
$Plot->Data[] = array('label' => 'TRIMP', 'color' => '#5555FF', 'data' => $TRIMPs, 'yaxis' => 3);
$Plot->Data[] = array('label' => __('day VDOT'), 'color' => '#444444', 'data' => $VDOTsday, 'yaxis' => 2);


$Plot->setMarginForGrid(5);
$Plot->setLinesFilled(array(0));
$Plot->setLinesFilled(array(1),0.3);
$Plot->setXAxisAsTime();

if (!$All && !$lastHalf)
	$Plot->setXAxisLimitedTo($Year);

$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, '%');
$Plot->setYTicks(1, 1);
$Plot->setYLimits(1, 0, 100);
$Plot->addYAxis(2, 'right');
$Plot->setYTicks(2, 1, 1);

$Plot->addYAxis(3, 'right');
$Plot->setYLimits(3, 0, $maxTrimp*2);

$Plot->showAsBars(3,1,2);

$Plot->showAsPoints(4);

$Plot->smoothing(false);

if ($lastHalf) $Plot->addMarkingArea('x',Plot::dayOfYearToJStime($StartYear, $HighestIndex-30 - $AddDays + $StartDayInYear), $index, 'rgba(255,255,255,0.3)');//'rgba(200,200,200,0.5)');

$Plot->setGridAboveData();

if ($All)
	$Plot->setTitle( __('Shape for all years') );
else
	$Plot->setTitle( __('Shape').' '.$Year);

if ($DataFailed)
	$Plot->raiseError( __('No data available.') );

$Plot->outputJavaScript();
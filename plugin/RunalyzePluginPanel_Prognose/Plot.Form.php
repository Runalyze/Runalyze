<?php
/**
 * Draw prognosis as function of time
 * Call:   include Plot.form.php
 * @package Runalyze\Plugins\Panels
 */

use Runalyze\Calculation\JD;
use Runalyze\Calculation\Math\MovingAverage\Kernel;
use Runalyze\Calculation\Math\MovingAverage\WithKernel;
use Runalyze\Calculation\Prognosis;
use Runalyze\Configuration;
use Runalyze\Activity\Distance;
use Runalyze\Util\Time;

if (is_dir(FRONTEND_PATH.'../plugin/RunalyzePluginStat_Wettkampf')) {
	$Factory = new PluginFactory();
	$WKplugin = $Factory->newInstance('RunalyzePluginStat_Wettkampf');
}

if (!isset($distance)) {
	$distance = 10;
}

$DataFailed = false;
$Prognosis  = array();
$Results    = array();

$Strategy = new Prognosis\Daniels();
$Strategy->adjustVDOT(false);

$PrognosisObj = new Prognosis\Prognosis();
$PrognosisObj->setStrategy($Strategy);

if (START_TIME != time()) {
	$Data = null; Cache::get('prognosePlotData');

	if (is_null($Data)) {
		$withElevation = Configuration::Vdot()->useElevationCorrection();

		$Data = DB::getInstance()->query('
			SELECT
				YEAR(FROM_UNIXTIME(`time`)) as `y`,
				MONTH(FROM_UNIXTIME(`time`)) as `m`,
				DAY(FROM_UNIXTIME(`time`)) as `d`,
				SUM('.JD\Shape::mysqlVDOTsum($withElevation).')/SUM('.JD\Shape::mysqlVDOTsumTime($withElevation).') as `vdot`
			FROM `'.PREFIX.'training`
			WHERE
				`accountid`='.\SessionAccountHandler::getId().' AND
				`vdot`>0 AND use_vdot<>0
			GROUP BY `y`, `m`, `d`
			ORDER BY `y` ASC, `m` ASC, `d` ASC')->fetchAll();

		Cache::set('prognosePlotData', $Data, '300');
	}

	if (!empty($Data)) {
		$StartTime = mktime(12, 0, 0, $Data[0]['m'], $Data[0]['d'], $Data[0]['y']);
		$windowWidth = Configuration::Vdot()->days();
		$VDOTs = [$Data[0]['vdot']];
		$Indices = [0];

		// A 'prefix' of 15 days is needed to use uniform kernel only as 'rear mirror'
		foreach ($Data as $dat) {
			$VDOTs[] = $dat['vdot'];
			$Indices[] = 15 + Time::diffInDays($StartTime, mktime(12, 0, 0, $dat['m'], $dat['d'], $dat['y']));
		}

		$MovingAverage = new WithKernel($VDOTs, $Indices);
		$MovingAverage->setKernel(new Kernel\Uniform($windowWidth));
		$MovingAverage->calculate();

		foreach ($MovingAverage->movingAverage() as $i => $value) {
			if ($i > 0) {
				// TODO: use correct GA
				$Strategy->setVDOT(Configuration::Data()->vdotFactor() * $value);

				$index = $StartTime + DAY_IN_S * ($Indices[$i] - 15);
				$Prognosis[$index.'000'] = $PrognosisObj->inSeconds($distance) * 1000;
			}
		}


		$ResultsData = Cache::get('prognosePlotDistanceData'.$distance);
		if (is_null($ResultsData)) {
			$ResultsData = DB::getInstance()->query('
				SELECT
					`time`,
					`id`,
					`s`
				FROM `'.PREFIX.'training`
				WHERE 
					`accountid`='.\SessionAccountHandler::getId().' AND
					`typeid`="'.Configuration::General()->competitionType().'"
					AND `distance`="'.$distance.'"
				ORDER BY
					`time` ASC')->fetchAll();

			Cache::set('prognosePlotDistanceData'.$distance, $ResultsData, '600');
		}

		foreach ($ResultsData as $dat) {
			if (!isset($WKplugin) || !$WKplugin->isFunCompetition($dat['id']))
				$Results[$dat['time'].'000'] = $dat['s'] * 1000;
		}
	} else {
		$DataFailed = true;
	}
} else {
	$DataFailed = true;
}

$Plot = new Plot("formverlauf_".str_replace('.', '_', $distance), 800, 450);

$Plot->Data[] = array('label' => __('Prognosis'), 'color' => '#880000', 'data' => $Prognosis, 'lines' => array('show' => true), 'points' => array('show' => false));
$Plot->Data[] = array('label' => __('Result'), 'color' => '#000000', 'data' => $Results, 'lines' => array('show' => false), 'points' => array('show' => true), 'curvedLines' => array('apply' => false));

$Plot->setZeroPointsToNull();

$Plot->setMarginForGrid(5);
$Plot->setXAxisAsTime();
$Plot->addYAxis(1, 'left');

if (!empty($Prognosis) && max($Prognosis) > 1000*3600)
	$Plot->setYAxisTimeFormat('%H:%M:%S');
else
	$Plot->setYAxisTimeFormat('%M:%S');

$Plot->setTitle( __('Prognosis trend').' '.Distance::format($distance));

if ($DataFailed || empty($Data))
	$Plot->raiseError( __('No data available.') );

$Plot->outputJavaScript();
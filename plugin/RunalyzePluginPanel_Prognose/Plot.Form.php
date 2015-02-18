<?php
/**
 * Draw prognosis as function of time
 * Call:   include Plot.form.php
 * @package Runalyze\Plugins\Panels
 */

use Runalyze\Calculation\JD;
use Runalyze\Configuration;
use Runalyze\Activity\Distance;

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

$Strategy = new RunningPrognosisDaniels;
$Strategy->adjustVDOT(false);

$PrognosisObj = new RunningPrognosis;
$PrognosisObj->setStrategy($Strategy);

if (START_TIME != time()) {
	$Data = Cache::get('prognosePlotData');

	if (is_null($Data)) {
		$withElevation = Configuration::Vdot()->useElevationCorrection();

		$Data = DB::getInstance()->query('
			SELECT
				YEAR(FROM_UNIXTIME(`time`)) as `y`,
				MONTH(FROM_UNIXTIME(`time`)) as `m`,
				SUM('.JD\Shape::mysqlVDOTsum($withElevation).')/SUM('.JD\Shape::mysqlVDOTsumTime($withElevation).') as `vdot`
			FROM `'.PREFIX.'training`
			WHERE
				`vdot`>0 AND use_vdot<>0
			GROUP BY `y`, `m`
			ORDER BY `y` ASC, `m` ASC')->fetchAll();

		Cache::set('prognosePlotData', $Data, '300');
	}

	foreach ($Data as $dat) {
		// TODO: use correct GA
		$Strategy->setVDOT( Configuration::Data()->vdotFactor() * $dat['vdot'] );

		$index = mktime(1,0,0,$dat['m'],15,$dat['y']);
		$Prognosis[$index.'000'] = $PrognosisObj->inSeconds($distance)*1000;
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
				`typeid`="'.Configuration::General()->competitionType().'"
				AND `distance`="'.$distance.'"
			ORDER BY
				`time` ASC')->fetchAll();

		Cache::set('prognosePlotDistanceData'.$distance, $ResultsData, '600');
	}

	foreach ($ResultsData as $dat) {
		if (!isset($WKplugin) || !$WKplugin->isFunCompetition($dat['id']))
			$Results[$dat['time'].'000'] = $dat['s']*1000;
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
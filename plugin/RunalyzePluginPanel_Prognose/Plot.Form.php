<?php
/**
 * Draw prognosis as function of time
 * Call:   include Plot.form.php
 * @package Runalyze\Plugins\Panels
 */

use Runalyze\Calculation\BasicEndurance;
use Runalyze\Calculation\JD;
use Runalyze\Calculation\Prognosis;
use Runalyze\Configuration;
use Runalyze\Activity\Distance;
use Runalyze\Util\LocalTime;

if (is_dir(FRONTEND_PATH.'../plugin/RunalyzePluginStat_Wettkampf')) {
	$Factory = new PluginFactory();

	if (!$Factory->isInstalled('RunalyzePluginStat_Wettkampf')) {
		$WKplugin = $Factory->newInstance('RunalyzePluginStat_Wettkampf');
	}
}

if (!isset($distance)) {
	$distance = 10;
}

$DataFailed = false;
$Prognosis = array();
$PrognosisWithBasicEndurance = array();
$BasicEndurance = array();
$Results = array();

$Strategy = new Prognosis\Daniels();
$Strategy->adjustVDOT(false);

$PrognosisObj = new Prognosis\Prognosis();
$PrognosisObj->setStrategy($Strategy);

$BasicEnduranceObj = new BasicEndurance();
$BasicEnduranceObj->readSettingsFromConfiguration();

if (START_TIME != time()) {
	$withElevation = Configuration::Vdot()->useElevationCorrection();

	$Data = DB::getInstance()->query('
		SELECT
			DATEDIFF(FROM_UNIXTIME(`time`), "'.date('Y-m-d').'") as `date_age`,
			DATE(FROM_UNIXTIME(`time`)) as `date`,
			`distance`,
			'.JD\Shape::mysqlVDOTsum($withElevation).' as `vdot_weighted`,
			'.JD\Shape::mysqlVDOTsumTime($withElevation).' as `vdot_sum_time`
		FROM `'.PREFIX.'training`
		WHERE
			`accountid`='.\SessionAccountHandler::getId().' AND
			`sportid`='.Configuration::General()->runningSport().'
		ORDER BY `date` ASC')->fetchAll();

	if (!empty($Data)) {
		$currentFirstIndexForVdot = 0;
		$currentFirstIndexForKm = 0;
		$currentFirstIndexForLongJogs = 0;

		$currentVdotWeightedSum = $Data[0]['vdot_weighted'];
		$currentVdotTimeSum = $Data[0]['vdot_sum_time'];
		$currentKmSum = $Data[0]['distance'];

		$vdotFactor = Configuration::Data()->vdotFactor();
		$maxAgeOfVdot = Configuration::Vdot()->days();
		$maxAgeOfKm = $BasicEnduranceObj->getDaysForWeekKm();
		$maxAgeOfLongJogs = $BasicEnduranceObj->getDaysToRecognizeForLongjogs();
		$minKmOfLongJogs = $BasicEnduranceObj->getMinimalDistanceForLongjogs();

		// Due to performance reasons, this is not clean code
		// To check values, use Runalyze\Calculation\JD\VdotShape::calculateAt($index)
		foreach ($Data as $currentIndex => $currentData) {
			while ($currentFirstIndexForVdot < $currentIndex && $currentData['date_age'] - $Data[$currentFirstIndexForVdot]['date_age'] > $maxAgeOfVdot) {
				$currentVdotWeightedSum -= $Data[$currentFirstIndexForVdot]['vdot_weighted'];
				$currentVdotTimeSum -= $Data[$currentFirstIndexForVdot]['vdot_sum_time'];
				$currentFirstIndexForVdot++;
			}
			while ($currentFirstIndexForKm < $currentIndex && $currentData['date_age'] - $Data[$currentFirstIndexForKm]['date_age'] > $maxAgeOfKm) {
				$currentKmSum -= $Data[$currentFirstIndexForKm]['distance'];
				$currentFirstIndexForKm++;
			}
			while ($currentFirstIndexForLongJogs < $currentIndex && $currentData['date_age'] - $Data[$currentFirstIndexForLongJogs]['date_age'] > $maxAgeOfLongJogs) {
				$currentFirstIndexForLongJogs++;
			}

			$currentVdotWeightedSum += $currentData['vdot_weighted'];
			$currentVdotTimeSum += $currentData['vdot_sum_time'];
			$currentKmSum += $currentData['distance'];

			$Data[$currentIndex]['vdot'] = ($currentVdotTimeSum > 0) ? $vdotFactor * $currentVdotWeightedSum / $currentVdotTimeSum : 0;

			if ($Data[$currentIndex]['vdot'] > 0) {
				$index = strtotime($currentData['date'].' 12:00').'000';
				$longJogPoints = 0;

				for ($i = $currentFirstIndexForLongJogs; $i <= $currentIndex; ++$i) {
					$longJogPoints += 2*(1 - ($currentData['date_age'] - $Data[$i]['date_age'])/$maxAgeOfLongJogs) * pow($Data[$i]['distance'] - $minKmOfLongJogs, 2);
				}

				$BasicEnduranceObj->setVDOT($Data[$currentIndex]['vdot']);
				$longJogPoints /= pow($BasicEnduranceObj->getTargetLongjogKmPerWeek(), 2);
				$BasicEndurance[$index] = $BasicEnduranceObj->asArray(0, ['km' => $currentKmSum, 'sum' => $longJogPoints])['percentage'];

				$Strategy->setVDOT($Data[$currentIndex]['vdot']);
				$Strategy->setBasicEnduranceForAdjustment($BasicEndurance[$index]);
				$Strategy->adjustVDOT(true);
				$PrognosisWithBasicEndurance[$index] = $PrognosisObj->inSeconds($distance) * 1000;

				$Strategy->adjustVDOT(false);
				$Prognosis[$index] = $PrognosisObj->inSeconds($distance) * 1000;
			}
		}

		$ResultsData = Cache::get('prognosePlotDistanceData'.$distance);
		if (is_null($ResultsData)) {
			$ResultsData = DB::getInstance()->query('
				SELECT
					`time`,
					`id`,
					`official_time`
				FROM `'.PREFIX.'raceresult` r
				    LEFT JOIN `'.PREFIX.'training` tr ON r.activity_id=tr.id
				WHERE 
					r.`accountid`='.\SessionAccountHandler::getId().' AND
					tr.`sportid`="'.Configuration::General()->runningSport().'"
					AND r.`official_distance`="'.$distance.'"
				ORDER BY
					`time` ASC')->fetchAll();

			Cache::set('prognosePlotDistanceData'.$distance, $ResultsData, '600');
		}

		foreach ($ResultsData as $dat) {
			if (!isset($WKplugin) || !$WKplugin->isFunCompetition($dat['id'])) {
				$Results[(new LocalTime($dat['time']))->toServerTimestamp().'000'] = $dat['official_time'] * 1000;
			}
		}
	} else {
		$DataFailed = true;
	}
} else {
	$DataFailed = true;
}

$Plot = new Plot("formverlauf_".str_replace('.', '_', $distance), 800, 450);

$Plot->Data[] = array('label' => __('Prognosis'), 'color' => '#880000', 'data' => $Prognosis, 'lines' => array('show' => true), 'points' => array('show' => false));
$Plot->Data[] = array('label' => __('Prognosis with basic endurance'), 'color' => '#000088', 'data' => $PrognosisWithBasicEndurance, 'lines' => array('show' => true), 'points' => array('show' => false));
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
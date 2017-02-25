<?php
/**
 * Draw prognosis as function of time
 * Call:   include Plot.form.php
 * @package Runalyze\Plugins\Panels
 */

use Runalyze\Calculation\BasicEndurance;
use Runalyze\Calculation\JD;
use Runalyze\Configuration;
use Runalyze\Activity\Distance;
use Runalyze\Sports\Running\Prognosis;
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

$PrognosisObj = new Prognosis\VO2max();

$BasicEnduranceObj = new BasicEndurance();
$BasicEnduranceObj->readSettingsFromConfiguration();

if (START_TIME != time()) {
	$withElevation = Configuration::VO2max()->useElevationCorrection();

	$Data = DB::getInstance()->query('
		SELECT
			DATEDIFF(FROM_UNIXTIME(`time`), "'.date('Y-m-d').'") as `date_age`,
			DATE(FROM_UNIXTIME(`time`)) as `date`,
			`distance`,
			'.JD\Shape::mysqlVO2maxSum($withElevation).' as `vo2max_weighted`,
			'.JD\Shape::mysqlVO2maxSumTime($withElevation).' as `vo2max_sum_time`
		FROM `'.PREFIX.'training`
		WHERE
			`accountid`='.\SessionAccountHandler::getId().' AND
			`sportid`='.Configuration::General()->runningSport().'
		ORDER BY `date` ASC')->fetchAll();

	if (!empty($Data)) {
		$currentFirstIndexForVO2max = 0;
		$currentFirstIndexForKm = 0;
		$currentFirstIndexForLongJogs = 0;

		$currentVO2maxWeightedSum = $Data[0]['vo2max_weighted'];
		$currentVO2maxTimeSum = $Data[0]['vo2max_sum_time'];
		$currentKmSum = $Data[0]['distance'];

		$vo2maxFactor = Configuration::Data()->vo2maxCorrectionFactor();
		$maxAgeOfVO2max = Configuration::VO2max()->days();
		$maxAgeOfKm = $BasicEnduranceObj->getDaysForWeekKm();
		$maxAgeOfLongJogs = $BasicEnduranceObj->getDaysToRecognizeForLongjogs();
		$minKmOfLongJogs = $BasicEnduranceObj->getMinimalDistanceForLongjogs();

		if ($Data[count($Data) - 1]['date_age'] < 0) {
		    $Data[] = [
		        'date_age' => 0,
                'date' => date('Y-m-d'),
                'distance' => 0,
                'vo2max_weighted' => 0,
                'vo2max_sum_time' => 0
            ];
        }

		// Due to performance reasons, this is not clean code
		// To check values, use Runalyze\Calculation\JD\VO2maxShape::calculateAt($index)
		foreach ($Data as $currentIndex => $currentData) {
			while ($currentFirstIndexForVO2max < $currentIndex && $currentData['date_age'] - $Data[$currentFirstIndexForVO2max]['date_age'] >= $maxAgeOfVO2max) {
				$currentVO2maxWeightedSum -= $Data[$currentFirstIndexForVO2max]['vo2max_weighted'];
				$currentVO2maxTimeSum -= $Data[$currentFirstIndexForVO2max]['vo2max_sum_time'];
				$currentFirstIndexForVO2max++;
			}
			while ($currentFirstIndexForKm < $currentIndex && $currentData['date_age'] - $Data[$currentFirstIndexForKm]['date_age'] >= $maxAgeOfKm) {
				$currentKmSum -= $Data[$currentFirstIndexForKm]['distance'];
				$currentFirstIndexForKm++;
			}
			while ($currentFirstIndexForLongJogs < $currentIndex && $currentData['date_age'] - $Data[$currentFirstIndexForLongJogs]['date_age'] >= $maxAgeOfLongJogs) {
				$currentFirstIndexForLongJogs++;
			}

			$currentVO2maxWeightedSum += $currentData['vo2max_weighted'];
			$currentVO2maxTimeSum += $currentData['vo2max_sum_time'];
			$currentKmSum += $currentData['distance'];

			$Data[$currentIndex]['vo2max'] = ($currentVO2maxTimeSum > 0) ? $vo2maxFactor * $currentVO2maxWeightedSum / $currentVO2maxTimeSum : 0;

			if ($Data[$currentIndex]['vo2max'] > Prognosis\VO2max::REASONABLE_VO2MAX_MINIMUM && $Data[$currentIndex]['vo2max'] < Prognosis\VO2max::REASONABLE_VO2MAX_MAXIMUM) {
				$index = strtotime($currentData['date'].' 12:00').'000';
				$longJogPoints = 0;

				for ($i = $currentFirstIndexForLongJogs; $i <= $currentIndex; ++$i) {
					$longJogPoints += 2*(1 - ($currentData['date_age'] - $Data[$i]['date_age'])/$maxAgeOfLongJogs) * pow(max(0, $Data[$i]['distance'] - $minKmOfLongJogs), 2);
				}

				$BasicEnduranceObj->setEffectiveVO2max($Data[$currentIndex]['vo2max']);
				$longJogPoints /= pow($BasicEnduranceObj->getTargetLongjogKmPerWeek(), 2);
				$BasicEndurance[$index] = $BasicEnduranceObj->asArray(0, ['km' => $currentKmSum, 'sum' => $longJogPoints])['percentage'];

				$PrognosisObj->setEffectiveVO2max($Data[$currentIndex]['vo2max']);
				$PrognosisObj->setMarathonShape($BasicEndurance[$index]);
				$PrognosisObj->adjustForMarathonShape(true);
				$prognosisInSecondsWithBasicEndurance = $PrognosisObj->getSeconds($distance);

				if ($prognosisInSecondsWithBasicEndurance > 0) {
					$PrognosisWithBasicEndurance[$index] = $prognosisInSecondsWithBasicEndurance * 1000;

					$PrognosisObj->adjustForMarathonShape(false);
					$Prognosis[$index] = $PrognosisObj->getSeconds($distance) * 1000;
				}
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

$Plot->Data[] = array('label' => __('Prognosis'), 'color' => '#880000', 'data' => $Prognosis, 'lines' => array('show' => true), 'points' => array('show' => false), 'curvedLines' => array('apply' => false));
$Plot->Data[] = array('label' => __('Prognosis with marathon shape'), 'color' => '#000088', 'data' => $PrognosisWithBasicEndurance, 'lines' => array('show' => true), 'points' => array('show' => false), 'curvedLines' => array('apply' => false));
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

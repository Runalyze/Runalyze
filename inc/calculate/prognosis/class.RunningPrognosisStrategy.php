<?php
/**
 * This file contains class::RunningPrognosisStrategy
 * @package Runalyze\Calculations\Prognosis
 */

use Runalyze\Configuration;

/**
 * Class: RunningPrognosisStrategy
 * @author Hannes Christiansen
 * @package Runalyze\Calculations\Prognosis
 */
abstract class RunningPrognosisStrategy {
	/**
	 * Running setup from database
	 */
	abstract public function setupFromDatabase();

	/**
	 * Prognosis in seconds
	 */
	abstract public function inSeconds($distance);

	/**
	 * Get top results (according to vdot_by_time
	 * @param int $numberOfResults number of results to return
	 * @param float $minimalDistanceRequired in km
	 * @return array
	 */
	public function getTopResults($numberOfResults = 1, $minimalDistanceRequired = 3) {
		$Query = '
			SELECT
				`time`, `distance`, `s`, `vdot_by_time`
			FROM (
				SELECT
					`time`, `distance`, `s`, `vdot_by_time`
				FROM `'.PREFIX.'training`
				WHERE
					`sportid`='.Configuration::General()->runningSport().'
					AND `distance` >= "'.$minimalDistanceRequired.'"
				ORDER BY `vdot_by_time` DESC
				LIMIT 20
			) as `tmp`
			GROUP BY `distance`
			ORDER BY `vdot_by_time` DESC
		';

		if ($numberOfResults <= 1)
			return DB::getInstance()->query($Query)->fetch();

		return DB::getInstance()->query($Query.' LIMIT '.$numberOfResults)->fetchAll();
	}
}
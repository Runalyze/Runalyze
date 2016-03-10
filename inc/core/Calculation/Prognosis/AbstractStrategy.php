<?php
/**
 * This file contains class::AbstractStrategy
 * @package Runalyze\Calculation\Prognosis
 */

namespace Runalyze\Calculation\Prognosis;

use DB;
use Runalyze\Configuration;

/**
 * Abstract strategy to calculate a race prognosis
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Prognosis
 */
abstract class AbstractStrategy {
	/**
	 * Running setup from database
	 */
	abstract public function setupFromDatabase();

	/**
	 * Prognosis in seconds
	 */
	abstract public function inSeconds($distance);

	/**
	 * @return bool
	 */
	public function valuesAreValid() {
		return true;
	}

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
					`accountid`='.\SessionAccountHandler::getId().' AND
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
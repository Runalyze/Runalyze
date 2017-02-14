<?php

namespace Runalyze\Calculation\Prognosis;

use DB;
use Runalyze\Configuration;

/**
 * Abstract strategy to calculate a race prognosis
 *
 * @deprecated since v3.3
 */
class TopResults
{
	/**
	 * Get top results (according to vdot_by_time)
	 * @param int $numberOfResults number of results to return
	 * @param float $minimalDistanceRequired in km
	 * @return array
	 */
	public function getTopResults($numberOfResults = 1, $minimalDistanceRequired = 3.0)
    {
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

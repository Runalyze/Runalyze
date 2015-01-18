<?php
/**
 * This file contains class::VDOTCorrector
 * @package Runalyze\Calculation\JD
 */

namespace Runalyze\Calculation\JD;

use PDO;
use Runalyze\Model\Activity;

/**
 * VDOT correction factor
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\JD
 */
class VDOTCorrector {
	/**
	 * Number of (best) races to look at
	 */
	const DB_LOOKUP_LIMIT = 3;

	/**
	 * Global correction factor
	 * @var float
	 */
	static private $GlobalFactor = 1;

	/**
	 * Set global factor
	 * @param float $factor
	 */
	static public function setGlobalFactor($factor) {
		self::$GlobalFactor = $factor;
	}

	/**
	 * Factor
	 * @var float
	 */
	protected $Factor;

	/**
	 * Construct new VDOT corrector
	 * @param float $factor [optional] static factor is used by default
	 */
	public function __construct($factor = null) {
		if (is_null($factor)) {
			$this->Factor = self::$GlobalFactor;
		} else {
			$this->Factor = $factor;
		}
	}

	/**
	 * Factor
	 * @return float
	 */
	public function factor() {
		return $this->Factor;
	}

	/**
	 * Apply
	 * @param \Runalyze\Calculation\JD\VDOT $value
	 */
	public function applyTo(VDOT $value) {
		$value->multiply($this->Factor);
	}

	/**
	 * Calculate factor from database
	 * 
	 * Simply looks for the best ratio of vdot by time and by heart rate.
	 * This method does not regard any other correction (e.g. elevation, ...).
	 * 
	 * @param PDO $database
	 * @param int $accountid
	 * @param int $typeid
	 */
	public function fromDatabase(PDO $database, $accountid, $typeid) {
		$factor = $database->query(
			'SELECT MAX(`factor`) as `factor`
			FROM (
				SELECT `vdot_by_time`*1.0/`vdot` AS `factor` 
				FROM `'.PREFIX.'training` 
				WHERE
					`typeid` = '.(int)$typeid.' AND
					`vdot` > 0 AND
					`accountid` = '.(int)$accountid.'
				ORDER BY  `vdot_by_time` DESC 
				LIMIT '.self::DB_LOOKUP_LIMIT.'
			) AS T
			LIMIT 1'
		)->fetchColumn();

		if ($factor > 0) {
			$this->Factor = $factor;
		} else {
			$this->Factor = 1;
		}
	}

	/**
	 * Calculate factor from activity
	 * 
	 * Simply calculates the ratio of vdot by time and by heart rate.
	 * This method does not regard any other correction (e.g. elevation, ...).
	 * 
	 * @param \Runalyze\Model\Activity\Object $activity
	 */
	public function fromActivity(Activity\Object $activity) {
		if ($activity->vdotByHeartRate() > 0) {
			$this->Factor = $activity->vdotByTime() / $activity->vdotByHeartRate();
		} else {
			$this->Factor = 1;
		}
	}
}
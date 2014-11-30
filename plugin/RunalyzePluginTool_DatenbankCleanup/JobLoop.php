<?php
/**
 * This file contains class::JobLoop
 * @package Runalyze\Plugin\Tool\DatabaseCleanup
 */

namespace Runalyze\Plugin\Tool\DatabaseCleanup;

use Runalyze\Configuration;
use Runalyze\Calculation\JD;

/**
 * JobLoop
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugin\Tool\DatabaseCleanup
 */
class JobLoop extends Job {
	/**
	 * Task key: elevation
	 * @var string
	 */
	const ELEVATION = 'activity-elevation';

	/**
	 * Task key: overwrite elevation
	 * @var string
	 */
	const ELEVATION_OVERWRITE = 'activity-elevation-overwrite';

	/**
	 * Task key: vdot
	 * @var string
	 */
	const VDOT = 'activity-vdot';

	/**
	 * Task key: jd points
	 * @var string
	 */
	const JD_POINTS = 'activity-jdpoints';

	/**
	 * Task key: trimp
	 * @var string
	 */
	const TRIMP = 'activity-trimp';

	/**
	 * Run job
	 */
	public function run() {
		$i = 0;
		$Query = $this->getQuery();
		$Update = $this->prepareUpdate();
		$calculateElevation =
			$this->isRequested(self::ELEVATION) ||
			($this->isRequested(self::VDOT) && Configuration::Vdot()->useElevationCorrection());

		while ($Data = $Query->fetch()) {
			$Training = new \TrainingObject($Data);

			if ($calculateElevation) {
				if (!empty($Data['arr_alt'])) {
					$GPS = new \GpsData($Data);
					$elevationArray = $GPS->calculateElevation(true);
				} else {
					$elevationArray = array(0,0,0);
				}

				if ($this->isRequested(self::ELEVATION)) {
					$Update->bindValue(':elevation_calculated', $elevationArray[0]);
				}

				if ($this->isRequested(self::VDOT)) {
					$Update->bindValue(':vdot_with_elevation', $Training->calculateVDOTbyHeartRateWithElevationFor($elevationArray[1], $elevationArray[2]));
				}

				if ($this->isRequested(self::ELEVATION_OVERWRITE)) {
					$Update->bindValue(':elevation', $elevationArray[0]);
				}
			}

			if ($this->isRequested(self::VDOT)) {
				$Update->bindValue(':vdot', $Training->calculateVDOTbyHeartRate());
				$Update->bindValue(':vdot_by_time', $Training->calculateVDOTbyTime());
			}

			if ($this->isRequested(self::JD_POINTS)) {
				$Update->bindValue(':jd_intensity', $Training->calculateJDintensity());
			}

			if ($this->isRequested(self::TRIMP)) {
				$Update->bindValue(':trimp', $Training->calculateTrimp());
			}

			$Update->bindValue(':id', $Data['id']);
			$Update->execute();
			$i++;
		}

		$this->addMessage( sprintf( __('%d activities have been updated.'), $i) );
	}

	/**
	 * Prepare statement
	 * @return \PDOStatement
	 */
	protected function prepareUpdate() {
		$Set = array();
	
		if ($this->isRequested(self::ELEVATION)) {
			$Set[] = 'elevation_calculated';
			$Set[] = 'vdot_with_elevation';

			if ($this->isRequested(self::ELEVATION_OVERWRITE)) {
				$Set[] = 'elevation';
			}
		} elseif ($this->isRequested(self::VDOT) && Configuration::Vdot()->useElevationCorrection()) {
			$Set[] = 'vdot_with_elevation';
		}

		if ($this->isRequested(self::VDOT)) {
			$Set[] = 'vdot';
			$Set[] = 'vdot_by_time';
		}

		if ($this->isRequested(self::JD_POINTS)) {
			$Set[] = 'jd_intensity';
		}

		if ($this->isRequested(self::TRIMP)) {
			$Set[] = 'trimp';
		}

		foreach ($Set as $i => $key) {
			$Set[$i] = '`'.$key.'`=:'.$key;
		}

		$Query = 'UPDATE `'.PREFIX.'training` SET '.implode(',', $Set).' WHERE `id`=:id LIMIT 1';

		return \DB::getInstance()->prepare($Query);
	}

	/**
	 * Get query statement
	 * @return \PDOStatement
	 */
	protected function getQuery() {
		// TODO: Use trackdata as well
		// TODO: Directly add accountid
		return \DB::getInstance()->query(
			'SELECT
				`id`,
				`sportid`,
				`typeid`,
				`distance`,
				`s`,
				`pulse_avg`,
				`arr_heart`,
				`arr_time`,
				`arr_alt`
			FROM `'.PREFIX.'training`
			WHERE 1'
		);
	}
}
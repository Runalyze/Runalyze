<?php
/**
 * This file contains class::JobLoop
 * @package Runalyze\Plugin\Tool\DatabaseCleanup
 */

namespace Runalyze\Plugin\Tool\DatabaseCleanup;

use DB;

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
	 * Calculated elevations
	 * @var array
	 */
	protected $ElevationResults = array();

	/**
	 * Run job
	 */
	public function run() {
		if ($this->isRequested(self::ELEVATION)) {
			$this->runRouteLoop();
		}

		if (count($this->updateSet())) {
			$this->runActivityLoop();
		}
	}

	/**
	 * Run route loop
	 */
	protected function runRouteLoop() {
		require_once __DIR__.'/ElevationsRecalculator.php';

		$Recalculator = new ElevationsRecalculator(DB::getInstance());
		$Recalculator->run();

		$this->ElevationResults = $Recalculator->results();

		$this->addMessage( sprintf( __('Elevations have been recalculated for %d routes.'), count($this->ElevationResults)) );
	}

	/**
	 * Run activity loop
	 */
	protected function runActivityLoop() {
		$i = 0;
		$Query = $this->getQuery();
		$Update = $this->prepareUpdate();

		while ($Data = $Query->fetch()) {
			$Training = new \TrainingObject($Data);
			$elevations = $this->elevationsFor($Data);

			if ($this->isRequested(self::ELEVATION) && $this->isRequested(self::ELEVATION_OVERWRITE)) {
				$Update->bindValue(':elevation', $elevations[0]);
			}

			if ($this->isRequested(self::VDOT)) {
				$Update->bindValue(':vdot', $Training->calculateVDOTbyHeartRate());
				$Update->bindValue(':vdot_by_time', $Training->calculateVDOTbyTime());
				$Update->bindValue(':vdot_with_elevation', $Training->calculateVDOTbyHeartRateWithElevationFor($elevations[1], $elevations[2]));
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
	 * Elevations for activity
	 * @param array $data activity data
	 * @return array ('total', 'up', 'down', 'calculated')
	 */
	protected function elevationsFor(array $data) {
		if (isset($this->ElevationResults[$data['id']])) {
			return $this->ElevationResults[$data['id']];
		}

		return array($data['elevation'], $data['elevation'], $data['elevation']);
	}

	/**
	 * Prepare statement
	 * @return \PDOStatement
	 */
	protected function prepareUpdate() {
		$Set = $this->updateSet();

		foreach ($Set as $i => $key) {
			$Set[$i] = '`'.$key.'`=:'.$key;
		}

		$Query = 'UPDATE `'.PREFIX.'training` SET '.implode(',', $Set).' WHERE `id`=:id LIMIT 1';

		return \DB::getInstance()->prepare($Query);
	}

	/**
	 * Keys to update
	 * @return array
	 */
	protected function updateSet() {
		$Set = array();
	
		if ($this->isRequested(self::ELEVATION) && $this->isRequested(self::ELEVATION_OVERWRITE)) {
			$Set[] = 'elevation';
		}

		if ($this->isRequested(self::VDOT)) {
			$Set[] = 'vdot';
			$Set[] = 'vdot_by_time';
			$Set[] = 'vdot_with_elevation';
		}

		if ($this->isRequested(self::JD_POINTS)) {
			$Set[] = 'jd_intensity';
		}

		if ($this->isRequested(self::TRIMP)) {
			$Set[] = 'trimp';
		}

		return $Set;
	}

	/**
	 * Get query statement
	 * @return \PDOStatement
	 */
	protected function getQuery() {
		// TODO: Directly add accountid
		$accountid = \SessionAccountHandler::getId();

		return \DB::getInstance()->query(
			'SELECT
				`'.PREFIX.'training`.`id`,
				`'.PREFIX.'training`.`sportid`,
				`'.PREFIX.'training`.`typeid`,
				`'.PREFIX.'training`.`distance`,
				`'.PREFIX.'training`.`s`,
				`'.PREFIX.'training`.`pulse_avg`,
				`'.PREFIX.'training`.`elevation`,
				`'.PREFIX.'trackdata`.`time` as `arr_time`,
				`'.PREFIX.'trackdata`.`heartrate` as `arr_heart`
			FROM `'.PREFIX.'training`
			LEFT JOIN `'.PREFIX.'trackdata` ON `'.PREFIX.'trackdata`.`activityid` = `'.PREFIX.'training`.`id`
			WHERE `'.PREFIX.'training`.`accountid` = '.$accountid
		);
	}
}
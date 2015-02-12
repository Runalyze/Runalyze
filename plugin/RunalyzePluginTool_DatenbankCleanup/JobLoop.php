<?php
/**
 * This file contains class::JobLoop
 * @package Runalyze\Plugin\Tool\DatabaseCleanup
 */

namespace Runalyze\Plugin\Tool\DatabaseCleanup;

use Runalyze\Calculation\Activity\Calculator;
use Runalyze\Configuration;
use Runalyze\Model\Activity;
use Runalyze\Model\Trackdata;
use Runalyze\Model\Route;

use DB;
use SessionAccountHandler;

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

			// This may be removed if single activities are not cached anymore.
			\Cache::clean();
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
			try {
				$Calculator = $this->calculatorFor($Data);

                $calculate_vdot=$Data['sportid']==Configuration::General()->runningSport();

				if ($this->isRequested(self::ELEVATION) && $this->isRequested(self::ELEVATION_OVERWRITE)) {
					$elevations = $this->elevationsFor($Data);
					$Update->bindValue(':elevation', $elevations[0]);
				}

				if ($this->isRequested(self::VDOT)) {
					$Update->bindValue(':vdot', $calculate_vdot?$Calculator->calculateVDOTbyHeartRate():0);
					$Update->bindValue(':vdot_by_time', $calculate_vdot?$Calculator->calculateVDOTbyTime():0);
					$Update->bindValue(':vdot_with_elevation', $calculate_vdot?$Calculator->calculateVDOTbyHeartRateWithElevation():0);
				}

				if ($this->isRequested(self::JD_POINTS)) {
					$Update->bindValue(':jd_intensity', $calculate_vdot ? $Calculator->calculateJDintensity() : 0);
				}

				if ($this->isRequested(self::TRIMP)) {
					$Update->bindValue(':trimp', $Calculator->calculateTrimp());
				}

				$Update->bindValue(':id', $Data['id']);
				$Update->execute();
				$i++;
			} catch (\RuntimeException $Exc) {
				$this->addMessage( sprintf( __('There was a problem with activity %d.<br>Error message: %s'), $Data['id'], $Exc->getMessage()) );
			}
		}

		$this->addMessage( sprintf( __('%d activities have been updated.'), $i) );
	}

	/**
	 * @param array $data
	 * @return \Runalyze\Calculation\Activity\Calculator
	 */
	protected function calculatorFor(array $data) {
		$elevations = $this->elevationsFor($data);

		return new Calculator(
			new Activity\Object($data),
			new Trackdata\Object(array(
				Trackdata\Object::TIME => $data['trackdata_time'],
				Trackdata\Object::HEARTRATE => $data['trackdata_heartrate']
			)),
			new Route\Object(array(
				Route\Object::ELEVATION => $elevations[0],
				Route\Object::ELEVATION_UP => $elevations[1],
				Route\Object::ELEVATION_DOWN => $elevations[2]
			))
		);
	}

	/**
	 * Elevations for activity
	 * @param array $data activity data
	 * @return array ('total', 'up', 'down', 'calculated')
	 */
	protected function elevationsFor(array $data) {
		if (isset($this->ElevationResults[$data['routeid']])) {
			return $this->ElevationResults[$data['routeid']];
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

		return DB::getInstance()->prepare($Query);
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
		$accountid = SessionAccountHandler::getId();

		return DB::getInstance()->query(
			'SELECT
				`'.PREFIX.'training`.`id`,
				`'.PREFIX.'training`.`routeid`,
				`'.PREFIX.'training`.`sportid`,
				`'.PREFIX.'training`.`typeid`,
				`'.PREFIX.'training`.`distance`,
				`'.PREFIX.'training`.`s`,
				`'.PREFIX.'training`.`pulse_avg`,
				`'.PREFIX.'training`.`elevation`,
				`'.PREFIX.'trackdata`.`time` as `trackdata_time`,
				`'.PREFIX.'trackdata`.`heartrate` as `trackdata_heartrate`
			FROM `'.PREFIX.'training`
			LEFT JOIN `'.PREFIX.'trackdata` ON `'.PREFIX.'trackdata`.`activityid` = `'.PREFIX.'training`.`id`
			WHERE `'.PREFIX.'training`.`accountid` = '.$accountid
		);
	}
}
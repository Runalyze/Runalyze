<?php
/**
 * This file contains class::TableZonesPace
 * @package Runalyze\DataObjects\Training\View
 */

use Runalyze\Activity\Pace;
use Runalyze\Activity\HeartRate;
use Runalyze\Calculation\Distribution\TimeSeriesForTrackdata;
use Runalyze\Calculation\Activity\PaceSmoother;
use Runalyze\Model\Trackdata;

/**
 * Display pace zones
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class TableZonesPace extends TableZonesAbstract {
	/**
	 * Step size to smooth pace date
	 * @var int
	 */
	const STEP_SIZE = 10;

	/**
	 * @var \Runalyze\Activity\PaceUnit\AbstractUnit
	 */
	protected $PaceUnit;

	/**
	 * Get title for average
	 * @return string
	 */
	public function titleForAverage() { return '&oslash;&nbsp;'.__('HR'); }

	/**
	 * Init data
	 */
	protected function initData() {
		$this->PaceUnit = $this->Context->sport()->paceUnit();

		$Zones = $this->computeZones();
		$Pace = new Pace(0, 1);
		$Pace->setUnit($this->PaceUnit);
		$HR = new HeartRate(0, Runalyze\Context::Athlete());

		foreach ($Zones as $paceInSeconds => $Info) {
			if ($Info['time'] > parent::MINIMUM_TIME_IN_ZONE) {
				$Pace->setTime($paceInSeconds);
				$HR->setBPM($Info['time'] > 0 ? $Info['hr'] / $Info['time'] : 0);

				$this->Data[] = array(
					'zone'     => $paceInSeconds == 0 ? __('faster') : '&gt; '.$Pace->valueWithAppendix(),
					'time'     => $Info['time'],
					'distance' => $Info['distance'],
					'average'  => $HR->asBPM() > 0 ? $HR->string() : '-'
				);
			}
		}
	}

	/**
	 * @return array
	 */
	protected function computeZones() {
		// TODO
		// - move this a calculation class
		// - make zones configurable
		$Zones = array();
		$SmoothTrackdata = clone $this->Context->trackdata();
		$Smoother = new PaceSmoother($SmoothTrackdata, true);
		$SmoothTrackdata->set(Trackdata\Entity::PACE, $Smoother->smooth(self::STEP_SIZE, PaceSmoother::MODE_STEP));
		$hasHR = $this->Context->trackdata()->has(Trackdata\Entity::HEARTRATE);

		$Distribution = new TimeSeriesForTrackdata(
			$SmoothTrackdata,
			Trackdata\Entity::PACE,
			array(Trackdata\Entity::DISTANCE),
			$hasHR ? array(Trackdata\Entity::HEARTRATE) : array()
		);
		$Data = $Distribution->data();

		foreach ($Distribution->histogram() as $paceInSeconds => $seconds) {
			$pace = $this->zoneFor($paceInSeconds);

			if (!isset($Zones[$pace])) {
				$Zones[$pace] = array(
					'time' => $seconds,
					'distance' => $Data[$paceInSeconds][Trackdata\Entity::DISTANCE],
					'hr' => $hasHR ? $Data[$paceInSeconds][Trackdata\Entity::HEARTRATE] * $seconds : 0,
				);
			} else {
				$Zones[$pace]['time'] += $seconds;
				$Zones[$pace]['distance'] += $Data[$paceInSeconds][Trackdata\Entity::DISTANCE];
				$Zones[$pace]['hr'] += $hasHR ? $Data[$paceInSeconds][Trackdata\Entity::HEARTRATE] * $seconds : 0;
			}
		}

		ksort($Zones, SORT_NUMERIC);

		return $Zones;
	}

	/**
	 * @param int $paceInSeconds
	 * @return int
	 */
	protected function zoneFor($paceInSeconds) {
		if ($paceInSeconds == 0) {
			return 0;
		}

		if ($this->PaceUnit->isDecimalFormat()) {
			$dividend = $this->PaceUnit->dividendForUnit();

			return $paceInSeconds > $dividend ? 0 : $dividend / floor($dividend / $paceInSeconds);
		}

		$factor = $this->PaceUnit->factorForUnit();

		return round(30 * floor($factor * $paceInSeconds / 30) / $factor);
	}
}
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
	 * @var enum
	 */
	protected $paceUnit;

	/**
	 * Get title for average
	 * @return string
	 */
	public function titleForAverage() { return '&oslash;&nbsp;'.__('HR'); }

	/**
	 * Init data
	 */
	protected function initData() {
		$this->paceUnit = $this->Context->sport()->paceUnit();

		$Zones = $this->computeZones();
		$hrMax = Runalyze\Configuration::Data()->HRmax();
		$Pace = new Pace(0, 1, $this->paceUnit);
		$HR = new HeartRate(0, Runalyze\Context::Athlete());

		foreach ($Zones as $paceInSeconds => $Info) {
			if ($Info['time'] > parent::MINIMUM_TIME_IN_ZONE) {
				$Pace->setTime($paceInSeconds);
				$HR->setBPM($Info['time'] > 0 ? $Info['hr'] / $Info['time'] : 0);

				$this->Data[] = array(
					'zone'     => $paceInSeconds == 0 ? __('faster') : '&gt; '.$Pace->valueWithAppendix(),
					'time'     => $Info['time'],
					'distance' => $Info['distance'],
					'average'  => $HR->string()
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
		$SmoothTrackdata->set(Trackdata\Object::PACE, $Smoother->smooth(self::STEP_SIZE, PaceSmoother::MODE_STEP));

		$Distribution = new TimeSeriesForTrackdata(
			$SmoothTrackdata,
			Trackdata\Object::PACE,
			array(Trackdata\Object::DISTANCE),
			array(Trackdata\Object::HEARTRATE)
		);
		$Data = $Distribution->data();

		foreach ($Distribution->histogram() as $paceInSeconds => $seconds) {
			$pace = $this->zoneFor($paceInSeconds);

			if (!isset($Zones[$pace])) {
				$Zones[$pace] = array(
					'time' => $seconds,
					'distance' => $Data[$paceInSeconds][Trackdata\Object::DISTANCE],
					'hr' => $Data[$paceInSeconds][Trackdata\Object::HEARTRATE] * $seconds,
				);
			} else {
				$Zones[$pace]['time'] += $seconds;
				$Zones[$pace]['distance'] += $Data[$paceInSeconds][Trackdata\Object::DISTANCE];
				$Zones[$pace]['hr'] += $Data[$paceInSeconds][Trackdata\Object::HEARTRATE] * $seconds;
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

		switch ($this->paceUnit) {
			case Pace::KM_PER_H:
				return $paceInSeconds > 720 ? 0 : 3600 / floor(3600 / $paceInSeconds / 5) / 5;

			case Pace::M_PER_S:
				return $paceInSeconds > 1000 ? 0 : 1000 / floor(1000 / $paceInSeconds);

			case Pace::MIN_PER_100M:
				return 50 * floor($paceInSeconds / 50);

			case Pace::MIN_PER_KM:
			default:
				return 60 * floor($paceInSeconds / 60);
		}
	}
}
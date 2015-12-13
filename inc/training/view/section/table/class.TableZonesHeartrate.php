<?php
/**
 * This file contains class::TableZonesHeartrate
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Activity\Pace;
use Runalyze\Calculation\Distribution\TimeSeriesForTrackdata;
use Runalyze\Model\Trackdata;

/**
 * Display heartrate zones
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class TableZonesHeartrate extends TableZonesAbstract {
	/**
	 * Get title for average
	 * @return string
	 */
	public function titleForAverage() { return '&oslash;&nbsp;'.__('Pace'); }

	/**
	 * Init data
	 */
	protected function initData() {
		$Zones = $this->computeZones();
		$Pace = new Pace(0, 0);
		$Pace->setUnit($this->Context->sport()->paceUnit());

		foreach ($Zones as $hf => $Info) {
			if ($Info['time'] > parent::MINIMUM_TIME_IN_ZONE) {
				$Pace->setTime($Info['time']);
				$Pace->setDistance($Info['distance']);

				$this->Data[] = array(
					'zone'     => '&lt;&nbsp;'.$hf.'&nbsp;&#37;',
					'time'     => $Info['time'],
					'distance' => $Info['distance'],
					'average'  => $Pace->value() > 0 ? $Pace->valueWithAppendix() : '-'
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
		$hrMax = Runalyze\Configuration::Data()->HRmax();
		$hasDistance = $this->Context->trackdata()->has(Trackdata\Entity::DISTANCE);

		$Distribution = new TimeSeriesForTrackdata(
			$this->Context->trackdata(),
			Trackdata\Entity::HEARTRATE,
			$hasDistance ? array(Trackdata\Entity::DISTANCE) : array()
		);
		$Data = $Distribution->data();

		foreach ($Distribution->histogram() as $bpm => $seconds) {
			$hf = $this->zoneFor($bpm, $hrMax);

			if (!isset($Zones[$hf])) {
				$Zones[$hf] = array(
					'time' => $seconds,
					'distance' => $hasDistance ? $Data[$bpm][Trackdata\Entity::DISTANCE] : 0
				);
			} else {
				$Zones[$hf]['time'] += $seconds;
				$Zones[$hf]['distance'] += $hasDistance ? $Data[$bpm][Trackdata\Entity::DISTANCE] : 0;
			}
		}

		ksort($Zones, SORT_NUMERIC);

		return $Zones;
	}

	/**
	 * @param int $bpm
	 * @param int $hrMax
	 * @return int
	 */
	protected function zoneFor($bpm, $hrMax) {
		// TODO
		return Helper::ceilFor(100 * $bpm / $hrMax, 10);
	}
}
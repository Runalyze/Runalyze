<?php
/**
 * This file contains class::TableZonesHeartrate
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Model\Trackdata;
use Runalyze\Calculation\Distribution\TimeSeries;

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
	public function titleForAverage() { return __('Pace'); }

	/**
	 * Init data
	 */
	protected function initData() {
		$Zones = $this->computeZones();

		foreach ($Zones as $hf => $Info) {
			if ($Info['time'] > parent::MINIMUM_TIME_IN_ZONE) {
				$this->Data[] = array(
					'zone'     => '&lt;&nbsp;'.$hf.'&nbsp;&#37;',
					'time'     => $Info['time'],
					'distance' => $Info['distance'],
					'average'  => ''
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
		// - calculate distance / average pace of zone
		$Zones = array();
		$hrMax = Runalyze\Configuration::Data()->HRmax();

		$Distribution = new TimeSeries(
			$this->Context->trackdata()->get(Trackdata\Object::HEARTRATE),
			$this->Context->trackdata()->get(Trackdata\Object::TIME)
		);

		foreach ($Distribution->histogram() as $bpm => $seconds) {
			$hf = $this->zoneFor($bpm, $hrMax);

			if (!isset($Zones[$hf])) {
				$Zones[$hf] = array(
					'time' => $seconds,
					'distance' => 0
				);
			} else {
				$Zones[$hf]['time'] += $seconds;
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
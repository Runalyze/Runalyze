<?php
/**
 * This file contains class::TableZonesPace
 * @package Runalyze\DataObjects\Training\View
 */

use Runalyze\Model\Trackdata;
use Runalyze\Activity\Pace;
use Runalyze\Calculation\Distribution\TimeSeries;

/**
 * Display pace zones
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class TableZonesPace extends TableZonesAbstract {
	/**
	 * Get title for average
	 * @return string
	 */
	public function titleForAverage() { return __('&oslash;&nbsp;Pace'); }

	/**
	 * Init data
	 */
	protected function initData() {
		$Zones = $this->computeZones();
		$hrMax = Runalyze\Configuration::Data()->HRmax();

		foreach ($Zones as $paceInSeconds => $Info) {
			if ($Info['time'] > parent::MINIMUM_TIME_IN_ZONE) {
				$Pace = new Pace($paceInSeconds, 1, Pace::MIN_PER_KM);

				$this->Data[] = array(
					'zone'     => $paceInSeconds == 0 ? __('faster') : '&gt; '.$Pace->valueWithAppendix(),
					'time'     => $Info['time'],
					'distance' => $Info['distance'],
					'average'  => $Info['hf-sum'] > 0 ? round(100*$Info['hf-sum']/$hrMax/$Info['num']).'&nbsp;&#37;' : '-'
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
		// - calculate distance / average hr of zone
		$Zones = array();

		$Distribution = new TimeSeries(
			$this->Context->trackdata()->get(Trackdata\Object::PACE),
			$this->Context->trackdata()->get(Trackdata\Object::TIME)
		);

		foreach ($Distribution->histogram() as $paceInSeconds => $seconds) {
			$pace = $this->zoneFor($paceInSeconds);

			if (!isset($Zones[$pace])) {
				$Zones[$pace] = array(
					'time' => $seconds,
					'distance' => 0,
					'hf-sum' => 0,
					'num' => 0
				);
			} else {
				$Zones[$pace]['time'] += $seconds;
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
		// TODO
		return Helper::ceilFor($paceInSeconds, 60);
	}
}
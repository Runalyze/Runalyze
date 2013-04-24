<?php
/**
 * This file contains class::ZonesHeartrate
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display heartrate zones
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class ZonesHeartrate extends ZonesAbstract {
	/**
	 * Get title
	 * @return string
	 */
	public function title() {
		return 'Pulszonen';
	}

	/**
	 * Get title for average
	 * @return string
	 */
	public function titleForAverage() { return 'Pace'; }

	/**
	 * Init data
	 */
	protected function initData() {
		$Zones = $this->Training->GpsData()->getPulseZonesAsFilledArrays();

		foreach ($Zones as $hf => $Info) {
			if ($Info['distance'] > self::$MINIMUM_DISTANCE_FOR_ZONE)
				$this->Data[] = array(
					'zone'     => '&lt; '.(10*$hf).'&nbsp;&#37;',
					'time'     => $Info['time'],
					'distance' => $Info['distance'],
					'average'  => SportSpeed::getSpeedWithAppendix($Info['num'], $Info['pace-sum'], SportSpeed::$MIN_PER_KM)
				);
		}
	}
}
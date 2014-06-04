<?php
/**
 * This file contains class::TableZonesHeartrate
 * @package Runalyze\DataObjects\Training\View\Section
 */
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
		$Zones = $this->Training->GpsData()->getPulseZonesAsFilledArrays();

		foreach ($Zones as $hf => $Info) {
			if ($Info['distance'] > self::$MINIMUM_DISTANCE_FOR_ZONE)
				$this->Data[] = array(
					'zone'     => '&lt;&nbsp;'.(10*$hf).'&nbsp;&#37;',
					'time'     => $Info['time'],
					'distance' => $Info['distance'],
					'average'  => SportSpeed::getSpeedWithAppendix($Info['num'], $Info['pace-sum'], SportSpeed::$MIN_PER_KM)
				);
		}
	}
}
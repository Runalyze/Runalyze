<?php
/**
 * This file contains class::TableZonesPace
 * @package Runalyze\DataObjects\Training\View
 */
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
		$Zones = $this->Training->GpsData()->getPaceZonesAsFilledArrays();

		foreach ($Zones as $min => $Info) {
			if ($Info['distance'] > self::$MINIMUM_DISTANCE_FOR_ZONE) {
				if ($Info['hf-sum'] > 0)
					$Avg = round(100*$Info['hf-sum']/Helper::getHFmax()/$Info['num']).'&nbsp;&#37;';
				else
					$Avg = '-';

				$this->Data[] = array(
					'zone'     => ($min == 0 ? __('faster') : '&gt; '.SportSpeed::getSpeedWithAppendix(1, $min*60, SportSpeed::$MIN_PER_KM)),
					'time'     => $Info['time'],
					'distance' => $Info['distance'],
					'average'  => $Avg);
			}
		}
	}
}
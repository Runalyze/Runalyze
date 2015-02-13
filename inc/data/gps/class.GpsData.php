<?php
/**
 * This file contains class::GpsData
 * @package Runalyze\Data\GPS
 */

/**
 * GPS data
 * @author Hannes Christiansen
 * @package Runalyze\Data\GPS
 */
class GpsData {

	/**
	 * Constructor
	 */
	public function __construct($TrainingDataAsArray) {
		throw new RuntimeException('class::GpsData is deprecated.');
	}

	/**
	 * Calculate distance between two coordinates
	 * @param double $lat1
	 * @param double $lon1
	 * @param double $lat2
	 * @param double $lon2
	 * @return double
	 */
	static public function distance($lat1, $lon1, $lat2, $lon2) {
		$rad1 = deg2rad($lat1);
		$rad2 = deg2rad($lat2);
		$dist = sin($rad1) * sin($rad2) +  cos($rad1) * cos($rad2) * cos(deg2rad($lon1 - $lon2)); 
		$dist = acos($dist); 
		$dist = rad2deg($dist); 
		$miles = $dist * 60 * 1.1515;

		if (is_nan($miles))
			return 0;
	
		return ($miles * 1.609344);
	}
}

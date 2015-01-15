<?php
/**
 * This file contains class::GpsData
 * @package Runalyze\Data\GPS
 */

use Runalyze\Data\Elevation;

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
	 * Correct the elevation data and return new array
	 * @return mixed
	 */
	public function getElevationCorrection() {
		if (!$this->hasPositionData())
			return;

		try {
			$Corrector = new Elevation\Correction\Corrector();
			$Corrector->correctElevation($this->arrayForLatitude, $this->arrayForLongitude);

			$elevationArray = $Corrector->getCorrectedElevation();

			if (!empty($elevationArray)) {
				$this->arrayForElevation = $elevationArray;
				$this->correctInvalidElevationValues();

				return $this->arrayForElevation;
			}
		} catch (Exception $Exception) {
			// TODO: Make this exception somehow visible
			Error::getInstance()->addError($Exception->getMessage());
		}

		return false;
	}

	/**
	 * Correct invalid values for elevation in case of missing latitude/longitude
	 */
	private function correctInvalidElevationValues() {
		$this->startLoop();
		$this->correctInvalidElevationValuesAtCurrentPoint();

		while ($this->nextStep())
			$this->correctInvalidElevationValuesAtCurrentPoint();

		if ($this->arrayForElevation[0] == 0) {
			array_filter($this->arrayForElevation, 'GpsData_Filter_Zero');
			$min = reset($this->arrayForElevation);
			array_walk($this->arrayForElevation, 'GpsData_Walk_Replace_Zero', $min);
		}
	}

	/**
	 * Correct invalid values at current point 
	 */
	private function correctInvalidElevationValuesAtCurrentPoint() {
		if ($this->getLatitude() == 0 || $this->getLongitude() == 0 || $this->getElevation() <= 0) {
			if (isset($this->arrayForLatitude[$this->arrayLastIndex])) {
				$this->arrayForLatitude[$this->arrayIndex] = $this->arrayForLatitude[$this->arrayLastIndex];
				$this->arrayForLongitude[$this->arrayIndex] = $this->arrayForLongitude[$this->arrayLastIndex];
				$this->arrayForElevation[$this->arrayIndex] = $this->arrayForElevation[$this->arrayLastIndex];
			} else {
				$this->arrayForElevation[$this->arrayIndex] = 0;
			}
		}
	}

	/**
	 * Calculate virtual power
	 * @see http://www.blog.ultracycle.net/2010/05/cycling-power-calculations
	 * @return array
	 */
	public function calculatePower() {
		if (!$this->hasDistanceData() || !$this->hasTimeData())
			return array();

		/* same step size as elevation, since we use that data
		 * to calculate grade
		 */
		$everyNthPoint  = self::$everyNthElevationPoint * ceil($this->arraySizes/1000);
		$n              = $everyNthPoint;
		$power          = array();
		$distance       = 0;
		$grade          = 0;
		$calcGrade      = $this->hasElevationData();

		$PowerFactor = 1.5; /* XXX CONFIG */

		$Wkg  = 75; /* XXX CONFIG */
		$Crr  = 0.004; /* XXX CONFIG */
		$g    = 9.8;
		$Frl  = $Wkg * $g * $Crr;

		$A    = 0.5;
		$Cw   = 0.5;
		$Rho  = 1.226; /* XXX CONFIG/COMPUTE? */
		$Fwpr = 0.5 * $A * $Cw * $Rho;

		$Fslp = $Wkg * $g;

		for ($i = 0; $i < $this->arraySizes-1; $i++) {
			if ($i%$everyNthPoint == 0) {
				if ($i+$n > $this->arraySizes-1)
					$n = $this->arraySizes-$i-1;
				$distance = ($this->arrayForDistance[$i+$n]-$this->arrayForDistance[$i])*1000;
				if ($distance == 0 || !$calcGrade)
					$grade = 0;
				else
					$grade = ($this->arrayForElevation[$i+$n]-$this->arrayForElevation[$i])/$distance;
			}

			$distance = $this->arrayForDistance[$i+1]-$this->arrayForDistance[$i];
			$time = $this->arrayForTime[$i+1]-$this->arrayForTime[$i];
			if ($time > 0) {
				$Vmps = $distance*1000/$time;
				$Fw   = $Fwpr * $Vmps * $Vmps;
				$Fsl  = $Fslp * $grade;
				$power[] = round(max($PowerFactor * ($Frl + $Fw + $Fsl) * $Vmps, 0));
				//error_log("(".$Frl." + ".$Fw." + ".$Fsl.") * ".$Vmps." = ".$power[$i]);
			} else {
				$power[] = 0;
			}
		}

		$power[] = $power[$this->arraySizes-2]; /* XXX */

		$this->arrayForPower = $power;

		return $power;
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

/**
 * Filter function to filter all negative/zero values out
 * @param mixed $value
 * @return boolean
 */
function GpsData_Filter_Zero($value) {
	return $value > 0;
}

/**
 * Walk function to replace zeros/negative values with another value
 * @param mixed $value
 * @param int $key
 * @param float $newValueForZeroes 
 */
function GpsData_Walk_Replace_Zero(&$value, $key, $newValueForZeros) {
	if ($value <= 0)
		$value = $newValueForZeros;
}

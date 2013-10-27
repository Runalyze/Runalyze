<?php
/**
 * This file contains class::Math
 * @package Runalyze\Calculations
 */
/**
 * Mathematic calculations
 * 
 * Some additional mathematic calculations
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculations
 */
class Math {
	/**
	 * Calculate the variance of an array
	 * @param array $array Array with numeric values
	 * @return double Variance of the dataset
	 */
	public static function Variance($array) {
		$avg = array_sum($array) / count($array);
		$d   = 0;

		foreach ($array as $dat)
			if (is_numeric($dat))
				$d += pow($dat - $avg, 2);

		return ($d / count($array));
	}

	/**
	 * Get a value with leading sign
	 * 
	 * Examples:
	 * <code>
	 *  Math::WithSign(5); // result: "+5"
	 *  Math::WithSign(0); // result: "0"
	 *  Math::WithSign(-3); // result: "-3"
	 * </code>
	 * 
	 * @param mixed $value
	 * @return string
	 */
	public static function WithSign($value) {
		if ($value == 0)
			return 0;
		if ($value > 0)
			return '+'.$value;
		if ($value < 0)
			return $value;
	}
}
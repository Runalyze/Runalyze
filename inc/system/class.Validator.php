<?php
/**
 * This file contains the class Validator for validating user inputs
 */
/**
 * Class: Validator
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 */

class Validator {
	/**
	 * Transform a datestring to timestamp
	 * @param string $dateAsString
	 * @param int $default
	 * @return int
	 */
	static public function dateToTimestamp($dateAsString, $default = 0) {
		$dateParts = self::removeEmptyValues(explode('.', $dateAsString));
		$numParts  = count($dateParts);

		if ($numParts == 3)
			return mktime(0, 0, 0, $dateParts[1], $dateParts[0], $dateParts[2]);
		if ($numParts == 2)
			return mktime(0, 0, 0, $dateParts[1], $dateParts[0], date('Y'));

		return $default;
	}

	/**
	 * Remove all empty values from array
	 * @param array $array
	 * @return array
	 */
	static private function removeEmptyValues($array) {
		foreach ($array as $key => $value)
			if (empty($value))
				unset($array[$key]);

		return $array;
	}

	/**
	 * Is the given value between $low and $high?
	 * @param float $low
	 * @param float $high
	 * @param float $value
	 * @return boolean
	 */
	static public function isInRange($low, $high, $value) {
		return ($value >= $low && $value <= $high);
	}
}
?>
<?php
/**
 * Class: Validator
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class Validator {
	/**
	 * Is the given array an associative one? (one associative key is enough for success)
	 * @param array $array
	 * @return bool
	 */
	public static function isAssoc($array) {
		return array_keys($array) !== range(0, count($array) - 1);
	}

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

	/**
	 * Is the given value close to the value it should be?
	 * @param type $value
	 * @param type $shouldBe
	 * @param type $precisionInPercent
	 * @return boolean 
	 */
	static public function isClose($value, $shouldBe, $precisionInPercent = 1) {
		return (abs($value - $shouldBe) / $shouldBe) <= ($precisionInPercent/100);
	}
}
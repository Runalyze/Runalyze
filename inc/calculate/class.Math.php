<?php
class Math {
	/**
	 * Disable constructor for public access 
	 */
	private function __construct() {}

	/**
	 * Calculate the variance of a given (numeric) array
	 * @param array $array
	 * @return double
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
?>

<?php
/**
 * This file contains class::StringReader
 * @package Runalyze\Util
 */

namespace Runalyze\Util;

use Runalyze\Activity\Duration;

/**
 * Read specific information from string
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Util
 */
class StringReader {
	/**
	 * @var string
	 */
	protected $String;

	/**
	 * @param string $string
	 */
	public function __construct($string = '') {
		$this->setString($string);
	}

	/**
	 * @param string $string
	 * @return \Runalyze\Util\StringReader
	 */
	public function setString($string) {
		$this->String = $string;

		return $this;
	}

	/**
	 * Find a pace goal within a string
	 * 
	 * This method can be used to extract the demanded pace within a description,
	 * e.g. to find '3:20' within '6x1000m in 3:20, 400m pauses'.
	 * It will look for a time string directly after the given search pattern.
	 * The time string will be interpreted as time per kilometer.
	 * 
	 * @param string $searchPattern [optional] String that must occur directly before the pace
	 * @return int Pace in s/km, 0 if nothing found
	 */
	public function findDemandedPace($searchPattern = ' in ') {
		$Lookup = explode($searchPattern, $this->String);

		if (count($Lookup) < 2)
			return 0;

		$cutPosition = strpos($Lookup[1], ' ');
		$timeString = $cutPosition !== false ? substr($Lookup[1], 0, $cutPosition) : $Lookup[1];

		$Duration = new Duration($timeString);

		return $Duration->seconds();
	}
}
<?php
/**
 * This file contains class::Set
 * @package Runalyze\Parameter
 */

namespace Runalyze\Parameter;

use Runalyze\Parameter;

/**
 * Array
 * @author Hannes Christiansen
 * @package Runalyze\Parameter
 */
class Set extends \Runalyze\Parameter {
	/**
	 * Seperator
	 * @var string
	 */
	const SEPERATOR = ',';

	/**
	 * Array to string
	 * @param array $array
	 * @return string
	 */
	static public function arrayToString(array $array) {
		$string = implode(self::SEPERATOR, $array);

		if (strlen($string) > Parameter::MAX_LENGTH)
			$string = substr($string, -Parameter::MAX_LENGTH);

		return $string;
	}

	/**
	 * Set from string
	 * @param string $valueAsString new value
	 */
	public function setFromString($valueAsString) {
		$this->set( ($valueAsString == 'true') );

		$valueAsArray = array();

		if (strlen($valueAsString) > 0) {
			$valueAsArray = explode(self::SEPERATOR, $valueAsString);

			foreach ($valueAsArray as $key => $value) {
				$valueAsArray[$key] = trim($value);
			}
		}

		$this->set( $valueAsArray );
	}

	/**
	 * Value as string
	 * @return string
	 */
	public function valueAsString() {
		return self::arrayToString( $this->value() );
	}

	/**
	 * Append
	 * @param mixed $value
	 */
	public function append($value) {
		$array = $this->value();
		$array[] = $value;

		$this->set( $array );
	}
}
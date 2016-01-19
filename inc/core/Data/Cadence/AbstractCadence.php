<?php
/**
 * This file contains class::AbstractCadence
 * @package Runalyze\Data\Cadence
 */

namespace Runalyze\Data\Cadence;

/**
 * Abstract class for cadence
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\Cadence
 */
abstract class AbstractCadence {
	/**
	 * Value
	 * @param int $value
	 */
	protected $Value = 0;

	/**
	 * Factor for manipulating value
	 * @var float
	 */
	protected $Factor = 1;

	/**
	 * Constructor
	 * @param int $value
	 */
	public function __construct($value = 0) {
		$this->Value = round($this->Factor*$value);
	}

	/**
	 * Value
	 * @return int
	 */
	final public function value() {
		return $this->Value;
	}

	/**
	 * As string
	 * @return string
	 * @codeCoverageIgnore
	 */
	final public function asString() {
		return $this->Value.'&nbsp;'.$this->unitAsString();
	}

	/**
	 * As string with tooltip
	 * @return string
	 * @codeCoverageIgnore
	 */
	final public function asStringWithTooltip() {
		return Ajax::tooltip($this->asString(), $this->Value.' '.$this->unitExplanation());
	}

	/**
	 * As string with tooltip
	 * @return string
	 * @codeCoverageIgnore
	 */
	final public function unitAsStringWithTooltip() {
		return \Ajax::tooltip($this->unitAsString(), $this->unitExplanation());
	}

	/**
	 * Manipulate array
	 * @param array $array
	 */
	final public function manipulateArray(array &$array) {
		$array = array_map(array($this, 'useFactor'), $array);
	}

	/**
	 * Change value by internal factor
	 * @param int $value
	 * @return float
	 */
	final public function useFactor($value) {
		return $this->Factor*$value;
	}

	/**
	 * Label
	 * @return string
	 * @codeCoverageIgnore
	 */
	abstract public function label();

	/**
	 * Unit as string
	 * @return string
	 * @codeCoverageIgnore
	 */
	abstract public function unitAsString();

	/**
	 * Explanation for unit
	 * @return string
	 * @codeCoverageIgnore
	 */
	abstract protected function unitExplanation();

	/**
	 * Formular unit
	 * @return string
	 * @codeCoverageIgnore
	 */
	abstract public function formularUnit();
}
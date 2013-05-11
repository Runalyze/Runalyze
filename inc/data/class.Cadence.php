<?php
/**
 * This file contains class::Cadence
 * @package Runalyze\Data
 */
/**
 * Cadence
 * 
 * This class displays the cadence of a training.
 * Cadence is used as "rotations per minute" for e.g. cycling.
 * 
 * This class can be extended for other units.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data
 */
class Cadence {
	/**
	 * Value
	 * @param int $value
	 */
	protected $value = 0;

	/**
	 * Factor for manipulating value
	 * @var float
	 */
	protected $factor = 1;

	/**
	 * Constructor
	 * @param int $value
	 */
	public function __construct($value) {
		$this->value = $this->factor*$value;
	}

	/**
	 * As string
	 * @return string
	 */
	final public function asString() {
		return $this->value.'&nbsp;'.$this->unitAsString();
	}

	/**
	 * As string with tooltip
	 * @return string
	 */
	final public function asStringWithTooltip() {
		return Ajax::tooltip($this->asString(), $this->value.' '.$this->unitExplanation());
	}

	/**
	 * As string with tooltip
	 * @return string
	 */
	final public function unitAsStringWithTooltip() {
		return Ajax::tooltip($this->unitAsString(), $this->unitExplanation());
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
		return $this->factor*$value;
	}

	/**
	 * Label
	 * @return string
	 */
	public function label() {
		return 'Trittfrequenz';
	}

	/**
	 * Unit as string
	 * @return string
	 */
	protected function unitAsString() {
		return 'rpm';
	}

	/**
	 * Explanation for unit
	 * @return string
	 */
	protected function unitExplanation() {
		return 'rotations per minute = Umdrehungen pro Minute';
	}

	/**
	 * Formular unit
	 * @return enum
	 */
	public function formularUnit() {
		return FormularUnit::$RPM;
	}
}
<?php
/**
 * This file contains class::FormularInputUTCDayAndDaytime
 * @package Runalyze\HTML\Formular
 */

use Runalyze\Util\LocalTime;

/**
 * Class for a double field for day and daytime
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularInputUTCDayAndDaytime extends FormularInputDayAndDaytime {
	/**
	 * Construct a new field
	 * @param string $name
	 * @param string $label
	 * @param string|array $value optional, default: loading from $_POST
	 */
	public function __construct($name, $label, $value = '') {
		parent::__construct($name, $label, $value);

		if (is_numeric($this->value)) {
			$this->value = (new LocalTime($this->value))->toServerTime()->getTimestamp();
		}
	}
	
	/**
	 * Validate value
	 * @return boolean
	 */
	public function validate() {
		parent::validate();

		$_POST[$this->name] = LocalTime::fromServerTime($_POST[$this->name])->getTimestamp();
	}

}
<?php
/**
 * This file contains class::TrainingSelectWeather
 * @package Runalyze\DataObjects\Training\Formular
 */

use \Runalyze\Data\Weather\Condition;

/**
 * Class for input fields: weather
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\Formular
 */
class TrainingSelectWeather extends FormularSelectBox {
	/**
	 * Construct new input field for: weather
	 * @param string $name
	 * @param string $label
	 * @param string $value optional, default: loading from $_POST
	 */
	public function __construct($name, $label, $value = '') {
		parent::__construct($name, $label, $value);

		$Condition = new Condition(0);

		foreach (Condition::completeList() as $id) {
			$Condition->set($id);

			$this->addOption($id, $Condition->string());
		}
	}
}
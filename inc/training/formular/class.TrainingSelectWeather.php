<?php
/**
 * This file contains class::TrainingSelectWeather
 * @package Runalyze\DataObjects\Training\Formular
 */
/**
 * Class for input fields: weather
 * @author Hannes Christiansen <mail@laufhannes.de>
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

		foreach (Weather::getFullArray() as $id => $data)
			$this->addOption($id, $data['name']);
	}
}
<?php
/**
 * This file contains class::TrainingSelectClothes
 * @package Runalyze\DataObjects\Training\Formular
 */
/**
 * Class for input fields: clothes
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @package Runalyze\DataObjects\Training\Formular
 */
class TrainingSelectClothes extends FormularCheckboxes {
	/**
	 * Construct new input field for: weather
	 * @param string $name
	 * @param string $label
	 * @param string $value optional, default: loading from $_POST
	 */
	public function __construct($name, $label, $value = '') {
		parent::__construct($name, $label, $value);

		foreach (ClothesFactory::OrderedClothes() as $data)
			$this->addCheckbox($data['id'], $data['short']);

		$this->setParser( FormularValueParser::$PARSER_ARRAY_CHECKBOXES );
	}
}
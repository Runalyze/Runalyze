<?php
/**
 * This file contains class::TrainingSelectType
 * @package Runalyze\DataObjects\Training\Formular
 */
/**
 * Class for input field: typeid
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @package Runalyze\DataObjects\Training\Formular
 */
class TrainingSelectType extends FormularSelectBox {
	/**
	 * Construct new input field for: typeid
	 * @param string $name
	 * @param string $label
	 * @param string $value optional, default: loading from $_POST
	 */
	public function __construct($name, $label, $value = '') {
		parent::__construct($name, $label, $value);

		$this->addLayoutClass( TrainingFormular::$ONLY_TYPES_CLASS );
		$this->addOption(0, '---- Typ ausw&auml;hlen');

		foreach (TypeFactory::NamesAsArray() as $id => $name)
			$this->addOption($id, $name);
	}
}
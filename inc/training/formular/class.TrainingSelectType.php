<?php
/**
 * This file contains class::TrainingSelectType
 * @package Runalyze\DataObjects\Training\Formular
 */
/**
 * Class for input field: typeid
 * @author Hannes Christiansen
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

		$this->addOption(0, '---- '.__('select type'), array('data-sport' => 'all'));

		foreach (TypeFactory::AllTypes() as $id => $data)
			$this->addOption($id, $data['name'], array('data-sport' => $data['sportid']));
	}
}
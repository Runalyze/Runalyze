<?php
/**
 * This file contains class::TrainingSelectShoe
 * @package Runalyze\DataObjects\Training\Formular
 */
/**
 * Class for input field: shoeid
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @package Runalyze\DataObjects\Training\Formular
 */
class TrainingSelectShoe extends FormularSelectBox {
	/**
	 * Construct new input field for: shoeid
	 * @param string $name
	 * @param string $label
	 * @param string $value optional, default: loading from $_POST
	 */
	public function __construct($name, $label, $value = '') {
		parent::__construct($name, $label, $value);

		$this->addLayoutClass( TrainingCreatorFormular::$ONLY_RUNNING_CLASS );
		$this->addOption(0, '---- Laufschuh ausw&auml;hlen');

		foreach (Shoe::getNamesAsArray( !$this->showAll() ) as $id => $name)
			$this->addOption($id, $name);
	}

	/**
	 * Boolean flag: show unused shoes too?
	 * @return boolean
	 */
	protected function showAll() {
		return !empty($this->value);
	}
}
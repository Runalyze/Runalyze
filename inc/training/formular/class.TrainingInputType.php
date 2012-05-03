<?php
/**
 * Class for input fields: typeid
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputType extends FormularSelectBox {
	/**
	 * Construct new input field for: typeid
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('typeid', 'Trainingstyp', $value);

		$this->addOption(0, '---- Typ ausw&auml;hlen');

		foreach (Type::getNamesAsArray() as $id => $name)
			$this->addOption($id, $name);
	}
}
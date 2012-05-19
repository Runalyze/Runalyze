<?php
/**
 * Class for input fields: shoeid
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputShoe extends FormularSelectBox {
	/**
	 * Construct new input field for: shoeid
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('shoeid', 'Laufschuh', $value);

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
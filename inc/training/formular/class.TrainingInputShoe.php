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

		foreach (Shoe::getNamesAsArray(false) as $id => $name)
			$this->addOption($id, $name);
	}
}
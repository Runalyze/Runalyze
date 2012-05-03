<?php
/**
 * Class for input fields: distance
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputDistance extends FormularInput {
	/**
	 * Construct new input field for: distance
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('distance', 'Distanz', $value);

		$this->setUnit( FormularUnit::$KM );
	}
}
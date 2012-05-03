<?php
/**
 * Class for input fields: speed
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputSpeed extends FormularInput {
	/**
	 * Construct new input field for: speed
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('kmh', 'Tempo', $value);

		$this->setUnit( FormularUnit::$KMH );
	}
}
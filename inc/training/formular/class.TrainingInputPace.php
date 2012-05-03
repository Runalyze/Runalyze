<?php
/**
 * Class for input fields: pace
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputPace extends FormularInput {
	/**
	 * Construct new input field for: pace
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('pace', 'Pace', $value);

		$this->setUnit( FormularUnit::$PACE );
	}
}
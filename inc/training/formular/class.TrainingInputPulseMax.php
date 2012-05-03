<?php
/**
 * Class for input fields: pulse_max
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputPulseMax extends FormularInput {
	/**
	 * Construct new input field for: pulse_max
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('pulse_max', 'max. Puls', $value);

		$this->setUnit( FormularUnit::$BPM );
	}
}
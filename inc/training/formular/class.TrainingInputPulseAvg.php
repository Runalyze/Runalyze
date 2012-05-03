<?php
/**
 * Class for input fields: pulse_avg 
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputPulseAvg extends FormularInput {
	/**
	 * Construct new input field for: pulse_avg
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('pulse_avg', '&oslash;-Puls', $value);

		$this->setUnit( FormularUnit::$BPM );
	}
}
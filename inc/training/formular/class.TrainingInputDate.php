<?php
/**
 * Class for input fields: date
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputDate extends FormularInput {
	/**
	 * Construct new input field for: date
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('datum', 'Datum', $value);
	}
}
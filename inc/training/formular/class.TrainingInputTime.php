<?php
/**
 * Class for input fields: time
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputTime extends FormularInput {
	/**
	 * Construct new input field for: time
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('s', 'Dauer', $value);
	}
}
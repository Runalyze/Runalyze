<?php
/**
 * Class for input fields: daytime
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputDaytime extends FormularInput {
	/**
	 * Construct new input field for: daytime
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('zeit', 'Uhrzeit', $value);
	}
}
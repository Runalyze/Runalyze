<?php
/**
 * Class for input fields: abc
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputABC extends FormularCheckbox {
	/**
	 * Construct new input field for: abc
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('abc', 'Lauf-ABC', $value);

		$this->addHiddenSentValue();
	}
}
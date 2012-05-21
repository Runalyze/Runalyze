<?php
/**
 * Class for input fields: is public?
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputIsPublic extends FormularCheckbox {
	/**
	 * Construct new input field for: is public?
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('is_public', '&Ouml;ffentlich', $value);

		$this->addHiddenSentValue();
	}
}
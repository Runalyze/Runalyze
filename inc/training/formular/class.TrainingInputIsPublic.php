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
		if ($value == '') {
			if (isset($_POST['is_public']))
				$value = $_POST['is_public'];
			else
				$value = CONF_TRAINING_MAKE_PUBLIC;
		}

		parent::__construct('is_public', '&Ouml;ffentlich', $value);

		$this->addHiddenSentValue();
	}
}
<?php
/**
 * Class for input fields: use vdot calculation?
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputUseVdot extends FormularCheckbox {
	/**
	 * Construct new input field for: use vdot?
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('use_vdot', 'VDOT f&uuml;r Form', $value);

		$this->addLayoutClass( TrainingCreatorFormular::$ONLY_RUNNING_CLASS );
		$this->addHiddenSentValue();
	}
}
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
		$Description = 'Trainings mit herausgestoppten Trabpausen oder
						anderen Gr&uuml;nden f&uuml;r ungew&ouml;hnliche Pulswerte
						sollten nicht zur Berechnung der VDOT-Form herangezogen werden.';

		parent::__construct('use_vdot', Ajax::tooltip('VDOT f&uuml;r Form',$Description), $value);

		$this->addLayoutClass( TrainingCreatorFormular::$ONLY_RUNNING_CLASS );
		$this->addHiddenSentValue();
	}
}
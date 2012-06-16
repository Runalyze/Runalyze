<?php
/**
 * Class for input fields: is track?
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputIsTrack extends FormularCheckbox {
	/**
	 * Construct new input field for: is track?
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('is_track', 'Bahn', $value);

		$this->addLayoutClass( TrainingCreatorFormular::$ONLY_RUNNING_CLASS );
		$this->addHiddenSentValue();
	}
}
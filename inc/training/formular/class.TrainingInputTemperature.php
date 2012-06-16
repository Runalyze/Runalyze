<?php
/**
 * Class for input fields: temperature
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputTemperature extends FormularInput {
	/**
	 * Construct new input field for: temperature
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('temperature', 'Temperatur', $value);

		$this->addLayoutClass( TrainingCreatorFormular::$ONLY_OUTSIDE_CLASS );
		$this->setUnit( FormularUnit::$CELSIUS );
	}
}
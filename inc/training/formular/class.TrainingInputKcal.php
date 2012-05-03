<?php
/**
 * Class for input fields: kcal 
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputKcal extends FormularInput {
	/**
	 * Construct new input field for: kcal
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('kcal', 'Kalorien', $value);

		$this->setUnit( FormularUnit::$KCAL );
	}
}
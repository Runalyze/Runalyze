<?php
/**
 * Class for input fields: weather
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputWeather extends FormularSelectBox {
	/**
	 * Construct new input field for: weather
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('weatherid', 'Wetter', $value);

		$this->addLayoutClass( TrainingCreatorFormular::$ONLY_OUTSIDE_CLASS );
		foreach (Weather::getFullArray() as $id => $data)
			$this->addOption($id, $data['name']);
	}
}
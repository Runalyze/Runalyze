<?php
/**
 * Class for input fields: elevation
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputElevation extends FormularInput {
	/**
	 * Construct new input field for: elevation
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('elevation', 'H&ouml;henmeter', $value);

		$this->addLayoutClass( TrainingCreatorFormular::$ONLY_OUTSIDE_CLASS );
		$this->setUnit( FormularUnit::$ELEVATION );
	}
}
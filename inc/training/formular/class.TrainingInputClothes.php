<?php
/**
 * Class for input fields: clothes
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputClothes extends FormularCheckboxes {
	/**
	 * Construct new input field for: clothes
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('clothes', 'Kleidung', $value);

		$this->addLayoutClass( TrainingCreatorFormular::$ONLY_RUNNING_CLASS );
		$this->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W50 );

		foreach (Clothes::getOrderedClothes() as $data)
			$this->addCheckbox($data['id'], $data['short']);

		$this->setParser( FormularValueParser::$PARSER_ARRAY_CHECKBOXES );
	}
}
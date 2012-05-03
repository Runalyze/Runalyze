<?php
/**
 * Class for input fields: partner
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputPartner extends FormularInput {
	/**
	 * Construct new input field for: partner
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('partner', 'Trainingspartner', $value);

		$this->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W50 );
		$this->setSize( FormularInput::$SIZE_FULL_INLINE );
	}
}
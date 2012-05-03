<?php
/**
 * Class for input fields: comment
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputComment extends FormularInput {
	/**
	 * Construct new input field for: comment
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('comment', 'Bemerkung', $value);

		$this->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W50 );
		$this->setSize( FormularInput::$SIZE_FULL_INLINE );
	}
}
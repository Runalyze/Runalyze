<?php
/**
 * Class for input fields: notes
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputNotes extends FormularTextarea {
	/**
	 * Construct new input field for: comment
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('notes', 'Notizen', $value);

		$this->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W50 );
		$this->addCSSclass( FormularInput::$SIZE_FULL_INLINE );
	}
}
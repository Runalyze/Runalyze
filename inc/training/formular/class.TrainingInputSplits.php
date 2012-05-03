<?php
/**
 * Class for input fields: splits 
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputSplits extends FormularTextarea {
	/**
	 * Construct new input field for: splits
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('splits', 'Zwischenzeiten', $value);

		$this->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W50 );
		$this->addAttribute( 'class', FormularInput::$SIZE_FULL_INLINE );
	}

	/**
	 * Get info text
	 * @return string 
	 */
	static public function getInfo() {
		return '<small>Format: K.M|M:SS-..., z.B. 1.0|4:20-1.0|4:23-1.0|4:21-1.0|4:15</small>';
	}
}
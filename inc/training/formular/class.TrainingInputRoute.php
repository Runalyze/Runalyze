<?php
/**
 * Class for input fields: route
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputRoute extends FormularInput {
	/**
	 * Construct new input field for: route
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('route', 'Strecke', $value);

		$this->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W50 );
		$this->setSize( FormularInput::$SIZE_FULL_INLINE );

		// TODO: DropDown-menu for old routes
	}
}
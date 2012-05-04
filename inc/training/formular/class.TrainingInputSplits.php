<?php
/**
 * Class for input fields: splits 
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputSplits extends FormularField {
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
	 * Get input fields for splits
	 * @return string
	 */
	protected function getInputs() {
		$Inputs = '';
		$Splits = new Splits( Splits::$FROM_POST );

		foreach ($Splits->asArray() as $split) {
			$FieldTime = new FormularInput('splits[time][]', '', $split['time']);
			$FieldTime->setLayout( FormularFieldset::$LAYOUT_FIELD_INLINE );
			$FieldTime->setLabelToRight();

			$FieldDistance = new FormularInput('splits[km][]', '', $split['km']);
			$FieldDistance->setUnit( FormularUnit::$KM );
			$FieldDistance->setLayout( FormularFieldset::$LAYOUT_FIELD_INLINE );

			$Inputs .= '<div>';
			$Inputs .= $FieldDistance->getCode();
			$Inputs .= '&nbsp;in&nbsp;';
			$Inputs .= $FieldTime->getCode();
			$Inputs .= '<span style="position:relative;top:3px;">';
			$Inputs .= '<img class="link" src="img/delete_gray.gif" alt="" onclick="$(this).parent().parent().remove()" /> ';
			$Inputs .= '<img class="link" src="img/addBig.gif" alt="" onclick="$e=$(this);$p=$e.parent().parent();$p.clone().insertAfter($p);" />';
			$Inputs .= '</span>';
			$Inputs .= '</div>';
		}

		return $Inputs;
	}

	/**
	 * Display this field
	 * @return string
	 */
	protected function getFieldCode() {
		$label  = '<label>'.$this->label.'</label>';
		$inputs = '<div id="formularSplitsContainer" class="fullSize left">'.$this->getInputs().'</div>';

		return $label.$inputs;
	}
}
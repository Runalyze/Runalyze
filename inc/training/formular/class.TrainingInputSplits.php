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

		foreach ($Splits->asArray() as $split)
			$Inputs .= $this->getInnerDivForSplit($split);

		$Inputs .= '<p id="addSplitsLink"><span class="link" onclick="$e=$(this);$($(\'#defaultInputSplit\').val()).insertBefore($e.parent());">neue Zwischenzeit hinzuf&uuml;gen</span></p>';
		$Inputs .= '<textarea id="defaultInputSplit" class="hide">'.HTML::textareaTransform($this->getInnerDivForSplit()).'</textarea>';

		return $Inputs;
	}

	/**
	 * Get code for inner div for one split
	 * @param array $split [optional]
	 * @return string 
	 */
	protected function getInnerDivForSplit($split = array('km' => '1.00', 'time' => '6:00')) {
		$Code  = $this->getDistanceInputCode($split['km']);
		$Code .= '&nbsp;in&nbsp;';
		$Code .= $this->getTimeInputCode($split['time']);
		$Code .= $this->getSpanForLinks();

		return '<div>'.$Code.'</div>';
	}

	/**
	 * Get code for links
	 * @return string 
	 */
	protected function getSpanForLinks() {
		$Span  = '<span style="position:relative;top:3px;">';
		$Span .= '<img class="link" src="img/delete_gray.gif" alt="" onclick="$(this).parent().parent().remove()" /> ';
		$Span .= '<img class="link" src="img/addBig.gif" alt="" onclick="$e=$(this);$p=$e.parent().parent();$p.clone().insertAfter($p);" />';
		$Span .= '</span>';

		return $Span;
	}

	/**
	 * Get input for time
	 * @param string $time
	 * @return FormularInput 
	 */
	protected function getTimeInputCode($time) {
		$FieldTime = new FormularInput('splits[time][]', '', $time);
		$FieldTime->setLayout( FormularFieldset::$LAYOUT_FIELD_INLINE );
		$FieldTime->setLabelToRight();

		return $FieldTime->getCode();
	}

	/**
	 * Get input for distance
	 * @param string $distance
	 * @return FormularInput 
	 */
	protected function getDistanceInputCode($distance) {
		$FieldDistance = new FormularInput('splits[km][]', '', $distance);
		$FieldDistance->setUnit( FormularUnit::$KM );
		$FieldDistance->setLayout( FormularFieldset::$LAYOUT_FIELD_INLINE );

		return $FieldDistance->getCode();
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
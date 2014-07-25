<?php
/**
 * This file contains class::TrainingInputSplits
 */
/**
 * Class for input fields: splits 
 * @package Runalyze\DataObjects\Training\Formular
 */
class TrainingInputSplits extends FormularField {
	/**
	 * Construct new input field for: splits
	 * 
	 * WARNING: This class was used with @code new TrainingInputSplits([$value]); @endcode previously.
	 * To be used in a standard formular created by a database scheme,
	 * this class has to use the default constructor for a FormularField again:
	 * @code new TrainingInputSplits($name, $label [, $value]); @endcode
	 * 
	 * @param string $name
	 * @param string $label
	 * @param string $value [optional]
	 */
	public function __construct($name = '', $label = '', $value = '') {
		if ($label == '')
			parent::__construct('splits', __('Laps'), $name);
		else
			parent::__construct($name, $label, $value);

		$this->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W50 );
		$this->addAttribute( 'class', FormularInput::$SIZE_FULL_INLINE );

		$this->setParser( FormularValueParser::$PARSER_SPLITS );
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

		$Inputs .= '<p id="addSplitsLink"><span class="link" onclick="$e=$(this);$($(\'#defaultInputSplit\').val()).insertBefore($e.parent());">'.__('add new lap').'</span></p>';
		$Inputs .= '<p><span class="link" onclick="$(\'input[name=\\\'splits[km][]\\\']\').each(function(e){$(this).val((Math.round(10*$(this).val())/10).toFixed(2));});">'.__('round for 100m').'</span></p>';
		$Inputs .= '<p><span class="link" onclick="sumSplitsToTotal();">'.__('apply as total distance').'</span></p>';
		$Inputs .= '<p><span class="link" onclick="allSplitsActive();">'.__('all active').'</span> - <span class="link" onclick="allSplitsRest();">'.__('all resting').'</span></p>';
		$Inputs .= '<textarea id="defaultInputSplit" class="hide">'.HTML::textareaTransform($this->getInnerDivForSplit()).'</textarea>';

		return $Inputs;
	}

	/**
	 * Get code for inner div for one split
	 * @param array $split [optional]
	 * @return string 
	 */
	protected function getInnerDivForSplit($split = array('km' => '1.00', 'time' => '6:00', 'active' => true)) {
		$Code  = $this->getDistanceInputCode($split['km']);
		$Code .= '&nbsp;'.__('in').'&nbsp;';
		$Code .= $this->getTimeInputCode($split['time']);
		$Code .= $this->getActiveInputCode($split['active']);
		$Code .= $this->getSpanForLinks();

		return '<div>'.$Code.'</div>';
	}

	/**
	 * Get code for links
	 * @return string 
	 */
	protected function getSpanForLinks() {
		$Span  = '<span class="link" onclick="$(this).parent().remove()">'.Icon::$DELETE.'</span> ';
		$Span .= '<span class="link" onclick="$e=$(this);$p=$e.parent();$p.clone().insertAfter($p);">'.Icon::$PLUS.'</span>';

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
	 * Get input for active
	 * @param bool $active
	 * @return FormularSelectBox 
	 */
	protected function getActiveInputCode($active) {
		$FieldActive = new FormularSelectBox('splits[active][]', '', (int)$active);
		$FieldActive->setOptions(array(__('Resting'), __('Active')));
		$FieldActive->setLayout( FormularFieldset::$LAYOUT_FIELD_INLINE );

		return $FieldActive->getCode();
	}

	/**
	 * Display this field
	 * @return string
	 */
	protected function getFieldCode() {
		$label  = '<label>'.$this->label.'</label>';
		$inputs = '<div id="formularSplitsContainer" class="full-size left">'.$this->getInputs().'</div>';

		return $label.$inputs;
	}
}
<?php
/**
 * This file contains class::TrainingInputSplits
 */

use Runalyze\Configuration;

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
			parent::__construct('splits', __('Laps').' '.$this->helpIcon(), $name);
		else
			parent::__construct($name, $label.' '.$this->helpIcon(), $value);

		$this->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W50 );
		$this->addAttribute( 'class', FormularInput::$SIZE_FULL_INLINE );

		$this->setParser( FormularValueParser::$PARSER_SPLITS, array('transform-unit' => true) );
	}

	/**
	 * Get input fields for splits
	 * @return string
	 */
	protected function getInputs() {
		$Splits = new Splits( Splits::$FROM_POST );
		$Inputs = '<ol class="splits">';

		foreach ($Splits->asArray() as $split)
			$Inputs .= $this->getInnerDivForSplit($split);

		$Inputs .= '</ol>';
		$Inputs .= '<p id="addSplitsLink"><span class="link add-split">'.__('add new lap').'</span></p>';

		if (Configuration::General()->distanceUnitSystem()->isMetric()) {
			$Inputs .= '<p><span class="link round-splits">'.__('round for 100m').'</span></p>';
		}

		$Inputs .= '<p><span class="link sum-splits">'.__('apply as total distance').'</span></p>';
		$Inputs .= '<p><span class="link active-splits">'.__('all active').'</span> - <span class="link rest-splits">'.__('all resting').'</span></p>';
		$Inputs .= '<p>'.__('alternating:').' <span class="link alternate-splits-rest">'.__('first resting').'</span>';
		$Inputs .= ' - <span class="link alternate-splits-active">'.__('first active').'</span></p>';
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
		$Code .= '&nbsp; '.__('in').'&nbsp;';
		$Code .= $this->getTimeInputCode($split['time']);
		$Code .= $this->getActiveInputCode($split['active']);
		$Code .= $this->getSpanForLinks();

		return '<li>'.$Code.'</li>';
	}

	/**
	 * Get code for links
	 * @return string 
	 */
	protected function getSpanForLinks() {
		$Span  = '&nbsp; <span class="link" onclick="$(this).parent().remove()">'.Icon::$DELETE.'</span> ';
		$Span .= '<span class="link" onclick="$e=$(this);$p=$e.parent();$p.clone().insertAfter($p);">'.Icon::$PLUS.'</span>';

		return $Span;
	}

	/**
	 * @return string
	 */
	protected function helpIcon() {
		return Ajax::tooltip('<i class="fa fa-fw fa-question-circle"></i>', __('Defining some laps as \'resting\' will hide them in the respective plot.'));
	}

	/**
	 * Get input for time
	 * @param string $time
	 * @return FormularInput 
	 */
	protected function getTimeInputCode($time) {
		$FieldTime = new FormularInput('splits[time][]', '', $time);
		$FieldTime->setLayout( FormularFieldset::$LAYOUT_FIELD_INLINE );
		$FieldTime->hideLabel();

		return $FieldTime->getCode();
	}

	/**
	 * Get input for distance
	 * @param string $distance
	 * @return FormularInput 
	 */
	protected function getDistanceInputCode($distance) {
		$FieldDistance = new FormularInput('splits[km][]', '', $distance);
		$FieldDistance->setUnit(Configuration::General()->distanceUnitSystem()->distanceUnit());
		$FieldDistance->setLayout( FormularFieldset::$LAYOUT_FIELD_INLINE );
		$FieldDistance->setParser(FormularValueParser::$PARSER_DISTANCE);
		$FieldDistance->hideLabel();

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

		return $label.$inputs.$this->getJScode();
	}

	/**
	 * @return string
	 */
	protected function getJScode() {
		return Ajax::wrapJSforDocumentReady(
			'splits = $("#formularSplitsContainer");'.
			'defaultSplit = $("#defaultInputSplit").val();'.
			'splits.find(".add-split").click(addSplit);'.
			'splits.find(".round-splits").click(roundSplits);'.
			'splits.find(".sum-splits").click(sumSplitsToTotal);'.
			'splits.find(".active-splits").click(allSplitsActive);'.
			'splits.find(".rest-splits").click(allSplitsRest);'.
			'splits.find(".alternate-splits-rest").click(function(){evenSplits(0);oddSplits(1);});'.
			'splits.find(".alternate-splits-active").click(function(){evenSplits(1);oddSplits(0);});'
		);
	}
}
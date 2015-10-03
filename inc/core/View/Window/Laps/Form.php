<?php
/**
 * This file contains class::Form
 * @package Runalyze\View\Window\Laps
 */

namespace Runalyze\View\Window\Laps;

use Request;
use Formular;
use FormularFieldset;
use FormularInput;
use FormularUnit;
use FormularCheckbox;
use Ajax;

/**
 * Display form for laps window
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Window\Laps
 */
class Form {
	/**
	 * @var \FormularFieldset
	 */
	protected $Fieldset;

	/**
	 * Construct form
	 */
	public function __construct() {
		$this->createFieldset();
	}

	/**
	 * Create fieldset for configuration
	 */
	protected function createFieldset() {
		$this->Fieldset = new FormularFieldset( __('Calculate laps') );
		$this->Fieldset->addField( $this->lapDistanceField() );
		$this->Fieldset->addField( $this->lapTimeField() );
		$this->Fieldset->addField( $this->distancesField() );
		$this->Fieldset->addField( $this->timeField() );
		$this->Fieldset->addField( $this->paceField() );
		$this->Fieldset->addField( $this->timesField() );
		$this->Fieldset->setLayoutForFields(FormularFieldset::$LAYOUT_FIELD_W33);
	}

	/**
	 * Field: lap distance
	 * @return \FormularInput
	 */
	protected function lapDistanceField() {
		$Field = new FormularInput('distance', Ajax::tooltip(__('Lap every ...'), __('Distance, after which a new lap should start') ) );
		$Field->setUnit( FormularUnit::$KM );

		return $Field;
	}

	/**
	 * Field: lap time
	 * @return \FormularInput
	 */
	protected function lapTimeField() {
		$Field = new FormularInput('time', Ajax::tooltip('<small>'.__('or').':</small> '.__('Lap every ...'), __('Time, after which a new lap should start') ) );
		$Field->setPlaceholder('h:mm:ss');
		$Field->addCSSclass('c');

		return $Field;
	}

	/**
	 * Field: manual distances
	 * @return \FormularInput
	 */
	protected function distancesField() {
		$Field = new FormularInput('manual-distances', Ajax::tooltip('<small>'.__('or').':</small> '.__('Manual distances'),
			__('List with all distances, comma seperated. Put "+" at the beginning to treat distances as intervals.') ));
		$Field->setSize( FormularInput::$SIZE_FULL_INLINE );
		$Field->setPlaceholder( __('e.g.').' 5, 10, 21.1 '.__('or').' +0.4, 0.8, 0.4');

		return $Field;
	}

	/**
	 * Field: manual times
	 * @return \FormularInput
	 */
	protected function timesField() {
		$Field = new FormularInput('manual-times', Ajax::tooltip('<small>'.__('or').':</small> '.__('Manual times'),
			__('List with all times, comma seperated. Put "+" at the beginning to treat times as intervals.') ));
		$Field->setSize( FormularInput::$SIZE_FULL_INLINE );
		$Field->setPlaceholder( __('e.g.').' 30:00, 1:00:00 '.__('or').' +15\', 30\', 15\'');

		return $Field;
	}

	/**
	 * Field: time goal
	 * @return \FormularInput
	 */
	protected function timeField() {
		$Field = new FormularInput('demanded-time', __('Lap time goal'));
		$Field->setPlaceholder('h:mm:ss');
		$Field->addCSSclass('c');

		return $Field;
	}

	/**
	 * Field: pace goal
	 * @return \FormularInput
	 */
	protected function paceField() {
		$Field = new FormularInput('demanded-pace', '<small>'.__('or').':</small> '.__('Pace goal') );
		$Field->setUnit( FormularUnit::$PACE );

		return $Field;
	}

	/**
	 * Activate difference
	 * Add checkbox and warning for differences between handmade laps and total distance.
	 */
	public function activateHandmadeDifference() {
		$Checkbox = new FormularCheckbox('calculate-for-splits', __('Calculate values for handmade splits although total distance does not match.'));
		$Checkbox->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_CHECKBOX );

		$this->Fieldset->addField($Checkbox);
		$this->Fieldset->addWarning( __('The total distance of your handmade laps differs from the gps distance.') );
	}

	/**
	 * Add info for handmade laps
	 */
	public function activateHandmadeInfo() {
		$this->Fieldset->addInfo( __('Leave lap distances empty to use your handmade laps.') );
	}

	/**
	 * Display
	 */
	public function display() {
		$Formular = new Formular();
		$Formular->setId('rounds-configurator');
		$Formular->addCSSclass('ajax');
		$Formular->addCSSclass('no-automatic-reload');
		$Formular->addFieldset( $this->Fieldset );
		$Formular->addHiddenValue('id', Request::sendId());
		$Formular->addSubmitButton( __('Show calculated laps'), 'submit-calculated-laps' );
		$Formular->addSubmitButton( __('Show manual laps'), 'submit-manual-laps' );
		$Formular->display();

		echo '<p>&nbsp;</p>';
		echo $this->getJScode();
	}

	/**
	 * @return string
	 */
	protected function getJScode() {
		return Ajax::wrapJS(
			'var buttonCalc = $("#rounds-configurator input[name=submit-calculated-laps]");'.
			'var buttonManual = $("#rounds-configurator input[name=submit-manual-laps]");'.
			'function updateButtonVisibility() {'.
				'if ($("#rounds-configurator input:text").filter(function() { return this.value != ""; }).length == 0) {'.
					'buttonCalc.addClass("hide");'.
					'buttonManual.removeClass("hide");'.
				'} else {'.
					'buttonCalc.removeClass("hide");'.
					'buttonManual.addClass("hide");'.
				'}'.
			'}'.
			'$("#rounds-configurator input[type=text]").change(updateButtonVisibility);'.
			'updateButtonVisibility();'
		);
	}
}

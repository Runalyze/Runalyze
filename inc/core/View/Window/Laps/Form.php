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
		$this->Fieldset->addField( $this->lapField() );
		$this->Fieldset->addField( $this->distancesField() );
		$this->Fieldset->addField( $this->timeField() );
		$this->Fieldset->addField( $this->paceField() );
	}

	/**
	 * Field: lap distance
	 * @return \FormularInput
	 */
	protected function lapField() {
		$Field = new FormularInput('distance', Ajax::tooltip(__('Lap every ...'), __('Distance, after which a new lap should start') ) );
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$Field->setUnit( FormularUnit::$KM );

		return $Field;
	}

	/**
	 * Field: manual distances
	 * @return \FormularInput
	 */
	protected function distancesField() {
		$Field = new FormularInput('manual-distances', Ajax::tooltip('<small>'.__('or').':</small> '.__('Manual laps'),
			__('List with all distances, comma seperated. Put "+" at the beginning to treat distances as intervals.') ));
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$Field->setSize( FormularInput::$SIZE_FULL_INLINE );
		$Field->setPlaceholder( __('e.g.').' 5, 10, 21.1 '.__('or').' +0.4, 0.8, 0.4');

		return $Field;
	}

	/**
	 * Field: time goal
	 * @return \FormularInput
	 */
	protected function timeField() {
		$Field = new FormularInput('demanded-time', __('Lap time goal'));
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
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
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
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
		$Formular->addSubmitButton( __('Show calculated laps') );
		$Formular->display();

		echo '<p>&nbsp;</p>';
	}
}

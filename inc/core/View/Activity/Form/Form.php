<?php
/**
 * This file contains class::Form
 * @package Runalyze\View\Activity\Form
 */

namespace Runalyze\View\Activity\Form;

use Runalyze\Model\Activity\DataObject;

use StandardFormular;
use FormularFieldset;
use Request;
use HTML;
use Ajax;

/**
 * Form
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Form
 */
class Form extends StandardFormular {
	/**
	 * CSS class for inputs only for running
	 * @var string
	 */
	static public $ONLY_RUNNING_CLASS = "only-running";

	/**
	 * CSS class for inputs only for not running
	 * @var string
	 */
	static public $ONLY_NOT_RUNNING_CLASS = "only-not-running";

	/**
	 * CSS class for inputs only for sports outside
	 * @var string
	 */
	static public $ONLY_OUTSIDE_CLASS = "only-outside";

	/**
	 * CSS class for inputs only for sports with types
	 * @var string
	 */
	static public $ONLY_TYPES_CLASS = "only-types";

        /*
	 * CSS class for inputs only for sports with distance
	 * @var string
	 */
	static public $ONLY_DISTANCES_CLASS = "only-distances";
        
        
	/**
	 * CSS class for inputs only for sports with power
	 * @var string
	 */
	static public $ONLY_POWER_CLASS = "only-power";

	/**
	 * @var \Runalyze\Model\Activity\DataObject
	 */
	protected $dataObject;

	/**
	 * Construct a new formular
	 * @param \Runalyze\Model\Activity\DataObject $dataObject
	 * @param enum $mode
	 */
	public function __construct(DataObject &$dataObject, $mode) {
		parent::__construct($dataObject, $mode);
	}

	/**
	 * Prepare for display
	 */
	protected function prepareForDisplayInSublcass() {
		parent::prepareForDisplayInSublcass();

		if ($this->submitMode == StandardFormular::$SUBMIT_MODE_EDIT) {
			$this->initGPSFieldset();
			$this->initDeleteFieldset();

			if (Request::param('mode') == 'multi') {
				$this->addHiddenValue('mode', 'multi');
				$this->submitButtons['submit'] = __('Save and continue');
			}
		}

		$this->appendJavaScript();
	}

	/**
	 * Display after submit
	 */
	protected function displayAfterSubmit() {
		if ($this->submitMode == StandardFormular::$SUBMIT_MODE_CREATE) {
			$this->displayHeader();
			echo HTML::okay( __('The activity has been successfully created.') );
			echo Ajax::closeOverlay();

			if (Configuration::ActivityForm()->showActivity())
				echo Ajax::wrapJS('Runalyze.Training.load('.$this->dataObject->id().');');
		} else {
			if (Request::param('mode') == 'multi') {
				echo Ajax::wrapJS('Runalyze.goToNextMultiEditor();');
			} else {
				parent::displayAfterSubmit();
			}
		}
	}

	/**
	 * Display fieldset: Delete training 
	 */
	protected function initDeleteFieldset() {
		$DeleteText = '<strong>'.__('Permanently delete this activity').' &raquo;</strong>';
		$DeleteUrl  = $_SERVER['SCRIPT_NAME'].'?delete='.$this->dataObject->id();
		$DeleteLink = Ajax::link($DeleteText, 'ajax', $DeleteUrl);

		$Fieldset = new FormularFieldset( __('Delete activity') );
		$Fieldset->addWarning($DeleteLink);
		$Fieldset->setCollapsed();

		$this->addFieldset($Fieldset);
	}

	/**
	 * Add fieldset for adding GPS-data 
	 */
	protected function initGPSFieldset() {
		if ($this->dataObject->hasPositionData()) {
			$this->initElevationCorrectionFieldset();
		}
	}

	/**
	 * Init fieldset for correct elevation
	 */
	protected function initElevationCorrectionFieldset() {
		if ($this->dataObject->get('elevation_corrected') == 1)
			return;

		$Fieldset = new FormularFieldset( __('Use elevation correction') );
		$Fieldset->setConfValueToSaveStatus('ELEVATION');

		$Fieldset->addInfo('
			<a class="ajax" target="gps-results" href="call/call.Training.elevationCorrection.php?id='.$this->dataObject->id().'"><strong>'.__('Correct elevation data').'</strong></a><br>
			<br>
			<small id="gps-results" class="block">
				'.__('Elevation data via GPS is very inaccurate. Therefore you can correct it via some satellite data.').'
			</small>');

		$this->addFieldset($Fieldset);
	}

	/**
	 * Append JavaScript 
	 */
	protected function appendJavaScript() {
		echo '<script type="text/javascript">';
		include FRONTEND_PATH.'../lib/jquery.form.include.php';
		echo '</script>';
	}
}
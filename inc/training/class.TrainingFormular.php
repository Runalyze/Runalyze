<?php
/**
 * This file contains class::TrainingFormular
 * @package Runalyze\DataObjects\Training
 */
/**
 * Formular for trainings
 * 
 * This training formular extends StandardFormular and can be used for creating
 * a new training as well as for editing an existing training.
 * 
 * Nearly all fields are set directly through the given DatabaseScheme for a training.
 * This class only extends the standard formular with some additional values
 * (e.g. created-timestamp) and additional fieldsets for adding gps-data etc.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training
 */
class TrainingFormular extends StandardFormular {
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

	/**
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
	 * Prepare for display
	 */
	protected function prepareForDisplayInSublcass() {
		parent::prepareForDisplayInSublcass();

		if ($this->submitMode == StandardFormular::$SUBMIT_MODE_EDIT) {
			$this->initGPSFieldset();
			$this->initDeleteFieldset();

			if (Request::param('mode') == 'multi') {
				$this->addHiddenValue('mode', 'multi');
				$this->submitButtons['submit'] = __('Edit and continue');
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

			if (CONF_TRAINING_SHOW_AFTER_CREATE)
				echo Ajax::wrapJS('Runalyze.loadTraining('.$this->dataObject->id().');');
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
		if ($this->dataObject->hasPositionData())
			$this->initElevationCorrectionFieldset();
		// TODO:
		// - add TCX is disabled
		// - new method: add/complete with file
		//elseif ($this->dataObject->hasDistance())
		//	$this->initAddGPSdataFieldset();
	}

	/**
	 * Init fieldset for correct elevation
	 */
	protected function initElevationCorrectionFieldset() {
		if ($this->dataObject->get('elevation_corrected') == 1)
			return;

		$Fieldset = new FormularFieldset( __('Use elevation correction') );
		$Fieldset->setConfValueToSaveStatus('FORMULAR_SHOW_ELEVATION');

		$Fieldset->addInfo('
			<a class="ajax" target="gps-results" href="call/call.Training.elevationCorrection.php?id='.$this->dataObject->id().'"><strong>'.__('Correct elevation data').'</strong></a><br>
			<br>
			<small id="gps-results" class="block">
				'.__('Elevation data via GPS is very inaccurate. Therefore you can correct it via some satellite data.').'
			</small>');

		$this->addFieldset($Fieldset);
	}

	/**
	 * Init fieldset for adding GPS data 
	 */
	protected function initAddGPSdataFieldset() {
		/*$Fieldset = new FormularFieldset('GPS-Daten hinzuf&uuml;gen');
		$Fieldset->setConfValueToSaveStatus('FORMULAR_SHOW_GPS');
		$Fieldset->addInfo('
		<span onmouseover="javascript:createUploader()">
			<strong>TCX-Datei nachtr&auml;glich hinzuf&uuml;gen</strong><br>
			<br>
			<span class="c button" id="file-upload-tcx">Datei hochladen</span>
			<script>
			function createUploader() {
				$("#file-upload-tcx").removeClass("hide");
				new AjaxUpload("#file-upload-tcx", {
					action: "'.$_SERVER['SCRIPT_NAME'].'?id='.$this->dataObject->id().'&json=true&hideHtmlHeader=true",
					onComplete : function(file, response){
						$("#ajax").loadDiv("'.$_SERVER['SCRIPT_NAME'].'?id='.$this->dataObject->id().'&tmp=true");
					}		
				});
			}
			</script>
		</span>');

		$this->addFieldset($Fieldset);*/
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
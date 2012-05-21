<?php
/**
 * Class for displaying the formular for editing trainings
 * @author Hannes Christiansen <mail@laufhannes.de> 
 */
class TrainingEditorFormular {
	/**
	 * Training to edit
	 * @var Training
	 */
	protected $Training = null;

	/**
	 * Formular
	 * @var Formular 
	 */
	protected $Formular = null;

	/**
	 * Constructor 
	 */
	public function __construct($id) {
		$this->Formular = new Formular($_SERVER['SCRIPT_NAME'].'?id='.$id);
		$this->Training = new Training($id);

		$this->initFormular();
	}

	/**
	 * Init all fieldsets for formular 
	 */
	public function initFormular() {
		$this->Training->overwritePostArray();

		$this->initHiddenFields();
		$this->initFieldsets();
		$this->initFormularAttributes();
	}

	/**
	 * Init all fieldsets 
	 */
	protected function initFieldsets() {
		$this->initSportFieldset();
		$this->initGeneralFieldset();
		$this->initDistanceFieldset();
		$this->initSplitsFieldset();
		$this->initOtherFieldset();
		$this->initWeatherFieldset();
		$this->initPublicFieldset();
		$this->initDeleteFieldset();
		$this->initGPSFieldset();
	}

	/**
	 * Display formular 
	 */
	public function display() {
		$this->displayHeader();

		$this->Formular->display();

		$this->appendJavaScript();
	}

	/**
	 * Display header 
	 */
	protected function displayHeader() {
		echo '<h1>';
		$this->Training->displayTitleWithNavigation();
		echo '</h1>';
	}

	/**
	 * Init attributes 
	 */
	protected function initFormularAttributes() {
		$this->Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W50 );
		$this->Formular->setId('training');
		$this->Formular->addCSSclass('ajax');
		$this->Formular->addAttribute('onsubmit', 'return false;');
		$this->Formular->addSubmitButton('Speichern');
	}

	/**
	 * Init hidden fields 
	 */
	protected function initHiddenFields() {
		$this->Formular->addHiddenValue('type', 'training');
		$this->Formular->addHiddenValue('id', $this->Training->id());
		$this->Formular->addHiddenValue('kcalPerHour', $this->Training->Sport()->kcalPerHour());

		$this->Formular->addHiddenValue('sportid');
		$this->Formular->addHiddenValue('s_old');
		$this->Formular->addHiddenValue('dist_old');
		$this->Formular->addHiddenValue('shoeid_old');
	}

	/**
	 * Init fieldset for sport 
	 */
	protected function initSportFieldset() {
		$Fieldset = new FormularFieldset('Sportart');
		$Fieldset->addField(new TrainingInputSport());

		if (!$this->Training->Sport()->hasTypes() || (!is_null($this->Training->Type()) && $this->Training->Type()->isUnknown()) )
			$Fieldset->setCollapsed();

		$this->Formular->addFieldset($Fieldset);

		if ($this->Training->Sport()->hasTypes())
			$Fieldset->addField(new TrainingInputType());
	}

	/**
	 * Init fieldset for general data 
	 */
	protected function initGeneralFieldset() {
		$Fieldset = new FormularFieldset('Allgemeines');

		$Fieldset->addField(new TrainingInputDate());
		$Fieldset->addField(new TrainingInputDaytime());
		$Fieldset->addField(new TrainingInputTime());
		$Fieldset->addField(new TrainingInputKcal());

		if ($this->Training->Sport()->usesPulse()) {
			$Fieldset->addField(new TrainingInputPulseAvg());
			$Fieldset->addField(new TrainingInputPulseMax());
		}

		$this->Formular->addFieldset($Fieldset);
	}

	/**
	 * Init fieldset for distance data 
	 */
	protected function initDistanceFieldset() {
		if (!$this->Training->Sport()->usesDistance())
			return;

		$Fieldset = new FormularFieldset('Distanz');
		$Fieldset->addField(new TrainingInputDistance());

		if ($this->Training->Sport()->isRunning())
			$Fieldset->addField(new TrainingInputIsTrack());

		$Fieldset->addField(new TrainingInputElevation());

		if ($this->Training->Sport()->isRunning())
			$Fieldset->addField(new TrainingInputABC());

		$Fieldset->addField(new TrainingInputPace());
		$Fieldset->addField(new TrainingInputSpeed());

		$this->Formular->addFieldset($Fieldset);
	}

	/**
	 * Init fieldset for splits
	 */
	protected function initSplitsFieldset() {
		if (!$this->Training->Sport()->hasTypes() || !$this->Training->Type()->hasSplits())
			return;

		$Splits = new Splits( Splits::$FROM_POST );
		$this->Formular->addFieldset( $Splits->getFieldset() );
		$this->Formular->addHiddenValue('splits_sent', 'true');
	}

	/**
	 * Init fieldset for weather data 
	 */
	protected function initWeatherFieldset() {
		if (!$this->Training->isOutside())
			return;

		$Fieldset = new FormularFieldset('Wetter');
		$Fieldset->addField(new TrainingInputWeather());
		$Fieldset->addField(new TrainingInputTemperature());
		$Fieldset->addField(new TrainingInputClothes());
		$Fieldset->setCollapsed();

		$this->Formular->addFieldset($Fieldset);
		$this->Formular->addHiddenValue('clothes_sent', 'true');
	}

	/**
	 * Init fieldset for other data 
	 */
	protected function initOtherFieldset() {
		$Fieldset = new FormularFieldset('Sonstiges');

		if ($this->Training->Sport()->isRunning()) {
			$ShoeInput = new TrainingInputShoe();
			$ShoeInput->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W50 );
			$Fieldset->addField($ShoeInput);
		}

		$Fieldset->addField(new TrainingInputComment());
		$Fieldset->addField(new TrainingInputPartner());

		if ($this->Training->isOutside())
			$Fieldset->addField(new TrainingInputRoute());

		$this->Formular->addFieldset($Fieldset);
	}

	/**
	 * Init fieldset for publishing
	 */
	protected function initPublicFieldset() {
		$Fieldset = new FormularFieldset('Privatsph&auml;re');
		$Fieldset->addField(new TrainingInputIsPublic());
		$Fieldset->setCollapsed();

		$this->Formular->addFieldset($Fieldset);
	}

	/**
	 * Display fieldset: Delete training 
	 */
	protected function initDeleteFieldset() {
		$DeleteText = '<strong>Training unwiderruflich l&ouml;schen &raquo;</strong>';
		$DeleteUrl  = $_SERVER['SCRIPT_NAME'].'?delete='.$this->Training->id();
		$DeleteLink = Ajax::link($DeleteText, 'ajax', $DeleteUrl);

		$Fieldset = new FormularFieldset('Training l&ouml;schen');
		$Fieldset->addWarning($DeleteLink);
		$Fieldset->setCollapsed();

		$this->Formular->addFieldset($Fieldset);
	}

	/**
	 * Add fieldset for adding GPS-data 
	 */
	protected function initGPSFieldset() {
		if ($this->Training->hasPositionData())
			$this->initElevationCorrectionFieldset();
		else
			$this->initAddGPSdataFieldset();
	}

	/**
	 * Init fieldset for correct elevation
	 */
	protected function initElevationCorrectionFieldset() {
		$Fieldset = new FormularFieldset('H&ouml;henkorrektur anwenden');
		$Fieldset->setCollapsed();
		$Fieldset->addInfo('
			<a class="ajax" target="gps-results" href="call/call.Training.elevationCorrection.php?id='.$this->Training->id().'" title="H&ouml;hendaten korrigieren"><strong>H&ouml;hendaten korrigieren</strong></a><br />
			<br />
			<small>
				Die H&ouml;hendaten k&ouml;nnen korrigiert werden, da diese beim GPS meist sehr ungenau sind.<br />
				Vorsicht: Die Abfrage kann lange dauern, bitte nicht abbrechen, bevor das Laden beendet ist.
			</small><br />
			<br />
			<small id="gps-results"></small>');

		$this->Formular->addFieldset($Fieldset);
	}

	/**
	 * Init fieldset for adding GPS data 
	 */
	protected function initAddGPSdataFieldset() {
		$Fieldset = new FormularFieldset('GPS-Daten hinzuf&uuml;gen');
		$Fieldset->setCollapsed();
		$Fieldset->addInfo('
		<span onmouseover="javascript:createUploader()">
			<strong>TCX-Datei nachtr&auml;glich hinzuf&uuml;gen</strong><br />
			<br />
			<span class="c button" id="file-upload-tcx">Datei hochladen</span>
			<script>
			function createUploader() {
				$("#file-upload-tcx").removeClass("hide");
				new AjaxUpload("#file-upload-tcx", {
					action: "'.$_SERVER['SCRIPT_NAME'].'?id='.$this->Training->id().'&json=true",
					onComplete : function(file, response){
						$("#ajax").loadDiv("'.$_SERVER['SCRIPT_NAME'].'?id='.$this->Training->id().'&tmp=true");
					}		
				});
			}
			</script>
		</span>');

		$this->Formular->addFieldset($Fieldset);
	}

	/**
	 * Append JavaScript 
	 */
	protected function appendJavaScript() {
		echo '<script type="text/javascript">';
		include '../lib/jQuery.form.include.php';
		echo '</script>';
	}
}
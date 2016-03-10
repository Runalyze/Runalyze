<?php
/**
 * This file contains class::TrainingFormular
 * @package Runalyze\DataObjects\Training
 */

use Runalyze\Configuration;
use Runalyze\Model\Factory;
use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\Model\Route\Entity as Route;
use Runalyze\Activity\DuplicateFinder;
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
	public static $ONLY_RUNNING_CLASS = "only-running";

	/**
	 * CSS class for inputs only for not running
	 * @var string
	 */
	public static $ONLY_NOT_RUNNING_CLASS = "only-not-running";

	/**
	 * CSS class for inputs only for sports outside
	 * @var string
	 */
	public static $ONLY_OUTSIDE_CLASS = "only-outside";
        
 	/**
	 * CSS class for inputs only for sports with distance
	 * @var string
	 */
	public static $ONLY_DISTANCES_CLASS = "only-distances";

	/**
	 * CSS class for inputs only for sports with power
	 * @var string
	 */
	public static $ONLY_POWER_CLASS = "only-power";

	/**
	 * @var string
	 */
	const POST_KEY_REMOVE_TRACKDATA = 'remove-trackdata';

	/**
	 * @var string
	 */
	const POST_KEY_REMOVE_ROUTE = 'remove-route';

	/**
	 * @var string
	 */
	const POST_KEY_REMOVE_ROUTE_GPS = 'gps';

	/**
	 * Prepare for display
	 */
	protected function prepareForDisplayInSublcass() {
		parent::prepareForDisplayInSublcass();

        $this->initTagFieldset();
		$this->addAdditionalHiddenFields();
		$this->initEquipmentFieldset();

		if ($this->submitMode == StandardFormular::$SUBMIT_MODE_EDIT) {
			$this->addOldObjectData();
			$this->initElevationCorrectionFieldset();
			$this->initFieldsetToRemoveDataSeries();
			$this->initDeleteFieldset();

			if (Request::param('mode') == 'multi') {
				$this->addHiddenValue('mode', 'multi');
				$this->submitButtons['submit'] = __('Save and continue');
			}
		} else if (is_numeric($this->dataObject->get('activity_id'))) {
			$isDuplicate = (new DuplicateFinder(DB::getInstance(), SessionAccountHandler::getId()))->checkForDuplicate($this->dataObject->get('activity_id'));
			if($isDuplicate)
				echo HTML::warning(__('It seems that you already have imported this activity'));
		}

		$this->appendJavaScript();
	}

	/**
	 * Check for submit, therefore all fields must be set 
	 */
	protected function checkForSubmit() {
		if (isset($_POST[self::POST_KEY_REMOVE_TRACKDATA]) || isset($_POST[self::POST_KEY_REMOVE_ROUTE])) {
			$this->removeChosenDataSeries();
		}

		parent::checkForSubmit();
	}

	/**
	 * Remove chosen series
	 */
	protected function removeChosenDataSeries() {
		$Factory = new Factory(SessionAccountHandler::getId());
		$Remover = new Runalyze\Model\Activity\DataSeriesRemover(
			DB::getInstance(),
			SessionAccountHandler::getId(),
			$Factory->activity($this->dataObject->id()),
			$Factory
		);

		if (isset($_POST[self::POST_KEY_REMOVE_TRACKDATA]) && is_array($_POST[self::POST_KEY_REMOVE_TRACKDATA])) {
			foreach (array_keys($_POST[self::POST_KEY_REMOVE_TRACKDATA]) as $key) {
				$Remover->removeFromTrackdata($key);
			}
		}

		if (isset($_POST[self::POST_KEY_REMOVE_ROUTE]) && is_array($_POST[self::POST_KEY_REMOVE_ROUTE])) {
			foreach (array_keys($_POST[self::POST_KEY_REMOVE_ROUTE]) as $key) {
				if ($key == self::POST_KEY_REMOVE_ROUTE_GPS) {
					$Remover->removeGPSpathFromRoute();
				} else {
					$Remover->removeFromRoute($key);
				}
			}
		}

		$Remover->saveChanges();
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
	 * Add additional hidden fields
	 */
	protected function addAdditionalHiddenFields() {
		$this->addHiddenValue('distance-to-km-factor', Configuration::General()->distanceUnitSystem()->distanceToKmFactor());
	}

	/**
	 * Add old object
	 */
	protected function addOldObjectData() {
		$this->addHiddenValue('old-data', base64_encode(serialize($this->dataObject->getArray())));
	}

	/**
	 * Read selected equipment from post data
	 * @param int $checkForSportID optional
	 * @return array
	 */
	public static function readEquipmentFromPost($checkForSportID = false) {
		$SelectedEquipment = array();

		if (!isset($_POST['equipment']) || !is_array($_POST['equipment']))
			return $SelectedEquipment;

		foreach ($_POST['equipment'] as $value) {
			if (is_array($value)) {
				$SelectedEquipment = array_merge($SelectedEquipment, array_keys($value));
			} else {
				$SelectedEquipment[] = $value;
			}
		}

		if ($checkForSportID) {
			$Factory = new Factory(SessionAccountHandler::getId());
			$TypeIDs = $Factory->equipmentTypeForSport($checkForSportID, true);
			foreach ($SelectedEquipment as $i => $id) {
				$Equipment = $Factory->equipment($id);

				if (!in_array($Equipment->typeid(), $TypeIDs)) {
					unset($SelectedEquipment[$i]);
				}
			}
		}

		return $SelectedEquipment;
	}

	/**
	 * Read selected tags from post data
	 * @return array
	 */
	public static function readTagFromPost() {
		if (isset($_POST['tags']) && is_array($_POST['tags'])) {
			$AllTags = (new Factory(SessionAccountHandler::getId()))->allTags();
			foreach ($_POST['tags'] as $key => $value) {
				if (!is_numeric($value)) {
					foreach ($AllTags as $Tag) {
						if ($Tag->tag() == $value) {
							$_POST['tags'][$key] = $Tag->id();
							continue;
						}
					}
				}
			}

			return $_POST['tags'];
		}

		return array();
	}

	/**
	 * Display fieldset: Tag
	 */
	protected function initTagFieldset() {
		$isCreateForm = ($this->submitMode == StandardFormular::$SUBMIT_MODE_CREATE);
		$Factory = new Factory(SessionAccountHandler::getId());
		$CurrentTags = $isCreateForm ? array() : $Factory->tagForActivity($this->dataObject->id(), true);
		$Fieldset = new FormularFieldset( __('Tags') );
		$Fieldset->addField(new FormularInputHidden('tag_old', '', implode(',', $CurrentTags)));

		if (isset($_POST['tags'])) {
			$CurrentTags = self::readTagFromPost();
		}	    

		$Field = new FormularSelectBox('tags', 'Tags', $CurrentTags);

		foreach ($Factory->allTags() as $tag) {
			$Field->addOption($tag->id(), $tag->tag());
		}

		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W50 );
		$Field->addCSSclass('chosen-select-create full-size');
		$Field->setMultiple();
		$Field->addAttribute('data-placeholder', __('Choose tags'));
		$Field->addAttribute('style', 'width=50px;');
		$Fieldset->addField( $Field );
		$this->addFieldset($Fieldset);
	}
      
	/**
	 * Display fieldset: Equipment
	 */
	protected function initEquipmentFieldset() {
		$isCreateForm = ($this->submitMode == StandardFormular::$SUBMIT_MODE_CREATE);
		$Factory = new Factory(SessionAccountHandler::getId());
		$AllEquipment = $Factory->allEquipments();
		$RelatedEquipment = $isCreateForm ? array() : $Factory->equipmentForActivity($this->dataObject->id(), true);

		$Fieldset = new FormularFieldset( __('Equipment') );
		$Fieldset->addField(new FormularInputHidden('equipment_old', '', implode(',', $RelatedEquipment)));

		if (isset($_POST['equipment'])) {
			$RelatedEquipment = self::readEquipmentFromPost();
		}

		foreach ($Factory->allEquipmentTypes() as $EquipmentType) {
			$options = array();
			$values = array();
			$attributes = array();

			foreach ($AllEquipment as $Equipment) {
				if (
					$Equipment->typeid() == $EquipmentType->id() &&
					(!$isCreateForm || $Equipment->isInUse())
				) {
					$options[$Equipment->id()] = $Equipment->name();
					$attributes[$Equipment->id()] = array(
						'data-start' => $Equipment->startDate(),
						'data-end' => $Equipment->endDate()
					);

					if (in_array($Equipment->id(), $RelatedEquipment)) {
						$values[$Equipment->id()] = 'on';
					}
				}
			}

			if (empty($options)) {
				continue;
			}

			if ($EquipmentType->allowsMultipleValues()) {
				$Field = new FormularCheckboxes('equipment['.$EquipmentType->id().']', $EquipmentType->name(), $values);

				foreach ($options as $key => $label) {
					$Field->addCheckbox($key, $label, $attributes[$key]);
				}
			} else {
				$selected = !empty($values) ? array_keys($values) : array(0);
				$Field = new FormularSelectBox('equipment['.$EquipmentType->id().']', $EquipmentType->name(), $selected[0]);
				$Field->addOption(0, '');

				foreach ($options as $key => $label) {
					$Field->addOption($key, $label, $attributes[$key]);
				}
			}

			$SportClasses = 'only-specific-sports';
			foreach ($Factory->sportForEquipmentType($EquipmentType->id(), true) as $id) {
				$SportClasses .= ' only-sport-'.$id;
			}

			$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W50 );
			$Field->addLayoutClass($SportClasses.' depends-on-date');
			$Field->addAttribute( 'class', FormularInput::$SIZE_FULL_INLINE );
			$Fieldset->addField($Field);
		}

		$this->addFieldset($Fieldset);
	}

	/**
	 * Init fieldset to remove data series
	 */
	protected function initFieldsetToRemoveDataSeries() {
		$Fields = $this->fieldOptionsForDataSeriesToRemove();

		if (!empty($Fields)) {
			$Fieldset = new FormularFieldset( __('Remove data series') );
			$Fieldset->addInfo( __('You may want to remove a data series if a sensor, e.g. your heart rate strap, produced unusable data.') );
			$Fieldset->addWarning( __('Attention: This operation cannot be undone.') );
			$Fieldset->setCollapsed();

			foreach ($Fields as $field) {
				$Fieldset->addField($field);
			}

			$Fieldset->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W100_CHECKBOX );
			$this->addFieldset($Fieldset);
		}
	}

	/**
	 * Get field options for data series to remove
	 * @return \FormularCheckbox[]
	 */
	protected function fieldOptionsForDataSeriesToRemove() {
		$Factory = new Factory(SessionAccountHandler::getId());
		$Trackdata = $Factory->trackdata($this->dataObject->id());
		$Route = $Factory->route($this->dataObject->get('routeid'));
		$Fields = array();

		$TrackdataKeys = array(
			Trackdata::TIME => __('Time'),
			Trackdata::DISTANCE => __('Distance'),
			Trackdata::HEARTRATE => __('Heart rate'),
			Trackdata::CADENCE => __('Cadence'),
			Trackdata::VERTICAL_OSCILLATION => __('Vertical oscillation'),
			Trackdata::GROUNDCONTACT_BALANCE => __('Ground contact time balance'),
			Trackdata::GROUNDCONTACT => __('Ground contact time'),
			Trackdata::POWER => __('Power'),
			Trackdata::TEMPERATURE => __('Temperature')
		);

		foreach ($TrackdataKeys as $key => $text) {
			if ($Trackdata->has($key)) {
				$Fields[] = new FormularCheckbox(self::POST_KEY_REMOVE_TRACKDATA.'['.$key.']', $text);
			}
		}

		$RouteKeys = array(
			Route::ELEVATIONS_ORIGINAL => __('Original elevation'),
			Route::ELEVATIONS_CORRECTED => __('Corrected elevation')
		);

		if ($Route->hasPositionData()) {
			$Fields[] = new FormularCheckbox(self::POST_KEY_REMOVE_ROUTE.'['.self::POST_KEY_REMOVE_ROUTE_GPS.']', __('GPS path'));
		}

		foreach ($RouteKeys as $key => $text) {
			if ($Route->has($key)) {
				$Fields[] = new FormularCheckbox(self::POST_KEY_REMOVE_ROUTE.'['.$key.']', $text);
			}
		}

		return $Fields;
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
	 * Init fieldset for correct elevation
	 */
	protected function initElevationCorrectionFieldset() {
		if ($this->dataObject->get('routeid') > 0) {
			$Route = Runalyze\Context::Factory()->route($this->dataObject->get('routeid'));

			if ($Route->hasPositionData() && !$Route->hasCorrectedElevations()) {
				$Fieldset = new FormularFieldset( __('Use elevation correction') );
				$Fieldset->setCollapsed();

				$Fieldset->addInfo('
					<a class="ajax" target="gps-results" href="call/call.Training.elevationCorrection.php?id='.$this->dataObject->id().'"><strong>'.__('Correct elevation data').'</strong></a><br>
					<br>
					<small id="gps-results" class="block">
						'.__('Elevation data via GPS is very inaccurate. Therefore you can correct it via some satellite data.').'
					</small>');

				$this->addFieldset($Fieldset);
			}
		}
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

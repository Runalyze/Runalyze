<?php
/**
 * This file contains class::SearchFormular
 * @package Runalyze\Search
 */
/**
 * Search formular
 *
 * @author Hannes Christiansen
 * @package Runalyze\Search
 */
class SearchFormular extends Formular {
	/**
	 * Fieldset
	 * @var FormularFieldset
	 */
	protected $Fieldset = null;

	/**
	 * Prepare display
	 */
	protected function prepareForDisplayInSublcass() {
		$this->setId('search');
		$this->addCSSclass('ajax');

		$this->setDefaultValues();
		$this->initGeneralFieldset();
		$this->initConditions();
		$this->addFieldSendToMultiEditor();
		$this->addSubmitBlock();
		$this->addPager();
		$this->addFieldset($this->Fieldset);

		$this->setSubmitButtonsCentered();
	}

	/**
	 * Set default values
	 */
	protected function setDefaultValues() {
		if (!isset($_POST['sportid']))
			$_POST['sportid'] = array_keys(SportFactory::AllSports());
		if (!isset($_POST['date-from']))
			$_POST['date-from'] = date('d.m.Y', START_TIME);
		if (!isset($_POST['date-to']))
			$_POST['date-to'] = date('d.m.Y');
	}

	/**
	 * Init general fieldsets
	 */
	protected function initGeneralFieldset() {
		$this->Fieldset = new FormularFieldset( __('Search activities') );

		$this->addFieldSport();
		$this->addFieldTimeRange();
		$this->addFieldSort();

		$this->Fieldset->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W50_IN_W33 );
	}

	/**
	 * Add block with submit button
	 */
	protected function addSubmitBlock() {
		$Field = new FormularSubmit(__('Search'), 'submit');
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W33.' c' );

		$this->Fieldset->addField($Field);
	}

	/**
	 * Add hidden page value
	 */
	private function addPager() {
		$this->addHiddenValue('page', 1);
	}

	/**
	 * Add field: time range
	 */
	private function addFieldTimeRange() {
		$Field = new FormularInputSearchTimeRange('search_time_range', __('Time range'));
		$this->Fieldset->addField( $Field );
	}

	/**
	 * Add field: sort
	 */
	private function addFieldSort() {
		$Field = new FormularSelectSearchSort('search_sort', __('Sorting'));
		$this->Fieldset->addField( $Field );
	}

	/**
	 * Add field: send to multi editor
	 */
	private function addFieldSendToMultiEditor() {
		$Label = Ajax::tooltip(
			__('Send to multi editor'),
			sprintf(__('Our multi editor is limited to a maximum of %d activities.'), SearchResults::MAX_LIMIT_FOR_MULTI_EDITOR),
			'atRight'
		);

		$Field = new FormularCheckbox('send-to-multi-editor', $Label);
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W33 );

		$this->Fieldset->addField( $Field );
	}

	/**
	 * Add field: sport
	 */
	private function addFieldSport() {
		$Field = new FormularSelectDb('sportid', __('Sport'));
		$Field->loadOptionsFrom('sport', 'name');
		$Field->addCSSclass('chzn-select full-size');
		$Field->setMultiple();
		$Field->addAttribute('data-placeholder', __('Choose sport(s)'));
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W33 );

		$this->Fieldset->addField( $Field );
	}

	/**
	 * Init conditions fieldset
	 */
	protected function initConditions() {
		$this->addConditionFieldWithChosen('typeid', 'type', 'name', __('Type'), __('Choose activity type(s)'));
		$this->addConditionFieldWithChosen('shoeid', 'shoe', 'name', __('Shoe'), __('Choose shoe(s)'));
		$this->addConditionFieldWithChosen('weatherid', 'weather', 'name', __('Weather'), __('Choose weather conditions'));
		$this->addConditionFieldWithChosen('clothes', 'clothes', 'name', __('Clothing'), __('Choose clothing'));

		$this->addConditionField('distance', __('Distance'), FormularInput::$SIZE_SMALL, FormularUnit::$KM);
		$this->addConditionField('elevation', __('Elevation'), FormularInput::$SIZE_SMALL, FormularUnit::$M);
		$this->addConditionField('route', __('Route'), FormularInput::$SIZE_MIDDLE);
		$this->addConditionField('s', __('Duration'), FormularInput::$SIZE_SMALL);
		$this->addConditionField('temperature', __('Temperature'), FormularInput::$SIZE_SMALL, FormularUnit::$CELSIUS);
		$this->addConditionField('comment', __('Comment'), FormularInput::$SIZE_MIDDLE);
		$this->addConditionField('pulse_avg', __('avg. HR'), FormularInput::$SIZE_SMALL, FormularUnit::$BPM);
		$this->addConditionField('kcal', __('Calories'), FormularInput::$SIZE_SMALL, FormularUnit::$KCAL);
		$this->addConditionField('partner', __('Partner'), FormularInput::$SIZE_MIDDLE);
		$this->addConditionField('pulse_max', __('max. HR'), FormularInput::$SIZE_SMALL, FormularUnit::$BPM);
		$this->addConditionField('cadence', __('Cadence'), FormularInput::$SIZE_SMALL, FormularUnit::$SPM);
		$this->addBooleanField('is_public', __('Is public'));
		$this->addConditionField('jd_intensity', __('JD points'), FormularInput::$SIZE_SMALL);
		$this->addConditionField('groundcontact', __('Ground contact'), FormularInput::$SIZE_SMALL, FormularUnit::$MS);
		$this->addBooleanField('use_vdot', __('Uses VDOT'));
		$this->addConditionField('trimp', __('TRIMP'), FormularInput::$SIZE_SMALL);
		$this->addConditionField('vertical_oscillation', __('Vertical oscillation'), FormularInput::$SIZE_SMALL, FormularUnit::$CM);
		$this->addBooleanField('abc', __('Running drills'));
		$this->addConditionField('stride_length', __('Stride length'), FormularInput::$SIZE_SMALL, FormularUnit::$M);
	}

	/**
	 * Add condition field with chosen
	 * @param string $name
	 * @param string $table
	 * @param string $key
	 * @param string $label
	 * @param string $placeholder
	 */
	private function addConditionFieldWithChosen($name, $table, $key, $label, $placeholder) {
		if ($table == 'weather') {
			$Field = new TrainingSelectWeather($name, $label);
		} else {
			$Field = new FormularSelectDb($name, $label);
			$Field->loadOptionsFrom($table, $key);
		}
		$Field->addCSSclass('chzn-select full-size');
		$Field->setMultiple();
		$Field->addAttribute('data-placeholder', $placeholder);
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W50_IN_W33 );

		$this->Fieldset->addField( $Field );
	}

	/**
	 * Add standard condition field
	 * @param type $key
	 * @param type $label
	 * @param type $size
	 * @param type $unit
	 */
	private function addConditionField($key, $label, $size = '', $unit = '') {
		$Field = new FormularInputWithEqualityOption($key, $label);
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W33 );

		if (!empty($size))
			$Field->setSize($size);
		if (!empty($unit))
			$Field->setUnit($unit);

		$this->Fieldset->addField($Field);
	}

	/**
	 * Add boolean field
	 * @param string $key
	 * @param string $label
	 */
	private function addBooleanField($key, $label) {
		$Field = new FormularSelectBox($key, $label);
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W33 );
		$Field->setOptions(array(
			'' => '',
			'1' => __('Yes'),
			'0' => __('No')
		));

		$this->Fieldset->addField($Field);
	}

	/**
	 * Transform old params to new params
	 */
	static public function transformOldParamsToNewParams() {
		if (isset($_POST['val']) && is_array($_POST['val']))
			foreach ($_POST['val'] as $key => $value)
				$_POST[$key] = $value;

		if (isset($_POST['time-gt']))
			$_POST['date-from'] = $_POST['time-gt'];
		if (isset($_POST['time-lt']))
			$_POST['date-to'] = $_POST['time-lt'];
		if (isset($_POST['order']))
			$_POST['search-sort-by'] = $_POST['order'];
		if (isset($_POST['sort']))
			$_POST['search-sort-order'] = $_POST['sort'];
	}
}
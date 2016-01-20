<?php
/**
 * This file contains class::SearchFormular
 * @package Runalyze\Search
 */

use Runalyze\Configuration;
use Runalyze\Data\Weather\Humidity;
use Runalyze\Data\Weather\WindSpeed;
use Runalyze\Data\Weather\Pressure;
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
		$Field = new FormularSelectDb('sportid', __('Sport').$this->shortLinksForSportField());
		$Field->loadOptionsFrom('sport', 'name');
		$Field->addCSSclass('chosen-select full-size');
		$Field->setMultiple();
		$Field->addAttribute('data-placeholder', __('Choose sport(s)'));
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W33 );

		$this->Fieldset->addField( $Field );
	}

	/**
	 * @return string
	 */
	protected function shortLinksForSportField() {
		$code = '<span class="link chosen-select-all" data-target="sportid">'.__('all').'</span>';
		$code .= ' | ';
		$code .= '<span class="link chosen-select-none" data-target="sportid">'.__('none').'</span>';

		return '<span class="right small">'.$code.'&nbsp;</span>';
	}

	/**
	 * Add field: notes
	 */
	private function addFieldNotes() {
		$Field = new FormularInput('notes', __('Notes'));
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W33 );
		$Field->setSize( FormularInput::$SIZE_FULL_INLINE );

		$this->Fieldset->addField( $Field );
		$this->Fieldset->addField( new FormularInputHidden('opt[notes]', '', 'like') );
	}

	/**
	 * Init conditions fieldset
	 */
	protected function initConditions() {
		$this->addConditionFieldWithChosen('typeid', 'type', 'name', __('Type'), __('Choose activity type(s)'));
		$this->addConditionFieldWithChosen('weatherid', 'weather', 'name', __('Weather'), __('Choose weather conditions'));
		$this->addConditionFieldWithChosen('equipmentid', 'equipment', 'name', __('Equipment'), __('Choose equipment'));
                $this->addConditionFieldWithChosen('tagid', 'tag', 'tag', __('Tag'), __('Choose tag'));


		$this->addFieldNotes();

		$this->addNumericConditionField('distance', __('Distance'), FormularInput::$SIZE_SMALL, Configuration::General()->distanceUnitSystem()->distanceUnit());
		$this->addNumericConditionField('elevation', __('Elevation'), FormularInput::$SIZE_SMALL, Configuration::General()->distanceUnitSystem()->elevationUnit());
		$this->addStringConditionField('route', __('Route'), FormularInput::$SIZE_MIDDLE);
		$this->addDurationField('s', __('Duration'));
		$this->addNumericConditionField('temperature', __('Temperature'), FormularInput::$SIZE_SMALL, Configuration::General()->temperatureUnit()->unit());
		$this->addNumericConditionField('humidity', __('Humidity'), FormularInput::$SIZE_SMALL, (new Humidity())->unit());
		$this->addNumericConditionField('pressure', __('Pressure'), FormularInput::$SIZE_SMALL, (new Pressure())->unit());
		$this->addNumericConditionField('wind_speed', __('Wind Speed'), FormularInput::$SIZE_SMALL, (new WindSpeed())->unit());
		$this->addStringConditionField('comment', __('Title'), FormularInput::$SIZE_MIDDLE);
		$this->addNumericConditionField('pulse_avg', __('avg. HR'), FormularInput::$SIZE_SMALL, FormularUnit::$BPM);
		$this->addNumericConditionField('kcal', __('Calories'), FormularInput::$SIZE_SMALL, FormularUnit::$KCAL);
		$this->addStringConditionField('partner', __('Partner'), FormularInput::$SIZE_MIDDLE);
		$this->addNumericConditionField('pulse_max', __('max. HR'), FormularInput::$SIZE_SMALL, FormularUnit::$BPM);
		$this->addNumericConditionField('cadence', __('Cadence'), FormularInput::$SIZE_SMALL, FormularUnit::$SPM);
		$this->addBooleanField('is_public', __('Is public'));
		$this->addNumericConditionField('jd_intensity', __('JD points'), FormularInput::$SIZE_SMALL);
		$this->addNumericConditionField('groundcontact', __('Ground contact'), FormularInput::$SIZE_SMALL, FormularUnit::$MS);
		$this->addBooleanField('use_vdot', __('Uses VDOT'));
		$this->addNumericConditionField('trimp', __('TRIMP'), FormularInput::$SIZE_SMALL);
		$this->addNumericConditionField('vertical_oscillation', __('Vertical oscillation'), FormularInput::$SIZE_SMALL, FormularUnit::$CM);
		$this->addNumericConditionField('vertical_ratio', __('Vertical ratio'), FormularInput::$SIZE_SMALL, FormularUnit::$PERCENT);
		$this->addNumericConditionField('groundcontact_balance', __('Ground Contact Balance'), FormularInput::$SIZE_SMALL, 'L'. FormularUnit::$PERCENT);
		$this->addNumericConditionField('stride_length', __('Stride length'), FormularInput::$SIZE_SMALL, Configuration::General()->distanceUnitSystem()->strideLengthUnit());
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
		$Field->addCSSclass('chosen-select full-size');
		$Field->setMultiple();
		$Field->addAttribute('data-placeholder', $placeholder);
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W50_IN_W33 );

		$this->Fieldset->addField( $Field );
	}

	/**
	 * @param string $key
	 * @param string $label
	 * @param string $size
	 * @param string $unit
	 */
	protected function addNumericConditionField($key, $label, $size = '', $unit = '') {
		$this->addConditionField($key, $label, $size, $unit, 'numeric');
	}

	/**
	 * @param string $key
	 * @param string $label
	 * @param string $size
	 * @param string $unit
	 */
	protected function addStringConditionField($key, $label, $size = '', $unit = '') {
		$this->addConditionField($key, $label, $size, $unit, 'string');
	}

	/**
	 * Add standard condition field
	 * @param string $key
	 * @param string $label
	 * @param string $size
	 * @param string $unit
	 * @param string $type options: all | numeric | string
	 */
	private function addConditionField($key, $label, $size = '', $unit = '', $type = 'all') {
		$Field = new FormularInputWithEqualityOption($key, $label);
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W33 );

		if (!empty($size))
			$Field->setSize($size);
		if (!empty($unit))
			$Field->setUnit($unit);

		if ($type == 'numeric') {
			$Field->setNumericOptions();
		} elseif ($type == 'string') {
			$Field->setStringOptions();
		}

		$this->Fieldset->addField($Field);
	}

	/**
	 * @param string $key
	 * @param string $label
	 */
	protected function addDurationField($key, $label) {
		$Field = new FormularInputWithEqualityOption($key, $label);
		$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W33);
		$Field->setSize(FormularInput::$SIZE_SMALL);
		$Field->setParser(FormularValueParser::$PARSER_TIME, array('hide-empty' => true));
		$Field->addAttribute('placeholder', 'h:mm:ss');
		$Field->setNumericOptions();

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
	public static function transformOldParamsToNewParams() {
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

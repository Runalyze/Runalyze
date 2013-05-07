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
		if (!empty($_POST))
			return;

		$_POST = array(
			'sportid'	=> array_keys(SportFactory::AllSports()),
			'date-from'	=> date('d.m.Y', START_TIME),
			'date-to'	=> date('d.m.Y')
		);
	}

	/**
	 * Init general fieldsets
	 */
	protected function initGeneralFieldset() {
		$this->Fieldset = new FormularFieldset('Trainings suchen');

		$this->addFieldSport();
		$this->addFieldTimeRange();
		$this->addFieldSort();

		$this->Fieldset->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W50_IN_W33 );
	}

	/**
	 * Add block with submit button
	 */
	protected function addSubmitBlock() {
		$Field = new FormularSubmit('Suchen', 'submit');
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
		$Field = new FormularInputSearchTimeRange('search_time_range', 'Zeitraum');
		$this->Fieldset->addField( $Field );
	}

	/**
	 * Add field: sort
	 */
	private function addFieldSort() {
		$Field = new FormularSelectSearchSort('search_sort', 'Sortierung');
		$this->Fieldset->addField( $Field );
	}

	/**
	 * Add field: send to multi editor
	 */
	private function addFieldSendToMultiEditor() {
		$Field = new FormularCheckbox('send-to-multi-editor', 'An Multi-Editor senden');
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W33 );

		$this->Fieldset->addField( $Field );
	}

	/**
	 * Add field: sport
	 */
	private function addFieldSport() {
		$Field = new FormularSelectDb('sportid', 'Sportart');
		$Field->loadOptionsFrom('sport', 'name');
		$Field->addCSSclass('chzn-select fullSize');
		$Field->setMultiple();
		$Field->addAttribute('data-placeholder', 'W&auml;hle die Sportarten');
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W33 );

		$this->Fieldset->addField( $Field );
	}

	/**
	 * Init conditions fieldset
	 */
	protected function initConditions() {
		$this->addConditionFieldWithChosen('typeid', 'type', 'name', 'Trainingstyp', 'W&auml;hle die Trainingstypen');
		$this->addConditionFieldWithChosen('shoeid', 'shoe', 'name', 'Schuhe', 'W&auml;hle die Schuhe');
		$this->addConditionFieldWithChosen('weatherid', 'weather', 'name', 'Wetter', 'W&auml;hle das Wetter');
		$this->addConditionFieldWithChosen('clothes', 'clothes', 'name', 'Kleidung', 'W&auml;hle die Kleidungsst&uuml;cke');

		$this->addConditionField('distance', 'Distanz', FormularInput::$SIZE_SMALL, FormularUnit::$KM);
		$this->addConditionField('route', 'Strecke', FormularInput::$SIZE_MIDDLE);
		$this->addConditionField('elevation', 'H&ouml;henmeter', FormularInput::$SIZE_SMALL, FormularUnit::$M);
		$this->addConditionField('s', 'Dauer', FormularInput::$SIZE_SMALL);
		$this->addConditionField('comment', 'Bemerkung', FormularInput::$SIZE_MIDDLE);
		$this->addConditionField('temperature', 'Temperatur', FormularInput::$SIZE_SMALL, FormularUnit::$CELSIUS);
		$this->addConditionField('pulse_avg', 'Puls', FormularInput::$SIZE_SMALL, FormularUnit::$BPM);
		$this->addConditionField('partner', 'Trainingspartner', FormularInput::$SIZE_MIDDLE);
		$this->addConditionField('kcal', 'Kalorien', FormularInput::$SIZE_SMALL, FormularUnit::$KCAL);
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
			$Options = Weather::getFullArray();
			foreach ($Options as $id => $data)
				$Options[$id] = $data['name'];

			$Field = new FormularSelectBox($name, $label);
			$Field->setOptions( $Options );
		} else {
			$Field = new FormularSelectDb($name, $label);
			$Field->loadOptionsFrom($table, $key);
		}
		$Field->addCSSclass('chzn-select fullSize');
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
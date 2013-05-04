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
		$this->setHeader('Trainings suchen');
		$this->setId('search');
		$this->addCSSclass('ajax');

		$this->setDefaultValues();
		$this->initFieldset();
		$this->addFieldset($this->Fieldset);

		$this->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W33 );
		$this->addSubmitButton('Suchen');
		$this->setSubmitButtonsCentered();
	}

	protected function setDefaultValues() {
		if (!empty($_POST))
			return;

		$_POST = array(
			'sport'		=> array(CONF_MAINSPORT),
			'date-from'	=> date('d.m.Y', START_TIME),
			'date-to'	=> date('d.m.Y')
		);
	}

	/**
	 * Init fieldset
	 */
	protected function initFieldset() {
		$this->Fieldset = new FormularFieldset();

		$this->addFieldTimeRange();
		$this->addFieldSort();
		$this->addFieldSendToMultiEditor();
		$this->addFieldSport();
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
		$this->Fieldset->addField( new FormularCheckbox('send-to-multi-editor', 'An Multi-Editor senden') );
	}

	/**
	 * Add field: sport
	 */
	private function addFieldSport() {
		$Field = new FormularSelectDb('sport', 'Sportart');
		$Field->loadOptionsFrom('sport', 'name');
		$Field->addCSSclass('chzn-select fullSize');
		$Field->setMultiple();
		$Field->addAttribute('data-placeholder', 'W&auml;hle die Sportarten');
		$Field->setLayout( FormularFieldset::$LAYOUT_FIELD_W100_IN_W33 );

		$this->Fieldset->addField( $Field );
	}
}
<?php
/**
 * This file contains class::FormularInputDayAndDaytime
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for a double field for day and daytime
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularInputDayAndDaytime extends FormularField {
	/**
	 * Internal field for time
	 * @var FormularInput
	 */
	protected $FieldTime = null;

	/**
	 * Internal field for day
	 * @var FormularInput
	 */
	protected $FieldDay = null;

	/**
	 * Internal field for daytime
	 * @var FormularInput
	 */
	protected $FieldDaytime = null;

	/**
	 * Internal layout to forward o internal fields
	 * @var string
	 */
	protected $internalLayout = '';

	/**
	 * Internal layout classes to forward o internal fields
	 * @var string
	 */
	protected $internalLayoutClasses = '';

	/**
	 * Get field name for day
	 * @return string
	 */
	private function getFieldDayName() {
		return $this->name.'_day';
	}

	/**
	 * Get field name for daytime
	 * @return string
	 */
	private function getFieldDaytimeName() {
		return $this->name.'_daytime';
	}

	/**
	 * Validate value
	 * @return boolean
	 */
	public function validate() {
		$this->setFields();

		if (!isset($_POST[$this->getFieldDayName()]) || !isset($_POST[$this->getFieldDaytimeName()]))
			return;

		$this->FieldDay->validate();
		$this->FieldDaytime->validate();

		$_POST[$this->name] = $_POST[$this->getFieldDayName()] + $_POST[$this->getFieldDaytimeName()];
	}

	/**
	 * Prepare for beeing display, may be overwritten
	 */
	protected function prepareForDisplay() {
		$this->setFields();
		$this->resetLayouts();
		$this->setLayoutsToFields();

		if (self::hasKeyFailed($this->getFieldDayName()) || self::hasKeyFailed($this->getFieldDaytimeName()))
			self::setKeyAsFailed($this->name);
	}

	/**
	 * Set internal fields
	 */
	protected function setFields() {
		if (!is_null($this->FieldTime))
			return;

		$this->FieldTime = new FormularInputHidden($this->name, 'Zeit', $this->value);

		$this->FieldDay = new FormularInput($this->getFieldDayName(), 'Datum', $this->value);
		$this->FieldDay->addCSSclass('pick-a-date');
		$this->FieldDay->setParser(FormularValueParser::$PARSER_DATE);

		$this->FieldDaytime = new FormularInput($this->getFieldDaytimeName(), 'Uhrzeit', $this->value);
		$this->FieldDaytime->setParser(FormularValueParser::$PARSER_DAYTIME);
	}

	/**
	 * Reset layouts
	 */
	protected function resetLayouts() {
		$this->internalLayout        = $this->layout;
		$this->internalLayoutClasses = $this->layoutClasses;
		$this->layout        = '';
		$this->layoutClasses = array();
	}

	/**
	 * Forward layout to internal fields
	 */
	protected function setLayoutsToFields() {
		$this->FieldDay->setLayout($this->internalLayout);
		$this->FieldDaytime->setLayout($this->internalLayout);

		foreach ($this->internalLayoutClasses as $Class) {
			$this->FieldDay->addLayoutClass($Class);
			$this->FieldDaytime->addLayoutClass($Class);
		}
	}

	/**
	 * Get code for displaying the field
	 * @return string
	 */
	protected function getFieldCode() {
		$Code  = '';
		$Code .= $this->FieldDay->getCode();
		$Code .= $this->FieldDaytime->getCode();

		return $Code;
	}
}
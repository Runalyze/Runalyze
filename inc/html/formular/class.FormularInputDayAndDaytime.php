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
	 * Internal layout classes to forward to internal fields
	 * @var array
	 */
	protected $internalLayoutClasses = array();

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
	 * @return bool
	 */
	public function validate() {
		$this->setFields();

		if (!isset($_POST[$this->getFieldDayName()]) || !isset($_POST[$this->getFieldDaytimeName()]))
			return false;

		$dateString = $_POST[$this->getFieldDayName()];
		$timeString = $_POST[$this->getFieldDaytimeName()];

		$validateDay = $this->FieldDay->validate();
		$validateDaytime = $this->FieldDaytime->validate();

		$date = $_POST[$this->getFieldDayName()];
		$time = $_POST[$this->getFieldDaytimeName()];

		if ($validateDay && $validateDaytime) {
			$_POST[$this->name] = strtotime($dateString.' '.$timeString);

			if (false === $_POST[$this->name]) { // i.e. FormularValueParser "ignored/fixed" some bad input
				$_POST[$this->name] = $date + $time;
			}

			return true;
		}

		return false;
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

		$this->FieldTime = new FormularInputHidden($this->name, __('Time'), $this->value);

		$this->FieldDay = new FormularInput($this->getFieldDayName(), __('Date'), $this->value);
		$this->FieldDay->addCSSclass('pick-a-date');
		$this->FieldDay->setParser(FormularValueParser::$PARSER_DATE);

		$this->FieldDaytime = new FormularInputTime($this->getFieldDaytimeName(), __('Time of day'), $this->value);
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

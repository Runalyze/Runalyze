<?php
/**
 * This file contains class::FormularCheckbox
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for a checkbox
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularCheckbox extends FormularField {
	/**
	 * Boolean flag: label is on the right side
	 * @var boolean
	 */
	private $labelOnRight = false;

	/**
	 * Boolean flag: add hidden field with '..._sent'
	 * @var boolean
	 */
	private $addHiddenSent = false;

	/**
	 * Construct a new field
	 * @param string $name
	 * @param string $label
	 * @param string $value optional, default: loading from $_POST
	 */
	public function __construct($name, $label, $value = '') {
		parent::__construct($name, $label, $value);

		$this->setParser(FormularValueParser::$PARSER_BOOL);
	}

	/**
	 * Prepare for beeing displayed 
	 */
	protected function prepareForDisplay() {
		$this->addAttribute('type', 'checkbox');
		$this->addAttribute('name', $this->name);
		$this->setId($this->name);

		if ($this->value)
			$this->addAttribute('checked', 'checked');
	}

	/**
	 * Set label to the right side 
	 */
	public function setLabelToRight() {
		$this->labelOnRight = true;
	}

	/**
	 * Add hidden value for sent-flag
	 */
	public function addHiddenSentValue() {
		$this->addHiddenSent = true;
	}

	/**
	 * Display this field
	 * @return string
	 */
	protected function getFieldCode() {
		$label = '<label class="checkable" for="'.$this->name.'">'.$this->label.'</label>';
		$input = '<input '.$this->attributes().' />';

		if ($this->addHiddenSent)
			$input .= HTML::hiddenInput($this->name.'_sent', 'true');

		if ($this->labelOnRight)
			return $input.' '.$label;

		return $label.' '.$input;
	}
}
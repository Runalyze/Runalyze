<?php
/**
 * Class for a checkbox
 * @author Hannes Christiansen <mail@laufhannes.de>
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
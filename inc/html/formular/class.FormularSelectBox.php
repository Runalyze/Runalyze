<?php
/**
 * Class for a standard select box
 */
class FormularSelectBox extends FormularField {
	/**
	 * Array with all possible options
	 * @var array
	 */
	private $options = array();

	/**
	 * Prepare for beeing displayed 
	 */
	protected function prepareForDisplay() {
		// No attributes needed, HTML-function used
	}

	/**
	 * Add option to selectBox
	 * @param mixed $key
	 * @param string $text 
	 */
	public function addOption($key, $text) {
		$this->options[$key] = $text;
	}

	/**
	 * Set all options
	 * @param array $options keys are values for selectBox
	 */
	public function setOptions($options) {
		$this->options = $options;
	}

	/**
	 * Display this field
	 * @return string
	 */
	protected function getFieldCode() {
		$label  = '<label for="'.$this->name.'">'.$this->label.'</label>';
		$select = HTML::selectBox($this->name, $this->options, $this->value, $this->name);

		return $label.$select;
	}
}
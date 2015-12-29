<?php
/**
 * This file contains class::FormularCheckboxes
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for multiple checkboxes
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularCheckboxes extends FormularField {
	/**
	 * Array with all possible checkboxes
	 * @var array
	 */
	private $checkboxes = array();

	/** @var array */
	protected $attributes = array();

	/**
	 * Add checkbox
	 * @param mixed $key
	 * @param string $label
	 * @param array $attributes
	 */
	public function addCheckbox($key, $label, $attributes = array()) {
		$this->checkboxes[$key] = $label;
		$this->attributes[$key] = $attributes;
	}

	/**
	 * Get name for one checkbox
	 * @param mixed $key
	 * @return string
	 */
	private function getNameForKey($key) {
		return $this->name.'['.$key.']';
	}

	/**
	 * Get all checkboxes
	 * @return string 
	 */
	private function getCheckboxes() {
		$Checkboxes = '';

		foreach ($this->checkboxes as $key => $label) {
			$value    = isset($this->value[$key]) && $this->value[$key] == 'on' ? 1 : 0;
			$label = Helper::Cut($label, 12);

			$Checkbox = new FormularCheckbox($this->getNameForKey($key), $label, $value);
			$Checkbox->setLayout( FormularFieldset::$LAYOUT_FIELD_SMALL_INLINE );
			$Checkbox->addLayout( FormularFieldset::$LAYOUT_FIELD_W25 );
			$Checkbox->setLabelToRight();

			foreach ($this->attributes[$key] as $attrKey => $attrValue) {
				$Checkbox->addAttribute($attrKey, $attrValue);
			}

			$Checkboxes .= $Checkbox->getCode();
		}

		return $Checkboxes;
	}

	/**
	 * Display this field
	 * @return string
	 */
	protected function getFieldCode() {
		$label      = '<label>'.$this->label.'</label>';
		$checkboxes = '<div class="full-size left">'.$this->getCheckboxes().'</div>';

		return $label.$checkboxes;
	}
}
<?php
/**
 * Class for multiple checkboxes
 */
class FormularCheckboxes extends FormularField {
	/**
	 * Array with all possible checkboxes
	 * @var array
	 */
	private $checkboxes = array();

	/**
	 * Prepare for beeing displayed 
	 */
	protected function prepareForDisplay() {
		// Nothing todo here: no attributes because this field is not a real field itself
	}

	/**
	 * Add checkbox
	 * @param mixed $key
	 * @param string $label 
	 */
	public function addCheckbox($key, $label) {
		$this->checkboxes[$key] = $label;
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
			$Checkbox = new FormularCheckbox($this->getNameForKey($key), $label);
			$Checkbox->setLayout( FormularFieldset::$LAYOUT_FIELD_SMALL_INLINE );
			$Checkbox->addLayout( FormularFieldset::$LAYOUT_FIELD_W25 );
			$Checkbox->setLabelToRight();

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
		$checkboxes = '<div class="fullSize left">'.$this->getCheckboxes().'</div>';

		return $label.$checkboxes;
	}
}
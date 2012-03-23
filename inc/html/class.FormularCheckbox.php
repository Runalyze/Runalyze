<?php
/**
 * Class for a checkbox
 */
class FormularCheckbox extends FormularField {
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
	 * Display this field 
	 */
	public function displayField() {
		echo '<label for="'.$this->name.'">'.$this->label.'</label>';
		echo '<input '.$this->attributes().' />';
		//echo '<label for="'.$this->name.'">'.$this->label.'</label>';
	}
}
?>

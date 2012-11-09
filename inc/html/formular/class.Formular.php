<?php
/**
 * Class for displaying a formular
 * @TODO Validation befor submitting
 * @author Hannes Christiansen 
 */
class Formular extends HtmlTag {
	/**
	 * Array with all hidden values, keys are names
	 * @var array 
	 */
	protected $hiddenValues = array();

	/**
	 * Array with all fieldsets
	 * @var array 
	 */
	protected $fieldsets = array();

	/**
	 * Array with all submit buttons, keys are names
	 * @var array 
	 */
	protected $submitButtons = array();

	/**
	 * Boolean flag: submit buttons centered
	 * @var boolean 
	 */
	protected $submitButtonsCentered = false;

	/**
	 * Formular action
	 * @var string 
	 */
	protected $action = '';

	/**
	 * Formular method
	 * @var string 
	 */
	protected $method = '';

	/**
	 * H1-header
	 * @var string 
	 */
	protected $header = '';

	/**
	 * All failures, beeing displayed above submit-button
	 * @var array
	 */
	protected $failures = array();

	/**
	 * Construct a new formular
	 * @param string $action
	 * @param string $method 
	 */
	public function __construct($action = '', $method = 'post') {
		if (empty($action))
			$action = $_SERVER['SCRIPT_NAME'];

		$this->action = $action;
		$this->method = $method;
	}

	/**
	 * Set header
	 * @param string $string 
	 */
	public function setHeader($string) {
		$this->header = $string;
	}

	/**
	 * Add a hidden value
	 * @param string $name
	 * @param string $value 
	 */
	public function addHiddenValue($name, $value = '') {
		if (empty($value) && isset($_POST[$name]))
			$value = $_POST[$name];

		$this->hiddenValues[$name] = $value;
	}

	/**
	 * Add a fieldset to formular
	 * @param FormularFieldset $Fieldset 
	 */
	public function addFieldset($Fieldset) {
		$Fieldset->setId($this->Id.'_legend_'.count($this->fieldsets));

		$this->fieldsets[] = $Fieldset;
	}

	/**
	 * Add a submit button
	 * @param string $value
	 * @param string $name optional, default 'submit'
	 */
	public function addSubmitButton($value, $name = 'submit') {
		$this->submitButtons[$name] = $value;
	}

	/**
	 * Set submit buttons centered 
	 */
	public function setSubmitButtonsCentered() {
		$this->submitButtonsCentered = true;
	}

	/**
	 * Add a failure, displayed above submit-button
	 * @param string $message 
	 */
	protected function addFailure($message) {
		$this->failures[] = $message;
	}

	/**
	 * Set a specific layout for all fields in every fieldset
	 */
	public function setLayoutForFields($layout) {
		foreach ($this->fieldsets as &$Fieldset)
			$Fieldset->setLayoutForFields($layout);
	}

	/**
	 * Set toggle-function: only one opened fieldset 
	 */
	public function allowOnlyOneOpenedFieldset() {
		foreach ($this->fieldsets as &$Fieldset)
			$Fieldset->allowOnlyOneOpenedFieldset();
	}

	/**
	 * Prepare object for beeing displayed 
	 */
	protected function prepareForDisplay() {
		$this->addAttribute('action', $this->action);
		$this->addAttribute('method', $this->method);

		$this->prepareForDisplayInSublcass();
	}

	/**
	 * Additional preparation for subclasses 
	 */
	protected function prepareForDisplayInSublcass() {}

	/**
	 * Display this formular 
	 */
	public function display() {
		$this->prepareForDisplay();

		echo '<form '.$this->attributes().'>';

		if (!empty($this->header))
			echo '<h1>'.$this->header.'</h1>';

		foreach ($this->hiddenValues as $name => $value)
			echo '<input type="hidden" name="'.$name.'" value="'.$value.'" />';

		foreach ($this->fieldsets as $Fieldset)
			$Fieldset->display();

		foreach ($this->failures as $message)
			echo HTML::error($message);

		if ($this->submitButtonsCentered)
			echo '<div class="c">';

		foreach ($this->submitButtons as $name => $value)
			echo '<input type="submit" name="'.$name.'" value="'.$value.'" />';

		if ($this->submitButtonsCentered)
			echo '</div>';

		echo '</form>';
	}
}
?>

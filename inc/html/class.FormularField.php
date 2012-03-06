<?php
/**
 * Class for a field as part of a formular
 * @author Hannes Christiansen 
 */
abstract class FormularField extends HtmlTag {
	/**
	 * CSS-class if validation failed
	 * @var string 
	 */
	static public $CSS_VALIDATION_FAILED = 'validationFailed';

	/**
	 * Name
	 * @var string 
	 */
	protected $name = '';

	/**
	 * Value
	 * @var string 
	 */
	protected $value = '';

	/**
	 * Label
	 * @var string 
	 */
	protected $label = '';

	/**
	 * Layout
	 * @var string 
	 */
	protected $layout = '';

	/**
	 * Enum from FormularValueParser
	 * @var enum
	 */
	protected $parser = null;

	/**
	 * Array with options for parser
	 * @var array 
	 */
	protected $parserOptions = array();

	/**
	 * Construct a new field
	 * @param string $name
	 * @param string $label
	 * @param string $value optional, default: loading from $_POST
	 */
	public function __construct($name, $label, $value = '') {
		$this->name = $name;
		$this->label = $label;

		if (!empty($value))
			$this->value = $value;
		elseif (isset($_POST[$name]))
			$this->value = $_POST[$name];
	}

	/**
	 * Set default value if empty
	 * @param string $value 
	 */
	public function defaultValue($value) {
		if (empty($this->value))
			$this->value = $value;
	}

	/**
	 * Set layout, used as css-class for a surrounding div
	 * @param string $layout 
	 */
	public function setLayout($layout) {
		$this->layout = $layout;
	}

	/**
	 * Set parser
	 * @param enum $parser
	 * @param array $options 
	 */
	public function setParser($parser, $options) {
		$this->parser = $parser;
		$this->parserOptions = $options;
	}

	/**
	 * Prepare for beeing display, may be overwritten
	 */
	protected function prepareForDisplay() {}

	/**
	 * Validate value
	 */
	final protected function validate() {
		// TODO
	}

	/**
	 * Parse value for display
	 */
	final protected function parseForDisplay() {
		FormularValueParser::parse($this->value, $this->parser, $this->parserOptions);
	}

	/**
	 * Display this field 
	 */
	public function display() {
		$this->parseForDisplay();
		$this->prepareForDisplay();

		echo '<div class="'.$this->layout.'">';
		$this->displayField();
		echo '</div>';
	}

	/**
	 * Each field has its own display-method 
	 */
	abstract public function displayField();
}
?>

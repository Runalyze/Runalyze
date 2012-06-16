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
	 * CSS classes for layout
	 * @var array
	 */
	protected $layoutClasses = array();

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
	 * Field is prepared for being displayed
	 * @var boolean
	 */
	private $prepared = false;

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
		elseif (substr($name, -1) == ']') {
			$posOfBracket = strpos($name, '[');
			$mainName     = substr($name, 0, $posOfBracket);
			$key          = substr($name, $posOfBracket + 1, -1);

			if (isset($_POST[$mainName][$key]))
				$this->value = $_POST[$mainName][$key];
		}
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
	 * Set layout if no layout is set
	 * @param string $layout 
	 */
	public function setLayoutIfEmpty($layout) {
		if (empty($this->layout))
			$this->setLayout($layout);
	}

	/**
	 * Add layout, used as additional css-class for a surrounding div
	 * @param string $layout 
	 */
	public function addLayout($layout) {
		$this->layout .= ' '.$layout;
	}

	/**
	 * Add CSS class for layout
	 * @param string $layout 
	 */
	public function addLayoutClass($layout) {
		$this->layoutClasses[] = $layout;
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
	 * Parse value for display
	 */
	final protected function parseForDisplay() {
		FormularValueParser::parse($this->value, $this->parser, $this->parserOptions);
	}

	/**
	 * Validate value
	 */
	final protected function validate() {
		// TODO
	}

	/**
	 * Display this field 
	 */
	public function display() {
		echo $this->getCode();
	}

	/**
	 * Get code for displaying this field with layout
	 * @return string 
	 */
	final public function getCode() {
		$this->prepare();

		if (!empty($this->layout))
			return '<div class="'.$this->layout.' '.implode($this->layoutClasses, ' ').'">'.$this->getFieldCode().'</div>';

		return $this->getFieldCode();
	}

	/**
	 * Prepare field for being displayed 
	 */
	private function prepare() {
		if (!$this->prepared) {
			$this->parseForDisplay();
			$this->prepareForDisplay();

			$this->prepared = true;
		}
	}

	/**
	 * Get code for displaying the field
	 * @return string
	 */
	abstract protected function getFieldCode();
}
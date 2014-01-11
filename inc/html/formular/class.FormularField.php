<?php
/**
 * This file contains class::FormularField
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for a field as part of a formular
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
abstract class FormularField extends HtmlTag {
	/**
	 * CSS-class if validation failed
	 * @var string 
	 */
	static public $CSS_VALIDATION_FAILED = 'validation-failed';

	/**
	 * Array with all failed keys
	 * @var array
	 */
	static private $FAILED_KEYS = array();

	/**
	 * Array with all validation failures
	 * @var array
	 */
	static private $VALIDATION_FAILURES = array();

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
	 * Set key as failed
	 * @param string $key 
	 */
	static public function setKeyAsFailed($key) {
		self::$FAILED_KEYS[] = $key;
	}

	/**
	 * Set key as failed
	 * @param string $key 
	 */
	static public function hasKeyFailed($key) {
		return in_array($key, self::$FAILED_KEYS);
	}

	/**
	 * Add validation failure
	 * @param string $failure
	 */
	static public function addValidationFailure($failure) {
		self::$VALIDATION_FAILURES[] = $failure;
	}

	/**
	 * Get all validation failures
	 * @return array
	 */
	static public function getValidationFailures() {
		return self::$VALIDATION_FAILURES;
	}

	/**
	 * Construct a new field
	 * @param string $name
	 * @param string $label
	 * @param string $value optional, default: loading from $_POST
	 */
	public function __construct($name, $label, $value = '') {
		$this->name = $name;
		$this->label = $label;

		if (strlen($value))
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
		if (empty($this->layout)) {
			$this->setLayout($layout);
		}
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
	public function setParser($parser, $options = array()) {
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

		if (self::hasKeyFailed($this->name))
			$this->addCSSclass(self::$CSS_VALIDATION_FAILED);
	}

	/**
	 * Validate value
	 */
	public function validate() {
		$validation = FormularValueParser::validatePost($this->name, $this->parser, $this->parserOptions);

		if ($validation !== true) {
			self::setKeyAsFailed($this->name);
			self::addValidationFailure(is_string($validation) ? $validation : 'Die Eingabe wird nicht akzeptiert. ('.$this->name.')');
		}
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

		return $this->getSurroundedByLayoutDiv($this->getFieldCode());
	}

	/**
	 * Surround given code by div container for layout
	 * @param string $Content
	 * @return string
	 */
	final protected function getSurroundedByLayoutDiv($Content) {
		if (!empty($this->layout))
			return '<div class="'.$this->layout.' '.implode($this->layoutClasses, ' ').'">'.$Content.'</div>';

		return $Content;
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
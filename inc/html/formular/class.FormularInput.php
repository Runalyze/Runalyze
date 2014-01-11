<?php
/**
 * This file contains class::FormularInput
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for a standard input field
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularInput extends FormularField {
	/**
	 * Size: full inline
	 * @var string 
	 */
	static public $SIZE_FULL_INLINE = 'fullSize';

	/**
	 * Size: full
	 * @var string 
	 */
	static public $SIZE_FULL = 'fullwidth';

	/**
	 * Size: middle
	 * @var string 
	 */
	static public $SIZE_MIDDLE = 'middleSize';

	/**
	 * Size: small
	 * @var string 
	 */
	static public $SIZE_SMALL = 'smallSize';

	/**
	 * Standard size for fields
	 * @var string 
	 */
	static private $standardSize = 'smallSize';

	/**
	 * Size
	 * @var string 
	 */
	protected $size = '';

	/**
	 * Unit
	 * @var string 
	 */
	protected $unit = '';

	/**
	 * Boolean flag: label is on the right side
	 * @var boolean
	 */
	private $labelOnRight = false;

	/**
	 * Boolean flag: hide label
	 * @var boolean
	 */
	private $hideLabel = false;

	/**
	 * Set standard size, used when no specific size is set (may be overwritten by css)
	 * @param string $size 
	 */
	static public function setStandardSize($size) {
		self::$standardSize = $size;
	}

	/**
	 * Set label to the right side 
	 */
	public function setLabelToRight() {
		$this->labelOnRight = true;
	}

	/**
	 * Hide label
	 */
	public function hideLabel() {
		$this->hideLabel = true;
	}

	/**
	 * Size size for this input field
	 * @param string $size 
	 */
	public function setSize($size) {
		$this->size = $size;
	}

	/**
	 * Set specific unit for this input field
	 * @param string $unit 
	 */
	public function setUnit($unit) {
		$this->unit = $unit;
	}

	/**
	 * Set input field disabled 
	 */
	public function setDisabled() {
		$this->addAttribute('disabled', 'disabled');
	}

	/**
	 * Prepare for beeing displayed 
	 */
	protected function prepareForDisplay() {
		$this->addAttribute('type', 'text');
		$this->addAttribute('name', $this->name);
		$this->addAttribute('value', $this->value);
		$this->setId($this->name);

		$this->addUnitAndSize();
	}

	/**
	 * Add unit and size as css-classes 
	 */
	protected function addUnitAndSize() {
		if (!empty($this->unit))
			$this->addCSSclass('withUnit '.$this->unit);

		if (!empty($this->size))
			$this->addCSSclass($this->size);
		elseif (!empty(self::$standardSize))
			$this->addCSSclass(self::$standardSize);
	}

	/**
	 * Set placeholder
	 * @param string $text
	 */
	public function setPlaceholder($text) {
		$this->addAttribute('placeholder', $text);
	}

	/**
	 * Display this field
	 * @return string
	 */
	protected function getFieldCode() {
		$label = '<label for="'.$this->name.'">'.$this->label.'</label>';
		$input = '<input '.$this->attributes().' />';

		if ($this->hideLabel)
			$label = '';

		if ($this->labelOnRight)
			return $input.' '.$label;

		return $label.' '.$input;
	}
}
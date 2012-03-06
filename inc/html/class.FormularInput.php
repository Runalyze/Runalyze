<?php
/**
 * Class for a standard input field 
 */
class FormularInput extends FormularField {
	/**
	 * Unit: percent
	 * @var string
	 */
	static public $UNIT_PERCENT = 'unitPercent';

	/**
	 * Unit: bpm
	 * @var string
	 */
	static public $UNIT_BPM = 'unitBpm';

	/**
	 * Unit: kg
	 * @var string
	 */
	static public $UNIT_KG = 'unitKg';

	/**
	 * Size: full
	 * @var string 
	 */
	static public $SIZE_FULL = 'fullWidth';

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
	 * Set standard size, used when no specific size is set (may be overwritten by css)
	 * @param string $size 
	 */
	static public function setStandardSize($size) {
		self::$standardSize = $size;
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
	 * Prepare for beeing displayed 
	 */
	protected function prepareForDisplay() {
		$this->addAttribute('type', 'text');
		$this->addAttribute('name', $this->name);
		$this->addAttribute('value', $this->value);
		$this->setId($this->name);

		if (!empty($this->unit))
			$this->addCSSclass('withUnit '.$this->unit);

		if (!empty($this->size))
			$this->addCSSclass($this->size);
		elseif (!empty(self::$standardSize))
			$this->addCSSclass(self::$standardSize);
	}

	/**
	 * Display this field 
	 */
	public function displayField() {
		echo '<label for="'.$this->name.'">'.$this->label.'</label>';
		echo '<input '.$this->attributes().' />';
	}
}
?>

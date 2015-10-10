<?php
/**
 * This file contains class::BoxedValue
 * @package Runalyze\HTML
 */
/**
 * Boxed value
 * 
 * @author Hannes Christiansen
 * @package Runalyze\HTML
 */
class BoxedValue {
	/**
	 * HTML class: surrounding div
	 * @var string
	 */
	public static $SURROUNDING_DIV = 'boxed-values';

	/**
	 * HTML class: container floating outer div
	 * @var string
	 */
	public static $CONTAINER_FLOATING_OUTER_DIV = 'boxed-value-outer';

	/**
	 * HTML class: container div
	 * @var string
	 */
	public static $CONTAINER_DIV = 'boxed-value-container';

	/**
	 * HTML class: container div with icon
	 * @var string
	 */
	public static $CONTAINER_DIV_WITH_ICON = 'boxed-value-container with-icon';

	/**
	 * HTML class: value div
	 * @var string
	 */
	public static $VALUE_DIV = 'boxed-value';

	/**
	 * HTML class: unit div
	 * @var string
	 */
	public static $VALUE_UNIT_DIV = 'boxed-value-unit';

	/**
	 * HTML class: info div
	 * @var string
	 */
	public static $VALUE_INFO_DIV = 'boxed-value-info';

	/**
	 * Value
	 * @var string
	 */
	protected $Value = '';

	/**
	 * Icon
	 * @var string
	 */
	protected $Icon = '';

	/**
	 * Unit
	 * @var string
	 */
	protected $Unit = '';

	/**
	 * Info
	 * @var string
	 */
	protected $Info = '';

	/**
	 * Additional classes 
	 * @var string
	 */
	protected $AdditionalClasses = '';

	/**
	 * Flag: as floating block
	 * @var boolean
	 */
	protected $AsFloatingBlock = false;

	/**
	 * Class: as floating block
	 * e.g. "w10", "w25", "w33", "w50"
	 * @var string
	 */
	protected $AsFloatingBlockClass = '';

	/**
	 * Fixed width
	 * @var string
	 */
	protected $AsFloatingBlockWidth = '';

	/**
	 * Constructor
	 * @param string $Value [optional]
	 * @param string $Unit [optional]
	 * @param string $Info [optional]
	 * @param string $Icon [optional]
	 */
	public function __construct($Value = '', $Unit = '', $Info = '', $Icon = '') {
		$this->setValue($Value);
		$this->setUnit($Unit);
		$this->setInfo($Info);
		$this->setIcon($Icon);
	}

	/**
	 * Set value
	 * @param string $Value
	 */
	public function setValue($Value) {
		$this->Value = $Value;
	}

	/**
	 * Set unit
	 * @param string $Unit
	 */
	public function setUnit($Unit) {
		$this->Unit = $Unit;
	}

	/**
	 * Set icon
	 * @param string $Icon
	 */
	public function setIcon($Icon) {
		$this->Icon = $Icon;
	}

	/**
	 * Set info
	 * @param string $Info
	 */
	public function setInfo($Info) {
		$this->Info = $Info;
	}

	/**
	 * Add class
	 * @param string $Class
	 */
	public function addClass($Class) {
		if (empty($this->AdditionalClasses))
			$this->AdditionalClasses .= ' ';

		$this->AdditionalClasses .= $Class;
	}

	/**
	 * Define as floating block
	 * 
	 * Multiple classes can be set, e.g. "w100 flexible-height".
	 * 
	 * @param string $widthClass e.g. "w25", "w33", "w50"
	 */
	public function defineAsFloatingBlock($widthClass) {
		$this->AsFloatingBlock = true;
		$this->AsFloatingBlockClass = $widthClass;
	}

	/**
	 * Define as floating block
	 * @param int $numberOfBlocks
	 */
	public function defineAsFloatingBlockWithFixedWidth($numberOfBlocks) {
		$this->AsFloatingBlock = true;
		$this->AsFloatingBlockWidth = round(100/$numberOfBlocks, 2).'%';
	}

	/**
	 * Display
	 */
	public function display() {
		echo $this->getCode();
	}

	/**
	 * Get code
	 * @return string
	 */
	public function getCode() {
		$Code = '';

		if ($this->AsFloatingBlock) {
			if ($this->AsFloatingBlockWidth)
				$Code .= '<div class="'.self::$CONTAINER_FLOATING_OUTER_DIV.'" style="width:'.$this->AsFloatingBlockWidth.';">';
			else
				$Code .= '<div class="'.self::$CONTAINER_FLOATING_OUTER_DIV.' '.$this->AsFloatingBlockClass.'">';
		}

		$Code .= '<div class="'.$this->getDivClass().'">';
		$Code .= $this->getDivForIcon();
		$Code .= $this->getDivForValue();
		$Code .= $this->getDivForInfo();
		$Code .= '</div>';

		if ($this->AsFloatingBlock)
			$Code .= '</div>';

		return $Code;
	}

	/**
	 * Get class
	 * @return string
	 */
	protected function getDivClass() {
		$Class = empty($this->Icon) ? self::$CONTAINER_DIV : self::$CONTAINER_DIV_WITH_ICON;
		$Class .= $this->AdditionalClasses;

		return $Class;
	}

	/**
	 * Get div for icon
	 * @return string
	 */
	protected function getDivForIcon() {
		return $this->Icon;
	}

	/**
	 * Get div for value
	 * @return string
	 */
	protected function getDivForValue() {
		$Code = '<div class="'.self::$VALUE_DIV.'">';
		$Code .= $this->Value;
		$Code .= $this->getDivForUnit();
		$Code .= '</div>';

		return $Code;
	}

	/**
	 * Get div for unit
	 * @return string
	 */
	protected function getDivForUnit() {
		if (empty($this->Unit))
			return '';

		$Code = ' <div class="'.self::$VALUE_UNIT_DIV.'">';
		$Code .= str_replace(' ', '&nbsp;', $this->Unit);
		$Code .= '</div>';

		return $Code;
	}

	/**
	 * Get div for info
	 * @return string
	 */
	protected function getDivForInfo() {
		if (empty($this->Info))
			return '';

		$Code = '<div class="'.self::$VALUE_INFO_DIV.'">';
		$Code .= str_replace(' ', '&nbsp;', $this->Info);
		$Code .= '</div>';

		return $Code;
	}

	/**
	 * Wrap values
	 * @param string $ValuesString
	 */
	public static function wrapValues($ValuesString) {
		echo self::getWrappedValues($ValuesString);
	}

	/**
	 * Get wrapped values
	 * @param string $ValuesString
	 * @return string
	 */
	public static function getWrappedValues($ValuesString) {
		return '<div class="'.self::$SURROUNDING_DIV.'">'.$ValuesString.'</div>';
	}
}
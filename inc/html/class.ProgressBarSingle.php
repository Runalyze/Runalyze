<?php
/**
 * This file contains class::ProgressBarSingle
 * @package Runalyze\HTML
 */
/**
 * Single progressbar (with HTML/CSS)
 * 
 * @author Hannes Christiansen
 * @package Runalyze\HTML
 */
class ProgressBarSingle {
	/**
	 * HTML class: balance positive
	 * @var string
	 */
	static private $CLASS_BALANCE_POSITIVE = 'balance-positive';

	/**
	 * HTML class: balance negative
	 * @var string
	 */
	static private $CLASS_BALANCE_NEGATIVE = 'balance-negative';

	/**
	 * HTML class: color blue
	 * @var string
	 */
	public static $COLOR_BLUE = 'colored-blue';

	/**
	 * HTML class: color red
	 * @var string
	 */
	public static $COLOR_RED = 'colored-red';

	/**
	 * HTML class: color green
	 * @var string
	 */
	public static $COLOR_GREEN = 'colored-green';

	/**
	 * HTML class: color yellow
	 * @var string
	 */
	public static $COLOR_YELLOW = 'colored-yellow';

	/**
	 * HTML class: color orange
	 * @var string
	 */
	public static $COLOR_ORANGE = 'colored-orange';

	/**
	 * HTML class: color grey
	 * @var string
	 */
	public static $COLOR_GREY = 'colored-grey';

	/**
	 * HTML class: color light (grey)
	 * @var string
	 */
	public static $COLOR_LIGHT = 'colored-light';

	/**
	 * Width
	 * @var int
	 */
	protected $Width = 0;

	/**
	 * Color
	 * @var string
	 */
	protected $Color = '';

	/**
	 * Boolean flag: left balanced?
	 * @var boolean
	 */
	protected $LeftBalanced = false;

	/**
	 * Boolean flag: right balanced?
	 * @var boolean
	 */
	protected $RightBalanced = false;

	/**
	 * Constructor
	 * @param int $Width
	 * @param string $Color
	 * @param mixed $LeftOrRight [optional] Can be 'left' or 'right' to set balance
	 */
	public function __construct($Width, $Color, $LeftOrRight = false) {
		$this->Width = max(0, min(100, (int)$Width));
		$this->Color = $Color;

		if ($LeftOrRight == 'left')
			$this->setLeftBalanced();

		if ($LeftOrRight == 'right')
			$this->setRightBalanced();
	}

	/**
	 * Set left balanced
	 */
	public function setLeftBalanced() {
		$this->LeftBalanced = true;
	}

	/**
	 * Set right balanced
	 */
	public function setRightBalanced() {
		$this->RightBalanced = true;
	}

	/**
	 * Get width
	 * @return int
	 */
	public function getWidth() {
		return $this->Width;
	}

	/**
	 * Get classes
	 * @return string
	 */
	public function getClasses() {
		$Class = $this->Color;

		if ($this->LeftBalanced)
			$Class .= ' '.self::$CLASS_BALANCE_NEGATIVE;
		if ($this->RightBalanced)
			$Class .= ' '.self::$CLASS_BALANCE_POSITIVE;

		return $Class;
	}
}
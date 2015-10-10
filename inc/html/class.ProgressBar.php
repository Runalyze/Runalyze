<?php
/**
 * This file contains class::ProgressBar
 * @package Runalyze\HTML
 */
/**
 * Progressbar (with HTML/CSS)
 * 
 * @author Hannes Christiansen
 * @package Runalyze\HTML
 */
class ProgressBar {
	/**
	 * HTML class: surrounding div
	 * @var string
	 */
	private static $DIV_CONTAINER = 'progress-bar-container';

	/**
	 * HTML class: container floating outer div
	 * @var string
	 */
	private static $CLASS_INLINE = 'progress-bar-container-inline';

	/**
	 * HTML class: animated
	 * @var string
	 */
	private static $CLASS_ANIMATED = 'animated';

	/**
	 * HTML class: container div
	 * @var string
	 */
	private static $DIV_INNER = 'progress-bar-inner';

	/**
	 * HTML class: container div with icon
	 * @var string
	 */
	private static $DIV_BAR = 'progress-bar';

	/**
	 * HTML class: div for goal line
	 * @var string
	 */
	private static $DIV_GOAL = 'progress-bar-goal';

	/**
	 * Single progress bars
	 * @var array
	 */
	protected $Bars = array();

	/**
	 * Tooltip
	 * @var string
	 */
	protected $Tooltip = '';

	/**
	 * Additional classes 
	 * @var string
	 */
	protected $AdditionalClasses = '';

	/**
	 * Goal line
	 * @var int 
	 */
	protected $Goal = 0;

	/**
	 * Constructor
	 * 
	 * Arguments can be passed to construct a single progress bar.
	 * Otherwise, addBar($Width, $Color) has to be used.
	 * 
	 * @param int $Width [optional] in percent between 0 and 100
	 * @param string $Color [optional]
	 */
	public function __construct($Width = 0, $Color = '') {
		if ($Width > 0)
			$this->addNewBar($Width, $Color);
	}

	/**
	 * Add existing bar
	 * @param ProgressBarSingle $SingleBar
	 */
	public function addBar(ProgressBarSingle &$SingleBar) {
		$this->Bars[] = $SingleBar;
	}

	/**
	 * Add new bar
	 * @param int $Width in percent between 0 and 100
	 * @param string $Color
	 */
	public function addNewBar($Width, $Color = '') {
		$this->Bars[] = new ProgressBarSingle($Width, $Color);
	}

	/**
	 * Set tooltip
	 * @param string $Tooltip
	 */
	public function setTooltip($Tooltip) {
		$this->Tooltip = $Tooltip;
	}

	/**
	 * Set as inline progress bar
	 */
	public function setInline() {
		$this->addClass( self::$CLASS_INLINE );
	}

	/**
	 * Set as animated
	 */
	public function setAnimated() {
		$this->addClass( self::$CLASS_ANIMATED );
	}

	/**
	 * Add class
	 * @param string $Class
	 */
	public function addClass($Class) {
		$this->AdditionalClasses .= ' '.$Class;
	}

	/**
	 * Set goal line
	 * @param int $Goal
	 */
	public function setGoalLine($Goal) {
		$this->Goal = max(0, min(100, (int)$Goal));
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
		$Code  = '<div class="'.$this->getDivClass().'"'.$this->getTooltip().'>';
		$Code .= '<div class="'.self::$DIV_INNER.'">';
		$Code .= $this->getDivForSingleBars();
		$Code .= $this->getCodeForGoalLine();
		$Code .= '</div>';
		$Code .= '</div>';

		return $Code;
	}

	/**
	 * Get class
	 * @return string
	 */
	protected function getDivClass() {
		return self::$DIV_CONTAINER.$this->AdditionalClasses;
	}

	/**
	 * Get additional stuff for tooltip
	 * @return string
	 */
	protected function getTooltip() {
		if (!empty($this->Tooltip))
			return ' '.Ajax::tooltip('', $this->Tooltip, false, true);

		return '';
	}

	/**
	 * Get div for icon
	 * @return string
	 */
	protected function getDivForSingleBars() {
		$Code = '';

		foreach ($this->Bars as &$Bar)
			$Code .= $this->getDivForSingleBar($Bar);

		return $Code;
	}

	/**
	 * Get div for single bar
	 * @param ProgressBarSingle $SingleBar
	 * @return string
	 */
	protected function getDivForSingleBar(ProgressBarSingle &$SingleBar) {
		$Code  = '<div class="'.self::$DIV_BAR.' '.$SingleBar->getClasses().'" style="width:'.$SingleBar->getWidth().'%;">';
		$Code .= '</div>';

		return $Code;
	}

	/**
	 * Get code for goal line
	 */
	protected function getCodeForGoalLine() {
		if ($this->Goal > 0)
			return '<div class="'.self::$DIV_GOAL.'" style="left:'.$this->Goal.'%;"></div>';

		return '';
	}
}
<?php
/**
 * This file contains class::TrainingViewSectionRowOnlyText
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Row of the training view: only text
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
abstract class TrainingViewSectionRowOnlyText extends TrainingViewSectionRowAbstract {
	/**
	 * Boxed values
	 * @var BoxedValue[]
	 */
	protected $BoxedValues = array();

	/**
	 * Content: right
	 * @var string
	 */
	protected $ContentRight = '';

	/**
	 * Content: left
	 * @var string
	 */
	protected $ContentLeft = '';

	/**
	 * Constructor
	 */
	public function __construct(TrainingObject &$Training) {
		$this->Training = $Training;

		$this->setContent();
	}

	/**
	 * Display
	 */
	final public function display() {
		echo '<div class="training-row">';
		echo '<div class="training-row-info">'.$this->ContentLeft.'</div>';
		echo '<div class="training-row-plot">'.$this->ContentRight.'</div>';
		echo '</div>';
	}
}
<?php
/**
 * This file contains class::TrainingViewSectionRowFullwidth
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Row of the training view (fullwidth)
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
abstract class TrainingViewSectionRowFullwidth extends TrainingViewSectionRowAbstract {
	/**
	 * Content
	 * @var string
	 */
	protected $Content = '';

	/**
	 * CSS-id
	 * @var string
	 */
	protected $id = '';

	/**
	 * Display
	 */
	final public function display() {
		echo '<div class="training-row fullwidth" id="'.$this->id.'">';
		echo $this->Content;
		echo '</div>';
	}
}
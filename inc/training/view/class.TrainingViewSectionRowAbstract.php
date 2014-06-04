<?php
/**
 * This file contains class::TrainingViewSectionRowAbstract
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Row of the training view
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
abstract class TrainingViewSectionRowAbstract {
	/**
	 * Training
	 * @var TrainingObject
	 */
	protected $Training = null;

	/**
	 * Constructor
	 */
	public function __construct(TrainingObject &$Training) {
		$this->Training = $Training;

		$this->setContent();
	}

	/**
	 * Set content
	 */
	abstract protected function setContent();

	/**
	 * Display
	 */
	abstract public function display();
}
<?php
/**
 * This file contains class::TrainingViewSectionRowAbstract
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\View\Activity\Context;

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
	 * @var \Runalyze\View\Activity\Context
	 */
	protected $Context;

	/**
	 * Constructor
	 */
	public function __construct(TrainingObject &$Training, Context &$Context = null) {
		$this->Training = $Training;
		$this->Context = $Context;

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
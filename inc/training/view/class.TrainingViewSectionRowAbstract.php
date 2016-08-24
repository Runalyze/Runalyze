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
	 * @var \Runalyze\View\Activity\Context
	 */
	protected $Context;

	/**
	 * Constructor
	 */
	public function __construct(Context $Context = null) {
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

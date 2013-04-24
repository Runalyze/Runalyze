<?php
/**
 * This file contains class::RoundsAbstract
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display rounds
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
abstract class RoundsAbstract {
	/**
	 * Training object
	 * @var TrainingObject
	 */
	protected $Training = null;

	/**
	 * Constructor
	 * @param TrainingObject $Training
	 */
	public function __construct(TrainingObject &$Training) {
		$this->Training = $Training;
	}

	/**
	 * Get key
	 * @return string
	 */
	abstract public function key();

	/**
	 * Get title
	 * @return string
	 */
	abstract public function title();

	/**
	 * Display
	 */
	abstract public function display();
}
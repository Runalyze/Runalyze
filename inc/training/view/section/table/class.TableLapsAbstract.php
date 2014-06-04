<?php
/**
 * This file contains class::TableLapsAbstract
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Table for laps
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
abstract class TableLapsAbstract {
	/**
	 * Training object
	 * @var TrainingObject
	 */
	protected $Training = null;

	/**
	 * Code
	 * @var string
	 */
	protected $Code = '';

	/**
	 * Constructor
	 * @param TrainingObject $Training
	 */
	public function __construct(TrainingObject &$Training) {
		$this->Training = $Training;

		$this->setCode();
	}

	/**
	 * Set code
	 */
	abstract protected function setCode();

	/**
	 * Get code
	 * @return string
	 */
	final public function getCode() {
		return $this->Code;
	}
}
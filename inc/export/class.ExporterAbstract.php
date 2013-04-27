<?php
/**
 * This file contains class::ExporterAbstract
 * @package Runalyze\Export\Types
 */
/**
 * Create exporter for given type
 *
 * @author Hannes Christiansen
 * @package Runalyze\Export\Types
 */
abstract class ExporterAbstract {
	/**
	 * Training
	 * @var TrainingObject
	 */
	protected $Training = null;

	/**
	 * Internal array with errors to display
	 * @var array
	 */
	private $Errors = array();

	/**
	 * Constructor
	 * @param TrainingObject $Training
	 */
	public function __construct(TrainingObject $Training) {
		$this->Training = $Training;
	}

	/**
	 * Display
	 */
	abstract public function display();

	/**
	 * Add error message to display to user
	 * @param string $message 
	 */
	final protected function addError($message) {
		$this->Errors[] = $message;
	}

	/**
	 * Get all errors
	 * @return array
	 */
	final public function getAllErrors() {
		return $this->Errors;
	}
}
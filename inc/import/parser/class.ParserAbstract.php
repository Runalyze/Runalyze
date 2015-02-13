<?php
/**
 * This file contains class::ParserAbstract
 * @package Runalyze\Import\Parser
 */
/**
 * Abstract parser class
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
abstract class ParserAbstract {
	/**
	 * Number of decimals to be parsed/saved
	 * @int decimals for [km]
	 */
	const DISTANCE_PRECISION = 5;

	/**
	 * File content
	 * @var string
	 */
	protected $FileContent = '';

	/**
	 * Internal array with errors
	 * @var array
	 */
	protected $Errors = array();

	/**
	 * Constructor
	 * @param string $FileContent file content
	 */
	public function __construct($FileContent) {
		$this->FileContent = $FileContent;
	}

	/**
	 * Get training objects
	 * @return array array of TrainingObjects
	 */
	abstract public function objects();

	/**
	 * Get training object
	 * @param int $index optional index
	 * @return TrainingObject
	 */
	abstract public function object($index = 0);

	/**
	 * Parse
	 */
	abstract public function parse();

	/**
	 * Parser failed?
	 * @return boolean
	 */
	final public function failed() {
		return !empty($this->Errors);
	}

	/**
	 * Get errors
	 * @return array
	 */
	final public function getErrors() {
		return $this->Errors;
	}

	/**
	 * Add an error to internal array
	 * @param string $message 
	 */
	final protected function addError($message) {
		$this->Errors[] = $message;
	}

	/**
	 * Add errors
	 * @param array $errors errors to add
	 */
	final protected function addErrors($errors) {
		$this->Errors = array_merge($this->Errors, $errors);
	}
}
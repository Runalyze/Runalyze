<?php
/**
 * This file contains class::ExporterAbstract
 * @package Runalyze\Export\Types
 */

use Runalyze\View\Activity\Context;

/**
 * Create exporter for given type
 *
 * @author Hannes Christiansen
 * @package Runalyze\Export\Types
 */
abstract class ExporterAbstract {
	/**
	 * Type
	 * @return enum
	 */
	static public function Type() {
		return ExporterType::Code;
	}

	/**
	 * Icon class
	 * @return string
	 */
	static public function IconClass() {
		return 'fa-file-code-o';
	}

	/**
	 * Activity context
	 * @var \Runalyze\View\Activity\Context
	 */
	protected $Context = null;

	/**
	 * Internal array with errors to display
	 * @var array
	 */
	private $Errors = array();

	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Context $context) {
		$this->Context = $context;
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
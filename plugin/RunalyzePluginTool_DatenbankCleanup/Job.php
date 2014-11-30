<?php
/**
 * This file contains class::Job
 * @package Runalyze\Plugin\Tool\DatabaseCleanup
 */

namespace Runalyze\Plugin\Tool\DatabaseCleanup;

/**
 * Job
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugin\Tool\DatabaseCleanup
 */
abstract class Job {
	/**
	 * Result messages
	 * @var array
	 */
	private $Messages = array();

	/**
	 * Is task requested?
	 * @param string $enum
	 * @return bool
	 */
	protected function isRequested($enum) {
		return isset($_POST[$enum]);
	}

	/**
	 * Run job
	 */
	abstract public function run();

	/**
	 * Add message
	 * @param string $string
	 */
	final protected function addMessage($string) {
		$this->Messages[] = $string;
	}

	/**
	 * Add message
	 * @param string $string
	 */
	final protected function addSuccessMessage($what, $oldValue, $newValue) {
		$this->Messages[] = sprintf( __('%s has been recalculated. New value: <strong>%s</strong> (old value: %s)'), $what, $newValue, $oldValue);
	}

	/**
	 * Get messages
	 * @return array
	 */
	final public function messages() {
		return $this->Messages;
	}
}
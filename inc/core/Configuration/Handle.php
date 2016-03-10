<?php
/**
 * This file contains class::Handle
 * @package Runalyze\Configuration
 */

namespace Runalyze\Configuration;

use Runalyze\Parameter;
use Runalyze\Parameter\SelectRow;
use Ajax;

/**
 * Configuration handle
 * @author Hannes Christiansen
 * @package Runalyze\Configuration
 */
class Handle {
	/**
	 * Key
	 * @var string
	 */
	protected $Key;

	/**
	 * Parameter
	 * @var Parameter
	 */
	protected $Parameter;

	/**
	 * Onchange callback
	 * @var callback
	 */
	protected $OnchangeCallback = '';

	/**
	 * Onchange reload flag
	 * @var mixed enum
	 */
	protected $OnchangeReloadFlag = '';

	/**
	 * Array with all values
	 * @var array
	 */
	private static $TableHandles = array();

	/**
	 * All table handles
	 * @return array array('key' => 'table')
	 */
	public static function tableHandles() {
		return self::$TableHandles;
	}

	/**
	 * Construct
	 * @param string $Key
	 * @param \Runalyze\Parameter $Parameter
	 */
	public function __construct($Key, Parameter $Parameter) {
		$this->Key = $Key;
		$this->Parameter = $Parameter;

		if ($Parameter instanceof SelectRow) {
			self::$TableHandles[$Key] = $Parameter->table();
		}
	}

	/**
	 * Key
	 * @return string
	 */
	final public function key() {
		return $this->Key;
	}

	/**
	 * Parameter object
	 * @return \Runalyze\Parameter
	 */
	final public function object() {
		return $this->Parameter;
	}

	/**
	 * Value
	 * @return mixed
	 */
	final public function value() {
		return $this->Parameter->value();
	}

	/**
	 * Register onchange event
	 * @param callback $callback
	 */
	final public function registerOnchangeEvent($callback) {
		$this->OnchangeCallback = $callback;
	}

	/**
	 * Register onchange flag
	 * @param string $flag
	 */
	final public function registerOnchangeFlag($flag) {
		$this->OnchangeReloadFlag = $flag;
	}

	/**
	 * Process onchange events
	 */
	final public function processOnchangeEvents() {
		if (!empty($this->OnchangeCallback)) {
			call_user_func($this->OnchangeCallback);
		}

		if (!empty($this->OnchangeReloadFlag)) {
			Ajax::setReloadFlag($this->OnchangeReloadFlag);
		}
	}
}
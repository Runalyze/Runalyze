<?php
/**
 * This file contains class::ConfigurationHandle
 * @package Runalyze\Parameter
 */
/**
 * Configuration handle
 * @author Hannes Christiansen
 * @package Runalyze\Configuration
 */
class ConfigurationHandle {
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
	 * Array with all values
	 * @var array
	 */
	static private $TableHandles = array();

	/**
	 * All table handles
	 * @return array array('key' => 'table')
	 */
	static public function tableHandles() {
		return self::$TableHandles;
	}

	/**
	 * Construct
	 * @param string $Key
	 * @param Parameter $Parameter
	 */
	public function __construct($Key, Parameter $Parameter) {
		$this->Key = $Key;
		$this->Parameter = $Parameter;

		if ($Parameter instanceof ParameterSelectRow) {
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
	 * @return Parameter
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
}
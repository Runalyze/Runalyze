<?php
/**
 * This file contains class::Parameter
 * @package Runalyze
 */

namespace Runalyze;

/**
 * Parameter
 * @author Hannes Christiansen
 * @package Runalyze
 */
abstract class Parameter {
	/**
	 * Max length
	 * @var int
	 */
	const MAX_LENGTH = 255;

	/**
	 * Value
	 * @var mixed
	 */
	private $Value = null;

	/**
	 * Options
	 * @var array
	 */
	protected $Options = array();

	/**
	 * Construct
	 * @param mixed $default
	 * @param array $options [optional]
	 */
	public function __construct($default, $options = array()) {
		$this->Value = $default;
		$this->Options = array_merge($this->Options, $options);
	}

	/**
	 * Set value
	 * @param mixed $value new value
	 */
	public function set($value) {
		$this->Value = $value;
	}

	/**
	 * Set from string
	 * @param string $valueAsString new value
	 */
	public function setFromString($valueAsString) {
		$this->set($valueAsString);
	}

	/**
	 * Value
	 * @return mixed
	 */
	final public function value() {
		return $this->Value;
	}

	/**
	 * Value as string
	 * @return string
	 */
	public function valueAsString() {
		return (string)$this->Value;
	}
}

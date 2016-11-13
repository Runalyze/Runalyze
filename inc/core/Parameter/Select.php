<?php
/**
 * This file contains class::Select
 * @package Runalyze\Parameter
 */

namespace Runalyze\Parameter;

/**
 * Select
 * @author Hannes Christiansen
 * @package Runalyze\Parameter
 */
class Select extends \Runalyze\Parameter {
	/** @var bool */
	protected $UseFallback = true;

	/** @var mixed */
	protected $Default;

	/**
	 * Construct
	 * @param string $default
	 * @param array $options [optional]
	 */
	public function __construct($default, $options = array()) {
		$this->Default = $default;
		$options = array_merge(array('options' => array()), $options);

		parent::__construct($default, $options);
	}

	/**
	 * Set value
	 * @param mixed $value new value
	 * @throws \InvalidArgumentException
	 */
	public function set($value) {
		if ($this->valueIsAllowed($value)) {
			parent::set($value);
		} elseif ($this->UseFallback) {
			parent::set($this->Default);
		} else {
			throw new \InvalidArgumentException('Invalid option ("'.$value.'") for select value.');
		}
	}

	/**
	 * Value allowed?
	 * @param string $value
	 * @return bool
	 */
	protected function valueIsAllowed($value) {
		return in_array($value, array_keys($this->Options['options']));
	}

	/**
	 * Long string
	 * @return string
	 */
	public function valueAsLongString() {
		return $this->Options['options'][$this->value()];
	}

	/**
	 * Options
	 * @return array
	 */
	public function options() {
		return $this->Options['options'];
	}
}

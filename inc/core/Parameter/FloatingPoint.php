<?php
/**
 * This file contains class::FloatingPoint
 * @package Runalyze\Parameter
 */

namespace Runalyze\Parameter;

use Helper;

/**
 * FloatingPoint (prev. Float)
 * @author Hannes Christiansen
 * @package Runalyze\Parameter
 */
class FloatingPoint extends \Runalyze\Parameter {
	/**
	 * Options
	 * @var array
	 */
	protected $Options = array(
		'min' => false,
		'max' => false,
		'null' => false
	);

	/**
	 * Set from string
	 * @param string $valueAsString new value
	 */
	public function setFromString($valueAsString) {
		if ($valueAsString == '' && $this->Options['null']) {
			$this->set(null);
		} else {
			$this->set((float)Helper::CommaToPoint($valueAsString));
		}
	}

	/**
	 * Set value
	 * @param mixed $value new value
	 */
	public function set($value) {
		if (null === $value && $this->Options['null']) {
			$value = null;
		} elseif (false !== $this->Options['min'] && $value < $this->Options['min']) {
			$value = $this->Options['min'];
		} elseif (false !== $this->Options['max'] && $value > $this->Options['max']) {
			$value = $this->Options['max'];
		}

		parent::set($value);
	}

	/**
	 * Value as string
	 * @return string
	 */
	public function valueAsString() {
		$value = $this->value();

		if (null === $value && $this->Options['null']) {
			return '';
		}

		return (string)$value;
	}

	/**
	 * @return bool
	 */
	public function isEmpty() {
		return (null === $this->value());
	}
}
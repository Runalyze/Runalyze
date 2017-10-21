<?php
/**
 * This file contains class::Rpecolor
 * @package Runalyze\View
 */

namespace Runalyze\View;

/**
 * Rpecolor
 *
 * @author Hannes Christiansen
 * @package Runalyze\View
 */
class Rpecolor {
	/**
	 * Value
	 * @var int
	 */
	protected $Value;

    /**
	 * @param int $value
	 */
	public function __construct($value = 0)
    {
		$this->setValue($value);
	}

	/**
	 * @param int $value
	 * @return \Runalyze\View\Rpecolor
	 */
	public function setValue($value)
    {
		$this->Value = $value;

		return $this;
	}

    /**
     * @return int
     */
    public function value() {
        if ($this->Value < 6 || $this->Value > 20) {
            return null;
        }
        return (int)$this->Value;
    }

	/**
	 * @return string
	 */
	public function backgroundColor()
    {
	    if ($this->value() < 6 || $this->value() > 20) {
            return null;
        } elseif ($this->value() == 6) {
            return "225ea8";
        } elseif($this->value() < 10) {
            return "41b6c4";
        } elseif($this->value() < 13) {
            return "a1dab4";
        } elseif($this->value() < 16) {
            return "fecc5c";
        } elseif($this->value() < 20) {
            return "fd8d3c";
        } elseif ($this->value() == 20) {
            return "e31a1c";
        }
	}

    /**
     * @return string
     */
    public function textColor()
    {
        if ($this->value() < 6 || $this->value() > 20) {
            return null;
        } elseif ($this->value() == 6) {
            return "ffffff";
        } elseif($this->value() < 10) {
            return "ffffff";
        } elseif($this->value() < 13) {
            return "000000";
        } elseif($this->value() < 16) {
            return "000000";
        } elseif($this->value() < 20) {
            return "ffffff";
        } elseif ($this->value() == 20) {
            return "ffffff";
        }  else {
            return null;
        }
    }

	/**
	 * String representation
	 *
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function string() {
	    if ($this->value() > 5 && $this->value() < 21)
        return '<span class="rpeicon" style="border: 1px solid #'.$this->backgroundColor().';" rel="tooltip" data-original-title="'.$this->value().'"> '.$this->value().'</span>';
	}
}

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
            return "c000ff";
        } elseif($this->value() < 10) {
            return "3600b3";
        } elseif($this->value() < 13) {
            return "00d900";
        } elseif($this->value() < 16) {
            return "efff00";
        } elseif($this->value() < 20) {
            return "ff7e00";
        } elseif ($this->value() == 20) {
            return "ff0000";
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
        return '<span class="windicon" style="background-color:#'.$this->backgroundColor().';color:#'.$this->textColor().';" rel="tooltip" data-original-title="'.$this->value().'"> '.$this->value().'</span>';
	}
}

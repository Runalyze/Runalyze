<?php
/**
 * This file contains class::VerticalRatio
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

/**
 * Vertical ratio
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Activity
 */
class VerticalRatio implements ValueInterface
{
	/**
	 * Value [%o]
	 * @var int
	 */
	protected $Permille;

	/**
	 * Format vertical ratio
	 * @param int $permille
	 * @param bool $withUnit
	 * @return string
	 */
	public static function format($permille, $withUnit = true)
	{
		return (new self($permille))->string($withUnit);
	}

	/**
	 * @param int $permille [%o]
	 */
	public function __construct($permille = 0)
	{
		$this->Permille = $permille;
	}

	/**
	 * Label for stride length
	 * @return string
	 */
	public function label()
	{
		return __('Vertical ratio');
	}

	/**
	 * Unit
	 * @return string
	 */
	public function unit()
	{
		return '%';
	}

	/**
	 * Set vertical ratio
	 * @param int $permille [%o]
	 * @return \Runalyze\Activity\VerticalRatio $this-reference
	 */
	public function set($permille)
	{
		$this->Permille = $permille;

		return $this;
	}

	/**
	 * Set vertical ratio in percent
	 * @param float $percent [%]
	 * @return \Runalyze\Activity\VerticalRatio $this-reference
	 */
	public function setPercent($percent)
	{
		$this->Permille = 10*$percent;

		return $this;
	}

	/**
	 * Get vertical ratio
	 * @return int [%o]
	 */
	public function value()
	{
		return $this->Permille;
	}

	/**
	 * Get vertical ratio
	 * @return float [%]
	 */
	public function inPercent()
	{
		return $this->Permille/10;
	}

	/**
	 * Format value as string
	 * @param bool $withUnit
	 * @return string
	 */
	public function string($withUnit = true)
	{
		if ($this->Permille > 0) {
			return number_format($this->Permille/10, 1, '.', '').($withUnit ? '&nbsp;%' : '');
		}

		return '';
	}
}

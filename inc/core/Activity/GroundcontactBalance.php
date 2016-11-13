<?php
/**
 * This file contains class::GroundcontactBalance
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

/**
 * Groundcontact balance
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Activity
 */
class GroundcontactBalance implements ValueInterface
{
	/**
	 * Value [%oo L]
	 * @var int
	 */
	protected $BasisPoint;

	/**
	 * Format groundcontact balance
	 * @param int $basispoint
	 * @param bool $withUnit
	 * @return string
	 */
	public static function format($basispoint, $withUnit = true)
	{
		return (new self($basispoint))->string($withUnit);
	}

	/**
	 * @param int $basispoint [%oo L]
	 */
	public function __construct($basispoint = 0)
	{
		$this->BasisPoint = $basispoint;
	}

	/**
	 * Label for stride length
	 * @return string
	 */
	public function label()
	{
		return __('Groundcontact balance');
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
	 * Set groundcontact balance
	 * @param int $basispoint [%oo L]
	 * @return \Runalyze\Activity\GroundcontactBalance $this-reference
	 */
	public function set($basispoint)
	{
		$this->BasisPoint = $basispoint;

		return $this;
	}

	/**
	 * Set groundcontact balance in percent
	 * @param float $percent [% L]
	 * @return \Runalyze\Activity\GroundcontactBalance $this-reference
	 */
	public function setPercent($percent)
	{
		$this->BasisPoint = 100*$percent;

		return $this;
	}

	/**
	 * Get groundcontact balance
	 * @return int [%oo]
	 */
	public function value()
	{
		return $this->BasisPoint;
	}

	/**
	 * Format value as string
	 * @param bool $withUnit
	 * @return string
	 */
	public function string($withUnit = true)
	{
		if ($this->isKnown()) {
			$string = $this->leftInPercent().'L/'.$this->rightInPercent().'R';
		} else {
			$string = '-/-';
		}

		if ($withUnit) {
			return $string.'&nbsp;%';
		}

		return $string;
	}

	/**
	 * @return string
	 */
	public function leftInPercent()
	{
		return number_format($this->BasisPoint/100, 1);
	}

	/**
	 * @return string
	 */
	public function rightInPercent()
	{
		return number_format(100 - $this->BasisPoint/100, 1);
	}

	/**
	 * @return bool
	 */
	public function isKnown()
	{
		return ($this->BasisPoint > 0);
	}
}

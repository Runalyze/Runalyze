<?php
/**
 * This file contains class::PoolLength
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

use Runalyze\Configuration;
use Runalyze\Parameter\Application\DistanceUnitSystem;

/**
 * Pool length
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Activity
 */
class PoolLength implements ValueInterface
{
	/**
	 * Value [cm]
	 * @var float
	 */
	protected $Centimeter;

	/**
	 * @var \Runalyze\Parameter\Application\DistanceUnitSystem 
	 */
	protected $UnitSystem;

	/**
	 * Format pool length
	 * @param int $centimeter
	 * @param bool $withUnit
	 * @return string
	 */
	public static function format($centimeter, $withUnit = true)
	{
		return (new self($centimeter))->string($withUnit);
	}

	/**
	 * @param int $centimeter
	 * @param \Runalyze\Parameter\Application\DistanceUnitSystem $unitSystem
	 */
	public function __construct($centimeter = 0, DistanceUnitSystem $unitSystem = null)
	{
		$this->Centimeter = $centimeter;
		$this->UnitSystem = (null !== $unitSystem) ? $unitSystem : Configuration::General()->distanceUnitSystem();
	}

	/**
	 * Label for pool length
	 * @return string
	 */
	public function label()
	{
		return __('Pool length');
	}

	/**
	 * Unit
	 * @return string
	 */
	public function unit()
	{
		if ($this->UnitSystem->isImperial()) {
			return DistanceUnitSystem::YARDS;
		}

		return DistanceUnitSystem::METER;
	}

	/**
	 * Set pool length
	 * @param int $centimeter
	 * @return \Runalyze\Activity\PoolLength $this-reference
	 */
	public function set($centimeter)
	{
		$this->Centimeter = $centimeter;

		return $this;
	}

	/**
	 * @param float $meter
	 * @return \Runalyze\Activity\PoolLength $this-reference
	 */
	public function setMeter($meter)
	{
		$this->Centimeter = $meter * 100;

		return $this;
	}

	/**
	 * @param float $yards
	 * @return \Runalyze\Activity\PoolLength $this-reference
	 */
	public function setYards($yards)
	{
		$this->Centimeter = $yards / DistanceUnitSystem::YARD_MULTIPLIER * 1000 * 100;

		return $this;
	}

	/**
	 * @param int $poolLength [mixed unit]
	 * @return \Runalyze\Activity\PoolLength $this-reference
	 */
	public function setInPreferredUnit($poolLength)
	{
		if ($this->UnitSystem->isImperial()) {
			$this->setYards($poolLength);
		} else {
			$this->setMeter($poolLength);
		}

		return $this;
	}

	/**
	 * Get pool length
	 * @return int [cm]
	 */
	public function value()
	{
		return round($this->Centimeter);
	}

	/**
	 * @return int
	 */
	public function cm()
	{
		return round($this->Centimeter);
	}

	/**
	 * @return float
	 */
	public function meter()
	{
		return round($this->Centimeter / 100, 2);
	}	

	/**
	 * @return float
	 */
	public function yards()
	{
		return round($this->Centimeter * DistanceUnitSystem::YARD_MULTIPLIER / 1000 / 100, 2);
	}

	/**
	 * @return mixed [mixed unit]
	 */
	public function valueInPreferredUnit()
	{
		if ($this->UnitSystem->isImperial()) {
			return $this->yards();
		}

		return $this->meter();
	}

	/**
	 * Format value as string
	 * @param bool $withUnit
	 * @return string
	 */
	public function string($withUnit = true)
	{
		if ($this->UnitSystem->isImperial()) {
			return $this->stringYards($withUnit);
		}

		return $this->stringMeter($withUnit);
	}

	/**
	 * @param bool $withUnit
	 * @return string
	 */
	public function stringMeter($withUnit = true)
	{
		return number_format($this->Centimeter/100, 0).($withUnit ? '&nbsp;'.DistanceUnitSystem::METER : '');
	}

	/**
	 * @param bool $withUnit
	 * @return string
	 */
	public function stringCM($withUnit = true)
	{
		return number_format($this->Centimeter, 0, '', '').($withUnit ? '&nbsp;'.DistanceUnitSystem::CM : '');
	}

	/**
	 * @param bool $withUnit
	 * @return string
	 */
	public function stringYards($withUnit = true)
	{
		return number_format($this->yards(), 2, '.', '').($withUnit ? '&nbsp;'.DistanceUnitSystem::YARDS : '');
	}
}
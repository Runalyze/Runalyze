<?php
/**
 * This file contains class::StrideLength
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

use Runalyze\Configuration;
use Runalyze\Parameter\Application\DistanceUnitSystem;

/**
 * Stride length
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Activity
 */
class StrideLength implements ValueInterface
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
	 * Format stride length
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
	 * Label for stride length
	 * @return string
	 */
	public function label()
	{
		return __('Stride length');
	}

	/**
	 * Unit
	 * @return string
	 */
	public function unit()
	{
		if ($this->UnitSystem->isImperial()) {
			return DistanceUnitSystem::FEET;
		}

		return DistanceUnitSystem::CM;
	}

	/**
	 * Set stride length
	 * @param int $centimeter
	 * @return \Runalyze\Activity\StrideLength $this-reference
	 */
	public function set($centimeter)
	{
		$this->Centimeter = $centimeter;

		return $this;
	}

	/**
	 * @param float $meter
	 * @return \Runalyze\Activity\StrideLength $this-reference
	 */
	public function setMeter($meter)
	{
		$this->Centimeter = $meter * 100;

		return $this;
	}

	/**
	 * @param int $feet
	 * @return \Runalyze\Activity\StrideLength $this-reference
	 */
	public function setFeet($feet)
	{
		$this->Centimeter = $feet / DistanceUnitSystem::FEET_MULTIPLIER * 1000 * 100;

		return $this;
	}

	/**
	 * @param int $strideLength [mixed unit]
	 * @return \Runalyze\Activity\StrideLength $this-reference
	 */
	public function setInPreferredUnit($strideLength)
	{
		if ($this->UnitSystem->isImperial()) {
			$this->setFeet($strideLength);
		} else {
			$this->setMeter($strideLength);
		}

		return $this;
	}

	/**
	 * Get stride length
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
	public function feet()
	{
		return round($this->Centimeter * DistanceUnitSystem::FEET_MULTIPLIER / 1000 / 100, 2);
	}

	/**
	 * @return int [mixed unit]
	 */
	public function valueInPreferredUnit()
	{
		if ($this->UnitSystem->isImperial()) {
			return $this->feet();
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
			return $this->stringFeet($withUnit);
		}

		return $this->stringMeter($withUnit);
	}

	/**
	 * @param bool $withUnit
	 * @return string
	 */
	public function stringMeter($withUnit = true)
	{
		return number_format($this->Centimeter/100, 2).($withUnit ? '&nbsp;'.DistanceUnitSystem::METER : '');
	}

	/**
	 * @param bool $withUnit
	 * @return string
	 */
	public function stringCM($withUnit = true)
	{
		return number_format($this->Centimeter, 0).($withUnit ? '&nbsp;'.DistanceUnitSystem::CM : '');
	}

	/**
	 * @param bool $withUnit
	 * @return string
	 */
	public function stringFeet($withUnit = true)
	{
		return number_format($this->feet(), 1, '.', '').($withUnit ? '&nbsp;'.DistanceUnitSystem::FEET : '');
	}
}
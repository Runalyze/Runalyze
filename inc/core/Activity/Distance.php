<?php
/**
 * This file contains class::Distance
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

use Runalyze\Configuration;
use Runalyze\Parameter\Application\DistanceUnitSystem;

// TODO: use
// Configuration::ActivityView()->decimals()

/**
 * Distance
 *
 * @author Hannes Christiansen <hannes@runalyze.de>
 * @author Michael Pohl <michael@runalyze.de>
 * @package Runalyze\Activity
 */
class Distance implements ValueInterface {
	/**
	 * Seperator for decimals
	 * @var string
	 */
	public static $DecimalSeparator = ',';

	/**
	 * Seperator for thousands
	 * @var string
	 */
	public static $ThousandsSeparator = '.';

	/**
	 * Default number of decimals
	 * @var int
	 */
	public static $DefaultDecimals = 2;

	/**
	 * Distance [km]
	 * @var float
	 */
	protected $Kilometer;

	/**
	 * @var \Runalyze\Parameter\Application\DistanceUnitSystem 
	 */
	protected $UnitSystem;

	/**
	 * Format
	 * @param float $km [km]
	 * @param bool $withUnit [optional] with or without unit
	 * @param int $decimals [optional] number of decimals
	 * @return string
	 */
	public static function format($km, $withUnit = true, $decimals = false)
	{
		return (new self($km))->string($withUnit, $decimals);
	}

	/**
	 * @param float $km
	 * @param \Runalyze\Parameter\Application\DistanceUnitSystem $unitSystem
	 */
	public function __construct($km = 0, DistanceUnitSystem $unitSystem = null)
	{
		$this->Kilometer = $km;
		$this->UnitSystem = (null !== $unitSystem) ? $unitSystem : Configuration::General()->distanceUnitSystem();
	}

	/**
	 * Label for distance
	 * @return string
	 */
	public function label()
	{
		return __('Distance');
	}

	/**
	 * Set distance
	 * @param float $km [km]
	 * @return \Runalyze\Activity\Distance $this-reference
	 */
	public function set($km)
	{
		$this->Kilometer = (float)str_replace(',', '.', $km);

		return $this;
	}
        
	/**
	 * Set distance in miles
	 * @param float $miles [mi]
	 * @return \Runalyze\Activity\Distance $this-reference
	 */
	public function setMiles($miles)
	{
		$this->Kilometer = (float)str_replace(',', '.', $miles) / DistanceUnitSystem::MILE_MULTIPLIER;

		return $this;
	}

	/**
	 * @param float $distance [mixed unit]
	 * @return \Runalyze\Activity\Distance $this-reference
	 */
	public function setInPreferredUnit($distance)
	{
		if ($this->UnitSystem->isImperial()) {
			$this->setMiles($distance);
		} else {
			$this->set($distance);
		}

		return $this;
	}

	/**
	 * Get distance
	 * @return float [km]
	 */
	public function value()
	{
		return $this->Kilometer;
	}

	/**
	 * Kilometer
	 * @return float [km]
	 */
	public function kilometer()
	{
		return $this->Kilometer;
	}

	/**
	 * Meter
	 * @return int [m]
	 */
	public function meter()
	{
		return round($this->Kilometer * 1000);
	}

	/**
	 * Miles
	 * @return float [miles]
	 */
	public function miles()
	{
		return $this->Kilometer * DistanceUnitSystem::MILE_MULTIPLIER;
	}

	/**
	 * Yards
	 * @return int [yards]
	 */
	public function yards()
	{
		return round($this->Kilometer * DistanceUnitSystem::YARD_MULTIPLIER);
	}

	/**
	 * Feet
	 * @return int [feet]
	 */
	public function feet()
	{
		return round($this->Kilometer * DistanceUnitSystem::FEET_MULTIPLIER);
	}

	/**
	 * Unit
	 * @return string
	 */
	public function unit()
	{
		if ($this->UnitSystem->isImperial()) {
			return DistanceUnitSystem::MILES;
		}

		return DistanceUnitSystem::KM;
	}

	/**
	 * @return float [mixed unit]
	 */
	public function valueInPreferredUnit()
	{
		if ($this->UnitSystem->isImperial()) {
			return $this->miles();
		}

		return $this->kilometer();
	}

	/**
	 * Format distance as string
	 * @param bool $withUnit [optional]
	 * @param int $decimals [optional] number of decimals
	 * @return string
	 */
	public function string($withUnit = true, $decimals = false)
	{
		if ($this->UnitSystem->isImperial()) {
			return $this->stringMiles($withUnit, $decimals);
		}

		return $this->stringKilometer($withUnit, $decimals);
	}

	/**
	 * Format distance with auto detection for track distances shown in meter
	 * @param bool $withUnit [optional]
	 * @param int $decimals [optional] number of decimals
	 * @return string
	 */
	public function stringAuto($withUnit = true, $decimals = false)
	{
		if ($this->Kilometer <= 1.0 || $this->Kilometer == 1.5 || $this->Kilometer == 3.0) {
			return $this->stringMeter($withUnit, $decimals);
		}

		if ($this->Kilometer == 5.0 || $this->Kilometer == 10.0) {
			return $this->stringKilometer($withUnit, $decimals);
		}

		return $this->string($withUnit, $decimals);
	}

	/**
	 * String: as kilometer
	 * @param bool $withUnit [optional]
	 * @param int $decimals [optional]
	 * @return string with unit
	 */
	public function stringKilometer($withUnit = true, $decimals = false)
	{
		if ($decimals === false) {
			$decimals = self::$DefaultDecimals;
		}

		return number_format($this->Kilometer, $decimals, self::$DecimalSeparator, self::$ThousandsSeparator).($withUnit ? '&nbsp;'.DistanceUnitSystem::KM : '');
	}

	/**
	 * String: as meter
	 * @param bool $withUnit [optional]
	 * @param int $decimals [optional]
	 * @return string with unit
	 */
	public function stringMeter($withUnit = true, $decimals = false)
	{
		return number_format($this->Kilometer * 1000, 0, '', '.').($withUnit ? DistanceUnitSystem::METER : '');
	}

	/**
	 * String: as feet
	 * @param bool $withUnit [optional]
	 * @param int $decimals [optional]
	 * @return string with unit
	 */
	public function stringFeet($withUnit = true, $decimals = false)
	{
		return number_format($this->Kilometer * DistanceUnitSystem::FEET_MULTIPLIER, 0, '', '.').($withUnit ? '&nbsp;'.DistanceUnitSystem::FEET : '');
	}

	/**
	 * String: as yards
	 * @param bool $withUnit [optional]
	 * @param int $decimals [optional]
	 * @return string with unit
	 */
	public function stringYards($withUnit = true, $decimals = false)
	{
		return number_format($this->Kilometer * DistanceUnitSystem::YARD_MULTIPLIER, 0, '', '.').($withUnit ? '&nbsp;'.DistanceUnitSystem::YARDS : '');
	}

	/**
	 * String: as miles
	 * @param bool $withUnit [optional]
	 * @param int $decimals [optional]
	 * @return string with unit
	 */
	public function stringMiles($withUnit = true, $decimals = false)
	{
		if ($decimals === false) {
			$decimals = self::$DefaultDecimals;
		}

		return number_format($this->Kilometer * DistanceUnitSystem::MILE_MULTIPLIER, $decimals, '.', ',').($withUnit ? '&nbsp;'.DistanceUnitSystem::MILES : '');
	}

	/**
	 * Multiply distance
	 * @param float $factor
	 * @return \Runalyze\Activity\Distance $this-reference
	 */
	public function multiply($factor)
	{
		$this->Kilometer *= $factor;

		return $this;
	}

	/**
	 * Add another distance
	 * @param \Runalyze\Activity\Distance $object
	 * @return \Runalyze\Activity\Distance $this-reference
	 */
	public function add(Distance $object)
	{
		$this->Kilometer += $object->kilometer();

		return $this;
	}

	/**
	 * Subtract another distance
	 * @param \Runalyze\Activity\Distance $object
	 * @return \Runalyze\Activity\Distance $this-reference
	 */
	public function subtract(Distance $object)
	{
		$this->Kilometer -= $object->kilometer();

		return $this;
	}

	/**
	 * Is distance negative?
	 * @return boolean
	 */
	public function isNegative()
	{
		return ($this->Kilometer < 0);
	}

	/**
	 * Is distance zero?
	 * @return boolean
	 */
	public function isZero()
	{
		return ($this->Kilometer == 0);
	}
        
}
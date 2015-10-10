<?php
/**
 * This file contains class::Weight
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

use Runalyze\Configuration;
use Runalyze\Parameter\Application\WeightUnit;

/**
 * Weight
 * 
 * @author Hannes Christiansen <hannes@runalyze.de>
 * @author Michael Pohl <michael@runalyze.de>
 * @package Runalyze\Activity
 */
class Weight implements ValueInterface {
	/**
	 * Default poun d multiplier
	 * @var double 
	*/
	const POUNDS_MULTIPLIER = 2.204622;

	/**
	 * Default stone multiplier
	 * @var double 
	*/
	const STONES_MULTIPLIER = 0.157473;

	/**
	 * Default number of decimals
	 * @var int
	 */
	public static $DefaultDecimals = 1;

	/**
	 * Weight [kg]
	 * @var float
	 */
	protected $Weight;

	/**
	 * Preferred unit
	 * @var \Runalyze\Parameter\Application\WeightUnit
	 */
	protected $PreferredUnit;

	/**
	 * Format
	 * @param float $weight [kg]
	 * @param bool $withUnit [optional] with or without unit
	 * @param int $decimals [optional] number of decimals
	 * @return string
	 */
	public static function format($weight, $withUnit = true, $decimals = false) {
		return (new self($weight))->string($withUnit, $decimals);
	}

	/**
	 * @param float $weight [kg]
	 * @param \Runalyze\Parameter\Application\WeightUnit $preferredUnit
	 */
	public function __construct($weight = 0, WeightUnit $preferredUnit = null) {
		$this->PreferredUnit = (null !== $preferredUnit) ? $preferredUnit : Configuration::General()->weightUnit();

		$this->set($weight);
	}

	public function label() {
		return __('Weight');
	}

	/**
	 * Unit
	 * @return string
	 */
	public function unit() {
		return $this->PreferredUnit->unit();
	}

	/**
	 * Set weight
	 * @param float $weight [kg]
	 * @return \Runalyze\Activity\Weight $this-reference
	 */
	public function set($weight) {
		$this->Weight = (float)str_replace(',', '.', $weight);

		return $this;
	}

	/**
	 * Set weight in pounds
	 * @param float $weight [pounds]
	 * @return \Runalyze\Activity\Weight $this-reference
	 */
	public function setPounds($weight) {
		$this->Weight = (float)str_replace(',', '.', $weight) / self::POUNDS_MULTIPLIER;

		return $this;
	}

	/**
	 * Set weight in stones
	 * @param float $weight [stones]
	 * @return \Runalyze\Activity\Weight $this-reference
	 */
	public function setStones($weight) {
		$this->Weight = (float)str_replace(',', '.', $weight) / self::STONES_MULTIPLIER;

		return $this;
	}

	/**
	 * @param float $weight [mixed unit]
	 * @return \Runalyze\Activity\Weight $this-reference
	 */
	public function setInPreferredUnit($weight) {
		if ($this->PreferredUnit->isPounds()) {
			$this->setPounds($weight);
		} elseif ($this->PreferredUnit->isStones()) {
			$this->setStones($weight);
		} else {
			$this->set($weight);
		}

		return $this;
	}

	/**
	 * Format weight as string
	 * @param bool $withUnit [optional] show unit?
	 * @param bool|int $decimals [optional] number of decimals
	 * @return string
	 */
	public function string($withUnit = true, $decimals = false) {
		if ($this->PreferredUnit->isPounds()) {
			return $this->stringPounds($withUnit, $decimals);
		} elseif ($this->PreferredUnit->isStones()) {
			return $this->stringStones($withUnit, $decimals);
		}

		return $this->stringKG($withUnit, $decimals);
	}

	/**
	 * @return float [kg]
	 */
	public function value() {
		return $this->Weight;
	}

	/**
	 * Weight
	 * @return float [kg]
	 */
	public function kg() {
		return $this->Weight;
	}

	/**
	 * Weight in pounds
	 * @return float [lbs]
	 */
	public function pounds() {
		return $this->Weight * self::POUNDS_MULTIPLIER;
	}

	/**
	 * Weight in stones
	 * @return float [st]
	 */
	public function stones() {
		return $this->Weight * self::STONES_MULTIPLIER;
	}

	/**
	 * @return float [mixed unit]
	 */
	public function valueInPreferredUnit() {
		if ($this->PreferredUnit->isPounds()) {
			return $this->pounds();
		} elseif ($this->PreferredUnit->isStones()) {
			return $this->stones();
		}

		return $this->kg();
	}

	/**
	 * String: as kg
	 * @param bool $withUnit [optional] show unit?
	 * @param bool|int $decimals [optional] number of decimals
	 * @return string with unit
	 */
	public function stringKG($withUnit = true, $decimals = false) {
		$decimals = ($decimals === false) ? self::$DefaultDecimals : $decimals;

		return number_format($this->Weight, $decimals, '.', '').($withUnit ? '&nbsp;'.WeightUnit::KG : '');
	}

	/**
	 * String: as pounds
	 * @param bool $withUnit [optional] show unit?
	 * @param bool|int $decimals [optional] number of decimals
	 * @return string with unit
	 */
	public function stringPounds($withUnit = true, $decimals = false) {
		$decimals = ($decimals === false) ? self::$DefaultDecimals : $decimals;

		return number_format($this->pounds(), $decimals, '.', '').($withUnit ? '&nbsp;'.WeightUnit::POUNDS : '');
	}

	/**
	 * String: as stone
	 * @param bool $withUnit [optional] show unit?
	 * @param bool|int $decimals [optional] number of decimals
	 * @return string with unit
	 */
	public function stringStones($withUnit = true, $decimals = false) {
		$decimals = ($decimals === false) ? self::$DefaultDecimals : $decimals;

		return number_format($this->stones(), $decimals, '.', '').($withUnit ? '&nbsp;'.WeightUnit::STONES : '');
	}
}
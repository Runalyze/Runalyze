<?php
/**
 * This file contains class::Distance
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

// TODO: use
// Configuration::ActivityView()->decimals()

/**
 * Distance
 *
 * @author Hannes Christiansen
 * @package Runalyze\Activity
 */
class Distance {
	/**
	 * Auto format
	 * @var string
	 */
	const FORMAT_AUTO = 'auto';

	/**
	 * Seperator for decimals
	 * @var string
	 */
	static public $DECIMAL_POINT = ',';

	/**
	 * Seperator for thousands
	 * @var string
	 */
	static public $THOUSANDS_POINT = '.';

	/**
	 * Default number of decimals
	 * @var int
	 */
	static public $DEFAULT_DECIMALS = 2;

	/**
	 * Distance [km]
	 * @var float
	 */
	protected $Distance;

	/**
	 * Format
	 * @param float $distance [km]
	 * @param mixed $format [optional] set as true for display as meter, can be 'auto'
	 * @param int $decimals [optional] number of decimals
	 * @return string
	 */
	static public function format($distance, $format = false, $decimals = false) {
		$Object = new Distance($distance);

		return $Object->string($format, $decimals);
	}

	/**
	 * Create distance
	 * @param float $distance [km]
	 */
	public function __construct($distance = 0) {
		$this->set($distance);
	}

	/**
	 * Set distance
	 * @param float $distance [km]
	 * @return \Runalyze\Activity\Distance $this-reference
	 */
	public function set($distance) {
		$this->Distance = (float)str_replace(',', '.', $distance);

		return $this;
	}

	/**
	 * Kilometer
	 * @return float [km]
	 */
	public function kilometer() {
		return $this->Distance;
	}

	/**
	 * Meter
	 * @return int [m]
	 */
	public function meter() {
		return round($this->Distance*1000);
	}

	/**
	 * Format distance as string
	 * @param mixed $format [optional] set as true for display as meter, can be 'auto'
	 * @param int $decimals [optional] number of decimals
	 * @return string
	 */
	public function string($format = false, $decimals = false) {
		if ($format == self::FORMAT_AUTO) {
			if ($this->Distance <= 1.0 || $this->Distance == 1.5 || $this->Distance == 3.0) {
				$format = true;
			} else {
				$format = false;
			}
		}

		if ($format === true) {
			return $this->stringMeter();
		} else {
			return $this->stringKilometer($decimals);
		}
	}

	/**
	 * String: as meter
	 * @param boolean $withUnit [optional]
	 * @return string with unit
	 */
	public function stringMeter($withUnit = true) {
		return number_format($this->Distance*1000, 0, '', '.').($withUnit ? 'm' : '');
	}

	/**
	 * String: as kilometer
	 * @param int $decimals [optional]
	 * @param boolean $withUnit [optional]
	 * @return string with unit
	 */
	public function stringKilometer($decimals = false, $withUnit = true) {
		if ($decimals === false) {
			$decimals = self::$DEFAULT_DECIMALS;
		}

		return number_format($this->Distance, $decimals, self::$DECIMAL_POINT, self::$THOUSANDS_POINT).($withUnit ? '&nbsp;km' : '');
	}

	/**
	 * Multiply distance
	 * @param float $factor
	 * @return \Runalyze\Activity\Distance $this-reference
	 */
	public function multiply($factor) {
		$this->Distance *= $factor;

		return $this;
	}

	/**
	 * Add another distance
	 * @param \Runalyze\Activity\Distance $object
	 * @return \Runalyze\Activity\Distance $this-reference
	 */
	public function add(Distance $object) {
		$this->Distance += $object->kilometer();

		return $this;
	}

	/**
	 * Subtract another distance
	 * @param \Runalyze\Activity\Distance $object
	 * @return \Runalyze\Activity\Distance $this-reference
	 */
	public function subtract(Distance $object) {
		$this->Distance -= $object->kilometer();

		return $this;
	}

	/**
	 * Is distance negative?
	 * @return boolean
	 */
	public function isNegative() {
		return ($this->Distance < 0);
	}

	/**
	 * Is distance zero?
	 * @return boolean
	 */
	public function isZero() {
		return ($this->Distance == 0);
	}
}
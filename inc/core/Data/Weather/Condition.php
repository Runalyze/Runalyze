<?php
/**
 * This file contains class::Condition
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

use Runalyze\View\Icon\Weather;

/**
 * Weather condition
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Weather
 */
class Condition {
	/**
	 * @var int
	 */
	const UNKNOWN = 1;

	/**
	 * @var int
	 */
	const SUNNY = 2;

	/**
	 * @var int
	 */
	const FAIR = 3;

	/**
	 * @var int
	 */
	const CLOUDY = 4;

	/**
	 * @var int
	 */
	const CHANGEABLE = 5;

	/**
	 * @var int
	 */
	const RAINY = 6;

	/**
	 * @var int
	 */
	const SNOWING = 7;

	/**
	 * Identifier
	 * @var int
	 */
	protected $identifier;

	/**
	 * Complete list
	 * @return array
	 */
	public static function completeList() {
		return array(
			self::UNKNOWN,
			self::SUNNY,
			self::FAIR,
			self::CLOUDY,
			self::CHANGEABLE,
			self::RAINY,
			self::SNOWING
		);
	}

	/**
	 * Weather condition
	 * @param int $identifier a class constant
	 */
	public function __construct($identifier) {
		$this->set($identifier);
	}

	/**
	 * Set
	 * @param int $identifier a class constant
	 */
	public function set($identifier) {
		if (!in_array($identifier, self::completeList())) {
			$this->identifier = self::UNKNOWN;
		} else { 
			$this->identifier = $identifier;
		}
	}

	/**
	 * Identifier
	 * @return int
	 */
	public function id() {
		return $this->identifier;
	}

	/**
	 * Is unknown?
	 * @return bool
	 */
	public function isUnknown() {
		return ($this->identifier == self::UNKNOWN);
	}

	/**
	 * Icon
	 * @return \Runalyze\View\Icon\WeatherIcon
	 */
	public function icon() {
		switch ($this->identifier) {
			case self::SUNNY:
				return new Weather\Sunny();
			case self::FAIR:
				return new Weather\Fair();
			case self::CLOUDY:
				return new Weather\Cloudy();
			case self::CHANGEABLE:
				return new Weather\Changeable();
			case self::RAINY:
				return new Weather\Rainy();
			case self::SNOWING:
				return new Weather\Snowing();
			case self::UNKNOWN:
			default:
				return new Weather\Unknown();
		}
	}

	/**
	 * String
	 * @return string
	 */
	public function string() {
		switch ($this->identifier) {
			case self::SUNNY:
				return __('sunny');
			case self::FAIR:
				return __('fair');
			case self::CLOUDY:
				return __('cloudy');
			case self::CHANGEABLE:
				return __('changeable');
			case self::RAINY:
				return __('rainy');
			case self::SNOWING:
				return __('snowing');
			case self::UNKNOWN:
			default:
				return __('unknown');
		}
	}
}
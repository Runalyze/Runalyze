<?php

namespace Runalyze\Data\Weather;

use Runalyze\Profile\Weather\WeatherConditionProfile;
use Runalyze\View\Icon\Weather;

class Condition
{
	/** @var int see \Runalyze\Profile\Weather\WeatherConditionProfile */
	protected $identifier;

	/**
	 * @return array
	 */
	public static function completeList()
	{
		return WeatherConditionProfile::getEnum();
	}

	/**
	 * @param int $identifier see \Runalyze\Profile\Weather\WeatherConditionProfile
	 */
	public function __construct($identifier)
	{
		$this->set($identifier);
	}

	/**
	 * @param int $identifier see \Runalyze\Profile\Weather\WeatherConditionProfile
	 */
	public function set($identifier)
	{
		if (!in_array($identifier, self::completeList())) {
			$this->identifier = WeatherConditionProfile::UNKNOWN;
		} else {
			$this->identifier = $identifier;
		}
	}

	/**
	 * @return int see \Runalyze\Profile\Weather\WeatherConditionProfile
	 */
	public function id()
	{
		return $this->identifier;
	}

	/**
	 * @return bool
	 */
	public function isUnknown()
	{
		return (WeatherConditionProfile::UNKNOWN == $this->identifier);
	}

	/**
	 * @return \Runalyze\View\Icon\WeatherIcon
	 */
	public function icon()
	{
		switch ($this->identifier) {
			case WeatherConditionProfile::SUNNY:
				return new Weather\Sunny();
			case WeatherConditionProfile::FAIR:
				return new Weather\Fair();
			case WeatherConditionProfile::CLOUDY:
				return new Weather\Cloudy();
			case WeatherConditionProfile::FOGGY:
				return new Weather\Foggy();
			case WeatherConditionProfile::CHANGEABLE:
				return new Weather\Changeable();
			case WeatherConditionProfile::THUNDERSTORM:
				return new Weather\Thunderstorm();
			case WeatherConditionProfile::RAINY:
				return new Weather\Rainy();
			case WeatherConditionProfile::HEAVYRAIN:
				return new Weather\Heavyrain();
			case WeatherConditionProfile::SNOWING:
				return new Weather\Snowing();
            case WeatherConditionProfile::WINDY:
                return new Weather\Windy();
			case WeatherConditionProfile::UNKNOWN:
			default:
				return new Weather\Unknown();
		}
	}

	/**
	 * @return string
	 */
	public function string()
	{
		switch ($this->identifier) {
			case WeatherConditionProfile::SUNNY:
				return __('sunny');
			case WeatherConditionProfile::FAIR:
				return __('fair');
			case WeatherConditionProfile::CLOUDY:
				return __('cloudy');
			case WeatherConditionProfile::CHANGEABLE:
				return __('changeable');
			case WeatherConditionProfile::RAINY:
				return __('rainy');
			case WeatherConditionProfile::SNOWING:
				return __('snowing');
			case WeatherConditionProfile::HEAVYRAIN:
				return __('heavy rain');
			case WeatherConditionProfile::FOGGY:
				return __('foggy');
			case WeatherConditionProfile::THUNDERSTORM:
				return __('thundery');
            case WeatherConditionProfile::WINDY:
                return __('windy');
			case WeatherConditionProfile::UNKNOWN:
			default:
				return __('unknown');
		}
	}
}

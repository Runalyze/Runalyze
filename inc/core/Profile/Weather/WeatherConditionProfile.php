<?php

namespace Runalyze\Profile\Weather;

use Runalyze\Common\Enum\AbstractEnum;

class WeatherConditionProfile extends AbstractEnum
{
	/** @var int */
	const UNKNOWN = 1;

	/** @var int */
	const SUNNY = 2;

	/** @var int */
	const FAIR = 3;

	/** @var int */
	const CLOUDY = 4;

	/** @var int */
	const CHANGEABLE = 5;

	/** @var int */
	const RAINY = 6;

	/** @var int */
	const SNOWING = 7;

	/** @var int */
	const HEAVYRAIN = 8;

	/** @var int */
	const FOGGY = 9;

	/** @var int */
	const THUNDERSTORM = 10;

	/** @var int */
	const WINDY = 11;
}

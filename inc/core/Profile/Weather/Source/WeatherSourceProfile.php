<?php

namespace Runalyze\Profile\Weather\Source;

use Runalyze\Common\Enum\AbstractEnum;
use Runalyze\Common\Enum\AbstractEnumFactoryTrait;

class WeatherSourceProfile extends AbstractEnum
{
    use AbstractEnumFactoryTrait;

    /** @var int */
    const OPEN_WEATHER_MAP = 1;

    /** @var int */
    const DATABASE_CACHE = 2;

    /** @var int */
    const DARK_SKY = 3;
}

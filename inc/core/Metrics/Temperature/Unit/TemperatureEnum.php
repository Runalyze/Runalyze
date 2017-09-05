<?php

namespace Runalyze\Metrics\Temperature\Unit;

use Runalyze\Common\Enum\AbstractEnum;
use Runalyze\Common\Enum\AbstractEnumFactoryTrait;

class TemperatureEnum extends AbstractEnum
{
    use AbstractEnumFactoryTrait;

    /** @var int */
    const CELSIUS = 0;

    /** @var int */
    const FAHRENHEIT = 1;
}

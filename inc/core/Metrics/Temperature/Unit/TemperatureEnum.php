<?php

namespace Runalyze\Metrics\Temperature\Unit;

use Runalyze\Util\AbstractEnum;
use Runalyze\Util\AbstractEnumFactoryTrait;

class TemperatureEnum extends AbstractEnum
{
    use AbstractEnumFactoryTrait;

    /** @var int */
    const CELSIUS = 0;

    /** @var int */
    const FAHRENHEIT = 1;
}

<?php

namespace Runalyze\Metrics\Distance\Unit;

use Runalyze\Util\AbstractEnum;
use Runalyze\Util\AbstractEnumFactoryTrait;

class DistanceEnum extends AbstractEnum
{
    use AbstractEnumFactoryTrait;

    /** @var int */
    const KILOMETER = 0;

    /** @var int */
    const MILES = 1;

    /** @var int */
    const METER = 2;

    /** @var int */
    const YARDS = 3;

    /** @var int */
    const CENTIMETER = 4;

    /** @var int */
    const FEET = 5;
}

<?php

namespace Runalyze\Metrics\Velocity\Unit;

use Runalyze\Util\AbstractEnum;
use Runalyze\Util\AbstractEnumFactoryTrait;

class PaceEnum extends AbstractEnum
{
    use AbstractEnumFactoryTrait;

    /** @var int */
    const SECONDS_PER_KILOMETER = 0;

    /** @var int */
    const SECONDS_PER_MILE = 1;

    /** @var int */
    const SECONDS_PER_500M = 2;

    /** @var int */
    const SECONDS_PER_500Y = 3;

    /** @var int */
    const SECONDS_PER_100M = 4;

    /** @var int */
    const SECONDS_PER_100Y = 5;

    /** @var int */
    const KILOMETER_PER_HOUR = 6;

    /** @var int */
    const MILES_PER_HOUR = 7;

    /** @var int */
    const METER_PER_SECOND = 8;
}

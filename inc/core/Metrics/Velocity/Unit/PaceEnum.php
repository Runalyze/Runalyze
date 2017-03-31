<?php

namespace Runalyze\Metrics\Velocity\Unit;

use Runalyze\Parameter\Application\PaceUnit;
use Runalyze\Util\AbstractEnum;
use Runalyze\Util\AbstractEnumFactoryTrait;
use Runalyze\Util\InterfaceChoosable;

class PaceEnum extends AbstractEnum implements InterfaceChoosable
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

    public static function getChoices() {
        return [
            (new SecondsPerKilometer())->getAppendix() => self::SECONDS_PER_KILOMETER,
            (new SecondsPerMile())->getAppendix() => self::SECONDS_PER_MILE,
            (new SecondsPer500m())->getAppendix() => self::SECONDS_PER_500M,
            (new SecondsPer500y())->getAppendix() => self::SECONDS_PER_500Y,
            (new SecondsPer100m())->getAppendix() => self::SECONDS_PER_100M,
            (new SecondsPer100y())->getAppendix() => self::SECONDS_PER_100Y,
            (new KilometerPerHour())->getAppendix() => self::KILOMETER_PER_HOUR,
            (new MilesPerHour())->getAppendix() => self::MILES_PER_HOUR,
            (new MeterPerSecond())->getAppendix() => self::METER_PER_SECOND
        ];
    }
}

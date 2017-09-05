<?php

namespace Runalyze\Metrics\Velocity\Unit;

use Runalyze\Parameter\Application\PaceUnit;
use Runalyze\Common\Enum\AbstractEnum;
use Runalyze\Common\Enum\AbstractEnumFactoryTrait;
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

    public static function getChoices()
    {
        $choices = [];

        foreach (self::getEnum() as $enum) {
            $choices[self::get($enum)->getUnit()] = $enum;
        }

        return $choices;
    }
}

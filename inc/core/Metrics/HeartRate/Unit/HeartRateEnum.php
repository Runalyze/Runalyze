<?php

namespace Runalyze\Metrics\HeartRate\Unit;

use Runalyze\Common\Enum\AbstractEnum;

class HeartRateEnum extends AbstractEnum
{
    /** @var int */
    const BEATS_PER_MINUTE = 0;

    /** @var int */
    const PERCENT_MAXIMUM = 1;

    /** @var int */
    const PERCENT_RESERVE = 2;

    /**
     * @param int $enum from internal enum
     * @param int $maximalHeartRate
     * @param int $restingHeartRate
     * @return AbstractHeartRateUnit
     *
     * @throws \InvalidArgumentException
     */
    public static function get($enum, $maximalHeartRate, $restingHeartRate)
    {
        if (self::BEATS_PER_MINUTE == $enum) {
            return new BeatsPerMinute();
        } elseif (self::PERCENT_MAXIMUM == $enum) {
            return new PercentMaximum($maximalHeartRate);
        } elseif (self::PERCENT_RESERVE == $enum) {
            return new PercentReserve($maximalHeartRate, $restingHeartRate);
        }

        throw new \InvalidArgumentException('Unknown heart rate unit enum "'.$enum.'".');
    }
}

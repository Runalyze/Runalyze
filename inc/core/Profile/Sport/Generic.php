<?php

namespace Runalyze\Profile\Sport;

use Runalyze\Metrics\Velocity\Unit\PaceEnum;

/**
 * Fallback for user-defined sport types
 *
 * @codeCoverageIgnore
 */
class Generic extends AbstractSport
{
    public function __construct()
    {
        parent::__construct(SportProfile::GENERIC);
    }

    public function getIconClass()
    {
        return 'icons8-Sports-Mode';
    }

    public function getName()
    {
        return __('Generic');
    }

    public function getCaloriesPerHour()
    {
        return 500;
    }

    public function getAverageHeartRate()
    {
        return 120;
    }

    public function hasDistances()
    {
        return false;
    }

    public function hasPower()
    {
        return false;
    }

    public function isOutside()
    {
        return false;
    }

    public function getPaceUnitEnum()
    {
        return PaceEnum::KILOMETER_PER_HOUR;
    }

    public function usesShortDisplay()
    {
        return false;
    }
}

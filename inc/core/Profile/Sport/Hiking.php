<?php

namespace Runalyze\Profile\Sport;

use Runalyze\Metrics\Velocity\Unit\PaceEnum;

/**
 * @codeCoverageIgnore
 */
class Hiking extends AbstractSport
{
    public function __construct()
    {
        parent::__construct(SportProfile::HIKING);
    }

    public function getIconClass()
    {
        return 'icons8-Trekking';
    }

    public function getName()
    {
        return __('Hiking');
    }

    public function getCaloriesPerHour()
    {
        return 340;
    }

    public function getAverageHeartRate()
    {
        return 100;
    }

    public function hasDistances()
    {
        return true;
    }

    public function hasPower()
    {
        return false;
    }

    public function isOutside()
    {
        return true;
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

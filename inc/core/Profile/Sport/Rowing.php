<?php

namespace Runalyze\Profile\Sport;

use Runalyze\Metrics\Velocity\Unit\PaceEnum;

/**
 * @codeCoverageIgnore
 */
class Rowing extends AbstractSport
{
    public function __construct()
    {
        parent::__construct(SportProfile::ROWING);
    }

    public function getIconClass()
    {
        return 'icons8-Rowing';
    }

    public function getName()
    {
        return __('Rowing');
    }

    public function getCaloriesPerHour()
    {
        return 510;
    }

    public function getAverageHeartRate()
    {
        return 120;
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
        return false;
    }

    public function getPaceUnitEnum()
    {
        return PaceEnum::SECONDS_PER_500M;
    }

    public function usesShortDisplay()
    {
        return false;
    }
}

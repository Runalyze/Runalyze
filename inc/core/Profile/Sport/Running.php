<?php

namespace Runalyze\Profile\Sport;

use Runalyze\Metrics\Velocity\Unit\PaceEnum;

/**
 * @codeCoverageIgnore
 */
class Running extends AbstractSport
{
    public function __construct()
    {
        parent::__construct(SportProfile::RUNNING);
    }

    public function getIconClass()
    {
        return 'icons8-Running';
    }

    public function getName()
    {
        return __('Running');
    }

    public function getCaloriesPerHour()
    {
        return 880;
    }

    public function getAverageHeartRate()
    {
        return 140;
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
        return PaceEnum::SECONDS_PER_KILOMETER;
    }

    public function usesShortDisplay()
    {
        return false;
    }
}

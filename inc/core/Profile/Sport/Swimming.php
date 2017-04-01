<?php

namespace Runalyze\Profile\Sport;

use Runalyze\Parameter\Application\PaceUnit;

/**
 * @codeCoverageIgnore
 */
class Swimming extends AbstractSport
{
    public function __construct()
    {
        parent::__construct(SportProfile::SWIMMING);
    }

    public function icon()
    {
        return 'icons8-Swimming';
    }

    public function name()
    {
        return __('Swimming');
    }

    public function caloriesPerHour()
    {
        return 743;
    }

    public function avgHR()
    {
        return 130;
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

    public function paceUnitEnum()
    {
        return PaceUnit::MIN_PER_100M;
    }

    public function usesShortDisplay()
    {
        return false;
    }
}

<?php

namespace Runalyze\Profile\Sport;

use Runalyze\Parameter\Application\PaceUnit;

/**
 * @codeCoverageIgnore
 */
class Hiking extends AbstractSport
{
    public function __construct()
    {
        parent::__construct(SportProfile::HIKING);
    }

    public function icon()
    {
        return 'icons8-Trekking';
    }

    public function name()
    {
        return __('Hiking');
    }

    public function caloriesPerHour()
    {
        return 340;
    }

    public function avgHR()
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

    public function paceUnitEnum()
    {
        return PaceUnit::KM_PER_H;
    }

    public function usesShortDisplay()
    {
        return false;
    }
}

<?php

namespace Runalyze\Profile\Sport;

use Runalyze\Parameter\Application\PaceUnit;

/**
 * @codeCoverageIgnore
 */
class Cycling extends AbstractSport
{
    public function __construct()
    {
        parent::__construct(SportProfile::CYCLING);
    }

    public function icon()
    {
        return 'icons8-Regular-Biking';
    }

    public function name()
    {
        return __('Cycling');
    }

    public function caloriesPerHour()
    {
        return 770;
    }

    public function avgHR()
    {
        return 120;
    }

    public function hasDistances()
    {
        return true;
    }

    public function hasPower()
    {
        return true;
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

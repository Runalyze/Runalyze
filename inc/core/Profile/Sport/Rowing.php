<?php

namespace Runalyze\Profile\Sport;

use Runalyze\Parameter\Application\PaceUnit;

/**
 * @codeCoverageIgnore
 */
class Rowing extends AbstractSport
{
    public function __construct()
    {
        parent::__construct(SportProfile::ROWING);
    }

    public function icon()
    {
        return 'icons8-Rowing';
    }

    public function name()
    {
        return __('Rowing');
    }

    public function caloriesPerHour()
    {
        return 510;
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
        return false;
    }

    public function isOutside()
    {
        return false;
    }

    public function paceUnitEnum()
    {
        return PaceUnit::MIN_PER_500M;
    }

    public function usesShortDisplay()
    {
        return false;
    }
}

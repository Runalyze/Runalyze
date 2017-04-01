<?php

namespace Runalyze\Profile\Sport;

use Runalyze\Parameter\Application\PaceUnit;

/**
 * @codeCoverageIgnore
 */
class Running extends AbstractSport
{
    public function __construct()
    {
        parent::__construct(SportProfile::RUNNING);
    }

    public function icon()
    {
        return 'icons8-Running';
    }

    public function name()
    {
        return __('Running');
    }

    public function caloriesPerHour()
    {
        return 880;
    }

    public function avgHR()
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

    public function paceUnitEnum()
    {
        return PaceUnit::MIN_PER_KM;
    }

    public function usesShortDisplay()
    {
        return false;
    }
}

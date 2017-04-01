<?php

namespace Runalyze\Profile\Sport;

use Runalyze\Parameter\Application\PaceUnit;

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

    public function icon()
    {
        return 'icons8-Sports-Mode';
    }

    public function name()
    {
        return __('Generic');
    }

    public function caloriesPerHour()
    {
        return 500;
    }

    public function avgHR()
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

    public function paceUnitEnum()
    {
        return PaceUnit::KM_PER_H;
    }

    public function usesShortDisplay()
    {
        return false;
    }
}

<?php

namespace Runalyze\Profile\Sport;

use Runalyze\Parameter\Application\PaceUnit;

/**
 * @codeCoverageIgnore
 */
class Generic extends AbstractSport
{
    public function __construct()
    {
        parent::__construct(SportProfile::GENERIC);
    }

    /**
     * @return string
     */
    public function icon()
    {
        return 'icons8-Sports-Mode';
    }

    /**
     * @return string
     */
    public function name()
    {
        return __('Generic');
    }

    /**
     * @return int
     */
    public function caloriesPerHour()
    {
        return 500;
    }

    /**
     * @return int
     */
    public function avgHR()
    {
        return 120;
    }

    /**
     * @return bool
     */
    public function hasDistances()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function hasPower()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isOutside()
    {
        return false;
    }

    /**
     * @return string see \Runalyze\Parameter\Application\PaceUnit
     */
    public function paceUnitEnum()
    {
        return PaceUnit::KM_PER_H;
    }

    /**
     * @return bool
     */
    public function usesShortDisplay()
    {
        return false;
    }
}

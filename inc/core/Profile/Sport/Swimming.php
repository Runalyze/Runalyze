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

    /**
     * @return string
     */
    public function icon()
    {
        return 'icons8-Swimming';
    }

    /**
     * @return string
     */
    public function name()
    {
        return __('Swimming');
    }

    /**
     * @return int
     */
    public function caloriesPerHour()
    {
        return 743;
    }

    /**
     * @return int
     */
    public function avgHR()
    {
        return 130;
    }

    /**
     * @return bool
     */
    public function hasDistances()
    {
        return true;
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
        return PaceUnit::MIN_PER_100M;
    }

    /**
     * @return bool
     */
    public function usesShortDisplay()
    {
        return false;
    }
}

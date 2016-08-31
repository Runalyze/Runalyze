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

    /**
     * @return string
     */
    public function icon()
    {
        return 'icons8-Rowing';
    }

    /**
     * @return string
     */
    public function name()
    {
        return __('Rowing');
    }

    /**
     * @return int
     */
    public function caloriesPerHour()
    {
        return 510;
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
        return PaceUnit::MIN_PER_500M;
    }

    /**
     * @return bool
     */
    public function usesShortDisplay()
    {
        return false;
    }
}

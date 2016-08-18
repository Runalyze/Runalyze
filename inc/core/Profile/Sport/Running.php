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

    /**
     * @return string
     */
    public function icon()
    {
        return 'icons8-Running';
    }

    /**
     * @return string
     */
    public function name()
    {
        return __('Running');
    }

    /**
     * @return int
     */
    public function caloriesPerHour()
    {
        return 880;
    }

    /**
     * @return int
     */
    public function avgHR()
    {
        return 140;
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
        return true;
    }

    /**
     * @return string see \Runalyze\Parameter\Application\PaceUnit
     */
    public function paceUnitEnum()
    {
        return PaceUnit::MIN_PER_KM;
    }

    /**
     * @return bool
     */
    public function usesShortDisplay()
    {
        return false;
    }
}

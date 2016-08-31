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

    /**
     * @return string
     */
    public function icon()
    {
        return 'icons8-Trekking';
    }

    /**
     * @return string
     */
    public function name()
    {
        return __('Hiking');
    }

    /**
     * @return int
     */
    public function caloriesPerHour()
    {
        return 340;
    }

    /**
     * @return int
     */
    public function avgHR()
    {
        return 100;
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

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

    /**
     * @return string
     */
    public function icon()
    {
        return 'icons8-Regular-Biking';
    }

    /**
     * @return string
     */
    public function name()
    {
        return __('Cycling');
    }

    /**
     * @return int
     */
    public function caloriesPerHour()
    {
        return 770;
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
        return true;
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

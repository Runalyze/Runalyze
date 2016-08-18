<?php

namespace Runalyze\Profile\Sport;

interface ProfileInterface
{
    /**
     * @return string
     */
    public function icon();

    /**
     * @return string
     */
    public function name();

    /**
     * @return int
     */
    public function caloriesPerHour();

    /**
     * @return int
     */
    public function avgHR();

    /**
     * @return bool
     */
    public function hasDistances();

    /**
     * @return bool
     */
    public function hasPower();

    /**
     * @return bool
     */
    public function isOutside();

    /**
     * @return string see \Runalyze\Parameter\Application\PaceUnit
     */
    public function paceUnitEnum();

    /**
     * @return bool
     */
    public function usesShortDisplay();
}

<?php

namespace Runalyze\Profile\Sport;

interface ProfileInterface
{
    /**
     * @return int
     */
    public function getInternalProfileEnum();

    /**
     * @return string
     */
    public function getIconClass();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return int
     */
    public function getCaloriesPerHour();

    /**
     * @return int
     */
    public function getAverageHeartRate();

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
     * @return int
     * @see \Runalyze\Metrics\Velocity\Unit\PaceEnum
     */
    public function getPaceUnitEnum();

    /**
     * @return string see \Runalyze\Parameter\Application\PaceUnit
     */
    public function getLegacyPaceUnitEnum();

    /**
     * @return bool
     */
    public function usesShortDisplay();
}

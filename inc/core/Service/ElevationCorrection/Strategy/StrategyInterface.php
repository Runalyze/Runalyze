<?php

namespace Runalyze\Service\ElevationCorrection\Strategy;

interface StrategyInterface
{
    /**
     * @return bool
     */
    public function isPossible();

    /**
     * @param float[] $latitudes
     * @param float[] $longitudes
     *
     * @return int[]|null altitude [m]
     */
    public function loadAltitudeData(array $latitudes, array $longitudes);
}

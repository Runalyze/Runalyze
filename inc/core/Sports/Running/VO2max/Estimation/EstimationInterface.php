<?php

namespace Runalyze\Sports\Running\VO2max\Estimation;

interface EstimationInterface
{
    /**
     * Calculate VO2max based on race result
     *
     * @param float $distance [km]
     * @param int $seconds [s]
     *
     * @return float [ml/kg/min]
     */
    public function estimateFromRaceResult($distance, $seconds);

    /**
     * Formerly known as 'oxygen cost'
     *
     * @param float $meterPerMinute [m/min] 100% of vVO2max (i.e. max. velocity for 11 minutes)
     *
     * @return float [ml/kg/min]
     */
    public function estimateFromVelocity($meterPerMinute);
}

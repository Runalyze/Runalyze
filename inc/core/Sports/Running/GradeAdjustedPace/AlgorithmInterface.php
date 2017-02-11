<?php

namespace Runalyze\Sports\Running\GradeAdjustedPace;

interface AlgorithmInterface
{
    /**
     * @param float $gradientInPercent gradient in percent [-1.00 1.00]
     * @return float relative time factor to adjust the time spent running a given distance with that gradient
     */
    public function getTimeFactor($gradientInPercent);

    /**
     * @param int|float $paceInSecondsPerKilometer [s/km]
     * @param float $gradientInPercent gradient in percent [-1.00 1.00]
     * @return int|float adjusted pace [s/km]
     */
    public function adjustPace($paceInSecondsPerKilometer, $gradientInPercent);
}

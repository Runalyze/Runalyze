<?php
/**
 * This file contains class::Cumulative
 * @package Runalyze\Calculation\Math\MovingAverage
 */

namespace Runalyze\Calculation\Math\MovingAverage;

/**
 * Cumulative moving average
 *
 * @see https://en.wikipedia.org/wiki/Moving_average#Cumulative_moving_average
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Math\MovingAverage
 */
class Cumulative extends AbstractMovingAverage
{
    /**
     * Calculate if index data is there
     */
    public function calculateWithIndexData()
    {
        $avg = 0;
        $last = 0;

        for ($i = 0; $i < $this->Length; ++$i) {
            $delta = $this->IndexData[$i] - $last;
            $avg = ($last + $delta > 0) ? ($avg * $last + $this->Data[$i] * $delta) / ($last + $delta) : 0;
            $last += $delta;

            $this->MovingAverage[] = $avg;
        }
    }

    /**
     * Calculate if index data is not there
     */
    public function calculateWithoutIndexData()
    {
        $avg = 0;

        for ($i = 0; $i < $this->Length; ++$i) {
            $avg += ($this->Data[$i] - $avg) / ($i + 1);
            $this->MovingAverage[] = $avg;
        }
    }
}
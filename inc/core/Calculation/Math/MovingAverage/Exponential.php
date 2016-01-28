<?php
/**
 * This file contains class::Exponential
 * @package Runalyze\Calculation\Math\MovingAverage
 */

namespace Runalyze\Calculation\Math\MovingAverage;

/**
 * Exponential moving average
 *
 * @see https://en.wikipedia.org/wiki/Moving_average#Exponential_moving_average
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Math\MovingAverage
 */
class Exponential extends AbstractMovingAverage
{
    /**
     * @var float
     */
    protected $Alpha = 0.5;

    /**
     * @param float $alpha
     */
    public function setAlpha($alpha)
    {
        if (!is_numeric($alpha) || $alpha <= 0.0 || $alpha >= 1.0) {
            throw new \InvalidArgumentException('Alpha value must be a double between 0.0 and 1.0.');
        }

        $this->Alpha = $alpha;
    }

    /**
     * Calculate if index data is there
     */
    public function calculateWithIndexData()
    {
        $tau = - 1 / log(1 - $this->Alpha);
        $avg = $this->Data[0];
        $this->MovingAverage[] = $avg;

        for ($i = 1; $i < $this->Length; ++$i) {
            $deltaT = $this->IndexData[$i] - $this->IndexData[$i-1];
            $alpha = 1 - exp(-$deltaT / $tau);
            $avg = $alpha * $this->Data[$i] + (1 - $alpha) * $avg;
            $this->MovingAverage[] = $avg;
        }
    }

    /**
     * Calculate if index data is not there
     */
    public function calculateWithoutIndexData()
    {
        $avg = $this->Data[0];
        $this->MovingAverage[] = $avg;

        for ($i = 1; $i < $this->Length; ++$i) {
            $avg = $this->Alpha * $this->Data[$i] + (1 - $this->Alpha) * $avg;
            $this->MovingAverage[] = $avg;
        }
    }
}
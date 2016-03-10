<?php
/**
 * This file contains class::MovingAverageInterface
 * @package Runalyze\Calculation\Math\MovingAverage
 */

namespace Runalyze\Calculation\Math\MovingAverage;

/**
 * Interface for moving averages
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Math\MovingAverage
 */
interface MovingAverageInterface
{
    /**
     * @return array
     */
    public function movingAverage();

    /**
     * @param int $index
     * @return float|int
     */
    public function at($index);
}
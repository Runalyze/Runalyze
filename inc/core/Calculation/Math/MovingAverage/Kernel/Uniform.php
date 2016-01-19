<?php
/**
 * This file contains class::Uniform
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

/**
 * Uniform kernel
 *
 * @see https://en.wikipedia.org/wiki/Kernel_(statistics)#Kernel_functions_in_common_use
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */
class Uniform extends AbstractKernel
{
    /**
     * @param float $u
     * @return float
     */
    public function atTransformed($u)
    {
        if (-1.0 <= $u && $u <= 1.0) {
            return 1.0;
        }

        return 0.0;
    }
}
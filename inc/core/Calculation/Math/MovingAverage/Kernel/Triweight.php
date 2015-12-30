<?php
/**
 * This file contains class::Triweight
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

/**
 * Triweight kernel
 *
 * @see https://en.wikipedia.org/wiki/Kernel_(statistics)#Kernel_functions_in_common_use
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */
class Triweight extends AbstractKernel
{
    /**
     * @inheritdoc
     * @param float $width
     */
    public function __construct($width)
    {
        $this->NormalizationFactor = 70/32;

        parent::__construct($width);
    }

    /**
     * @param float $u
     * @return float
     */
    public function atTransformed($u)
    {
        if (-1.0 <= $u && $u <= 1.0) {
            return pow(1 - $u*$u, 3);
        }

        return 0.0;
    }
}
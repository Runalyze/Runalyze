<?php
/**
 * This file contains class::Quartic
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

/**
 * Quartic kernel
 *
 * @see https://en.wikipedia.org/wiki/Kernel_(statistics)#Kernel_functions_in_common_use
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */
class Quartic extends AbstractKernel
{
    /**
     * @inheritdoc
     * @param float $width
     */
    public function __construct($width)
    {
        $this->NormalizationFactor = 30/16;

        parent::__construct($width);
    }

    /**
     * @param float $u
     * @return float
     */
    public function atTransformed($u)
    {
        if (-1.0 <= $u && $u <= 1.0) {
            return pow(1 - $u*$u, 2);
        }

        return 0.0;
    }
}